<?php

namespace Galaxy\FrontendBundle\Service;

use Qwer\Curl\Curl;

class UserService
{

    private $getUserUrl;

    public function getUser($login)
    {
        $url = str_replace("{login}", $login, $this->getUserUrl);
        $response = json_decode(Curl::makeRequest($url));
        return $response;
    }

    public function setGetUserUrl($getUserUrl)
    {
        $this->getUserUrl = $getUserUrl;
    }

}