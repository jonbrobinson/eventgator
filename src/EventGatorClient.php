<?php

namespace EventGator;

use EventGator\Helpers\EbApiHelper;
use EventGator\Helpers\FbApiHelper;

Class EventGatorClient
{
    protected $fbApiHelper;

    public function __construct($config)
    {
        $this->fbApiHelper = new FbApiHelper($config);
        $this->ebApiHelper = new EbApiHelper($config);
    }

    public function getEvents()
    {
        $fbEvents = $this->fbApiHelper->getEvents();
        $ebEvents = $this->ebApiHelper->getEvents();
        $allEvents = array_merge($fbEvents, $ebEvents);

        $sorted = $this->buildSortEventsByTime($allEvents);
        $events = $this->sanitizeSortedEvents($sorted);

        return $events;
    }

    public function setFbNode($id)
    {
        $this->fbApiHelper->setNodeEntityId($id);
    }

    public function setEbNode($id)
    {
        $this->ebApiHelper->setOrdId($id);
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
     *
     * @return array
     */
    protected function sanitizeSortedEvents($events)
    {
        $validEvents = array();

        foreach ($events as $time => $platforms) {
            if (count($platforms) > 1) {
                $validEvents[] = $events[$time]['eventbrite'];
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