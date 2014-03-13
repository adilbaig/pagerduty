<?php

namespace PagerDuty;

/**
 * PagerDuty Integration API
 * Class to communicate with the PagerDuty Integration API REST service.
 * 
 * http://developer.pagerduty.com/documentation/integration/events
 * 
 * Usage:
 * 
 * $pagerDuty = new PagerDuty("my GUID");
 * $request = $pagerDuty->makeRequest(PagerDuty::TYPE_TRIGGER, "Service is down");
 * echo "Request : ", json_encode($request);
 * 
 * $result = array();
 * $responseCode = $pagerDuty->send($request, $result);
 * 
 * if ($responseCode > 204) { 
  throw new \Exception("$result['status'] : $result['message']");
  }
 */
class PagerDuty
{

    const TYPE_TRIGGER = 'trigger';
    const TYPE_ACKNOWLEDGE = 'acknowledge';
    const TYPE_RESOLVE = 'resolve';

    /**
     * The service key
     * 
     * @var string 
     */
    protected $serviceKey;

    /**
     * Ctor
     * 
     * @param string $serviceKey - The GUID of one of your "Generic API" services.
     */
    public function __construct($serviceKey)
    {
        $this->serviceKey = $serviceKey;
    }

    /**
     * Make a JSON request.
     * It returns an assoc. array that will be used as the JSON body of the CURL request.
     * 
     * @param string $type - One of self::TYPE_* constants
     * @param string $desc - The 'description' for this event
     * @param string $incidentKey (Opt) - The incident key. If it doesn't exist a new incident will be created
     * @param array  $details (Opt) - An optional payload as key/value pairs. The 'details' parameter
     * @param string $client (Opt) - The 'client' parameter
     * @param string $clientUrl (Opt) - The 'client_url' parameter
     * 
     * @return array - The JSON request.
     */
    public function makeRequest($type, $desc, $incidentKey = null, array $details = null, $client = null, $clientUrl = null)
    {
        $json = array(
            "service_key" => $this->serviceKey,
            "event_type" => $type,
            "description" => $desc,
        );

        if (!empty($incidentKey)) {
            $json['incident_key'] = $incidentKey;
        }

        if (!empty($client)) {
            $json['client'] = $client;
        }

        if (!empty($clientUrl)) {
            $json['client_url'] = $clientUrl;
        }

        if (!empty($details)) {
            $json['details'] = $details;
        }

        return $json;
    }

    /**
     * Trigger an event 
     * 
     * @param array $request - Use self::makeRequest to generate
     * @param array $result (Opt)(Pass by reference) - If this parameter is given the result of the CURL call will be filled here. The response is an associative array
     * 
     * @return int - HTTP response
     */
    public function send(array $request, &$result = null)
    {
        $jsonStr = json_encode($request);

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
