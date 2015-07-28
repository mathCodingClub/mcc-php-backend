<?php

namespace mcc\obj\trialEvents\services;

class results {

  static public function save($data) {
    try {
      $result = new \mcc\obj\trialEvents\repositories\result();
      $result->initByArray(array('participation_id' => $data['participation_id'],
          'lap' => $data['lap'],
          'section' => $data['section']
      ));
      if ($data['penaltyPoints'] == '') {
        // remove
        \mcc\obj\trialEvents\repositories\result::delete($result->getid());
        $result = null;
      } else {
        $result->setpenaltyPoints($data['penaltyPoints']);
      }
    } catch (\mcc\obj\mccException $e) {
      $result = new \mcc\obj\trialEvents\repositories\result($data);
    }
    return $result;
  }

}
