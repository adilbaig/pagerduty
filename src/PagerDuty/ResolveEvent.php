<?php

namespace PagerDuty;

/**
 * A 'resolve' Event
 *
 * @author adil
 */
class ResolveEvent extends Event
{

    public function __construct($routingKey, $dedupKey)
    {
        parent::__construct($routingKey, 'resolve');

        $this->setDeDupKey($dedupKey);
    }
}
