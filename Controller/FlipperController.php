<?php

namespace Galaxy\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Galaxy\FrontendBundle\Form\JumpRequestType;
use Galaxy\FrontendBundle\Entity\JumpRequest;

class FlipperController extends Controller
{

    /**
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    public function getUserAction()
    {
        $user = $this->getUser();
        $response = new Response();
        $response->setContent(json_encode($user->jsonSerialize()));
        return $response;
    }

    public function getPrizesInfoAction()
    {
        $session = $this->getRequest()->getSession();
        if($session->has('prize_elements')){
            $prizesElements = $session->get('prize_elements');
        }else{
            $service = $this->get("galaxy.user_info.service");
            $prizesElements = $service->getPrizeInfo();
            $session->set('prize_elements', $prizesElements);
        }
        
        $response = new Response();
        $response->setContent(json_encode($prizesElements));
        return $response;
    }

    /**
     * @Template("GalaxyFrontendBundle:Flipper:logs.html.twig")
     */
    public function getUserLogsAction($page, $length)
    {
        $user = $this->getUser();
        $userInfoService = $this->container->get("galaxy.user_info.service");
        $userId = $user->getId();
        $userLogs = $userInfoService->getLogsInfo($userId, $page, $length);
        $userLogsCount = $userInfoService->getLogsCount($userId);
        $pagesCount = ceil($userLogsCount->count / $length);
        $data = array(
            "userLogs" => $userLogs,
            'page' => $page,
            "count" => $pagesCount,
            "length" => $length,
        );

        return $data;
    }
    
    /**
     * @Template("GalaxyFrontendBundle:Flipper:basket.html.twig")
     */
    public function getUserBasketAction()
    {
        $user = $this->getUser();
        $userInfoService = $this->container->get("galaxy.user_info.service");
        $prizeService = $this->container->get("galaxy.prize.service");
        $basket = $user->getGameInfo()->basket;
        $prizeList = $userInfoService->getPrizesFromSpace();
        $buyElementsPrize = $prizeService->getElementsPrize($prizeList, $basket);
        
        $data = array(
            "items" => $buyElementsPrize,
        );

        return $data;
    }

    public function jumpAction(Request $request)
    {
        $jumpRequest = new JumpRequest();
        $form = $this->createForm(new JumpRequestType(), $jumpRequest);

        $form->bind($request);
        $result = array("result" => "fail");
        if ($form->isValid()) {
            $jumpUrl = $this->get("service_container")->getParameter("jump.url");

            $token = $this->container->get('security.context')->getToken();
            $user = $token->getUser();
            $x = $jumpRequest->getX();
            $y = $jumpRequest->getY();
            $z = $jumpRequest->getZ();
            $superJump = $jumpRequest->getSuperjump();
            $userId = $user->getId();
            $data = array(
                "x" => $x,
                "y" => $y,
                "z" => $z,
                "superjump" => (int) $superJump,
                "userId" => $userId,
            );

            $response = json_decode($this->makeRequest($jumpUrl, $data));
            $result = array("result" => "success", "req" => $response);
            if ($response->result == "success") {
                $result["pointType"] = $response->response->type->name;
                $tag = $response->response->type->tag;
                $result["tag"] = $tag;
                $pointImageFolder = $this->container->getParameter("image.folder");
                $imagePath = str_replace("{tag}", $tag, $pointImageFolder);
                $result["pointImagePath"] = $imagePath;
                $userInfoService = $this->get("galaxy.user_info.service");
                $fundsInfo = $userInfoService->getFundsInfo($userId);
                $gameInfo = $userInfoService->getGameInfo($userId);
                $user->setFundsInfo($fundsInfo);
                $user->setGameInfo($gameInfo);

                $token->setUser($user);
                $result["user"] = $user->jsonSerialize();
                $tag == "black" ? $this->container->get('security.context')->setToken() : '';
            } else {
                $result["result"] = $response->result;
            }
        }

        $response = new Response();
        $response->setContent(json_encode($result));
        return $response;
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