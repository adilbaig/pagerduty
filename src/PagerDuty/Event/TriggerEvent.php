<?php

namespace PagerDuty\Event;

use PagerDuty\Event\Context\Context;
use PagerDuty\Event\Context\ImageContext;
use PagerDuty\Event\Context\LinkContext;

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
        parent::__construct($serviceKey, 'trigger');
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
}
