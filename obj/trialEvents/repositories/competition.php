<?php

namespace mcc\obj\trialEvents\repositories;

class competition extends \mcc\obj\repo\repobase {

  // database variables end with _
  protected $category_id_;
  protected $competition_;

  /**
   * @type: timestamp
   */
  protected $startTime_;
  protected $ridingTime_;
  protected $sections_;
  protected $laps_;
  //
  protected $sectionsArray = null;

  const TABLE = 'events_competitions';

  protected function getData() {
    $data = parent::getData();
    $data['sections'] = $this->getSectionsArray();
    return $data;
  }

  public function getFullData() {
    $children = $this->getChildrenOrderparticipation(array('position'))['obj'];
    $comp = $this->getData();
    $ar = array('competition' => $comp, 'results' => array());
    foreach ($children as $child) {
      $rider = new rider($child->getrider_id());
      $results = $child->getResultsArray();
      $data = array('participation' => $child->getData(),
          'results' => $results,
          'rider' => $rider->getData());
      array_push($ar['results'], $data);
    }
    return $ar;
  }

  public function getParticipants() {
    $children = $this->getChildrenOrderparticipation(array('position'))['obj'];
    $ar = array();
    foreach ($children as $child) {
      $rider = new rider($child->getrider_id());
      $data = array('participation' => $child->getData(),
          'rider' => $rider->getData());
      array_push($ar, $data);
    }
    $sortFun = function($a, $b) {
      if ($a['rider']['familyName'] != $b['rider']['familyName']) {
        return ($a['rider']['familyName'] < $b['rider']['familyName']) ? -1 : 1;
      }
      return ($a['rider']['givenName'] < $b['rider']['givenName']) ? -1 : 1;
    };
    usort($ar,$sortFun);    
    return $ar;
  }

  public function hasLap($lap) {
    if ($lap > $this->getlaps() || $lap < 1) {
      throw new \mcc\obj\mccException(array('msg' => 'This competition does not have given lap.'));
    }
    return true;
  }

  public function hasSection($section) {
    $sections = $this->getSectionsArray();
    if (array_search($section, $sections) === false) {
      throw new \mcc\obj\mccException(array('msg' => 'This competition does not have given section.'));
    }
    return true;
  }

  public function set($data) {
    if (array_key_exists('sections', $data)) {
      sort($data['sections']);
      $data['sections'] = implode(',', $data['sections']);
    }
    parent::setData($data);
  }

  public function sortParticipants() {
    $participants = $this->getChildrenparticipation()['obj'];
    foreach ($participants as $participant) {
      $participant->initChildrenresult();
    }
    $sortFun = $this->getNamespace() . 'participation::compare';
    usort($participants, $sortFun);
    foreach ($participants as $key => $participant) {
      $pos = $key + 1;
      $points = $participant->getPenaltiesFromResults();
      $participant->set(array('position' => $pos, 'penalties' => $points));
    }
  }

  public function getSectionsArray() {
    $this->initSections();
    return $this->sectionArray;
  }

  private function initSections() {
    if (is_null($this->sectionsArray)) {
      $sect = $this->getsections();
      if (strlen($sect) > 0) {
        $this->sectionArray = explode(',', $sect);
      } else {
        $this->sectionArray = array(1, 2, 3, 4, 5, 6);
      }
    }
  }

  protected function reset() {
    $this->sectionsArray = null;
  }

}
