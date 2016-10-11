<?php

namespace PagerDuty;

use PagerDuty\Event\AcknowledgeEvent;
use PagerDuty\Event\Event;
use PagerDuty\Event\TriggerEvent;
use PagerDuty\Event\ResolveEvent;

/**
 * PagerDuty Events API v2
 * @link https://v2.developer.pagerduty.com/docs/events-api
 * 
 * See examples in README.md
 * 
 */
class PagerDuty
{

    /**
     * Quickfire a 'trigger' event
     * For full control of triggers, with many optional features, 
     * create a TriggerEvent class and use $this->send($triggerEvent)
     * 
     * @param string $serviceKey
     * @param string $description
     * @param string $incidentKey (Opt) - Read more @link https://v2.developer.pagerduty.com/v2/docs/events-api#incident-de-duplication-and-incident_key
     * 
     * @return int - HTTP status code. See send()
     */
    public function trigger($serviceKey, $description, $incidentKey = null)
    {
        $ev = new TriggerEvent($serviceKey, $description);

        if (!empty($incidentKey)) {
            $ev->setIncidentKey($incidentKey);
        }

        return $this->send($ev);
    }

    /**
     * Same as $this->trigger() except auto-generates an `incident_key` based on $description
     * the incident_key is the md5 hash of $description. This prevents
     * PagerDuty from flooding admins with incidents that are essentially the same.
     * 
     * @param string $serviceKey
     * @param string $description
     * 
     * @return int - HTTP status code. See send()
     */
    public function triggerSingleIncident($serviceKey, $description)
    {
        $ev = new TriggerEvent($serviceKey, $description);
        $ev->setIncidentKey("md5-" . md5($description));

        $this->send($ev);
    }

    /**
     * Acknowledge an event
     * 
     * @param string $serviceKey
     * @param string $incidentKey
     * 
     * @return int - HTTP status code. See send()
     */
    public function acknowledge($serviceKey, $incidentKey)
    {
        return $this->send(new AcknowledgeEvent($serviceKey, $incidentKey));
    }

    /**
     * Resolve an event
     * 
     * @param string $serviceKey
     * @param string $incidentKey
     * 
     * @return int - HTTP status code. See send()
     */
    public function resolve($serviceKey, $incidentKey)
    {
        return $this->send(new ResolveEvent($serviceKey, $incidentKey));
    }

    /**
     * Trigger an event 
     * 
     * @param Event $event - A concrete Event object
     * @param array $result (Opt)(Pass by reference) - If this parameter is given the result of the CURL call will be filled here. The response is an associative array
     * 
     * @return int - HTTP response
     *  200 - Event Processed
     *  400 - Invalid Event. Check $result
     *  403 - Rate Limited. Slow down and try again later.
     */
    public function send(Event $event, &$result = null)
    {
        $jsonStr = json_encode($event);

        $curl = curl_init("https://events.pagerduty.com/generic/2010-04-15/create_event.json");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonStr)
        ));

        $result = json_decode(curl_exec($curl), true);

        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $responseCode;
    }
}
