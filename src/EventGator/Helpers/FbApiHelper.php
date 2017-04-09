<?php


namespace EventGator\Helpers;


use EventGator\EventFormatterAbstractCLass;

class FbApiHelper extends EventFormatterAbstractCLass
{
    const AAULYP_FB_PAGE_ID = 135986973127166;
    const FB_BASE_URL = 'https://graph.facebook.com/v2.6/';

    protected $facebookHelper;
    protected $googleMaps;
    protected $currentEvent;
    protected $albumBlacklist = array('352412258151302', '139721309420399', '574486469277212', '136089056450291');

    public function __construct(GoogleMapsApi $googleMapsApi)
    {
        $this->facebookHelper = new Facebook([
            'app_id' => env('FB_APP_ID'),
            'app_secret' => env('FB_APP_SECRET'),
            'default_graph_version' => 'v2.6',
            'default_access_token' => env('FB_ACCESS_TOKEN'),
        ]);

        $this->googleMaps = $googleMapsApi;

        $this->guzzle = new Guzzle([
            // Base URI is used with relative requests
            'base_uri' => self::FB_BASE_URL,
        ]);
    }

    /**
     * Gets contents of a single folder
     *
     * @return array
     */
    public function getEvents()
    {
        $events = $this->getEventsArray();

        $transformedEvents = array();
        foreach($events as $event) {
            $transformedEvents[] = $this->processSingleEvent($event);
        }

        return $transformedEvents;
    }
}