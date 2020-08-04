<?php

namespace PagerDuty;

/**
 * A 'trigger' event
 * @link https://developer.pagerduty.com/docs/events-api-v2/trigger-events/
 *
 * @author adil
 */
class TriggerEvent extends Event
{

    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const INFO = 'info';

    /**
     *
     * @var bool
     */
    private $autoDeDupKey;

    /**
     * Ctor
     *
     * @param string $routingKey - The routing key, taken from your PagerDuty 'Configuration' > 'Services' page
     * @param string $summary - The Error message
     * @param string $source - The unique location of the affected system, preferably a hostname or FQDN.
     * @param string $severity - One of 'critical', 'error', 'warning' or 'info'. Use the constants above
     * @param boolean $autoDeDupKey - If true, autogenerates a `dedup_key` based on the md5 hash of the $summary
     */
    public function __construct($routingKey, $summary, $source, $severity, $autoDeDupKey = false)
    {
        parent::__construct($routingKey, 'trigger');
        $this->setPayloadSummary($summary);
        $this->setPayloadSource($source);
        $this->setPayloadSeverity($severity);

        $this->autoDeDupKey = (bool) $autoDeDupKey;
    }

    /**
     * A human-readable error message.
     * This is what PD will read over the phone.
     *
     * @param string $summary
     *
     * @return self
     */
    public function setPayloadSummary($summary)
    {
        $this->dict['payload']['summary'] = (string) $summary;
        return $this;
    }

    /**
     * The unique location of the affected system, preferably a hostname or FQDN.
     *
     * @param string $source
     * @return self
     */
    public function setPayloadSource($source)
    {
        $this->dict['payload']['source'] = (string) $source;
        return $this;
    }

    /**
     * One of critical, error, warning or info. Use the class constants above
     *
     * @param string $value
     * @return self
     */
    public function setPayloadSeverity($value)
    {
        $this->dict['payload']['severity'] = (string) $value;
        return $this;
    }

    /**
     * The time this error occured.
     *
     * @param string $timestamp - Can be a datetime string as well. See the example @ https://v2.developer.pagerduty.com/docs/send-an-event-events-api-v2
     * @return self
     */
    public function setPayloadTimestamp($timestamp)
    {
        $this->dict['payload']['timestamp'] = (string) $timestamp;
        return $this;
    }

    /**
     * From the PD docs: "Component of the source machine that is responsible for the event, for example `mysql` or `eth0`"
     *
     * @param string $value
     * @return self
     */
    public function setPayloadComponent($value)
    {
        $this->dict['payload']['component'] = (string) $value;
        return $this;
    }

    /**
     * From the PD docs: "Logical grouping of components of a service, for example `app-stack`"
     *
     * @param string $value
     * @return self
     */
    public function setPayloadGroup($value)
    {
        $this->dict['payload']['group'] = (string) $value;
        return $this;
    }

    /**
     * From the PD docs: "The class/type of the event, for example `ping failure` or `cpu load`"
     *
     * @param string $value
     * @return self
     */
    public function setPayloadClass($value)
    {
        $this->dict['payload']['class'] = (string) $value;
        return $this;
    }

    /**
     * An associative array of additional details about the event and affected system
     *
     * @param array $dict
     * @return self
     */
    public function setPayloadCustomDetails(array $dict)
    {
        $this->dict['payload']['custom_details'] = $dict;
        return $this;
    }

    /**
     * Attach a link to the incident.
     *
     * @link https://developer.pagerduty.com/docs/events-api-v2/trigger-events/#the-links-property
     *
     * @param string $href URL of the link to be attached.
     * @param string $text Optional. Plain text that describes the purpose of the link, and can be used as the link's text.
     *
     * @return self
     */
    public function addLink($href, $text = null)
    {
        if (!array_key_exists('links', $this->dict)) {
            $this->dict['links'] = [];
        }

        $link = ['href' => (string) $href];
        if (!empty($text)) {
            $link['text'] = (string) $text;
        }
        $this->dict['links'][] = $link;

        return $this;
    }

    /**
     * Attach an image to the incident.
     *
     * @link https://developer.pagerduty.com/docs/events-api-v2/trigger-events/#the-images-property
     *
     * @param string $src The source (URL) of the image being attached to the incident. This image must be served via HTTPS.
     * @param string $href Optional URL; makes the image a clickable link.
     * @param string $alt Optional alternative text for the image.
     *
     * @return self
     */
    public function addImage($src, $href = null, $alt = null)
    {
        if (!array_key_exists('images', $this->dict)) {
            $this->dict['images'] = [];
        }

        $image = ['src' => (string) $src];
        if (!empty($href)) {
            $image['href'] = (string) $href;
        }
        if (!empty($alt)) {
            $image['alt'] = (string) $alt;
        }
        $this->dict['images'][] = $image;

        return $this;
    }

    public function toArray()
    {
        if ($this->autoDeDupKey) {
            $this->setDeDupKey("md5-" . md5($this->dict['payload']['summary']));
        }
        return $this->dict;
    }
}
