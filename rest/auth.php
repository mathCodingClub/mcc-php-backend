<?php

namespace mcc\rest;

class auth extends \mcc\obj\slimClass\service {

  const COOKIE = 'session';

  public function post() {
    $data = $this->getBodyAsJSON();
    if (!array_key_exists('username', $data) ||
            !array_key_exists('password', $data)) {
      throw new \mcc\obj\mccException(array(
  'code' => 401,
  'dict' => 'LOGIN.LOGIN_FALSE_CREDENTIALS',
  'msg' => 'False credentials.'
      ));
    }

    $user = \mcc\obj\user::initByUserNameAndPassword(
                    $data['username'], $data['password']);

    $this->app->setCookie(self::COOKIE, $user->getSession(), '10 years');
    $this->sendArrayAsJSON(
            array('msg' => 'Successfully logged in.',
                'dict' => 'LOGIN.LOGIN_SUCCESS',
                'user' => array('name' => $user->getName())));
  }

  public function get() {
    $cookie = $this->app->getCookie(self::COOKIE);
    $user = \mcc\obj\user::initByCookie($cookie);
    $this->sendArrayAsJSON(
            array('user' => array('name' => $user->getName())));
  }

  public function getLogout() {
    $cookie = $this->app->getCookie(self::COOKIE);
    $user = \mcc\obj\user::initByCookie($cookie);
    $user->logout();
    $this->app->deleteCookie(self::COOKIE);
    $this->sendArrayAsJSON(
            array('dict' => 'LOGIN.LOGGED_OUT',
                'user' => array(),
                'msg' => 'Successfully logged out.'));
  }

}
