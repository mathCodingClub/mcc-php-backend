<?php

namespace mcc\obj\trialEvents\repositories;

class event extends \mcc\obj\repo\repobase {

  // database variables (end with _ and are protected)
  protected $name_;
  protected $description_;
  protected $isPublic_;

  /**
   * @type: timestamp
   */
  protected $startTime_;

  /**
   * @type: timestamp
   */
  protected $endTime_;

  /**
   * @type: timestamp
   */
  protected $signupStart_;

  /**
   * @type: timestamp
   */
  protected $signupEnd_;

  // constant
  const TABLE = 'events';

  public function getFullData() {
    $children = $this->getChildrencategory()['obj'];
    $ar = array('event' => $this->getData(), 'categories' => array());
    foreach ($children as $child) {
      $ar['categories'][$child->getcategory()] = $child->getFullData();
    }
    return $ar;
  }

  public function canSign() {
    $now = date('Y-m-d H:i:s');
    return ($now > $this->signupStart_) && ($now < $this->signupEnd_);
  }

  public function getSignUps() {
    $statement = 'select su.* from ' . signup::TABLE . ' su ' .
            'join ' . category::TABLE . ' cat on su.category_id = cat.id ' .
            'join ' . self::TABLE . ' ev on cat.event_id = ev.id ' .
            'where ev.id=:id order by su.familyName, su.givenName';
    $db = self::getDB();
    $query = $db->prepare($statement);
    $query->bindValue(':id', $this->getid());
    $data = $db->getData($query);
    $ar = array();
    foreach ($data as $row) {
      $obj = new signup();
      $obj->initByData($row);      
      array_push($ar, $obj);
    }    
    return $ar;
  }
  
}
