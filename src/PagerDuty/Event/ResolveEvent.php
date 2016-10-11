<?php

namespace PagerDuty\Event;

/**
 * A 'resolve' Event
 *
 * @author adil
 */
class ResolveEvent extends Event
{

    public function __construct($serviceKey, $incidentKey)
    {
        parent::__construct($serviceKey, 'resolve');

        $this->setIncidentKey($incidentKey);
    }
}
