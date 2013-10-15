<?php

namespace Galaxy\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Galaxy\FrontendBundle\Form\JumpRequestType;
use Galaxy\FrontendBundle\Entity\JumpRequest;
use Qwer\Curl\Curl;
use Galaxy\FrontendBundle\Entity\Zone;

class FlipperController extends Controller {

    private $accounts = array(
        "1" => "active",
        "2" => "safe",
        "3" => "deposite"
    );

    /**
     * @Template()
     */
    public function indexAction() {
        return array();
    }

    public function mainAction() {
        return $this->redirect($this->generateUrl('flipper'));
    }

    public function getUserAction() {
        $user = $this->getUser();
        $content = array("result" => "fail");
        $status = 401;
        if ($user) {
            $content = $user->jsonSerialize();
            $status = 200;
        }
        $response = new Response();
        $response->setContent(json_encode($content));
        $response->setStatusCode($status);
        return $response;
    }

    public function deleteZoneAction() {
        $response = new Response();
        $user = $this->getUser();
        $gameInfo = $user->getGameInfo();
        $gameService = $this->container->get("game.service");
        $responseRadar['result'] = 'fail';
        if ($gameService->resetUserInfoRadar($gameInfo->id)) {
            $responseRadar['result'] = 'success';
        }
        $responseRadar['user'] = $this->updateFunds();
        $response->setContent(json_encode($responseRadar));
        return $response;
    }

    public function radarAction(Request $request) {
        $response = new Response();
        $user = $this->getUser();
        $gameInfo = $user->getGameInfo();
        $gameService = $this->container->get("game.service");
        $pointType = $request->get('pointType');
        $responseDebit = $this->radarDebitFunds($user->getId(), $gameInfo->flipper);
        if ($responseDebit) {
            $gameService->resetUserInfoRadar($gameInfo->id);
            $responseRadar = (array) $gameService->radarStart($gameInfo->id, $pointType);
        }
        $responseRadar['user'] = $this->updateFunds();
        $response->setContent(json_encode($responseRadar));
        return $response;
    }

    private function radarDebitFunds($userId, $flipper) {
        $documentsService = $this->container->get("documents.service");
        $userFunds = (array) $documentsService->getFunds($userId);
        $cost = $flipper->radarCost;
        $accountId = $flipper->radarSpec ? 3 : 1;
        $accountTitle = $this->accounts[$accountId];
        $response = false;
        if ($userFunds[$accountTitle] > $cost) {
            $data = array(
                'OA1' => $userId,
                'summa1' => $cost,
                'account' => $accountId
            );
            $response = $documentsService->debitFunds($data);
        }
        return $response;
    }

    public function getPrizesInfoAction() {
        $session = $this->getRequest()->getSession();
        if ($session->has('prize_elements')) {
            $prizesElements = $session->get('prize_elements');
        } else {
            $service = $this->get("galaxy.user_info.service");
            $prizesElements = $service->getPrizeInfo();
            $session->set('prize_elements', $prizesElements);
        }
        $callback = function($item) {
                    return $item["prizeName"];
                };
        $result = array_count_values(array_map($callback, $prizesElements));
        $prizesElements["count"] = $result;
        $response = new Response();
        $response->setContent(json_encode($prizesElements));
        return $response;
    }

