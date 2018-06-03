<?php

namespace PagerDuty;

/**
 * An 'acknowledge' Event
 *
 * @author adil
 */
class AcknowledgeEvent extends Event
{

    public function __construct($routingKey, $dedupKey)
    {
        parent::__construct($routingKey, 'acknowledge');

        $this->setDeDupKey($dedupKey);
    }
}
