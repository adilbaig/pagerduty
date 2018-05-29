<?php

spl_autoload_register(function ($class_name) {
    require_once 'src/' . str_replace("\\", "/", $class_name) . '.php';
});

use \PagerDuty\Context\ImageContext;
use \PagerDuty\Context\LinkContext;
use \PagerDuty\PagerDutyException;
use \PagerDuty\TriggerEvent;

$serviceKey = "1d334a4819fc4b67a795b1c54f9a"; //Replace this with the integration key of your service.

// EXAMPLE 1
// In this example, we're triggering a "Service is down" message.
try {
    $event = new TriggerEvent($serviceKey, "Service is down");
    $responseCode = $event->send();
    if ($responseCode == 200) {
        echo "Success";
    } elseif ($responseCode == 403) { //You're being throttled. Slow down.
        echo "Rate Limited";
    }
} catch (PagerDutyException $exception) { //This doesn't happen unless you've broken their guidelines. The API tries to minimize user mistakes
    var_dump($exception->getErrors());
}

//EXAMPLE 2
//Taken from the `trigger` example @ https://v2.developer.pagerduty.com/v2/docs/trigger-events

$event = new TriggerEvent($serviceKey, "FAILURE for production/HTTP on machine srv01.acme.com");
$event
    ->setClient("Sample Monitoring Service")
    ->setClientURL("https://monitoring.service.com")
    ->setDetails(["ping time" => "1500ms", "load avg" => 0.75])
    ->addContext(new LinkContext("http://acme.pagerduty.com"))
    ->addContext(new LinkContext("http://acme.pagerduty.com", "View the incident on PagerDuty"))
    ->addContext(new ImageContext("https://chart.googleapis.com/chart?chs=600x400&chd=t:6,2,9,5,2,5,7,4,8,2,1&cht=lc&chds=a&chxt=y&chm=D,0033FF,0,0,5,1"));

try {
    // Pass in the '$response' variable by reference if you want to inspect PD's response. This is optional, and you probably don't need this in production.
    $response = null;
    $responseCode = $event->send($response);
} catch (PagerDutyException $exception) { //This doesn't happen unless you've broken their guidelines. The API tries to minimize user mistakes
    var_dump($exception->getErrors());
} finally {
    var_dump($response);
}
