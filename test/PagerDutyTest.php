<?php

use PagerDuty\AcknowledgeEvent;
use PagerDuty\Context\ImageContext;
use PagerDuty\Context\LinkContext;
use PagerDuty\ResolveEvent;
use PagerDuty\TriggerEvent;

class PagerDutyTest extends PHPUnit_Framework_TestCase
{

    public function testAckEvent()
    {
        $serviceKey = "sv123";
        $incidentKey = "inc123";

        $event = new AcknowledgeEvent($serviceKey, $incidentKey);

        $expect = ['service_key' => $serviceKey, 'incident_key' => $incidentKey, 'event_type' => 'acknowledge'];
        $this->assertEquals($expect, $event->toArray());
    }

    public function testResolveEvent()
    {
        $serviceKey = "sv123";
        $incidentKey = "inc123";

        $event = new ResolveEvent($serviceKey, $incidentKey);

        $expect = ['service_key' => $serviceKey, 'incident_key' => $incidentKey, 'event_type' => 'resolve'];
        $this->assertEquals($expect, $event->toArray());
    }

    public function testTriggerEvent()
    {
        $serviceKey = "sv123";

        $event = new TriggerEvent($serviceKey, "FAILURE for production/HTTP on machine srv01.acme.com");
        $event
            ->setClient("Sample Monitoring Service")
            ->setClientURL("https://monitoring.service.com")
            ->setDetails(["ping time" => "1500ms", "load avg" => 0.75])
            ->addContext(new LinkContext("http://acme.pagerduty.com"))
            ->addContext(new LinkContext("http://acme.pagerduty.com", "View the incident on PagerDuty"))
            ->addContext(new ImageContext("https://chart.googleapis.com/chart?chs=600x400&chd=t:6,2,9,5,2,5,7,4,8,2,1&cht=lc&chds=a&chxt=y&chm=D,0033FF,0,0,5,1"))
        ;

        $this->assertArrayNotHasKey('incident_key', $event->toArray());

        $expect = [
            'service_key' => $serviceKey,
            'event_type' => 'trigger',
            'description' => "FAILURE for production/HTTP on machine srv01.acme.com",
            'details' => ["ping time" => "1500ms", "load avg" => 0.75],
            'client' => "Sample Monitoring Service",
            'client_url' => "https://monitoring.service.com",
            'contexts' => [
                ['type' => 'link', 'href' => "http://acme.pagerduty.com"],
                ['type' => 'link', 'href' => "http://acme.pagerduty.com", 'text' => "View the incident on PagerDuty"],
                ['type' => 'image', 'src' => "https://chart.googleapis.com/chart?chs=600x400&chd=t:6,2,9,5,2,5,7,4,8,2,1&cht=lc&chds=a&chxt=y&chm=D,0033FF,0,0,5,1"],
            ],
        ];

        $this->assertEquals($expect, $event->toArray());
    }

    public function testTriggerHashEvent()
    {
        $serviceKey = "sv123";

        $msg = "FAILURE for production/HTTP on machine srv01.acme.com";
        $event = new TriggerEvent($serviceKey, $msg, true);

        $expect = ['incident_key' => "md5-" . md5($msg)];
        $this->assertArraySubset($expect, $event->toArray());
    }
}
