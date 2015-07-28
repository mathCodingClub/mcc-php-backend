<?php

namespace mcc\obj;

class user {

  const COOKIE_NAME = 'session';

  private $dbHandle;
  private $tableUser = 'users';
  private $tableSession = 'sessions';
  private $user;
  private $session;

  /*
   *  constructor is private, can be only initialized via static methods
   */

  private function __construct() {
    $this->dbHandle = \mcc\obj\sql::get();
  }   
  
  /*
   * Static methods
   */

  static public function add($username, $password, $givenName, $familyName, $email, $role) {
    $user = new self();
    $user->addFromData($username, $password, $givenName, $familyName, $email, $role);
    return $user;
  }

  static public function initByUserNameAndPassword($username, $password) {
    $user = new self();
    $user->initFromUserNameAndPassword($username, $password);
    return $user;
  }

  static public function initByCookie($cookie = null) {
    if (is_null($cookie) && isset($_COOKIE['session'])) {
      $cookie = $_COOKIE['session'];
    } else if (is_null($cookie) && !isset($_COOKIE['session'])) {
      throw new \Exception("NOT_AUTHORIZED", 401);
    }
    $user = new user();
    $user->initFromSession($cookie);
    return $user;
  }

  /*
   * Public methods that can be called after initialization
   */

  public function logout() {
    $this->dbHandle->deleteById($this->tableSession, $this->session, 'session');
  }

  public function getName() {
    return $this->user['givenName'] . ' ' . $this->user['familyName'];
  }

  public function getSession() {
    if (!isset($this->session)) {
      throw new \Exception('One needs to have session via session init or log in. Now there is no session.', 400);
    }
    return $this->session;
  }

  /*
   * Private initializers
   */

  private function initFromUserNameAndPassword($username, $password) {
    $this->user = $this->dbHandle->getById($this->tableUser, $username, 'username');
    $this->comparePassword($password);
    $this->setSession();
  }

  private function initFromId($id) {
    $this->user = $this->dbHandle->getById($this->tableUser, $id);
  }

  private function initFromSession($session) {
    $data = $this->dbHandle->getById($this->tableSession, $session, 'session');
    if (!isset($data['user_id'])) {
      throw new \Exception('Not authorized. Expired session.', 401);
    }
    $this->initFromId($data['user_id']);
    $this->session = $data['session'];
  }

  /*
   * Private helpers
   */

  private function hashPassword($password) {
    $options = array('cost' => 11);
    return password_hash($password, PASSWORD_BCRYPT, $options);
  }

  private function comparePassword($password) {
    if (!isset($this->user['passwordHash']) ||
            !password_verify($password, $this->user['passwordHash'])) {
      throw new \Exception('Not authorized. False credentials.', 401);
    }
  }

  private function addFromData($username, $password, $givenName, $familyName, $email, $role) {
    $this->dbHandle->insert($this->tableUser, array('username' => $username,
        'givenName' => $givenName,
        'familyName' => $familyName,
        'email' => $email,
        'passwordHash' => $this->hashPassword($password),
        'role' => $role));
    $id = $this->dbHandle->getLastInsertId();
    $this->initFromId($id);
  }

  private function setSession() {
    $hash = hash('ripemd160', uniqid() . $this->user['passwordHash'] . $this->user['username']);
    $data = array('user_id' => $this->user['id'], 'session' => $hash);
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
      $data['device'] = $_SERVER['HTTP_USER_AGENT'];
    }
    $this->dbHandle->insert($this->tableSession, $data);
    $this->session = $hash;
  }

}
