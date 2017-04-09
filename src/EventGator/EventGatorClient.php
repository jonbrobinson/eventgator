<?php

namespace EventGator;

use EventGator\Helpers\FbApiHelper;

Class EventGatorClient
{
    protected $fbApiHelper;

    public function __construct($config)
    {
        $this->fbApiHelper = new FbApiHelper($config);
    }

    public function getEvents()
    {
        $events = $this->fbApiHelper->getEvents();

        return $events;
    }
}