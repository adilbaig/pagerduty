PHP PagerDuty Events API
=========
PHP implementation of the [PagerDuty Events API](https://v2.developer.pagerduty.com/v2/docs/events-api)

**Important**: v2 is a complete rewrite of the library. It is not backwards compatible with v1.

For all new projects i suggest using v2. It is more flexible and easier to use than v1, and overcomes some of its predecessor's design limitations.

[![Latest Stable Version](https://poser.pugx.org/adilbaig/pagerduty/v/stable.svg)](https://packagist.org/packages/adilbaig/pagerduty) [![Total Downloads](https://poser.pugx.org/adilbaig/pagerduty/downloads.svg)](https://packagist.org/packages/adilbaig/pagerduty) 

Features :
----

- Trigger, acknowledge and resolve incidents.
- Support for [Event contexts](https://v2.developer.pagerduty.com/v2/docs/trigger-events#contexts). 
- Works with [Events API V1](https://v2.developer.pagerduty.com/v2/docs/#the-events-api).
- Unit Tests


Installation :
----
Add this line to your project's `composer.json`
````
{
...
    "require": {
        "adilbaig/pagerduty": "2.*"
    }
...
}
````

The packagist URL is https://packagist.org/packages/adilbaig/pagerduty

Usage:
----

Trigger an event
 
````php
use \PagerDuty\TriggerEvent;
use \PagerDuty\PagerDutyException;

$serviceKey = "1d334a4819fc4b67a795b1c54f9a"; //Replace this with the integration key of your service.

// In this example, we're triggering a "Service is down" message.
try {
    $responseCode = (new TriggerEvent($serviceKey, "Service is down"))->send();
    if($responseCode == 200)
        echo "Success";
    elseif($responseCode == 403)
        echo "Rate Limited";  //You're being throttled. Slow down.
} catch(PagerDutyException $exception) { //This doesn't happen unless you've broken their guidelines. The API tries to minimize user mistakes
    var_dump($exception->getErrors());
}

````

Automatically send only one PagerDuty incident for repeated errors

````php

//After this example, you will see just one incident on PD

(new TriggerEvent($serviceKey, "Service is down", true))->send();
(new TriggerEvent($serviceKey, "Service is down", true))->send();
(new TriggerEvent($serviceKey, "Service is down", true))->send();

````

Create a detailed 'trigger' event, add optional data. Dump the event and inspect 
response from PD

````php
use \PagerDuty\TriggerEvent;
use \PagerDuty\Context\LinkContext;
use \PagerDuty\Context\ImageContext;

//Taken from the `trigger` example @ https://v2.developer.pagerduty.com/v2/docs/trigger-events

$event = new TriggerEvent($serviceKey, "FAILURE for production/HTTP on machine srv01.acme.com");
$event
    ->setClient("Sample Monitoring Service")
    ->setClientURL("https://monitoring.service.com")
    ->setDetails(["ping time" => "1500ms", "load avg" => 0.75])
    ->addContext(new LinkContext("http://acme.pagerduty.com"))
    ->addContext(new LinkContext("http://acme.pagerduty.com", "View the incident on PagerDuty"))
    ->addContext(new ImageContext("https://chart.googleapis.com/chart?chs=600x400&chd=t:6,2,9,5,2,5,7,4,8,2,1&cht=lc&chds=a&chxt=y&chm=D,0033FF,0,0,5,1"));

// Pass in the '$response' variable by reference if you want to inspect PD's response. This is optional, and you probably don't need this in production.
$response = null;
$responseCode = $event->send($response);
var_dump($response);
````

Acknowledge an event

````php
(new AcknowledgeEvent($serviceKey, "incident key"))->send();
````

Resolve an event

````php
(new ResolveEvent($serviceKey, "incident key"))->send();
````

Questions
----

**Q.** How do i get the service key from PagerDuty?

**A.** In your PagerDuty console, click 'Configuration' > 'Services'. Click the link under 'Integrations' column. It's the 'Integration Key'

Read more here : https://v2.developer.pagerduty.com/v2/docs/events-api#getting-started

Requirements
----
This library needs the [curl pecl extension](https://php.net/curl).

In Ubuntu 16.04, install it like so :

    sudo apt install php-curl

