<?php

namespace PagerDuty;

/**
 * An abstract Event
 * @link https://v2.developer.pagerduty.com/v2/docs/trigger-events
 * @author adil
 */
abstract class Event implements \ArrayAccess, \JsonSerializable
{

    protected $dict;

    /**
     * ctor
     *
     * @param string $serviceKey
     * @param string $eventType - One of 'trigger', 'acknowledge' or 'resolve'
     */
    protected function __construct(string $serviceKey, string $eventType)
    {
        $this->dict['service_key'] = $serviceKey;
        $this->dict['event_type'] = $eventType;
    }

    /**
     * A unique incident key to identify an outage.
     * For 'trigger' events this is optional. If not provided, PagerDuty will generate one and return it in the response.
     * Multiple events with the same $incidentKey will be grouped into one open incident. From the PD docs :
     *
     * `Submitting subsequent events for the same incident_key will result in those events being applied to an open incident
     * matching that incident_key. Once the incident is resolved, any further events with the same incident_key will
     * create a new incident (for trigger events) or be dropped (for acknowledge and resolve events).`
     *
     * @link https://v2.developer.pagerduty.com/v2/docs/trigger-events
     * @link https://v2.developer.pagerduty.com/docs/events-api#incident-de-duplication-and-incident_key
     *
     * @param string $incidentKey
     *
     * @return self
     */
    public function setIncidentKey(string $incidentKey): self
    {
        $this->dict['incident_key'] = $incidentKey;
        return $this;
    }

    /**
     * Get the array
     * Useful for debugging or logging.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->dict;
    }

    /**
     * Send the event to PagerDuty
     *
     * @param Event $event - Send this event.
     *
     * @throws PagerDutyException - If status code == 400
     *
     * @return array - An associative array with the following params.
     *  1. 'code' => HTTP response code
     *      200 - Event Processed
     *      400 - Invalid Event. Throws a PagerDutyException
     *      403 - Rate Limited. Slow down and try again later.
     *  2. Any other fields as returned by PagerDuty
     */
    public function send(): array
    {
        return sendEvent($this);
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

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

function sendEvent(Event $event): array
{
    $jsonStr = json_encode($event);

    $curl = curl_init("https://events.pagerduty.com/generic/2010-04-15/create_event.json");
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonStr);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonStr),
    ));

    // See response types: https://v2.developer.pagerduty.com/v2/docs/trigger-events
    $result = json_decode(curl_exec($curl), true);
    if (empty($result)) {
        $result = [];
    }

    $result['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return $result;
}
