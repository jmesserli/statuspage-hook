<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2016-2017 Joel Messerli
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit
 * persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of
 * the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
require 'vendor/autoload.php';
require_once 'config.php';

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;


//region Logger setup
$logger = new Logger('main');

if (isset($config['mail'])) {
    $mailConfig = $config['mail'];
    $transport = Swift_SmtpTransport::newInstance($mailConfig['server'], $mailConfig['port'], $mailConfig['security'])
        ->setUsername($mailConfig['username'])
        ->setPassword($mailConfig['password']);

    $mailer = Swift_Mailer::newInstance($transport);

    $message = Swift_Message::newInstance('Log Message from Status-Hook')
        ->setFrom($mailConfig['sender'])
        ->setTo($mailConfig['recipient']);

    $logger->pushHandler(new SwiftMailerHandler($mailer, $message, Logger::WARNING));
}

$logger->pushHandler(new StreamHandler(__DIR__ . '/statushook.log', Logger::DEBUG));
//endregion

// region Routes
Flight::route('/api/monitor/@monitorId:[0-9]+/status/@uptimerobotStatus:[12]', function ($monitorId, $uptimerobotStatus) use ($config, $logger) {
    $token = Flight::request()->query['token'];

    if (!$token || $token !== $config['urlSecret']) {
        $logger->info("Tried to set \"$monitorId\" to status \"$uptimerobotStatus\" with illegal token \"$token\"");
        Flight::json(['status' => 'error', 'message' => 'Illegal token'], 403);
    }

    if (!isset($config['mappings'][$monitorId])) {
        $logger->warning("Tried to update unknown monitor \"$monitorId\" (no mapping)");
        Flight::json(['status' => 'error', 'message' => 'Unknown monitor'], 400);
    }

    $components = $config['mappings'][$monitorId];

    // Update all statuspage components
    foreach ($components as $component => $status) {
        $newStatus = $uptimerobotStatus == 2 ? 1 : $status;
        $logger->info("Setting monitor \"$monitorId\" (cachet id \"$component\") to status \"$newStatus\"");
        $response = Requests::put(
            "{$config['cachetUrl']}/api/v1/components/$component", // URL
            [ // Headers
                'X-Cachet-Token' => $config['cachetApiToken'],
                'Content-Type' => 'application/json'
            ], json_encode(['status' => $newStatus]) // Body
        );

        if (!$response->success) {
            $logger->error("Could not set monitor \"$monitorId\" (cachet id \"$component\") to status \"$newStatus\": $response->body");
            Flight::json(['status' => 'error', 'message' => "Internal Server Error: {$response->status_code}"], 500);
        } elseif (json_decode($response->body)->data->status !== $newStatus) {
            $logger->error("Could not set monitor \"$monitorId\" (cachet id \"$component\") to status \"$newStatus\": Cachet still returns old status");
            Flight::json(['status' => 'error', 'message' => 'Status change not reflected in the cachet api']);
        }
    }

    $logger->debug("Updating successful");
    Flight::json(array('status' => 'ok', 'message' => 'Statuspage updated'));
});
// endregion

// Start the application
Flight::start();
