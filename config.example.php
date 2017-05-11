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

require_once "config-functions.php";

$config = array(

    // Those mappings from Uptimerobot monitor ids to Cachet component ids
    "mappings" => mappings(

        // Fluent API style, mapping the Uptimerobot monitor id 12345 to the Cachet component 1 with the status STATUS_MAJR_OUTAGE
        map(12345)->toComponent(1)->andStatus(STATUS_MAJR_OUTAGE),

        // The default status is STATUS_MAJR_OUTAGE so you can omit it
        map(54321)->toComponent(2),

        // You can map a monitor to multiple components with multiple statuses if you like
        map(98742)->toComponent(3)->andStatus(STATUS_PERF_DEGRADED)
                  ->toComponent(5) // Default is again STATUS_MAJR_OUTAGE
    ),

    // No trailing slash!
    "cachetUrl" => "https://status.pegnu.cloud",

    // The api token from your Cachet dashboard
    "cachetApiToken" => "",

    // Must be passed as ?token={urlSecret} to trigger
    "urlSecret" => ""
);