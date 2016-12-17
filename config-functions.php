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

const STATUS_OPERATIONAL = 1;
const STATUS_PERF_DEGRADED = 2;
const STATUS_PART_OUTAGE = 3;
const STATUS_MAJR_OUTAGE = 4;

class Map
{
    public $monitorId;
    public $componentStatusMappings = array();
    private $lastComponent = 0;

    public function __construct($monitorId)
    {
        $this->monitorId = $monitorId;
    }

    /**
     * @param $componentId int The id of the component to map the monitor to
     * @return Map $this
     */
    public function toComponent($componentId)
    {
        if (!isset($this->componentStatusMappings[$componentId])) {
            $this->componentStatusMappings[$componentId] = 4;
        }

        $this->lastComponent = $componentId;

        return $this;
    }

    /**
     * @param $statusId int The id of the status to set when the monitor goes down (1-4)
     * @return Map $this
     * @throws Error
     */
    public function andStatus($statusId)
    {
        if ($this->lastComponent === 0)
            trigger_error("You must first call .toComponent(...) before adding a status", E_USER_ERROR);

        if ($statusId > 0 && $statusId < 5)
            $this->componentStatusMappings[$this->lastComponent] = $statusId;

        return $this;
    }
}

/**
 * @param Map[] ...$maps
 * @return array
 */
function mappings()
{
    $resultArray = array();

    // PHP 5.5
    $maps = func_get_args();

    foreach ($maps as $map){
        $resultArray[$map->monitorId] = array();

        foreach($map->componentStatusMappings as $monitorId => $statusId)
            $resultArray[$map->monitorId][$monitorId] = $statusId;
    }

    return $resultArray;
}

/**
 * @param $monitorId int The id of the Uptimerobot Monitor
 * @return Map
 */
function map($monitorId)
{
    return new Map($monitorId);
}