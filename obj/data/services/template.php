<?php

namespace mcc\obj\data\services;

class template extends \mcc\obj\service\base {

  const REPO = '\mcc\obj\data\repositories\template';

  static public function create($array) {
    $data = new \mcc\obj\data\repositories\template($array);
    return $data;
  }

  static public function delete($dataOrId) {
    $id = is_object($dataOrId) ? $dataOrId->getid() : $dataOrId;
    \mcc\obj\data\repositories\template::delete($id);
    unset($dataOrId);
    return true;
  }

  static public function getAllTitles(){
    $db = \mcc\obj\sql::get();
    $table = \mcc\obj\data\repositories\template::TABLE;
    $data = $db->getData("select * from $table order by code");
    $obj = \mcc\obj\utils\ar::convertQueryResultToRepoObjects(self::REPO,$data);
    return \mcc\obj\utils\ar::convertObjectsToArray($obj, 'getTitleData');    
  }
  
  static public function getByCode($code) {
    $data = new \mcc\obj\data\repositories\template();
    $data->initByCode($code);
    return $data;
  }

  static public function getById($id) {
    $data = new \mcc\obj\data\repositories\template($id);
    return $data;
  }

  static public function update($dataOrId, $array) {
    if (!is_object($dataOrId)) {
      $dataOrId = new \mcc\obj\data\repositories\template($dataOrId);
    }
    $dataOrId->set($array);
    return $dataOrId;
  }

}
