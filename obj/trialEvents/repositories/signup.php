<?php

namespace mcc\obj\trialEvents\repositories;

class signup extends \mcc\obj\repo\repobase {

  protected $category_id_;
  protected $givenName_;
  protected $familyName_;
  protected $email_;
  protected $mobile_;
  protected $notes_;

  /**
   * @type: timestamp
   */
  protected $time_;

  const TABLE = 'events_signups';
 
  public function set($data){
    if (array_key_exists('notes',$data) && is_array($data['notes'])){
      $data['notes'] = json_encode($data['notes'], JSON_PRETTY_PRINT);
    }
    $this->setData($data);    
  }

  protected function getData() {
    $data = parent::getData();
    // is always json string (if not empty)
    if (strlen($data['notes']) > 0) {
      $data['notes'] = json_decode($data['notes'], true);
    }
    // add also participations
    $data['participations'] = $this->getParticipatingCompetitions();
    return $data;
  }

  public function getParticipatingCompetitions() {
    $conn = $this->getDB();
    $query = $conn->prepare('select * from ' . competition::TABLE .
            ' comp join ' . participation::TABLE . ' part on comp.id = part.competition_id where part.signup_id=:id');
    $query->bindValue(':id', $this->getid());
    return $conn->getData($query);
  }

  public function getPublic() {
    return parent::getData(array('givenName_', 'familyName_', 'id_', 'category_id_'));
  }

}
