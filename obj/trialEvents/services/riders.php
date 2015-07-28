<?php

namespace mcc\obj\trialEvents\services;

class riders extends \mcc\obj\service\base {

  const REPO = '\mcc\obj\trialEvents\repositories\rider';

  static public function getRider($familyName, $givenName) {
    try {
      $rider = new \mcc\obj\trialEvents\repositories\rider();
      $rider->initByfamilyName($familyName);
      if ($rider->getgivenName() != $givenName) {
        throw new \mcc\obj\mccException();
      }
    } catch (\mcc\obj\mccException $e) {
      $rider = new \mcc\obj\trialEvents\repositories\rider(array('familyName' => $familyName, 'givenName' => $givenName));
    }
    return $rider;
  }

  static public function getRiderForSignUp($signup) {
    if (!is_object($signup)) {
      $signup = new \mcc\obj\trialEvents\repositories\signup($signup);
    }
    $familyName = $signup->getfamilyName();
    $givenName = $signup->getgivenName();
    return riders::getRider($familyName, $givenName);
  }

}
