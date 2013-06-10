<?php

namespace Galaxy\FrontendBundle\Entity;

use FOS\UserBundle\Entity\User as FOSUser;

class User extends FOSUser // implements  \JsonSerializable
{
    protected $gameInfo;
    protected $fundsInfo;
    protected $sessionJump = 0;
    
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getGameInfo()
    {
        return $this->gameInfo;
    }

    public function setGameInfo($gameInfo)
    {
        $this->gameInfo = $gameInfo;
    }

    public function getFundsInfo()
    {
        return $this->fundsInfo;
    }

    public function setFundsInfo($fundsInfo)
    {
        $this->fundsInfo = $fundsInfo;
    }
    
    public function getSessionJump()
    {
        return $this->sessionJump;
    }

    public function setSessionJump($sessionJump)
    {
        $this->sessionJump = $sessionJump;
    }

    
    public function serialize()
    {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->gameInfo,
            $this->fundsInfo,
            $this->sessionJump,
        ));
    }
    
     public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->gameInfo,
            $this->fundsInfo,
            $this->sessionJump,
        ) = $data;
    }

    public function jsonSerialize()
    {
        $res = array();
        foreach($this as $key => $value){
            $res[$key] = $value;
        }
        return $res;
    }
}