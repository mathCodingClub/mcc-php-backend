<?php

namespace mcc\rest\trialEvents;

class pub extends \mcc\obj\slimClass\service {

  /**
   * @route: /event/:id/category_all
   */
  public function getCategories($eventId) {
    $this->sendArrayAsJSON(\mcc\obj\trialEvents\services\events::getCategoriesAndCompetitions($eventId));
  }

  public function getEvents() {
    $this->sendArrayAsJSON(\mcc\obj\trialEvents\services\events::getEvents());
  }

  /**
   * @route: /event/:id/fullresults
   */
  public function getFullResults($eventId) {
    $this->sendArrayAsJSON(\mcc\obj\trialEvents\services\events::getFullEvent($eventId));
  }

  /**
   * @route: /event/:id/signup
   */
  public function postSignUp($eventId) {    
    $event = \mcc\obj\trialEvents\services\events::get($eventId);        
    /*
    if (!$event->canSign()) {
      throw new \mcc\obj\mccException(array('msg' => 'Sign-up to event has terminated.'));
    }      
     */      
    $data = $this->getBodyAsJSON();
    $signup = $data['signup'];
    $categoryName = $signup['category'];
    $category = \mcc\obj\trialEvents\services\events::getCategoryByName($event, $categoryName);
    $signup['notes'] = json_encode($signup['notes'], JSON_PRETTY_PRINT);
    \mcc\obj\trialEvents\services\events::signUp($signup['givenName'], $signup['familyName'], $category, $signup['email'], $signup['mobile'], $signup['notes']);
    $this->sendArrayAsJSON(array('msg' => 'Sign-up for event OK.',
        'dict' => 'TRIAL.EVENTS.SIGNUP_OK'));
  }

  /**
   * @route: /event/:id/fullsignups
   */
  public function getSignUps($eventId) {
    $this->sendArrayAsJSON(\mcc\obj\trialEvents\services\events::getFullSignups($eventId));
  }

}
