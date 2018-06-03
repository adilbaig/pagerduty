<?php

/**
 * cd into root
 * ./vendor/bin/phpunit test
 */

use PagerDuty\AcknowledgeEvent;
use PagerDuty\Context\ImageContext;
use PagerDuty\Context\LinkContext;
use PagerDuty\ResolveEvent;
use PagerDuty\TriggerEvent;

class PagerDutyTest extends \PHPUnit\Framework\TestCase
{

    public function testAckEvent()
    {
        $routingKey = 'sv123';
        $dedupKey = 'inc123';

        $event = new AcknowledgeEvent($routingKey, $dedupKey);

        $expect = ['routing_key' => $routingKey, 'dedup_key' => $dedupKey, 'event_action' => 'acknowledge'];
        $this->assertEquals($expect, $event->toArray());
    }

    public function testResolveEvent()
    {
        $routingKey = 'sv123';
        $dedupKey = 'inc123';

        $event = new ResolveEvent($routingKey, $dedupKey);

        $expect = ['routing_key' => $routingKey, 'dedup_key' => $dedupKey, 'event_action' => 'resolve'];
        $this->assertEquals($expect, $event->toArray());
    }

    public function testTriggerEvent()
    {
        $routingKey = 'sv123';

        $event = new TriggerEvent($routingKey, 'FAILURE for production/HTTP on machine srv01.acme.com', 'localhost', TriggerEvent::ERROR);
        $event
            ->setPayloadClass('ping failure')
            ->setPayloadComponent('web server')
            ->setPayloadTimestamp('2018-05-01T08:42:58.315+0000')
            ->setPayloadCustomDetails(['ping_time' => '1500ms', 'load_avg' => 0.75])
            ->addContext(new LinkContext('http://acme.pagerduty.com'))
            ->addContext(new LinkContext('http://acme.pagerduty.com', 'View the incident on PagerDuty'))
            ->addContext(new ImageContext('https://chart.googleapis.com/chart?chs=600x400&chd=t:6,2,9,5,2,5,7,4,8,2,1&cht=lc&chds=a&chxt=y&chm=D,0033FF,0,0,5,1'))
        ;

        $this->assertArrayNotHasKey('dedup_key', $event->toArray());

        $expect = [
            'routing_key' => $routingKey,
            'event_action' => 'trigger',
            'payload' => [
                'summary' => 'FAILURE for production/HTTP on machine srv01.acme.com',
                'source' => 'localhost',
                'severity' => 'error',
                'class' => 'ping failure',
                'component' => 'web server',
                'timestamp' => '2018-05-01T08:42:58.315+0000',
                'custom_details' => [
                    'ping_time' => '1500ms',
                    'load_avg' => 0.75,
                ],
            ],
            'contexts' => [
                ['type' => 'link', 'href' => 'http://acme.pagerduty.com'],
                ['type' => 'link', 'href' => 'http://acme.pagerduty.com', 'text' => 'View the incident on PagerDuty'],
                ['type' => 'image', 'src' => 'https://chart.googleapis.com/chart?chs=600x400&chd=t:6,2,9,5,2,5,7,4,8,2,1&cht=lc&chds=a&chxt=y&chm=D,0033FF,0,0,5,1'],
            ],
        ];

        $this->assertEquals($expect, $event->toArray());
    }

    public function testTriggerHashEvent()
    {
        $routingKey = 'sv123';

        $msg = 'FAILURE for production/HTTP on machine srv01.acme.com';
        $event = new TriggerEvent($routingKey, $msg, 'localhost', TriggerEvent::ERROR, true);

        $expect = ['dedup_key' => 'md5-' . md5($msg)];
        $this->assertArraySubset($expect, $event->toArray());
    }

    /**
     * @expectedException TypeError
     */
    public function testTypeError()
    {
        $event = new TriggerEvent('sv123', 'Blah');
        $event->setDetails(null);
    }
}
