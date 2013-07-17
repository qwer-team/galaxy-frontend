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

    /**
     * @Template()
     */
    public function indexAction()
    {
        $pointService = $this->get("point.service");
        $messageType = $pointService->getMessageType();
        return array(
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
        $user = $this->getUser();
        $userId = $user->getId();
        $gameService = $this->get("game.service");
        $response = $this->getDebitFunds($userId, $this->deposite);
        print_r($response);
        if ($response->result == 'success') {
            $response = $gameService->increaseUserCountMessages($userId);
            $url = $this->generateUrl('store');
        }
        return $this->redirect($url);
    }

    public function activeToDepositeAction(Request $request)
    {
        $value = $request->get('value');
        $response = new Response();
        $user = $this->getUser();
        $userId = $user->getId();
        $response = $this->activeToDepositeFunds($userId, $value);
        print_r($response);
        if ($response->result == 'success') {
        $response->setContent(array("result" => "success"));
        return $response;
        }
    }

    public function depositeToActiveAction(Request $request)
    {
        $value = $request->get('value');
        $user = $this->getUser();
        $userId = $user->getId();
        $response = $this->depositeToActiveFunds($userId, $value);
        print_r($response);
        if ($response->result == 'success') {
            return array(
                "result" => "success"
            );
        }
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

    private function getTransFunds($userId)
    {
        $pointService = $this->get("point.service");
        $documentsService = $this->get("documents.service");
        $messageType = $pointService->getMessageType();
        $cost = $messageType->cost;
        $data = array(
            'OA1' => $userId,
            'summa1' => $cost,
            'account' => 1
        );
        $response = $documentsService->transFunds($data);
        return $response;
    }

    private function getDebitFunds($userId, $account)
    {
        $pointService = $this->get("point.service");
        $documentsService = $this->get("documents.service");
        $messageType = $pointService->getMessageType();
        $cost = $messageType->cost;
        $data = array(
            'OA1' => $userId,
            'summa1' => $cost,
            'account' => $account
        );
        $response = $documentsService->debitFunds($data);
        return $response;
    }

    private function activeToDepositeFunds($userId, $value)
    {
        $documentsService = $this->get("documents.service");
        $data = array(
            'OA1' => $userId,
            'summa1' => $value,
            'account' => 1
        );
        $response = $documentsService->debitFunds($data);
        if ($response->result == 'success') {
            $dataTrans = array(
                'OA1' => $userId,
                'summa1' => $this->activeToDepositeRates($value),
                'account' => 5
            );
            $documentsService->transFunds($dataTrans);
        }
        return $response;
    }

    private function depositeToActiveFunds($userId, $value)
    {
        $documentsService = $this->get("documents.service");
        $data = array(
            'OA1' => $userId,
            'summa1' => $value,
            'account' => 3
        );
        $response = $documentsService->debitFunds($data);
        if ($response->result == 'success') {
            $dataTrans = array(
                'OA1' => $userId,
                'summa1' => $this->depositeToActiveRates($value),
                'account' => 4
            );
            $documentsService->transFunds($dataTrans);
        }
        return $response;
    }

    private function activeToDepositeRates($value)
    {
        $user = $this->getUser();
        $fundsInfo = $user->getFundsInfo();
        $transferValue = $value * 0.76;
        return $transferValue;
    }
    
    private function depositeToActiveRates($value)
    {
        $user = $this->getUser();
        $fundsInfo = $user->getFundsInfo();
        $transferValue = $value * 0.5;
        return $transferValue;
    }

}