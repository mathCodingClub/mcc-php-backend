<?php

namespace mcc\obj\trialEvents\services;

class participations extends \mcc\obj\service\base {

  const REPO = '\mcc\obj\trialEvents\repositories\participation';

  static public function addRider($competition, $rider) {
    if (!is_object($competition)) {
      $competition = new \mcc\obj\trialEvents\repositories\competition($competition);
    }
    if (!is_object($rider)) {
      $rider = new \mcc\obj\trialEvents\repositories\rider($rider);
    }
    $participation = new \mcc\obj\trialEvents\repositories\participation(
            array('rider_id' => $rider->getid(),
        'competition_id' => $competition->getid(),
        'signup_id' => null));
    return $participation;
  }

  static public function addSignup($competition, $signup) {
    if (!is_object($competition)) {
      $competition = new \mcc\obj\trialEvents\repositories\competition($competition);
    }
    if (!is_object($signup)) {
      $signup = new \mcc\obj\trialEvents\repositories\signup($signup);
    }
    $rider = riders::getRiderForSignUp($signup);
    $participation = new \mcc\obj\trialEvents\repositories\participation(
            array('rider_id' => $rider->getid(),
        'competition_id' => $competition->getid(),
        'signup_id' => $signup->getid())
    );
    return array($participation, $rider);
  }

}
