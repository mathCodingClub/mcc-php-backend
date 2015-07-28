<?php

namespace mcc\rest\trialEvents;

class pri extends \mcc\obj\slimClass\service {

  private $user;

  public function middleware() {
    $this->user = \mcc\obj\user::initByCookie();
  }

  // delete
  public function deleteCategory($id) {
    \mcc\obj\trialEvents\services\categories::deleteById($id);
    $this->sendArrayAsJSON(array(
        'dict' => 'TRIAL.EVENTS.CATEGORY_REMOVED_OK',
        'msg' => 'Category removed ok.'));
  }

  public function deleteCompetition($id) {
    \mcc\obj\trialEvents\services\competitions::deleteById($id);
    $this->sendArrayAsJSON(array(
        'dict' => 'TRIAL.EVENTS.COMPETITION_REMOVED_OK',
        'msg' => 'Competition removed ok.'));
  }

  public function deleteParticipation($id) {
    $competition = \mcc\obj\trialEvents\services\competitions::getByParticipationId($id);
    \mcc\obj\trialEvents\services\participations::deleteById($id);
    $competition->sortParticipants();
    $this->sendArrayAsJSON(array(
        'dict' => 'TRIAL.EVENTS.PARTICIPATION_REMOVED_OK',
        'msg' => 'Participation removed ok.'));
  }

  // getters
  public function getEvent($eventId) {
    $event = \mcc\obj\trialEvents\services\events::get($eventId);
    $this->sendArrayAsJSON(array('event' => $event->get()));
  }

  public function getCategory($categoryId) {
    $category = \mcc\obj\trialEvents\services\categories::get($categoryId);
    $this->sendArrayAsJSON(array('category' => $category->get()));
  }

  public function getCompetition($competitionId) {
    $competition = \mcc\obj\trialEvents\services\competitions::get($competitionId);
    $this->sendArrayAsJSON(array('competition' => $competition->get()));
  }

  /**
   * @route: /competition/:id/participants
   */
  public function getParticipantsToCompetition($competitionId) {
    $eventId = \mcc\obj\trialEvents\services\events::getByCompetitionId($competitionId);
    $signups = \mcc\obj\trialEvents\services\events::getAllSignups($eventId);
    $competition = \mcc\obj\trialEvents\services\competitions::get($competitionId);
    $category = \mcc\obj\trialEvents\services\categories::get($competition->getcategory_id());
    $data = array(
        'competition' => $competition->get(),
        'category' => $category->get(),
        'allSignUps' => $signups,
        'participants' => \mcc\obj\trialEvents\services\competitions::getParticipants($competitionId));
    $this->sendArrayAsJSON($data);
  }

  /**
   * @route: /competition/:competitionId/signup/:signupId
   */
  public function getSetSignupToCompetition($competitionId, $signupId) {
    \mcc\obj\trialEvents\services\signups::setToCompetition($signupId, $competitionId);
    // $this->sendArrayAsJSON($data);
    $this->sendArrayAsJSON(array(
        'msg' => 'Sign-up added to competition ok.',
        'dict' => 'TRIAL.EVENTS.SIGNUP_ADDED_TO_COMPETITION'
    ));
  }

  /**
   * @route: /event/:id/fullsignups
   */
  public function getSignUpsInEveryCategory($eventId) {
    $this->sendArrayAsJSON(
            array('data' => \mcc\obj\trialEvents\services\events::getFullSignups($eventId, false),
                'msg' => 'All sign-ups to event fetched ok.'
    ));
  }

  /**
   * @route: /category/:id/signups
   */
  public function getSignUps($categoryId) {
    $this->sendArrayAsJSON(
            array('data' => \mcc\obj\trialEvents\services\events::getSignUpsForCategory($categoryId),
                'msg' => 'Sign-ups for given category fetched ok'
    ));
  }

  // new 
  public function postEvent() {
    $data = $this->getBodyAsJSON();
  }

  /**
   * @route: /event/:eventId/category
   */
  public function postCategory($eventId) {
    $data = $this->getBodyAsJSON();
    $this->idMissMatchCheck($eventId, $data['event_id']);
    $obj = \mcc\obj\trialEvents\services\categories::create($data);
    $this->sendArrayAsJSON(array('category' => $obj->get(),
        'dict' => 'ACTION_OK',
        'msg' => 'Category added.'));
  }

