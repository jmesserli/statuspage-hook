<?php
/**
 $ The MIT License (MIT)
 $
 $ Copyright (c) 2016 Joel Messerli
 $
 $ Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 $ documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 $ the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 $ to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 $
 $ The above copyright notice and this permission notice shall be included in all copies or substantial portions of
 $ the Software.
 $
 $ THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 $ WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
 $ OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 $ OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

require 'vendor/autoload.php';

require_once 'config.php';

// region Routes
Flight::route('/api/monitor/@monitorId:[0-9]+/status/@uptimerobotStatus:[12]', function ($monitorId, $uptimerobotStatus) use ($config) {
    $token = Flight::request()->query['token'];

    if (!$token || $token !== $config['urlSecret']) {
        Flight::json(['status' => 'error', 'message' => 'Illegal token'], 403);
    }

    if (!isset($config['mappings'][$monitorId])) {
        Flight::json(['status' => 'error', 'message' => 'Unknown monitor'], 400);
    }

    $components = $config['mappings'][$monitorId];

    // Update all statuspage components
    foreach ($components as $component => $status) {
        $newStatus = $uptimerobotStatus == 2 ? 1 : $status;
        $response = Requests::put(
            "{$config['cachetUrl']}/api/v1/components/$component", // URL
            [ // Headers
                'X-Cachet-Token' => $config['cachetApiToken'],
                'Content-Type' => 'application/json'
            ], json_encode(['status' => $newStatus]) // Body
        );

        if (!$response->success) {
            Flight::json(['status' => 'error', 'message' => "Internal Server Error: {$response->status_code}"], 500);
        } elseif (json_decode($response->body)->data->status !== $newStatus) {
            Flight::json(['status' => 'error', 'message' => 'Status change not reflected in the cachet api']);
        }
    }

    Flight::json(array('status' => 'ok', 'message' => 'Statuspage updated'));
});
// endregion

// Start the application
Flight::start();
