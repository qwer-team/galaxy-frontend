<?php

namespace Galaxy\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class QuestionController extends Controller
{

    public function resetQuestionAction($questionId, $result)
    {
        $service = $this->get("memcache.default");
        $questionKey = $this->get("service_container")
                ->getParameter("question.memcache.key");
        $key = $questionKey . $questionId;
        $service->set($key, $result, 24 * 3600);

        $response = new Response();
        $response->setContent(json_encode(array("result" => "success")));
        return $response;
    }

}