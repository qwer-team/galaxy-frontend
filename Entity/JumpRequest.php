<?php

namespace Galaxy\FrontendBundle\Entity;

/**
 * Description of JumpRequest
 *
 * @author root
 */
class JumpRequest
{

    private $x;
    private $y;
    private $z;
    private $superjump;

    public function getX()
    {
        return $this->x;
    }

    public function setX($x)
    {
        $this->x = $x;
    }

    public function getY()
    {
        return $this->y;
    }

    public function setY($y)
    {
        $this->y = $y;
    }

    public function getZ()
    {
        return $this->z;
    }

    public function setZ($z)
    {
        $this->z = $z;
    }

    public function getSuperjump()
    {
        return $this->superjump;
    }

    public function setSuperjump($superjump)
    {
        $this->superjump = $superjump;
    }

}