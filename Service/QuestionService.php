<?php

namespace Galaxy\FrontendBundle\Service;

use Lsw\MemcacheBundle\Cache\AntiDogPileMemcache;

class QuestionService
{

    /**
     *
     * @var \Lsw\MemcacheBundle\Cache\AntiDogPileMemcache 
     */
    private $memcache;

    /**
     *
     * @var string 
     */
    private $questionMemcacheKey;

    public function listenResult($questionId, $seconds)
    {
        $key = $this->questionMemcacheKey . $questionId;
        for ($i = 0; $i <= $seconds; $i++) {
            $result = $this->memcache->get($key);
            if ($result) {
                return $result;
            } else {
                sleep(1);
            }
        }
    }

    public function setMemcache($memcache)
    {
        $this->memcache = $memcache;
    }

    public function setQuestionMemcacheKey($questionMemcacheKey)
    {
        $this->questionMemcacheKey = $questionMemcacheKey;
    }

}