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

    public function jumpAction(Request $request)
    {
        $jumpRequest = new JumpRequest();
        $form = $this->createForm(new JumpRequestType(), $jumpRequest);
        
        $form->bind($request);
        $result = array("result" => "fail");
        if($form->isValid()){
            $jumpUrl = $this->get("service_container")->getParameter("jump.url");
            
            $user = $this->getUser();
            $data = array(
                "x" => $jumpRequest->getX(),
                "y" => $jumpRequest->getY(),
                "z" => $jumpRequest->getZ(),
                "superjump" => $jumpRequest->getSuperjump(),
                "userId" => $user->getId(),
            );
            
            $response = json_decode($this->makeRequest($jumpUrl, $data));
            $result = array("result" => $response->result, "req" => 1);
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