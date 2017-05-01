<?php

namespace EventGator;

use EventGator\Handlers\ConfigFormatterTrait;
use EventGator\Helpers\EbApiHelper;
use EventGator\Helpers\FbApiHelper;

Class EventGatorClient
{
    use ConfigFormatterTrait;

    protected $fbApiHelper;
    protected $config;

    public function __construct($config)
    {
        $this->config = $this->validateConfig($config);
        $this->fbApiHelper = new FbApiHelper($this->config['facebook']);
        $this->ebApiHelper = new EbApiHelper($this->config['eventbrite']);
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