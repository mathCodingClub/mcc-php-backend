<?php

namespace mcc\obj\trialEvents\repositories;

class participation extends \mcc\obj\repo\repobase {

  protected $rider_id_;
  protected $competition_id_;
  protected $signup_id_;
  protected $handicap_;
  protected $compNumber_;
  protected $ridingTime_;
  protected $penalties_;
  protected $penaltiesExtra_;
  protected $position_;
  protected $notes_;
  protected $startTime_;
  //
  protected $children_result = array();

  const TABLE = 'events_participations';

  public function getResultsArray() {
    $results = $this->getChildrenresult()['obj'];
    $ar = array();
    foreach ($results as $result) {
      $ar['lap' . $result->getlap() . 'section' . $result->getsection()] = $result->getData();
    }
    return $ar;
  }

  public function getPenaltiesFromResults() {
    $pen = $this->penaltiesExtra_ + $this->handicap_;
    foreach ($this->children_result as $result) {
      $pen += $result->getpenaltyPoints();
    }
    return $pen;
  }

  public function getNumberOfSectionsWith($penaltyPoints) {
    $sum = 0;
    foreach ($this->children_result as $result) {
      if ($result->getpenaltyPoints() == $penaltyPoints) {
        $sum++;
      }
    }
    return $sum;
  }

  static public function compare($a, $b) {
    $apoints = $a->getPenaltiesFromResults();
    $bpoints = $b->getPenaltiesFromResults();
    if ($apoints != $bpoints) {
      return ($apoints < $bpoints) ? -1 : 1;
    }
    for ($k = 0; $k < 5; $k++) {
      $asect = $a->getNumberOfSectionsWith($k);
      $bsect = $b->getNumberOfSectionsWith($k);
      if ($asect != $bsect) {
        return ($asect > $bsect) ? -1 : 1;
      }
    }
    $atime = $a->getridingTime();
    $btime = $b->getridingTime();
    if ($atime != $btime) {
      return ($atime < $btime) ? -1 : 1;
    }
    $anumber = $a->getcompNumber();
    $bnumber = $b->getcompNumber();
    if ($anumber != $bnumber) {
      return ($anumber < $bnumber) ? -1 : 1;
    }
    // last possibility
    return -1;
  }

}
