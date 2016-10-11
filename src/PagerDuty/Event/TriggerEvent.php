<?php

namespace PagerDuty\Event;

use PagerDuty\Event\Context\Context;

/**
 * A 'trigger' event
 *
 * @author adil
 */
class TriggerEvent extends Event
{

    /**
     * ctor
     * 
     * @param string $serviceKey
     * @param string $description
     */
    public function __construct($serviceKey, $description)
    {
        parent::__construct($serviceKey, parent::TYPE_TRIGGER);
        $this->setDescription($description);
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
}
