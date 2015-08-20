<?php

namespace mcc\obj\user\repositories;

class user extends \mcc\obj\repo\repobase {

  const TABLE = 'users';

  protected $username_;
  protected $givenName_;
  protected $familyName_;
  protected $email_;
  protected $passwordHash_;
  protected $role_;
  private $session;

  public function getName() {
    return $this->givenName_ . ' ' . $this->familyName_;
  }

  public function getSession() {
    return $this->session;
  }

  public function setSession($session) {
    $this->session = $session;
  }

  public function get(){
    $data = $this->getData();
    unset($data['passwordHash']);
    return $data;
  }
  
  public function setPassword($password){
    $passwordHash = self::hashPassword($password);
    $this->setpasswordHash($passwordHash);
  }
  
  public function comparePassword($password) {
    return password_verify($password, $this->getpasswordHash());
  }

  static public function hashPassword($password) {
    $options = array('cost' => 11);
    return password_hash($password, PASSWORD_BCRYPT, $options);
  }

}