    /**
     * @Template("GalaxyFrontendBundle:Flipper:logs.html.twig")
     */
    public function getUserLogsAction($page, $length) {
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
    public function getUserBasketAction() {
        $user = $this->getUser();
        $userInfoService = $this->container->get("galaxy.user_info.service");
        $prizeService = $this->container->get("galaxy.prize.service");
        $basket = $user->getGameInfo()->basket;
        $fundsInfo = $user->getFundsInfo();
        $prizeList = $userInfoService->getPrizesFromSpace();
        $buyElementsPrize = $prizeService->getElementsPrize($prizeList, $basket, $fundsInfo);
        $data = array(
            "items" => $buyElementsPrize,
        );

        return $data;
    }

    /**
     * @Template("GalaxyFrontendBundle:Flipper:basketSell.html.twig")
     */
    public function getUserBasketSellAction() {
        $user = $this->getUser();
        $userInfoService = $this->container->get("galaxy.user_info.service");
        $prizeService = $this->container->get("galaxy.prize.service");
        $basket = $user->getGameInfo()->basket;
        $prizeList = $userInfoService->getPrizesFromSpace();
        $buyElementsPrize = $prizeService->getElementsPrize($prizeList, $basket, $user->getFundsInfo());
        $moneyYok = !$this->checkMoneyForJump($user);

        $data = array(
            "items" => $buyElementsPrize,
            "moneyYok" => $moneyYok,
        );

        return $data;
    }

    private function checkMoneyForJump($user) {
        $flipper = $user->getGameInfo()->flipper;
        $funds = $user->getFundsInfo();

        if ($flipper->paymentFromDeposit) {
            if ($flipper->costJump > $funds->deposite) {
                return false;
            }
        } else {
            if ($flipper->costJump > $funds->active) {
                return false;
            }
        }
        return true;
    }

    public function jumpAction(Request $request) {
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
            //echo $this->makeRequest($jumpUrl, $data);
            $response = json_decode($this->makeRequest($jumpUrl, $data));
            $result = array("result" => "success", "req" => $response);
            if ($response->result == "success") {
                //print_r($response);
                $result["pointType"] = $response->response->type->name;
                $tag = $response->response->type->tag;
                $image = isset($response->response->type->image) ? $response->response->type->image : "";
                $result["tag"] = $tag;
                $result["image"] = $image;
                if($tag == "message")
                {
                    $result["message"] = $response->response->message;
                }
                $pointImageFolder = $this->container->getParameter("image.folder");
                $imagePath = str_replace("{tag}", $tag, $pointImageFolder);
                $result["pointImagePath"] = $imagePath;
                $result["user"] = $this->updateFunds();
                $tag == "black" ? $this->container->get('security.context')->setToken() : '';
            } else {
                $result["result"] = $response->result;
            }
        }

        $response = new Response();
        $response->setContent(json_encode($result));
        return $response;
    }

    public function buyElementAction() {
        $rawUrl = $this->get("service_container")->getParameter("buy_element.url");
        $token = $this->container->get('security.context')->getToken();
        $user = $token->getUser();

        $url = str_replace("{userId}", $user->getId(), $rawUrl);
        $json = json_decode($this->makeRequest($url));

        $result = array('result' => 'fail');
        if ($json->result == 'success') {
            $userId = $user->getId();
            $result['result'] = 'success';
            $userInfoService = $this->get("galaxy.user_info.service");
            $fundsInfo = $userInfoService->getFundsInfo($userId);
            $gameInfo = $userInfoService->getGameInfo($userId);
            $user->setFundsInfo($fundsInfo);
            $user->setGameInfo($gameInfo);
            $token->setUser($user);

            $info = $userInfoService->getPrizesFromSpace();
            $result["prize"] = null;
            $prize = $this->checkAllPrize($info, $gameInfo->basket, $json->elementId);
            if ($prize) {
                $result["prize"] = $prize;
                $this->sendMessage($prize, $user->getEmail());
            }

            $result["user"] = $user->jsonSerialize();
        }

        $response = new Response();
        $response->setContent(json_encode($result));
        return $response;
    }

    public function getQuestionAction() {
        $token = $this->container->get('security.context')->getToken();
        $user = $token->getUser();
        $game = $user->getGameInfo();

        $result = array("result" => "fail");
        if ($game->questions) {
            $userService = $this->container->get("galaxy.user.service");
            $user = $this->getUser();
            $response = $userService->getUser($user->getUsername());

            $expires = null;
            $curdate = new \DateTime;
            if (isset($response->data->locked_expires_at) &&
                    $response->data->locked_expires_at) {
                $expires = new \DateTime($response->data->locked_expires_at);
            }
            if ($expires && $curdate < $expires) {
                $this->container->get('security.context')->setToken();
            } else {
                $service = $this->get("galaxy.user_info.service");
                $userId = $user->getId();
                $fundsInfo = $service->getFundsInfo($userId);
                $user->setFundsInfo($fundsInfo);
                $result = $service->getQuestion($userId);
                if (isset($result->messageId)) {
                    $result->rightAnswer = null;
                    $time = new \DateTime($result->started);
                    $now = new \DateTime();
                    $interval = $now->diff($time);
                    $intervalSeconds = $interval->i * 60 + $interval->s;
                    $questionSeconds = $result->seconds;
                    $seconds = $result->seconds - $intervalSeconds;

                    $result->interval = $intervalSeconds;
                    $result->seconds = $seconds;
                    $result->procent = (int) ($seconds / $questionSeconds * 100);
                } else {
                    $this->updateFunds();
                }
            }
        }

        $response = new Response();
        $response->setContent(json_encode($result));
        return $response;
    }

