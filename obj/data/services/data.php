<?php

namespace mcc\obj\data\services;

class data extends \mcc\obj\service\base {

  const REPO = '\mcc\obj\data\repositories\data';

  static public function create($array) {
    $data = new \mcc\obj\data\repositories\data($array);
    return $data;
  }

  static public function delete($dataOrId) {
    $id = is_object($dataOrId) ? $dataOrId->getid() : $dataOrId;
    \mcc\obj\data\repositories\data::delete($id);
    unset($dataOrId);
    return true;
  }

  static public function getAllTitles(){
    $db = \mcc\obj\sql::get();
    $table = \mcc\obj\data\repositories\data::TABLE;
    $data = $db->getData("select * from $table order by code");
    $obj = \mcc\obj\utils\ar::convertQueryResultToRepoObjects(self::REPO,$data);
    return \mcc\obj\utils\ar::convertObjectsToArray($obj, 'getTitleData');    
  }
  
  static public function getByCode($code) {
    $data = new \mcc\obj\data\repositories\data();
    $data->initBycode($code);
    return $data;
  }

  static public function getById($id) {
    $data = new \mcc\obj\data\repositories\data($id);
    return $data;
  }

  static public function update($dataOrId, $array) {
    if (!is_object($dataOrId)) {
      $dataOrId = new \mcc\obj\data\repositories\data($dataOrId);
    }
    $dataOrId->set($array);
    return $dataOrId;
  }

}