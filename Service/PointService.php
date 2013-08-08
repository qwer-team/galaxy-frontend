<?php

namespace Galaxy\FrontendBundle\Service;

use Qwer\Curl\Curl;

class PointService
{

    private $getMessageTypeUrl;

    public function setGetMessageTypeUrl($getMessageTypeUrl)
    {
        $this->getMessageTypeUrl = $getMessageTypeUrl;
    }

    public function getMessageType()
    {
        $response = json_decode($this->makeRequest($this->getMessageTypeUrl));
        return $response;
    }

    private function makeRequest($url, $data = null)
    {
        return Curl::makeRequest($url, $data);
    }

}