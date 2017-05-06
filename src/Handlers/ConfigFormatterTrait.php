<?php

namespace EventGator\Handlers;

trait ConfigFormatterTrait
{
    protected $validPlatforms = ['facebook', 'eventbrite'];
    protected $validFbKeys = ['app_id', 'app_secret', 'default_graph_version', 'graph_node_id'];
    protected $validEbKeys = ['app_key', 'app_version', 'organizer_id'];

    /**
     * @param array $config
     *
     * @return array
     * @throws \Exception
     */
    protected function validateConfig($config)
    {
        foreach($config as $platform => $details){
            if (!in_array($platform, $this->validPlatforms)) {
                throw new \Exception("Error: Please enter a valid Platform. Platform Entered: ".$platform);
            }

            $this->validatePlatformKeys($platform, $config[$platform]);
        }

        return $config;
    }

    /**
     * @param array $config
     *
     * @return mixed
     */
    protected function getPreferredPlatform($config)
    {
        foreach (array_keys($config) as $platform) {
            return $platform;
        }

        return false;
    }

    /**
     * @param string $platform
     * @param array  $credentials
     *
     * @throws \Exception
     */
    protected function validatePlatformKeys($platform, $credentials)
    {
        foreach(array_keys($credentials) as $key) {
            if (!in_array($key, $this->getValidKeysByPlatform($platform))) {
                throw new \Exception("Error: Please enter a valid Key. Key Entered: ".$key);
            }
        }
    }

    /**
     * @param $platform
     *
     * @return array
     * @throws \Exception
     */
    protected function getValidKeysByPlatform($platform)
    {
        switch ($platform) {
            case 'facebook':
                return $this->validFbKeys;

            case 'eventbrite':
                return $this->validEbKeys;
        }

        throw new \Exception("Missing Valid Platform. Platform Entered: ".$platform);
    }
}