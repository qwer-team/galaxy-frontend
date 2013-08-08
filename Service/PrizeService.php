<?php

namespace Galaxy\FrontendBundle\Service;

class PrizeService
{

    public function getElementsPrize($prizeList, $basket, $fundsInfo)
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
                if($element->basket){
                    if($element->account == 1){
                        $rate = 1;
                    } else {
                        $acc = "3";
                        $rate = $fundsInfo->rates->$acc;
                    }
                    $cost = $element->price * ($element->basket->jumpsRemain / $element->available) * $rate * (1 - ($prize->penalty / 100));
                    $element->cost = ceil($cost);
                }
            }
        }
        return $prizes;
    }

}

