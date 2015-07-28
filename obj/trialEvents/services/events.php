<?php

namespace mcc\obj\trialEvents\services;

class events extends \mcc\obj\service\base {

  const REPO = '\mcc\obj\trialEvents\repositories\event';

  static public function create($data) {
    $entry = array('name' => $data['name']);
    \mcc\obj\utils\ar::populatIfNotNull($entry, $data, array(
        'startTime',
        'isPublic',
        'description',
        'endTime',
        'signupStart',
        'signupEnd'));
    $event = self::create_($entry);
    return $event;
  }

  static public function getAllSignups($id) {
    $event = new \mcc\obj\trialEvents\repositories\event($id);
    $signups = $event->getSignUps();
    return \mcc\obj\utils\ar::convertObjectsToArray($signups, 'get');
  }

  static public function getByCompetitionId($id) {
    $competition = new \mcc\obj\trialEvents\repositories\competition($id);
    $category = new \mcc\obj\trialEvents\repositories\category($competition->getcategory_id());
    return $category->getevent_id();
  }

  static public function getCategoriesAndCompetitions($event) {
    if (!is_object($event)) {
      $event = new \mcc\obj\trialEvents\repositories\event($event);
    }
    $categories = $event->getChildrencategory()['obj'];
    $data = \mcc\obj\utils\ar::convertObjectsToArray($categories, 'get');
    foreach ($categories as $key => $cat) {
      $comp = $cat->getChildrencompetition()['obj'];
      $compData = \mcc\obj\utils\ar::convertObjectsToArray($comp, 'get');
      $data[$key]['competitions'] = $compData;
    }
    return $data;
  }

  static public function getCategoryByName($event, $categoryName) {
    if (!is_object($event)) {
      $event = new \mcc\obj\trialEvents\repositories\event($event);
    }
    $category = new \mcc\obj\trialEvents\repositories\category();
    $category->initByArray(array('event_id' => $event->getid(),
        'category' => $categoryName));
    return $category;
  }

  static public function getEvents($json = true) {
    $db = \mcc\obj\sql::get();
    $data = $db->getByPagination(\mcc\obj\trialEvents\repositories\event::TABLE);
    $events = \mcc\obj\repo\repobase::data2obj($data['data'], '\mcc\obj\trialEvents\repositories\event');
    if ($json) {
      $ar = array();
      foreach ($events as $ev) {
        array_push($ar, $ev->get());
      }
      return $ar;
    }
    return $events;
  }

  static public function getFullEvent($event) {
    if (!is_object($event)) {
      $event = new \mcc\obj\trialEvents\repositories\event($event);
    }
    return $event->getFullData();
  }

  static public function getFullSignups($event, $isPublic = true) {
    if (!is_object($event)) {
      $event = new \mcc\obj\trialEvents\repositories\event($event);
    }
    $categories = $event->getChildrencategory()['obj'];
    $data = array();
    $getFun = $isPublic ? 'getPublic' : 'get';
    foreach ($categories as $cat) {
      $signups = $cat->getChildrenOrdersignup(array('id'))['obj'];
      $ar = array('category' => $cat->get(),
          'signups' => \mcc\obj\utils\ar::convertObjectsToArray($signups, $getFun));
      if (!$isPublic) {
        $ar['competitions'] = \mcc\obj\utils\ar::convertObjectsToArray($cat->getChildrencompetition()['obj'], 'get');
      }
      $data[$cat->getcategory()] = $ar;
    }
    return $data;
  }

  static public function getSignUpsForCategory($category, $asArray = true) {
    if (!is_object($category)) {
      $category = new \mcc\obj\trialEvents\repositories\category($category);
    }
    $signups = $category->getChildrenOrdersignup(array('id'))['obj'];
    if (!$asArray) {
      return $signups;
    }
    return \mcc\obj\utils\ar::convertObjectsToArray($signups, 'get');
  }

  static public function setPenaltyPoints($participation, $section, $lap, $penaltyPoints, $notes = null) {
    if (!is_object($participation)) {
      $participation = new \mcc\obj\trialEvents\repositories\participation($participation);
    }
    $competition = new \mcc\obj\trialEvents\repositories\competition($participation->getcompetition_id());
    if (!$competition->hasLap($lap) || !$competition->hasSection($section)) {
      die();
    }
    $result = new \mcc\obj\trialEvents\repositories\result();
    $result->insertOrUpdate($participation->getid(), $section, $lap, $penaltyPoints, $notes);
    return array('participation' => $participation,
        'competition_id' => $competition,
        'result' => $result);
  }

  static public function setRiderToCompetition($rider, $competition, $signup = null) {
    if (!is_object($rider)) {
      $rider = new \mcc\obj\trialEvents\repositories\rider($rider);
    }
    if (!is_object($competition)) {
      $competition = new \mcc\obj\trialEvents\repositories\rider($competition);
    }
    $data = array('rider_id' => $rider->getid(),
        'competition_id' => $competition->getid());
    if ($signup != null) {
      if (!is_object($signup)) {
        $signup = new \mcc\obj\trialEvents\repositories\signup($signup);
      }
      $data['signup_id'] = $signup->getid();
    }
    try {
      $participation = new \mcc\obj\trialEvents\repositories\participation();
      $participation->initByArray($data);
    } catch (\mcc\obj\mccException $e) {
      $participation = new \mcc\obj\trialEvents\repositories\participation($data);
    }
    return $participation;
  }

  static public function setSignUpToCompetition($signup, $competition, $asarray = true) {
    $rider = self::getRiderForSignUp($signup);
    if (!is_object($competition)) {
      $competition = new \mcc\obj\trialEvents\repositories\competition($competition);
    }
    $participation = self::setRiderToCompetition($rider, $competition, $signup);
    $competition->sortParticipants();
    return $asarray ? $participation->get() : $participation;
  }

  static public function signUp($givenName, $familyName, $category, //
          $email = null, $mobile = null, $notes = null) {
    if (!is_object($category)) {
      $category = new \mcc\obj\trialEvents\repositories\category($category);
    }
    /*
      if (!$category->canSign()) {
      throw new \mcc\obj\mccException(array('msg' => 'Cannot sign to this anymore.'));
      }
     */
    $data = array('category_id' => $category->getid(), 'givenName' => $givenName, 'familyName' => $familyName);
    \mcc\obj\utils\ar::populatIfNotNull($data, array('email' => $email, 'mobile' => $mobile, 'notes' => $notes));
    $signup = new \mcc\obj\trialEvents\repositories\signup($data);
    return $signup;
  }

}
