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
        Flight::json(array('status' => 'error', 'message' => 'Illegal token'), 403);
    }

    if (!ISSET($config['mappings'][$monitorId])) {
        Flight::json(array('status' => 'error', 'message' => 'Unknown monitor'), 400);
    }

    $components = $config['mappings'][$monitorId];

    // Update all statuspage components
    foreach ($components as $component => $status) {
        // Perform the request
        $requestOptions = array(
            'http' => array(
                'header' => array(
                    "Content-type: application/json",
                    "X-Cachet-Token: {$config['cachetApiToken']}"
                ),
                'method' => 'PUT',
                'content' => '{ "status": ' . ($uptimerobotStatus == 2 ? 1 : $status) . ' }'
            )
        );

        $url = $config['cachetUrl'] . "/api/v1/components/$component";

        $ctx = stream_context_create($requestOptions);
        $result = file_get_contents($url, false, $ctx);

        if (!$result) {
            Flight::json(array('status' => 'error', 'message' => 'Internal Server Error'), 500);
        }
    }

    Flight::json(array('status' => 'ok', 'message' => 'Statuspage updated'));
});
// endregion

// Start the application
Flight::start();