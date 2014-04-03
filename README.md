PHP PagerDuty Integration API
=========
This library provides a PHP class to trigger events using the [PagerDuty Integration API](http://developer.pagerduty.com/documentation/integration/events)

Installation :
----
Add this line to your project's `composer.json`
````
{
...
    "require": {
        "adilbaig/pagerduty": "dev-master"
    }
...
}
````

Usage:
----
````
use \PagerDuty\PagerDuty;

$pagerDuty = new PagerDuty("my GUID");
$request = $pagerDuty->makeRequest(PagerDuty::TYPE_TRIGGER, "Service is down");
echo "Request : ", json_encode($request);

$result = array();
$responseCode = $pagerDuty->send($request, $result);

echo "ResponseCode : ", $responseCode, " Response : ", json_encode($response);
````

Requirements
----
This library needs the [curl pecl extension](https://php.net/curl).

In Ubuntu, install it like so :

    sudo apt-get install php5-curl
