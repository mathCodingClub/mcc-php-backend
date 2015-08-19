<?php

namespace mcc\rest\data;

class pri extends \mcc\obj\slimClass\service {

  private $user;

  public function middleware() {
    $this->user = \mcc\obj\user\services\user::initByCookie();
  }

  // delete
  public function delete($id) {
    \mcc\obj\data\services\data::delete($id);
    $this->sendArrayAsJSON(array(
        'dict' => 'DATA.DELETED_OK',
        'msg' => 'Data removed ok.'));
  }

  /**
   * @route: /:code/code
   */
  public function deleteByCode($code) {
    $data = \mcc\obj\data\services\data::getByCode($code);
    \mcc\obj\data\services\data::delete($data);
    $this->sendArrayAsJSON(array(
        'dict' => 'DATA.DELETED_OK',
        'msg' => 'Data removed ok.'));
  }
 
  public function get($id) {
    if (is_numeric($id)){
      $data = \mcc\obj\data\services\data::getById($id);
    }
    else {
      $this->getByCode($id);
      return;
    }    
    $this->sendArrayAsJSON(array(
        'dict' => 'GET_OK',
        'data' => $data->get()));
  }

  public function getAllTitles(){
    $this->sendArrayAsJSON(array(
       'dict' => 'GET_OK',
        'data' => \mcc\obj\data\services\data::getAllTitles()
    ));
  }
  
  /**
   * @route: /:code/code
   */
  public function getByCode($code) {
    $data = \mcc\obj\data\services\data::getByCode($code);
    $this->sendArrayAsJSON(array(
        'dict' => 'GET_OK',
        'data' => $data->get()));
  }

  // post
  public function post() {
    $array = $this->getBodyAsJSON();
    $data = \mcc\obj\data\services\data::create($array);
    $this->sendArrayAsJSON(array(
        'dict' => 'CREATED_OK',
        'data' => $data->get()));
  }

  // update
  public function put($id) {
    $array = $this->getBodyAsJSON();
    $this->idMissMatchCheck($id, $array['id']);
    $data = \mcc\obj\data\services\data::update($id, $array);
    $this->sendArrayAsJSON(array(
        'dict' => 'UPDATED_OK',
        'data' => $data->get()));
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
