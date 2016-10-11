<?php

namespace PagerDuty\Event;

/**
 * An 'acknowledge' Event
 *
 * @author adil
 */
class AcknowledgeEvent extends Event
{

    public function __construct($serviceKey, $incidentKey)
    {
        parent::__construct($serviceKey, parent::TYPE_ACKNOWLEDGE);

        $this->setIncidentKey($incidentKey);
    }
}
