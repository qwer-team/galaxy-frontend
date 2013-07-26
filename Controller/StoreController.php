<?php

namespace Galaxy\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Galaxy\FrontendBundle\Form\MessageType;

/**
 * Description of StoreController
 *
 * @author root
 */
class StoreController extends Controller
{

    private $active = 1;
    private $deposite = 3;
    private $safe = 2;
    private $transSafe = 5;
    private $transActive = 4;

    /**
     * @Template()
     */
    public function indexAction()
    {
        $pointService = $this->get("point.service");
        $gameService = $this->get("game.service");
        $fliperList = $gameService->getFlippersList();
        $messageType = $pointService->getMessageType();

        return array(
            "flippers" => $fliperList,
            "messageType" => $messageType,
        );
    }

    /**
     * @Template()
     */
    public function newMessageAction()
    {
        $user = $this->getUser();
        $form = $this->getMessageForm();
        return array(
            "form" => $form->createView(),
            "user" => $user->jsonSerialize(),
        );
    }

    /**
     * @Template("GalaxyFrontendBundle:Store:newMessage.html.twig")
     */
    public function createMessageAction(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $infoService = $this->get("info.service");
        $storage = $this->get("storage");
        $form = $this->getMessageForm();
        $form->bind($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $data['userId'] = $userId;
            $img = $data['imgfile'];
            if (!is_null($img)) {
                $path = $storage->saveImage($img);
                $data['img'] = $path;
                unset($data['imgfile']);
            }
            $response = $this->getDebitFunds($userId, $this->active);
            print_r($response);
            if ($response->result == 'success') {
                $resp = $infoService->createMessage($data);
            }

            $id = $resp->message->id;
            $this->updateFunds();
            $url = $this->generateUrl('store');
            return $this->redirect($url);
        } else {
            echo $form->getErrorsAsString();
        }
        return array(
            "form" => $form->createView(),
        );
    }

    public function buyMessageAction()
    {
        $response = new Response();
        $user = $this->getUser();
        $userId = $user->getId();
        $gameService = $this->get("game.service");
        $responseDebit = $this->messageCountDebitFunds($userId, $this->deposite);
        if ($responseDebit->result == 'success') {
            $responseFunds = (array) $gameService->increaseUserCountMessages($userId);
            $this->updateFunds();
            $responseFunds['user'] = $this->updateFunds();
            $responseFunds['result'] = "success";
            $response->setContent(json_encode($responseFunds));
            return $response;
        }
    }

    public function buyFlipperAction(Request $request)
    {
        $response = new Response();
        $user = $this->getUser();
        $userId = $user->getId();
        $buyFlipper = $request->get('flipperId');
        $responseFlipper = $this->buyFlipper($userId, $buyFlipper);
        $responseFunds['result'] = "fail";
        if ($responseFlipper) {
            $responseFunds['user'] = $this->updateFunds();
            $responseFunds['result'] = "success";
        }
        $response->setContent(json_encode($responseFunds));
        return $response;
    }

    public function activeToSafeAction(Request $request)
    {
        $value = $request->get('value');
        $response = new Response();
        $user = $this->getUser();
        $userId = $user->getId();
        $responseFunds = $this->transferFunds($userId, $value, $this->active, $this->transSafe);
        $responseFunds['user'] = $this->updateFunds();
        $response->setContent(json_encode($responseFunds));
        return $response;
    }

    public function depositeToActiveAction(Request $request)
    {
        $response = new Response();
        $value = $request->get('value');
        $user = $this->getUser();
        $userId = $user->getId();
        $responseFunds = $this->transferFunds($userId, $this->depositeToActiveRates($value), $this->active, $this->deposite);
        $responseFunds['user'] = $this->updateFunds();
        $response->setContent(json_encode($responseFunds));
        return $response;
    }

    public function safeToActiveAction(Request $request)
    {
        $response = new Response();
        $value = $request->get('value');
        $user = $this->getUser();
        $userId = $user->getId();
        $responseFunds = $this->transferFunds($userId, $value, $this->safe, $this->transActive);
        $responseFunds['user'] = $this->updateFunds();
        $response->setContent(json_encode($responseFunds));
        return $response;
    }

    private function updateFunds()
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $userInfoService = $this->get("galaxy.user_info.service");
        $fundsInfo = $userInfoService->getFundsInfo($userId);
        $gameInfo = $userInfoService->getGameInfo($userId);
        $user->setFundsInfo($fundsInfo);
        $user->setGameInfo($gameInfo);
        return $user->jsonSerialize();
    }

    private function getMessageForm($message = null, $template = array())
    {
        $infoService = $this->get("info.service");
        $form = new MessageType();

        $themes = $infoService->getThemesList();
        foreach ($themes as $theme) {
            $themesArr[$theme->id] = $theme->title;
        }
        $form->setThemes($themesArr);
        $data = array("answers" => array(
                array("answer" => ""),
            ),
        );
        $data += $template;

        return $this->createForm($form, $message === null ? $data : $message);
    }

    private function messageCountDebitFunds($userId, $account)
    {
        $pointService = $this->get("point.service");
        $documentsService = $this->get("documents.service");
        $messageType = $pointService->getMessageType();
        $cost = $messageType->messCost;
        $data = array(
            'OA1' => $userId,
            'summa1' => $cost,
            'account' => $account
        );
        $response = $documentsService->debitFunds($data);
        return $response;
    }

    private function transferFunds($userId, $value, $from, $to)
    {
        $documentsService = $this->get("documents.service");
        $userFunds = $documentsService->getFunds($userId);
        $response = array("result" => "fail");
        if ($userFunds->active >= $value) {
            $data = array(
                'OA1' => $userId,
                'summa1' => $value,
                'account' => $from
            );
            $debitResponse = $documentsService->debitFunds($data);
            if ($debitResponse->result == 'success') {
                $dataTrans = array(
                    'OA1' => $userId,
                    'summa1' => $value,
                    'account' => $to
                );
                $transFunds = $documentsService->transFunds($dataTrans);
                $response["result"] = $transFunds->result;
            }
        }
        return $response;
    }

    private function depositeToActiveRates($value)
    {
        $user = $this->getUser();
        $fundsInfo = $user->getFundsInfo();
        $transferValue = $value * 0.76;
        return $transferValue;
    }

    private function buyFlipper($userId, $flipperId)
    {
        $userInfoService = $this->get("galaxy.user_info.service");
        $gameService = $this->get("game.service");
        $documentsService = $this->get("documents.service");
        $buyFlipper = $gameService->getFlipper($flipperId);
        $fundsInfo = $userInfoService->getFundsInfo($userId);
        $gameInfo = $userInfoService->getGameInfo($userId);
        $currentFlipperId = $gameInfo->flipper->id;
        if ($flipperId > 2
                && $buyFlipper->countRentMess > $gameInfo->countMessages
                || $flipperId - $currentFlipperId != 1
                || $fundsInfo->active < $buyFlipper->rentCost) {
            return false;
        } else {
            $data = array(
                'OA1' => $userId,
                'summa1' => $buyFlipper->rentCost,
                'account' => 1
            );
            $documentsService->debitFunds($data);
            $userInfoService->increaseFlipper($gameInfo->id, $flipperId);
            return true;
        }
    }

}