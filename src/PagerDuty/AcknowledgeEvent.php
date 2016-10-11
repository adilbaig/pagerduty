<?php

namespace PagerDuty;

/**
 * An 'acknowledge' Event
 *
 * @author adil
 */
class AcknowledgeEvent extends Event
{

    public function __construct($serviceKey, $incidentKey)
    {
        parent::__construct($serviceKey, 'acknowledge');

        $this->setIncidentKey($incidentKey);
    }
}
