<?php

namespace EventGator\Helpers;

use EventGator\Handlers\EventFormatterAbstractClass;
use GuzzleHttp\Client as Guzzle;

class EbApiHelper extends EventFormatterAbstractClass
{
    const EB_BASE_URL = 'https://www.eventbriteapi.com';
    const DEFAULT_VERSION = 'v3';

    protected $eb;
    protected $orgId;
    protected $guzzleHttp;


    public function __construct($config)
    {
        $this->eb= array(
            "app_key" => $config["app_key"],
            "app_version" => $config["app_version"]
        );

        $this->orgId = $config["organizer_id"];

        $this->guzzleHttp = $this->getGuzzleHttp();
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        $events = $this->getPlatformEvents();

        $transformedEvents = array();
        foreach($events as $event) {
            $transformedEvents[] = $this->processSingleEvent($event);
        }

        return $transformedEvents;
    }

    /**
     * @param $id
     */
    public function setOrdId($id)
    {
        $this->orgId = $id;
    }

    protected function getPlatformEvents()
    {
        $endpoint = "/organizers/".$this->orgId."/events/";

        $events = $this->sendFbGraphRequest('GET', $endpoint, $this->getBaseOptions());

        $decoded = json_decode($events, true);

        $events = $decoded['events'];

        return $events;
    }

    protected function processSingleEvent($event)
    {
        $eventDetails = $this->addRelationalDetails($event);
        $relations = $this->getPlatformRelations();
        $processedEvent = $this->convertEventToStandard('eventbrite', $relations, $eventDetails);

        return $processedEvent;
    }

    /**
     * Add relational details to eventbrite data
     *
     * @param array $event
     *
     * @return array
     */
    protected function addRelationalDetails($event)
    {
        if (array_key_exists('start', $event) && array_key_exists('local', $event['start'])) {
            $event['start_epoch'] = strtotime($event['start']['local']);
        }

        if (array_key_exists('end', $event) && array_key_exists('local', $event['end'])) {
            $event['end_epoch'] = strtotime($event['end']['local']);
        }

        if (isset($event['venue_id'])) {
            $event['venue_details'] = json_decode($this->getCategoryById("venues", $event['venue_id']), true);
        }

        if (isset($event['logo_id'])) {
            $event['image_details'] = json_decode($this->getCategoryById("media", $event['logo_id']), true);
        }

        $venue = $this->getSimpleVenueDetails();

        if (array_key_exists('venue_details', $event)) {
            $venue = $this->transformVenueAddress($event['venue_details']);
        }

        $event['venue_address'] = $venue;


        if (array_key_exists('image_details', $event)) {
            $event['cover_image'] = $event['image_details']['original']['url'];
        }

        return $event;
    }

    /**
     * Validates Venue information and returns a simple address and name for venue
     *
     * @param $venueDetails
     *
     * @return array
     */
    protected function transformVenueAddress($venueDetails)
    {
        $ebKeys = array('address_1', 'address_2', 'city', 'postal_code', 'region');
        $details = $this->getSimpleVenueDetails();
        $missingEbKeys = array();

        foreach ($ebKeys as $ebKey) {
            if (!array_key_exists($ebKey, $venueDetails['address']) ) {
                $missingEbKeys[] = $ebKey;
                continue;
            }

            if (!isset($venueDetails['address'][$ebKey])) {
                $missingEbKeys[] = $ebKey;
                continue;
            }
        }

        if (in_array('address_2', $missingEbKeys) && (count($missingEbKeys) > 1)) {
//            if (!empty(floatval($venueDetails['latitude'])) && !empty(floatval($venueDetails['longitude']))) {
//                $address = $this->googleMapsApi->getAddressFromLatLong($venueDetails['latitude'], $venueDetails['longitude']);
//                $details['address'] = $address['street_number']." ".$address['street'];
//                $details['city'] = $address['city'];
//                $details['state'] = $address['state'];
//                $details['zip'] = $address['zip'];
//                $details['display'] = $address['formatted_address'];
//
//                return $details;
//            }
        }

        if (count($missingEbKeys) > 2) {
            return $details;
        }

        $details['address'] = $venueDetails['address']['address_1'];
        $details['city'] = $venueDetails['address']['city'];
        $details['postal_code'] = $venueDetails['address']['postal_code'];
        $details['state'] = $venueDetails['address']['region'];

        if (isset($venueDetails['address']['address_2'])) {
            $details['address'] .= " ".$venueDetails['address']['address_2'];
        }


        $details['display'] = $details['address'].", ".$details['city'].", ".$details['state'].$details['display'];

        return $details;
    }

    /**
     * @param string $mediaId
     *
     * @return object
     */
    public function getCategoryById($category, $mediaId)
    {
        $endpoint = "/{$category}/{$mediaId}";

        $content = $this->sendFbGraphRequest('GET', $endpoint, $this->getBaseOptions());

        return $content;
    }

    /**
     * @return array
     */
    protected function getPlatformRelations()
    {
        $relations = [
            'id' => 'id',
            'title' => 'name',
            'description' => 'description',
            'time_start' => 'start_epoch',
            'time_end' => 'end_epoch',
            'venue' => 'venue_address',
            'cover_image' => 'cover_image',
            'ticket_url' => 'url',
            'platform_url' => 'url'
        ];

        return $relations;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array  $options
     *
     * @return array
     */
    protected function sendFbGraphRequest($method, $endpoint, $options = array())
    {
        $url = $this->getBaseUrl().$endpoint;

        $response = $this->guzzleHttp->request($method, $url, $options);

        $content = $response->getBody()->getContents();

        return $content;
    }

    /**
     * @return array
     */
    protected function getBaseOptions()
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->eb["app_key"],
            'Content-Type' => 'application/json',
        ];

        $options = [
            'headers' => $headers
        ];

        return $options;
    }

    /**
     * @return string
     */
    protected function getBaseUrl()
    {
        $version = self::DEFAULT_VERSION;

        if ($this->eb['app_version']) {
            $version = $this->eb['app_version'];
        }

        $baseUrl = self::EB_BASE_URL."/".$version;

        return $baseUrl;
    }

    /**
     * @return Guzzle
     */
    protected function getGuzzleHttp()
    {
        $guzzle = new Guzzle();

        return $guzzle;
    }
}