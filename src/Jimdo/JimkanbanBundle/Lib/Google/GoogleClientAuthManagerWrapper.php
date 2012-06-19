<?php

namespace Jimdo\JimkanbanBundle\Lib\Google;
use \Buzz\Browser;
use \Jimdo\JimkanbanBundle\Lib\Google\GoogleClient;
use \Zend\Cache\Storage\Adapter\AbstractAdapter;

class GoogleClientAuthManagerWrapper implements GoogleClientInterface
{
    const CACHE_PREFIX = 'goog';
    /**
     * @var \Jimdo\JimkanbanBundle\Lib\Google\GoogleClient
     */
    private $client;

    /**
     * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    private $cache;

    public function __construct(GoogleClient $client, AbstractAdapter $cache)
    {
        $this->client = $client;
        $this->cache = $cache;

        $this->setClientAuthToken($this->getTokenFromCache());
    }

    /**
     * @return string
     */
    private function getCacheKey()
    {
        return sha1(self::CACHE_PREFIX . $this->client->getEmail() . $this->client->getPassword());
    }

    /**
     * @return mixed
     */
    private function getTokenFromCache()
    {
        return $this->cache->getItem($this->getCacheKey());
    }

    /**
     * @param $token
     * @return void
     */
    private function setTokenToCache($token)
    {
        $this->cache->setItem($this->getCacheKey(), $token);
    }

    /**
     * @param $token
     * @return void
     */
    private function setClientAuthToken($token)
    {
        $this->client->setAuthToken($token);
    }

    private function handleResponseAuthHeader(\Buzz\Message\Response $response)
    {
        $token = $response->getHeader('Update-Client-Auth');

        if ($token) {
            $this->client->setAuthToken($token);
            $this->setTokenToCache($token);
        }
    }

    /**
     * @param $url
     * @param array $headers
     * @return \Buzz\Message\Response
     */
    public function get($url, $headers = array())
    {
        $response = $this->client->get($url, $headers);
        $this->handleResponseAuthHeader($response);

        return $response;
    }

    /**
     * @param $url
     * @param array $headers
     * @param $content
     * @return \Buzz\Message\Response
     */
    public function post($url, $headers = array(), $content)
    {
        $response =  $this->client->post($url, $headers, $content);
        $this->handleResponseAuthHeader($response);

        return $response;
    }
}