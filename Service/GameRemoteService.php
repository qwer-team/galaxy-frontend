<?php

namespace Galaxy\FrontendBundle\Service;

use Qwer\Curl\Curl;

class GameRemoteService
{

    private $incMessCountUrl;
    private $getFlipperUrl;
    private $flippersListUrl;
    private $resetRadarUrl;
    private $startRadarUrl;
    private $zoneBuyUrl;

    public function setZoneBuyUrl($zoneBuyUrl)
    {
        $this->zoneBuyUrl = $zoneBuyUrl;
    }

    public function setStartRadarUrl($startRadarUrl)
    {
        $this->startRadarUrl = $startRadarUrl;
    }

    public function setResetRadarUrl($resetRadarUrl)
    {
        $this->resetRadarUrl = $resetRadarUrl;
    }

    public function setFlippersListUrl($flippersListUrl)
    {
        $this->flippersListUrl = $flippersListUrl;
    }

    public function setGetFlipperUrl($getFlipperUrl)
    {
        $this->getFlipperUrl = $getFlipperUrl;
    }

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

    public function getFlipper($id)
    {
        $url = str_replace("{id}", $id, $this->getFlipperUrl);

        $response = json_decode($this->makeRequest($url));
        return $response;
    }

    public function getFlippersList()
    {
        $response = json_decode($this->makeRequest($this->flippersListUrl));
        return $response;
    }

    public function resetUserInfoRadar($id)
    {
        $url = str_replace("{id}", $id, $this->resetRadarUrl);

        $response = json_decode($this->makeRequest($url));
        return $response;
    }

    public function radarStart($id, $type)
    {
        $find = array("{id}", "{type}");
        $replace = array($id, $type);

        $url = str_replace($find, $replace, $this->startRadarUrl);
        $response = json_decode($this->makeRequest($url));
        return $response;
    }

    public function zoneBuy($id, $jumps)
    {
        $find = array("{id}", "{jumps}");
        $replace = array($id, $jumps);

        $url = str_replace($find, $replace, $this->zoneBuyUrl);
        $response = json_decode($this->makeRequest($url));
        return $response;
    }

    private function makeRequest($url, $data = null)
    {
        return Curl::makeRequest($url, $data);
    }

}