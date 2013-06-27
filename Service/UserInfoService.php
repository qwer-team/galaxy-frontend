<?php

namespace Galaxy\FrontendBundle\Service;
use Symfony\Component\DependencyInjection\ContainerAware;

class UserInfoService extends ContainerAware
{
    
    public function getGameInfo($userId){
        $rawUrl = $this->container->getParameter("user_providers.game_info.url");
        $url = str_replace("{userId}", $userId, $rawUrl);
        return $response = json_decode($this->makeRequest($url));
    }
    
    public function getFundsInfo($userId){
        $rawUrl = $this->container->getParameter("user_providers.funds_info.url");
        $url = str_replace("{userId}", $userId, $rawUrl);
        return $response = json_decode($this->makeRequest($url));
    }
    
    public function getLogsInfo($userId, $page, $length){
        $rawUrl = $this->container->getParameter("user_providers.log_info.url");
        $find = array("{userId}","{page}", "{length}");
        $replace = array($userId, $page, $length);

        $url = str_replace($find, $replace, $rawUrl);
        return $response = json_decode($this->makeRequest($url));
    }
    
    public function getLogsCount($userId)
    {
        $rawUrl = $this->container->getParameter("user_providers.log_info_count.url");
        $url = str_replace("{userId}", $userId, $rawUrl);
        $response = json_decode($this->makeRequest($url));
        return $response;
    }
    
    public function getPrizesFromSpace(){
        $url = $this->container->getParameter("space.prizes_info.url");
        $response = json_decode($this->makeRequest($url));
        return $response;
    }
    
    public function getPrizeInfo()
    {
        $response = $this->getPrizesFromSpace();
        
        $elements = array();
        foreach($response as $prize){
            $prizeName = $prize->name;
            foreach($prize->elements as $element){
                $elements[$element->id] = array(
                    "name" => $element->name,
                    "prizeName" => $prizeName,
                    "img1" => $element->img1,
                    "available" => $element->available,
                    "account"   => $element->account,
                    "price"   => $element->price,
                );
            }
        }
        return $elements;
    }


    private function makeRequest($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!is_null($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}