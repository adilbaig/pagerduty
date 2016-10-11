<?php

namespace PagerDuty;

/**
 * An abstract Event
 * 
 * @author adil
 */
abstract class Event implements \ArrayAccess, \JsonSerializable
{

    protected $dict;

    /**
     * ctor
     * 
     * @param string $serviceKey
     * @param string $type - One of self::TYPE_*
     */
    protected function __construct($serviceKey, $type)
    {
        $this->dict['service_key'] = (string) $serviceKey;
        $this->dict['event_type'] = (string) $type;
    }

    /**
     * A human-readable error message. 
     * This is what PD will read over the phone.
     * 
     * @param string $desc
     * 
     * @return self
     */
    public function setDescription($desc)
    {
        $this->dict['description'] = (string) $desc;
        return $this;
    }

    /**
     * An associative array of any user-defined values.
     * This will be displayed along with the error in PD. Useful for debugging.
     * 
     * @param array $details - An associative array
     * 
     * @return self
     */
    public function setDetails(array $details)
    {
        $this->dict['details'] = $details;
        return $this;
    }

    /**
     * A unique incident key to identify an outage.
     * Multiple events with the same $incidentKey will be grouped into one open incident. From the PD docs :
     * 
     * `Submitting subsequent events for the same incident_key will result in those events being applied to an open incident 
     * matching that incident_key. Once the incident is resolved, any further events with the same incident_key will 
     * create a new incident (for trigger events) or be dropped (for acknowledge and resolve events).`
     * 
     * @link https://v2.developer.pagerduty.com/docs/events-api#incident-de-duplication-and-incident_key 
     * 
     * @param string $incidentKey
     * 
     * @return self
     */
    public function setIncidentKey($incidentKey)
    {
        $this->dict['incident_key'] = (string) $incidentKey;
        return $this;
    }

    /**
     * Get the array
     * Useful for debugging or logging.
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->dict;
    }

    /**
     * Send the event to PagerDuty
     * 
     * @param array $result (Opt)(Pass by reference) - If this parameter is given the result of the CURL call will be filled here. The response is an associative array.
     * 
     * @throws PagerDutyException - If status code == 400
     * 
     * @return int - HTTP response code
     *  200 - Event Processed
     *  400 - Invalid Event. Throws a PagerDutyException
     *  403 - Rate Limited. Slow down and try again later.
     */
    public function send(&$result = null)
    {
        $jsonStr = json_encode($this);

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

        if ($responseCode == 400) {
            throw new PagerDutyException($result['message'], $result['errors']);
        }

        return $responseCode;
    }
    /* -------- ArrayAccess -------- */

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->dict);
    }

    public function offsetGet($key)
    {
        if (!$this->offsetExists($key)) {
            return;
        }

        return $this->dict[$key];
    }

    public function offsetSet($key, $value)
    {
        if (empty($key) || is_string($key)) {
            throw new \Exception("Key must be a non-empty string. It is `" . var_export($key, true) . "`");
        }

        $this->dict[$key] = $value;
    }

    public function offsetUnset($key)
    {
        if ($this->offsetExists($key)) {
            unset($this->dict[$key]);
        }
    }
    /* -------- JsonSerializable -------- */

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
