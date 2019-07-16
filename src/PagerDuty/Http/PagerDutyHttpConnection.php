<?php

namespace PagerDuty\Http;

use PagerDuty\Event;
use PagerDuty\Exceptions\PagerDutyConfigurationException;
use PagerDuty\Exceptions\PagerDutyException;

class PagerDutyHttpConnection
{
    /**
     * Some default options for curl
     *
     * @var array
     */
    public static $defaultCurlOptions = array(
        CURLOPT_SSLVERSION      => 6,
        CURLOPT_CONNECTTIMEOUT  => 10,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_TIMEOUT         => 60,                                                                                  // maximum number of seconds to allow cURL functions to execute
        CURLOPT_USERAGENT       => 'PagerDuty-PHP-SDK',
        CURLOPT_VERBOSE         => 0,
        CURLOPT_SSL_VERIFYHOST  => 2,
        CURLOPT_SSL_VERIFYPEER  => 1,
        CURLOPT_SSL_CIPHER_LIST => 'TLSv1:TLSv1.2'
    );

    const HEADER_SEPARATOR = ';';

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $headers = array();

    /**
     * @var array
     */
    protected $curlOptions = array();

    /**
     * @var int
     */
    protected $responseCode;

    /**
     * PagerDutyHttpConnection constructor.
     *
     * @param null|string $url - PagerDuty's API
     */
    public function __construct($url = null)
    {
        $url = ($url !== null) ? $url : 'https://events.pagerduty.com/v2/enqueue';

        $this->setUrl($url);
        $this->setCurlOptions(self::$defaultCurlOptions);
        $this->addHeader('Content-Type','application/json');                                                            # assume this is default; can override anytime

        $curl       = curl_version();
        $sslVersion = isset($curl['ssl_version']) ? $curl['ssl_version'] : '';

        if ($sslVersion
            && substr_compare($sslVersion, "NSS/", 0, strlen("NSS/")) === 0)
        {
            //Remove the Cipher List for NSS
            $this->removeCurlOption(CURLOPT_SSL_CIPHER_LIST);
        }
    }

    /**
     * Set Headers
     *
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        if (!is_array($headers)) {
            throw new \InvalidArgumentException('Argument expected to be of type array');
        }

        $this->headers = $headers;
    }

    /**
     * Adds a Header
     *
     * @param      $name
     * @param      $value
     * @param bool $overWrite allows you to override header value
     */
    public function addHeader($name, $value, $overWrite = true)
    {
        if (!array_key_exists($name, $this->headers)
            || $overWrite)
        {
            $this->headers[$name] = $value;
        }
        else {
            $this->headers[$name] = $this->headers[$name] . self::HEADER_SEPARATOR . $value;
        }
    }

    /**
     * Gets all Headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get Header by Name
     *
     * @param $name
     * @return string|null
     */
    public function getHeader($name)
    {
        if (array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        }

        return null;
    }

    /**
     * Removes a Header
     *
     * @param $name
     */
    public function removeHeader($name)
    {
        unset($this->headers[$name]);
    }

    /**
     * Set service url
     *
     * @param $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get Service url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Add Curl Option
     *
     * @param string $name
     * @param mixed  $value
     */
    public function addCurlOption($name, $value)
    {
        $this->curlOptions[$name] = $value;
    }

    /**
     * Removes a curl option from the list
     *
     * @param $name
     */
    public function removeCurlOption($name)
    {
        unset($this->curlOptions[$name]);
    }

    /**
     * Set Curl Options. Overrides all curl options
     *
     * @param array $options
     */
    public function setCurlOptions($options)
    {
        if (!\is_array($options)) {
            throw new \InvalidArgumentException('Argument expected to be of type array');
        }

        $this->curlOptions = $options;
    }

    /**
     * Gets all curl options
     *
     * @return array
     */
    public function getCurlOptions()
    {
        return $this->curlOptions;
    }

    /**
     * Get Curl Option by name
     *
     * @param $name
     * @return mixed|null
     */
    public function getCurlOption($name)
    {
        if (array_key_exists($name, $this->curlOptions)) {
            return $this->curlOptions[$name];
        }

        return null;
    }

    /**
     * Set ssl parameters for certificate based client authentication
     *
     * @param      $certPath
     * @param null $passPhrase
     */
    public function setSSLCert($certPath, $passPhrase = null)
    {
        $this->curlOptions[CURLOPT_SSLCERT] = realpath($certPath);

        if ($passPhrase !== null
            && trim($passPhrase) !== '')
        {
            $this->curlOptions[CURLOPT_SSLCERTPASSWD] = $passPhrase;
        }
    }

    /**
     * Set connection timeout in seconds
     *
     * @param integer $timeout
     */
    public function setTimeout($timeout)
    {
        $this->curlOptions[CURLOPT_CONNECTTIMEOUT] = $timeout;
    }

    /**
     * Set HTTP proxy information
     *
     * @param string $proxy
     * @throws PagerDutyConfigurationException
     */
    public function setProxy($proxy)
    {
        $urlParts = parse_url($proxy);

        if ($urlParts === false
            || !array_key_exists('host', $urlParts))
        {
            throw new PagerDutyConfigurationException('Invalid proxy configuration ' . $proxy);
        }

        $this->curlOptions[CURLOPT_PROXY] = $urlParts['host'];

        if (isset($urlParts['port'])) {
            $this->curlOptions[CURLOPT_PROXY] .= ':' . $urlParts['port'];
        }

        if (isset($urlParts['user'])) {
            $this->curlOptions[CURLOPT_PROXYUSERPWD] = $urlParts['user'] . ':' . $urlParts['pass'];
        }
    }

    /**
     * Sets response code from curl call
     *
     * @param int $code
     */
    public function setResponseCode($code)
    {
        $this->responseCode = $code;
    }

    /**
     * Returns response code
     *
     * @return int|null
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Sets the User-Agent string on the HTTP request
     *
     * @param string $userAgentString
     */
    public function setUserAgent($userAgentString)
    {
        $this->curlOptions[CURLOPT_USERAGENT] = $userAgentString;
    }

    /**
     * Send the event to PagerDuty
     *
     * @param Event $payload
     * @param array $result (Opt)(Pass by reference) - If this parameter is given the result of the CURL call will be filled here. The response is an associative array.
     *
     * @throws PagerDutyException - If status code == 400
     *
     * @return int - HTTP response code
     *  202 - Event Processed
     *  400 - Invalid Event. Throws a PagerDutyException
     *  403 - Rate Limited. Slow down and try again later.
     */
    public function send($payload, &$result = null)
    {
        if (!$payload instanceof Event) {
            throw new \InvalidArgumentException('Argument expected to be of type Event');
        }

        $result       = $this->post(json_encode($payload));
        $responseCode = $this->getResponseCode();

        if ($responseCode === 400) {
            throw new PagerDutyException($result['message'], $result['errors']);
        }

        return $responseCode;
    }

    /**
     * POST data to PagerDuty
     *
     * @param string $payload
     * @return mixed
     */
    protected function post($payload)
    {
        if (!\is_string($payload)) {
            throw new \InvalidArgumentException('Argument expected to be of type string');
        }

        $url = $this->getUrl();
        $this->addHeader('Content-Length', \strlen($payload));

        $curl = curl_init($url);

        $options = $this->getCurlOptions();
        curl_setopt_array($curl, $options);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders());

        $response = curl_exec($curl);
        $result   = is_array($response) ? json_decode($response, true): $response;

        $this->setResponseCode(curl_getinfo($curl, CURLINFO_HTTP_CODE));

        curl_close($curl);

        return $result;
    }
}
