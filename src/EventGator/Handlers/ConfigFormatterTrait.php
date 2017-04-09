<?php

namespace EventGator\Handlers;

trait ConfigFormatterTrait
{
    protected $validPlatforms = array('facebook', 'eventbrite');

    /**
     * @param array $config
     *
     * @throws \Exception
     */
    public function validateConfig($config)
    {
        foreach(array_keys($config) as $platform){
            if (!in_array($platform, $this->validPlatforms)) {
                throw new \Exception("Error: Please enter a valid Platform. Platform Entered: ".$platform);
            }
        }
    }
}