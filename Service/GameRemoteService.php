<?php

namespace Galaxy\FrontendBundle\Service;

use Qwer\Curl\Curl;


class GameRemoteService
{

    private $incMessCountUrl;

    public function setIncMessCountUrl($incMessCountUrl)
    {
        $this->incMessCountUrl = $incMessCountUrl;
    }

    public function increaseUserCountMessages($id)
    {
        $url = str_replace("{id}", $id, $this->incMessCountUrl);

        $response = json_decode($this->makeRequest($url));
        return $response;
    }

    private function makeRequest($url, $data = null)
    {
        return Curl::makeRequest($url, $data);
    }

}