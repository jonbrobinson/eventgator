<?php

namespace EventGator\Handlers;

trait EventFormatterTrait
{
    /**
     * @param array  $events
     * @param string $preferredPlatform
     *
     * @return array
     */
    protected function processPlatformEvents($events, $preferredPlatform)
    {
        $sorted = $this->buildSortEventsByTime($events);
        $events = $this->sanitizeSortedEvents($sorted, $preferredPlatform);

        return $events;
    }

    /**
     * @param array $events
     *
     * @return array
     */
    protected function buildSortEventsByTime($events)
    {
        $sorted = array();

        foreach ($events as $event) {
            $time_start = $event['time_start'];
            $sorted[$time_start] = array($event['platform'] => $event);
        }

        krsort($sorted);

        return $sorted;
    }

    /**
     * @param array  $events
     * @param string $preferredPlatform
     *
     * @return array
     */
    protected function sanitizeSortedEvents($events, $preferredPlatform)
    {
        $validEvents = array();

        foreach ($events as $time => $platforms) {
            if (count($platforms) > 1) {
                $validEvents[] = $events[$time][$preferredPlatform];
                continue;
            }

            if (array_key_exists('facebook', $events[$time])) {
                $validEvents[] = $events[$time]['facebook'];
                continue;
            }

            if (array_key_exists('eventbrite', $events[$time])) {
                $validEvents[] = $events[$time]['eventbrite'];
                continue;
            }
        }

        return $validEvents;
    }
}