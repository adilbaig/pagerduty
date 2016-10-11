<?php

namespace PagerDuty;

use PagerDuty\Context\Context;
use PagerDuty\Context\ImageContext;
use PagerDuty\Context\LinkContext;

/**
 * A 'trigger' event
 *
 * @author adil
 */
class TriggerEvent extends Event
{

    /**
     *
     * @var bool 
     */
    private $autoIncidentKey;

    /**
     * Ctor
     * 
     * When $autoIncidentKey is true it auto-generates an `incident_key` based on $description.
     * The incident_key is an md5 hash of the $description. This prevents
     * PagerDuty from flooding admins with incidents that are essentially the same.
     * 
     * @param string $serviceKey
     * @param string $description
     * @param bool $autoIncidentKey (Opt) - Default: false
     */
    public function __construct($serviceKey, $description, $autoIncidentKey = false)
    {
        parent::__construct($serviceKey, 'trigger');
        $this->setDescription($description);

        $this->autoIncidentKey = (bool) $autoIncidentKey;
    }

    /**
     * The name of the monitoring client that is triggering this event
     * 
     * @param string $client
     * @return self
     */
    public function setClient($client)
    {
        $this->dict['client'] = (string) $client;
        return $this;
    }

    /**
     * The URL of the monitoring client that is triggering this event.
     * 
     * @param string $clientUrl
     * @return self
     */
    public function setClientURL($clientUrl)
    {
        $this->dict['client_url'] = (string) $clientUrl;
        return $this;
    }

    /**
     * Add a Link context
     * 
     * @param string $href
     * @param string $text (Opt)
     * 
     * @return self
     */
    public function addLink($href, $text = null)
    {
        return $this->addContext(new LinkContext($href, $text));
    }

    /**
     * Add an Image context
     * 
     * @param string $src
     * @param string $href (Opt)
     * @param string $text (Opt)
     * 
     * @return self
     */
    public function addImage($src, $href = null, $text = null)
    {
        return $this->addContext(new ImageContext($src, $href, $text));
    }

    /**
     * A context is an additional asset that can be attached to an incident.
     * 
     * @link https://v2.developer.pagerduty.com/v2/docs/trigger-events#contexts
     * 
     * @param Context $context
     * @return self
     */
    public function addContext(Context $context)
    {
        if (!array_key_exists('contexts', $this->dict)) {
            $this->dict['contexts'] = [];
        }

        $this->dict['contexts'][] = $context;
        return $this;
    }

    public function toArray()
    {
        if ($this->autoIncidentKey) {
            $this->setIncidentKey("md5-" . md5($this->dict['description']));
        }

        $ret = $this->dict;

        if (array_key_exists('contexts', $ret)) {
            foreach ($ret['contexts'] as $k => $v) {
                $ret['contexts'][$k] = $v->toArray();
            }
        }

        return $ret;
    }
}
