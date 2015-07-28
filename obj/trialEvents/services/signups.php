<?php

namespace mcc\obj\trialEvents\services;

class signups extends \mcc\obj\service\base {

  const REPO = '\mcc\obj\trialEvents\repositories\signup';

  static public function setToCompetition($signup, $competition) {
    if (!is_object($signup)) {
      $signup = new \mcc\obj\trialEvents\repositories\signup($signup);
    }
    if (!is_object($competition)) {
      $competition = new \mcc\obj\trialEvents\repositories\competition($competition);
    }
        
    list($participation,$rider) = participations::addSignup($competition,$signup);
    
    return array(
        'rider' => $rider->get(),
        'participation' => $participation->get(),
        'signup' => $signup->get()
    );
  } 

}
