<?php

namespace mcc\obj\trialEvents\repositories;

class result extends \mcc\obj\repo\repobase {

  protected $participation_id_;
  protected $section_;
  protected $lap_;
  protected $penaltyPoints_;
  protected $notes_;

  const TABLE = 'events_results';

  public function insertOrUpdate($participation_id, $section, $lap, $penaltyPoints, $notes) {
    try {
      $this->initByArray(array('participation_id' => $participation_id,
          'section' => $section,
          'lap' => $lap));
    } catch (\mcc\obj\mccException $ex) {
      print $ex->getMessage();
      print $participation_id . ':' . $section . ':' . $lap . PHP_EOL;      
      $data = array('participation_id' => $participation_id,
          'section' => $section,
          'lap' => $lap,          
          'penaltyPoints' => $penaltyPoints, 
          'notes' => $notes);
      $id = $this->create($data);
      $this->initByid($id);
      return;
    }
    $this->setData(array('penaltyPoints' => $penaltyPoints, 'notes' => $notes));
  }

}
