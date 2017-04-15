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
        $events = array_merge($fbEvents, $ebEvents);

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
}