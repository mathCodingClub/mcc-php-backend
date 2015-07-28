<?php

namespace mcc\obj\trialEvents\services;

class competitions extends \mcc\obj\service\base {

  const REPO = '\mcc\obj\trialEvents\repositories\competition';   
  
  static public function getByParticipationId($id){
    $participation = new \mcc\obj\trialEvents\repositories\participation($id);
    return new \mcc\obj\trialEvents\repositories\competition($participation->getcompetition_id());
  }
   
  static public function getParticipants($id){
    $competition = self::get($id);
    return $competition->getParticipants();    
  }
  
  static public function sort($competition) {
    if (!is_object($competition)) {
      $competition = new \mcc\obj\trialEvents\repositories\competition($competition);
    }
    $competition->sortParticipants();
    return $competition;
  }

}
