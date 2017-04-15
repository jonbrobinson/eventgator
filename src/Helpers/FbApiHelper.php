<?php


namespace EventGator\Helpers;

use EventGator\Handlers\EventFormatterAbstractClass;
use GuzzleHttp\Client as Guzzle;

class FbApiHelper extends EventFormatterAbstractClass
{
    const FB_BASE_URL = 'https://graph.facebook.com';
    const MIN_GRAPH_VERSION = 'v2.6';

    protected $fb;
    protected $guzzleHttp;
    protected $nodeEntity;

    public function __construct($config)
    {
        $this->fb= array(
            "app_id" => $config['facebook']['app_id'],
            "app_secret" => $config['facebook']['app_secret'],
            "default_graph_version" => $config['facebook']['default_graph_version']
        );

        $this->guzzleHttp = $this->getGuzzleHttp();
    }

    /**
     * Gets contents of a single folder
     *
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
    public function setNodeEntityId($id)
    {
        $this->nodeEntity = $id;
    }

    /**
     * @param array $event
     *
     * @return array
     */
    protected function processSingleEvent($event)
    {
        $eventDetails = $this->addRelationalDetails($event);
        $relations = $this->getPlatformRelations();
        $processedEvent = $this->convertEventToStandard('facebook', $relations, $eventDetails);

        return $processedEvent;
    }

    /**
     * @param string $event
     *
     * @return mixed
     */
    protected function addRelationalDetails($event)
    {
        $venue = $this->getSimpleVenueDetails();

        if (!empty($event['place']) && !empty($event['place']['location'])) {
            $venue = $this->transformVenueDetails($event['place']);
        }

        $event['venue_address'] = $venue;

        if ($event['cover']) {
            $event['cover_image'] = $event['cover']['source'];
        }

        $event['description'] = array(
            'text' => $event['description'],
            'html' => nl2br($event['description'])
        );

        $event['name'] = array(
            'text' => $event['name'],
            'html' => nl2br($event['name'])
        );

        if(!empty($event['start_time'])) {

            $event['start_epoch'] = strtotime($event['start_time']);
        }

        if (!empty($event['end_time'])) {
            $event['end_epoch'] = strtotime($event['end_time']);
        }

        $event['platform_url'] = "https://www.facebook.com/events/".$event['id'];


        return $event;
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
            'ticket_url' => 'ticket_uri',
            'platform_url' => 'platform_url',
        ];

        return $relations;
    }

    protected function transformVenueDetails($venueDetails)
    {
        $fbKeys = array('street', 'city', 'zip', 'state');
        $details = $this->getSimpleVenueDetails();
        $missingFbKeys = array();

        if ((isset($venueDetails['location']))) {
            foreach ($fbKeys as $fbKey) {
                if (!array_key_exists($fbKey, $venueDetails['location'])) {
                    $missingFbKeys[] = $fbKey;
                    continue;
                }

                if (empty($venueDetails['location'][$fbKey])) {
                    $missingFbKeys[] = $fbKey;
                    continue;
                }
            }
        }

        $missingCount = count($missingFbKeys);

        if (isset($venueDetails['name'])) {
            $details['name'] = $venueDetails['name'];
        }

        if ($missingCount > 0) {
            //Todo: Need to Handle Google Maps
//            $latitude = $venueDetails['location']['latitude'];
//            $longitude = $venueDetails['location']['longitude'];
//            $mapsAddress = $this->googleMaps->getAddressFromLatLong($latitude, $longitude);
//            $details['display'] = $mapsAddress['formatted_address'];

            return $details;
        }

        $details['address'] = $venueDetails['location']['street'];
        $details['city'] = $venueDetails['location']['city'];
        $details['postal_code'] = $venueDetails['location']['zip'];
        $details['state'] = $venueDetails['location']['state'];

        $details['display'] = $details['address'].", ".$details['city'].", ".$details['state']." ".$details['postal_code'];

        return $details;
    }

    protected function getPlatformEvents()
    {
        $endpoint = "/".$this->nodeEntity;
        $options = array(
            "query" => array(
                "fields" => "events{id,name,category,description,place,cover,attending_count,interested_count,start_time,end_time,ticket_uri}",
                "access_token" => $this->getAccessToken()
            )
        );

        $contents = $this->sendFbGraphRequest("GET", $endpoint, $options);

        $decoded = json_decode($contents, true);
        $events = $decoded["events"]["data"];

        return $events;
    }

    protected function getAccessToken()
    {
        $endpoint = "/oauth/access_token";
        $options = array(
            "query" => array(
                "client_id" => $this->fb['app_id'],
                "client_secret" => $this->fb['app_secret'],
                "grant_type" => "client_credentials"
            )
        );

        $content = $this->sendFbGraphRequest("GET", $endpoint, $options);
        $decoded = json_decode($content, true);
        $token = $decoded["access_token"];

        return $token;
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
     * @return string
     */
    protected function getBaseUrl()
    {
        $version = self::MIN_GRAPH_VERSION;

        if ($this->fb['default_graph_version']) {
            $version = $this->fb['default_graph_version'];
        }

        $baseUrl = self::FB_BASE_URL."/".$version;

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