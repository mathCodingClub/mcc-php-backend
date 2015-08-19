<?php

namespace mcc\rest\user;

class admin extends \mcc\obj\slimClass\service {

  private $user;

  public function middleware() {
    $this->user = \mcc\obj\user\services\user::initByCookie();
  }

  public function deleteUser($id) {
    if ($id == $this->user->getid()){
    throw new \mcc\obj\mccException(
        array(
            'dict' => 'ERROR',
            'msg' => 'You cannot delete yourself.',
            'code' => '400'
        )
        );
    }
    \mcc\obj\user\services\user::deleteById($id);
    $this->response->body(json_encode(array('msg' => 'User deleted successfully.',
        'dict' => 'ACTION_OK'), JSON_NUMERIC_CHECK));
  }

  public function getUser($id) {
    $this->sendArrayAsJSON(array(
        'user' => \mcc\obj\user\services\user::get($id)->get(),
        'dict' => 'GET_OK'
    ));
  }

  public function getAll() {
    $this->sendArrayAsJSON(array(
        'dict' => 'GET_OK',
        'users' => \mcc\obj\user\services\user::getAll()
    ));
  }

  public function postUser() {
    $data = $this->getBodyAsJSON();
    $data['password'] = uniqid();
    \mcc\obj\user\services\user::add($data['username'], $data['password'], $data['givenName'], $data['familyName'], $data['email'], $data['role']);
    $this->response->body(json_encode(array('msg' => 'User added successfully.',
        'dict' => 'ACTION_OK'), JSON_NUMERIC_CHECK));
  }

  public function putUser($id) {
    $data = $this->getBodyAsJSON();
    \mcc\obj\user\services\user::save($data);
    $this->response->body(json_encode(array('msg' => 'User modified successfully.',
        'dict' => 'ACTION_OK'), JSON_NUMERIC_CHECK));
  }

}
