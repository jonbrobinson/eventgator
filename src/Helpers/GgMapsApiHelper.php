<?php

namespace App\Aaulyp\Tools\Api;

use GuzzleHttp\Client as Guzzle;

class GgMapsApiHelper
{
    const GOOGLE_MAPS_BASE_URL = "https://maps.googleapis.com/maps/api";

    protected $guzzle;
    protected $config;

    public function __construct($config)
    {
        $this->config  = array(
            "api_key" => $config["google"]['api_key']
        );

        $this->guzzle = new Guzzle([
            'base_uri' => self::GOOGLE_MAPS_BASE_URL,
        ]);
    }

    /**
     * Gets contents of a single folder
     *
     * @return array
     */
    public function getAddressFromLatLong($latitude, $longitude)
    {
        $url = self::GOOGLE_MAPS_BASE_URL . '/geocode/json';
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $query = [
            'latlng' => $latitude . ',' . $longitude,
            'result_type' => 'street_address',
            'key' => $this->config['api_key'],
        ];

        $options = [
            'headers' => $headers,
            'query' => $query
        ];

        $response = $this->guzzle->request('GET', $url, $options);

        $addressJson = json_decode($response->getBody()->getContents());

        if ($addressJson->results) {
            $address = $this->sanitizeGoogleMapsLocation($addressJson->results[0]);
        } else {
            $address = array();
        }

        return $address;
    }

    protected function sanitizeGoogleMapsLocation($location)
    {
        $details = array();
        $locationArray = json_decode(json_encode($location), true);
        $components = $locationArray['address_components'];

        foreach ($components as $component) {
            if ('street_number' == $component['types'][0]) {
                $details[$component['types'][0]] = $component['long_name'];
            }

            if ('route' == $component['types'][0]) {
                $details['street'] = $component['short_name'];
            }

            if ('locality' == $component['types'][0]) {
                $details['city'] = $component['long_name'];
            }

            if ('locality' == $component['types'][0]) {
                $details['city'] = $component['long_name'];
            }

            if ('administrative_area_level_1' == $component['types'][0]) {
                $details['state'] = $component['short_name'];
            }

            if ('postal_code' == $component['types'][0]) {
                $details['zip'] = $component['long_name'];
            }
        }

        $details['formatted_address'] = $locationArray['formatted_address'];

        return $details;
    }
}