<?php

namespace Galaxy\FrontendBundle\Service;

class PrizeService
{

    public function getElementsPrize($prizeList, $basket)
    {
        $prizes = array();
        foreach ($prizeList as $prize) {
            foreach ($prize->elements as $element) {
                $element->basket = false;
                foreach ($basket as $item) {
                    if ($item->elementId == $element->id && $item->bought == true) {
                        $prizes[$prize->id] = array();
                        $element->basket = $item;
                        $prizes[$prize->id] = $prize;
                    }
                }
            }
        }
        return $prizes;
    }

}

