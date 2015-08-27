<?php

namespace mcc\obj\repo;

abstract class repobase extends repodb {

  protected $id_; // needs to be in every node

  public function __construct($idOrData = null) {
    if (!is_null($idOrData) && is_numeric($idOrData)) {
      $this->initByid($idOrData);
    } elseif (!is_null($idOrData) && is_array($idOrData)) {
      $id = $this->create($idOrData);
      $this->initByid($id);
    }
  }

  public function __call($name, $args) {
    if (preg_match('@^initByData$@', $name)) {
      $this->initByData($args[0]);
    } elseif (preg_match('@^initByArray$@', $name)) {
      $this->initBy(null, $args[0]);
    } elseif (preg_match('@^initBy@', $name)) {
      $this->initBy(lcfirst(preg_replace('@^initBy@', '', $name)), $args[0]);
    } elseif (preg_match('@^initChildren[a-zA-Z]@', $name)) { // at leat one after init children
      $this->initChildrenData(lcfirst(preg_replace('@^initChildren@', '', $name)));
    } elseif (preg_match('@^get$@', $name)) {
      return $this->getData();
    } elseif (preg_match('@^getChildrenOrder@', $name)) {
      return $this->getChildren(lcfirst(preg_replace('@^getChildrenOrder@', '', $name)), $args[0]);
    } elseif (preg_match('@^getChildren@', $name)) {
      return $this->getChildren(lcfirst(preg_replace('@^getChildren@', '', $name)));
    } elseif (preg_match('@^get@', $name)) {
      return $this->getValue(lcfirst(preg_replace('@^get@', '', $name)));
    } elseif (preg_match('@^set$@', $name)) {
      return $this->setData($args[0]);
    } elseif (preg_match('@^set@', $name)) {
      return $this->setValue(lcfirst(preg_replace('@^set@', '', $name)), $args[0]);
    } else {
      throw new \Exception("Unknown magic method '$name' called", 400);
    }
  }

  public static function __callStatic($name, $args) {
    if (preg_match('@^delete$@', $name)) {
      self::deleteBy('id', $args[0]);
    }
  }

  /* Magic methods are mapped here and all operate only with database variables */

  protected function create($data) {    
    $db = self::getDB();
    $this->fixTypes($data);
    $db->insert(static::TABLE, $data);
    $id = $db->getLastInsertId();
    return $id;
  }

  protected static function deleteBy($key, $value) {
    $db = self::getDB();
    $db->deleteById(static::TABLE, $value, $key);
  }

  protected function getData($var = array()) {
    if (count($var) == 0) {
      $var = $this->getDatabaseVariables();
    }
    $data = array();
    foreach ($var as $key) {
      $data[substr($key, 0, strlen($key) - 1)] = $this->$key;
    }
    $this->fixTypesReturn($data);
    return $data;
  }

  protected function getChildren($type, $order = array()) {
    $class = $this->getNamespace() . $type;
    $key = $this->getClass();
    $table = $class::TABLE;
    return $this->initChildren($table, $class, "{$key}_id", $order);
  }

  protected function getValue($key) {
    $var = $key . '_';
    $data = array($key => $this->$var);
    $this->fixTypesReturn($data);
    return $data[$key];
  }

  protected function initByData($data) {
    foreach ($data as $key => $value) {
      $key .= '_';
      $this->$key = $value;
    }
  }

  protected function initBy($key, $value) {
    $db = self::getDB();
    $data = $db->getById(static::TABLE, $value, $key);
    if (!isset($data['id'])) {
      throw new \mcc\obj\mccException(array('msg' => 'Data does not exist',
  'type' => 'nodata'));
    }
    $var = $this->getDatabaseVariables();
    foreach ($var as $key) {
      $this->$key = $data[substr($key, 0, strlen($key) - 1)];
    }
  }

  protected function initChildren($table, $class, $foreignKey, $order = array(), $from = 0, $num = 10000) {
    $db = self::getDB();
    $data = $db->getByPagination($table, $from, $num, $this->id_, $foreignKey, $order);
    return array('num' => count($data['data']),
        'tot' => $data['tot'],
        'from' => $from,
        'max' => $from + count($data['tot']),
        'obj' => self::data2obj($data['data'], $class));
  }

  protected function initChildrenData($type) {
    $key = 'children_' . $type;
    $this->$key = $this->getChildren($type)['obj'];
  }

  protected function setData($data) {
    $db = self::getDB();
    $this->fixTypes($data);    
    $db->updateById(static::TABLE, $data, $this->id_);    
    $this->initByid($this->id_);
  }

  protected function setValue($key, $value) {
    $this->setData(array($key => $value));
  }

  private function fixTypes(&$data) {
    foreach ($data as $key => $value) {
      try {
        $property = $this->getPropertyType($key . '_');
        switch ($property) {
          case 'timestamp';
            if (is_int($value)) {
              if ($value > mktime(12, 0, 0, 1, 1, 2100)) {
                $value = $value / 1000; // heuristics, now assumes microtimeformat and converts to time
              }
              $data[$key] = $value;
              $data[$key] = date('Y-m-d H:i:s', $value);
            }
        }
      } catch (\Exception $e) {
        unset($data[$key]);
      }
    }
  }

  private function fixTypesReturn(&$data) {
    foreach ($data as $key => $value) {
      $property = $this->getPropertyType($key . '_');
      switch ($property) {
        case 'timestamp';
          $data[$key] = \mcc\obj\utils\time::datetime2timestamp($value) * 1000;
      }
    }
  }

  private function getPropertyType($propertyName) {
    $class = new \ReflectionClass($this);
    $prop = $class->getProperty($propertyName);
    $doc = $prop->getDocComment();
    preg_match('#@(type)(:)(.*?)\n#s', $doc, $annotations);
    if (count($annotations) > 0) {
      return trim($annotations[3]);
    }
    return null;
  }

  static function data2obj($data, $class) {
    $obj = array();
    foreach ($data as $row) {
      $o = new $class();

      $o->initByData($row);
      array_push($obj, $o);
    }
    return $obj;
  }

}