  /**
   * @route: /category/:categoryId/competition
   */
  public function postCompetition($categoryId) {
    $data = $this->getBodyAsJSON();
    $this->idMissMatchCheck($categoryId, $data['category_id']);
    $comp = \mcc\obj\trialEvents\services\competitions::create($data);
    $eventId = \mcc\obj\trialEvents\services\events::getByCompetitionId($comp->getid());
    $this->sendArrayAsJSON(array('competition' => $comp->get(),
        'eventId' => $eventId,
        'dict' => 'ACTION_OK',
        'msg' => 'Competition added.'));
  }

  /**
   * @route: /participation/:id/result
   */
  public function postResult($participationId) {
    $data = $this->getBodyAsJSON();
    $result = \mcc\obj\trialEvents\services\results::save($data);
    $competition = \mcc\obj\trialEvents\services\competitions::getByParticipationId($data['participation_id']);
    $competition->sortParticipants();
    $this->sendArrayAsJSON(array(
        'dict' => 'ACTION_OK',
        'msg' => 'Result saved successfully and participants sorted.'));
  }

  /**
   * @route: /competition/:competitionId/rider
   */
  public function postRiderAndAddtoCompetition($competitionId) {
    $data = $this->getBodyAsJSON();
    $rider = \mcc\obj\trialEvents\services\riders::getRider($data['familyName'], $data['givenName']);
    $participation = \mcc\obj\trialEvents\services\participations::addRider($competitionId, $rider);
    $this->sendArrayAsJSON(array('participation' => $participation->get(),
        'dict' => 'TRIAL.EVENTS.NEW_RIDER_ADDED_TO_COMPETITION',
        'msg' => 'New rider added to competition added.'));
  }

  // update
  public function putCompetition($competitionId) {
    $data = $this->getBodyAsJSON();
    $this->idMissMatchCheck($competitionId, $data['id']);
    $comp = \mcc\obj\trialEvents\services\competitions::save($data);
    $eventId = \mcc\obj\trialEvents\services\events::getByCompetitionId($comp->getid());
    $this->sendArrayAsJSON(array(
        'dict' => 'ACTION_OK',
        'eventId' => $eventId,
        'competition' => $comp->get()));
  }

  public function putCategory($categoryId) {
    $data = $this->getBodyAsJSON();
    $this->idMissMatchCheck($categoryId, $data['id']);
    $cat = \mcc\obj\trialEvents\services\categories::save($data);
    $this->sendArrayAsJSON(array(
        'dict' => 'ACTION_OK',
        'category' => $cat->get()));
  }

  public function putEvent($eventId) {
    $data = $this->getBodyAsJSON();
    $this->idMissMatchCheck($eventId, $data['id']);
    $event = \mcc\obj\trialEvents\services\events::save($data);
    $this->sendArrayAsJSON(array(
        'dict' => 'ACTION_OK',
        'competition' => $event->get()));
  }

  public function putParticipation($participationId) {
    $data = $this->getBodyAsJSON();
    $this->idMissMatchCheck($participationId, $data['id']);
    $participation = \mcc\obj\trialEvents\services\participations::save($data);
    $competition = \mcc\obj\trialEvents\services\competitions::get($participation->getcompetition_id());
    $competition->sortParticipants();
    $this->sendArrayAsJSON(array(
        'dict' => 'ACTION_OK',
        'participation' => $participation->get()));
  }

  public function putSignup($signupId = null) {
    $data = $this->getBodyAsJSON();
    $signup = \mcc\obj\trialEvents\services\signups::save($data);
    $this->sendArrayAsJSON(array(
        'dict' => 'ACTION_OK',
        'signup' => $signup->get()));
  }

  private function idMissMatchCheck($id1, $id2) {
    if ($id1 != $id2) {
      throw new \mcc\obj\mccException(array(
  'code' => 404,
  'dict' => 'ERROR_MISUSE',
  'msg' => 'Id in url and post data do not match.'
      ));
    }
  }

}
