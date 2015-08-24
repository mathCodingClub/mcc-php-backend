<?php

namespace mcc\obj\user\services;

class user extends \mcc\obj\service\base {

  const REPO = '\mcc\obj\user\repositories\user';

  static public function add($username, $password, $givenName, $familyName, $email, $role) {
    return self::create(array('username' => $username,
            'password' => \mcc\obj\user\repositories\user::hashPassword($password),
            'givenName' => $givenName,
            'familyName' => $familyName,
            'email' => $email,
            'role' => $role
    ));
  }

  static public function getAll() {
    $dbHandle = self::getDB();
    $table = \mcc\obj\user\repositories\user::TABLE;    
    $data = $dbHandle->getData("select * from $table order by familyName, givenName");
    $users = array();
    foreach ($data as $value) {
      $user = new \mcc\obj\user\repositories\user();
      $user->initByData($value);
      array_push($users,$user);
    }    
    return \mcc\obj\utils\ar::convertObjectsToArray($users, 'get');
  }
  
  static public function initByCookie($cookie = null) {
    $cookie = self::getCookieValue($cookie);
    $session = self::getSession($cookie);
    $user = new \mcc\obj\user\repositories\user($session->getuser_id());
    return $user;
  }

  static public function initByUserNameAndPassword($username, $password) {
    $user = new \mcc\obj\user\repositories\user();
    $user->initByUsername($username);
    if (!$user->comparePassword($password)) {
      throw new \mcc\obj\mccException(
      array('dict' => 'NOT_AUTHORIZED',
      'msg' => 'Not authorized. False credentials.',
      'code' => 401));
    }
    // needs to create new session when user is logged in with password and username    
    $session = \mcc\obj\user\repositories\session::createFor($user);
    return $user;
  }

  static public function initByToken($token) {
    $user = \mcc\obj\user\repositories\token::getUserFor($token);
    \mcc\obj\user\repositories\session::createFor($user);
    return $user;
  }

  static public function logout($cookie = null) {
    $cookie = self::getCookieValue($cookie);
    $session = self::getSession($cookie);
    \mcc\obj\user\repositories\user::delete($session->getid());
    unset($session);
  }
  
  static public function updatePassword($id,$password){
    $user = new \mcc\obj\user\repositories\user($id);
    $user->setPassword($password);
  }

  // private

  static private function getCookieValue($cookie) {
    if (is_null($cookie)) {
      return $_COOKIE[\mcc\rest\user\auth::COOKIE];
    }
    return $cookie;
  }

  static private function getSession($cookie = null) {
    $cookie = self::getCookieValue($cookie);
    $session = new \mcc\obj\user\repositories\session();
    $session->initBysession($cookie);
    return $session;
  }

}
