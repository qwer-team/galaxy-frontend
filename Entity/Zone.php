<?php

namespace Galaxy\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zone
 */
class Zone
{

    /**
     * @var integer
     */
    private $minRadius;

    /**
     * @var integer
     */
    private $maxRadius;

    /**
     * @var integer
     */
    private $pointId;




    /**
     * Set minRadius
     *
     * @param integer $minRadius
     * @return Zone
     */
    public function setMinRadius($minRadius)
    {
        $this->minRadius = $minRadius;
    
        return $this;
    }

    /**
     * Get minRadius
     *
     * @return integer 
     */
    public function getMinRadius()
    {
        return $this->minRadius;
    }

    /**
     * Set maxRadius
     *
     * @param integer $maxRadius
     * @return Zone
     */
    public function setMaxRadius($maxRadius)
    {
        $this->maxRadius = $maxRadius;
    
        return $this;
    }

    /**
     * Get maxRadius
     *
     * @return integer 
     */
    public function getMaxRadius()
    {
        return $this->maxRadius;
    }

    /**
     * Set pointId
     *
     * @param integer $pointId
     * @return Zone
     */
    public function setPointId($pointId)
    {
        $this->pointId = $pointId;
    
        return $this;
    }

    /**
     * Get pointId
     *
     * @return integer 
     */
    public function getPointId()
    {
        return $this->pointId;
    }

}
