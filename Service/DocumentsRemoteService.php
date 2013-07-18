<?php

namespace Galaxy\FrontendBundle\Service;

use Qwer\Curl\Curl;

class DocumentsRemoteService
{

    private $transFundsUrl;
    private $debitFundsUrl;
    private $getFundsUrl;

    public function setGetFundsUrl($getFundsUrl)
    {
        $this->getFundsUrl = $getFundsUrl;
    }

    public function setDebitFundsUrl($debitFundsUrl)
    {
        $this->debitFundsUrl = $debitFundsUrl;
    }

    public function setTransFundsUrl($transFundsUrl)
    {
        $this->transFundsUrl = $transFundsUrl;
    }

    public function transFunds($data)
    {
        $response = json_decode($this->makeRequest($this->transFundsUrl, $data));
        return $response;
    }

    public function debitFunds($data)
    {
        $response = json_decode($this->makeRequest($this->debitFundsUrl, $data));
        return $response;
    }

    public function getFunds($userId)
    {
        $url = str_replace("{userId}", $userId, $this->getFundsUrl);

        $response = json_decode($this->makeRequest($url));
        return $response;
    }

    private function makeRequest($url, $data = null)
    {
        return Curl::makeRequest($url, $data);
    }

}