<?php

namespace EventGator;

use EventGator\Handlers\ConfigFormatterTrait;

Class EventGatorConfig
{
    use ConfigFormatterTrait;

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;

        $this->validateConfig($this->config);
    }

    /**
     * Get Constructed Configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}