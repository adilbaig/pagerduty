PHP PagerDuty Events API
=========
PHP implementation of the [PagerDuty Events API](https://v2.developer.pagerduty.com/v2/docs/events-api)

**Important**: v2 is a complete rewrite of the library. It is not backwards compatible with v1.

For all new projects i suggest using v2. It is more flexible and easier to use than v1, and overcomes some of it's predecessor's design limitations.

[![Latest Stable Version](https://poser.pugx.org/adilbaig/pagerduty/v/stable.svg)](https://packagist.org/packages/adilbaig/pagerduty) [![Total Downloads](https://poser.pugx.org/adilbaig/pagerduty/downloads.svg)](https://packagist.org/packages/adilbaig/pagerduty) 

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
use \PagerDuty\PagerDuty;

$serviceKey = "1d334a4819fc4b67a795b1c54f9a"; //Replace this with the integration key of your service.

// In this example, we're triggering a "Service is down" message.
$response = (new PagerDuty())->trigger($serviceKey, "Service is down");
if($response == 200)
    echo "Success";
elseif($response == 400)
    echo "Invalid Event"; //This doesn't happen unless you've broken their guidelines. The API tries to minimize user mistakes
elseif($response == 403)
    echo "Rate Limited";  //You're being throttled. Slow down.
````

Automatically send only one PagerDuty incident for repeated errors

````
use \PagerDuty\PagerDuty;


//After this example, you will see just one incident on PD

$pd = new PagerDuty();
$pd->triggerSingleIncident($serviceKey, "Service is down");
$pd->triggerSingleIncident($serviceKey, "Service is down");
$pd->triggerSingleIncident($serviceKey, "Service is down");
$pd->triggerSingleIncident($serviceKey, "Service is down");

````

Create a detailed 'trigger' event, add optional data. Dump the event and inspect 
response from PD

````
use \PagerDuty\PagerDuty;
use \PagerDuty\Event\TriggerEvent;
use \PagerDuty\Event\Context\LinkContext;
use \PagerDuty\Event\Context\ImageContext;

//Taken from the `trigger` example @ https://v2.developer.pagerduty.com/v2/docs/trigger-events

$event = new TriggerEvent($serviceKey, "FAILURE for production/HTTP on machine srv01.acme.com");
$event
    ->setClient("Sample Monitoring Service")
    ->setClientURL("https://monitoring.service.com")
    ->setDetails(["ping time": "1500ms", "load avg": 0.75])
    ->addContext(new LinkContext("http://acme.pagerduty.com"))
    ->addContext(new LinkContext("http://acme.pagerduty.com", "View the incident on PagerDuty"))
    ->addContext(new ImageContext("https://chart.googleapis.com/chart?chs=600x400&chd=t:6,2,9,5,2,5,7,4,8,2,1&cht=lc&chds=a&chxt=y&chm=D,0033FF,0,0,5,1"))

$response = null;
$rez = (new PagerDuty())->send($event, &$response);
var_dump($response);
````

Acknowledge an event

````
(new PagerDuty())->acknowledge($serviceKey, "incident key");
````

Resolve an event

````
(new PagerDuty())->resolve($serviceKey, "incident key");
````

Questions
----

**Q.** How do i get the service key from PagerDuty?

**A.** In your PagerDuty console, click 'Configuration' > 'Services'. Click the link under 'Integrations' column. It's the 'Integration Key'

Read more here : https://v2.developer.pagerduty.com/v2/docs/events-api#getting-started

Requirements
----
This library needs the [curl pecl extension](https://php.net/curl).

In Ubuntu, install it like so :

    sudo apt-get install php5-cur
