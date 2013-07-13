<?php

namespace Galaxy\FrontendBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Qwer\Curl\Curl;

class UserInfoService extends ContainerAware
{

    public function getGameInfo($userId)
    {
        $rawUrl = $this->container->getParameter("user_providers.game_info.url");
        $url = str_replace("{userId}", $userId, $rawUrl);
        return $response = json_decode($this->makeRequest($url));
    }

    public function getFundsInfo($userId)
    {
        $rawUrl = $this->container->getParameter("user_providers.funds_info.url");
        $url = str_replace("{userId}", $userId, $rawUrl);
        return $response = json_decode($this->makeRequest($url));
    }

    public function getLogsInfo($userId, $page, $length)
    {
        $rawUrl = $this->container->getParameter("user_providers.log_info.url");
        $find = array("{userId}", "{page}", "{length}");
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

    public function getPrizesFromSpace()
    {
        $url = $this->container->getParameter("space.prizes_info.url");
        $response = json_decode($this->makeRequest($url));
        return $response;
    }

    public function getPrizeInfo()
    {
        $response = $this->getPrizesFromSpace();

        $elements = array();
        foreach ($response as $prize) {
            $prizeName = $prize->name;
            foreach ($prize->elements as $element) {
                $elements[$element->id] = array(
                    "name" => $element->name,
                    "prizeName" => $prizeName,
                    "img1" => $element->img1,
                    "available" => $element->available,
                    "account" => $element->account,
                    "price" => $element->price,
                );
            }
        }
        return $elements;
    }

    public function getQuestion($userId)
    {
        $rawUrl = $this->container->getParameter("game.get_question.url");
        $url = str_replace("{userId}", $userId, $rawUrl);

        $response = $this->makeRequest($url);
        return json_decode($response);
    }

    public function answerQuestion($questionId, $answer)
    {
        $rawUrl = $this->container->getParameter("game.answer_question.url");
        $search = array(
            "{questionId}", "{answer}"
        );
        $replace = array(
            $questionId, $answer
        );
        $url = str_replace($search, $replace, $rawUrl);

        $response = $this->makeRequest($url);
        return json_decode($response);
    }

    private function makeRequest($url, $data = null)
    {
        return Curl::makeRequest($url, $data);
    }

}