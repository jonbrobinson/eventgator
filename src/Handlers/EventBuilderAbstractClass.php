<?php

namespace EventGator\Handlers;

abstract class EventBuilderAbstractClass
{
    protected $validPlatform = ["facebook","eventbrite"];

    abstract public function getEvents();
    abstract protected function getPlatformEvents();
    abstract protected function getPlatformRelations();
    abstract protected function processSingleEvent($event);

    /**
     * @return array
     */
    public function getSimpleVenueDetails()
    {
        $details = array(
            "address" => "",
            "city" => "",
            "postal_code" => "",
            "state" => "",
            'longitude' => "",
            'latitude' => "",
            'display' => "",
            'name' => "",
        );

        return $details;
    }


    /**
     * Convert Details of an Event
     *
     * @param array $keys
     * @param array $event
     *
     * @return array
     */
    protected function convertDetails($keys, $event)
    {
        $newEvent = array();

        foreach ($keys as $index => $key) {
            if (array_key_exists($key, $event)) {
                $newEvent[$index] = $event[$key];
            } else {
                $newEvent[$index] = "";
            }
        }

        return $newEvent;
    }

    /**
     * @param string $platform
     * @param array  $relations
     * @param array  $event
     *
     * @return array
     */
    protected function convertEventToStandard($platform, $relations, $event)
    {
        $finalEvent = $this->convertDetails($relations, $event);

        $finalEvent['platform'] = $platform;

        return $finalEvent;
    }
}