<?php

namespace Galaxy\FrontendBundle\Entity;

use FOS\UserBundle\Entity\User as FOSUser;

class User extends FOSUser
{

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

}