<?php

namespace EventGator;

use EventGator\Handlers\ConfigFormatterTrait;

Class EventGatorConfig
{
    use ConfigFormatterTrait;

    protected $config;

    public function __construct($config)
    {
        $this->config = array(
            "facebook" => array(
                "app_id" => "{$config['facebook']['app_id']}",
                "app_secret" => "{$config['facebook']['app_secret']}",
                "default_graph_version" => "{$config['facebook']['default_graph_version']}",
            )
        );

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