PHP PagerDuty Integration API
=========
This library provides a PHP class to trigger events using the [PagerDuty Integration API](http://developer.pagerduty.com/documentation/integration/events)

[![Latest Stable Version](https://poser.pugx.org/adilbaig/pagerduty/v/stable.svg)](https://packagist.org/packages/adilbaig/pagerduty) [![Total Downloads](https://poser.pugx.org/adilbaig/pagerduty/downloads.svg)](https://packagist.org/packages/adilbaig/pagerduty) 

Installation :
----
Add this line to your project's `composer.json`
````
{
...
    "require": {
        "adilbaig/pagerduty": "1.0.*"
    }
...
}
````

The packagist URL is https://packagist.org/packages/adilbaig/pagerduty

Usage:
----
````
use \PagerDuty\PagerDuty;

// Initialize the PagerDuty object with your GUID
$pagerDuty = new PagerDuty("my GUID");

// Create a request. In this example, we're triggering a "Service is down" message.
$request = $pagerDuty->makeRequest(PagerDuty::TYPE_TRIGGER, "Service is down");
echo "Request : ", json_encode($request);

//Send the request and read the response in $response
$response = array();
$responseCode = $pagerDuty->send($request, $response);

echo "ResponseCode : ", $responseCode, " Response : ", json_encode($response);
````

Requirements
----
This library needs the [curl pecl extension](https://php.net/curl).

In Ubuntu, install it like so :

    sudo apt-get install php5-curl


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/adilbaig/pagerduty/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