    public function answerQuestionAction($questionId, $answer) {
        $token = $this->container->get('security.context')->getToken();
        $user = $token->getUser();
        $game = $user->getGameInfo();
        $hasQuestion = false;
        if ($game->questions) {
            foreach ($game->questions as $question) {
                if ($question->id == $questionId) {
                    $hasQuestion = true;
                    break;
                }
            }
        }
        $result = array("result" => "fail");
        if ($hasQuestion) {
            $service = $this->get("galaxy.user_info.service");
            $result = $service->answerQuestion($questionId, $answer);
            $user = $this->getUser();
            $userService = $this->get("galaxy.user.service");
            $response = $userService->getUser($user->getUsername());
            $expires = null;
            $curdate = new \DateTime;
            if (isset($response->data->locked_expires_at) &&
                    $response->data->locked_expires_at) {
                $expires = new \DateTime($response->data->locked_expires_at);
            }
            if ($expires && $curdate < $expires) {
                $result = array("result" => "disabled");
                $this->container->get('security.context')->setToken();
            } else {
                $result->user = $this->updateFunds();
            }
        }
        $response = new Response();
        $response->setContent(json_encode($result));
        return $response;
    }

    private function sendMessage($prize, $email) {
        $message = \Swift_Message::newInstance()
                ->setSubject('prize')
                ->setFrom('gala@gala.com')
                ->setTo($email)
                ->setBody($prize)
        ;
        $this->get('mailer')->send($message);
    }

    private function checkAllPrize($info, $basket, $elementId) {
        $prizeCur = null;
        foreach ($info as $prize) {
            foreach ($prize->elements as $element) {
                if ($element->id == $elementId) {
                    $prizeCur = $prize;
                    break;
                }
            }
            if ($prizeCur) {
                break;
            }
        }
        $basketIds = array();
        foreach ($basket as $item) {
            if ($item->bought) {
                $basketIds[] = $item->elementId;
            }
        }
        $all = true;
        foreach ($prizeCur->elements as $element) {
            if (!in_array($element->id, $basketIds)) {
                $all = false;
                break;
            }
        }
        if ($all) {
            return $prizeCur->name;
        }
        return null;
    }

    private function makeRequest($url, $data = null) {
        return Curl::makeRequest($url, $data);
    }

    public function checkQuestionAction($questionId) {
        $token = $this->container->get('security.context')->getToken();
        $user = $token->getUser();
        $game = $user->getGameInfo();
        $hasQuestion = false;
        if ($game->questions) {
            foreach ($game->questions as $question) {
                if ($question->id == $questionId) {
                    $hasQuestion = true;
                    break;
                }
            }
        }

        $questionService = $this->get("question.service");
        $seconds = 15;
        $result = $questionService->listenResult($questionId, $seconds);
        $resArr = array("result" => $result);
        if (!is_null($result)) {
            $userInfoService = $this->get("galaxy.user_info.service");
            $userId = $user->getId();
            $gameInfo = $userInfoService->getGameInfo($userId);
            $fundsInfo = $userInfoService->getFundsInfo($userId);
            $user->setGameInfo($gameInfo);
            $user->setFundsInfo($fundsInfo);
            if ($result == 2) {
                $resArr['result'] = 'secondo';
                sleep(3);
                $userService = $this->container->get("galaxy.user.service");
                $response = $userService->getUser($user->getUsername());

                $expires = null;
                $curdate = new \DateTime;
                if (isset($response->data->locked_expires_at) &&
                        $response->data->locked_expires_at) {
                    $expires = new \DateTime($response->data->locked_expires_at);
                }
                if ($expires && $curdate < $expires) {
                    $this->container->get('security.context')->setToken();
                    $resArr['result'] = "disabled";
                }
            }
        }
        $response = new Response();
        $response->setContent(json_encode($resArr));
        return $response;
    }

    private function updateFunds() {
        $token = $this->container->get('security.context')->getToken();
        $user = $user = $token->getUser();
        $userId = $user->getId();
        $userInfoService = $this->get("galaxy.user_info.service");
        $fundsInfo = $userInfoService->getFundsInfo($userId);
        $gameInfo = $userInfoService->getGameInfo($userId);
        $user->setFundsInfo($fundsInfo);
        $user->setGameInfo($gameInfo);
        $token->setUser($user);
        return $user->jsonSerialize();
    }

}

