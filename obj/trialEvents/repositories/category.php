<?php

namespace mcc\obj\trialEvents\repositories;

class category extends \mcc\obj\repo\repobase {

  protected $event_id_;
  protected $category_;

  const TABLE = 'events_categories';

  public function getFullData() {
    $children = $this->getChildrencompetition()['obj'];
    $ar = array('category' => $this->getData(), 'competitions' => array());    
    foreach ($children as $child){      
      array_push($ar['competitions'],$child->getFullData());      
    }
    return $ar;    
  }
  
  public function setCompetitionNumbers(){
    $firstNumber = 1;
    $children = $this->getChildrenOrdersignup(array('time'))['obj'];
    foreach ($children as $child){
      $child->setcompNumber($firstNumber);      
      $firstNumber++;
    }    
  }

}
