<?php

namespace mcc\obj\db\sql;

class sql {

  private static $conn = null; // static, so different instances do not create new handle
  private static $transactionOngoing = false;
  private static $transactionAutorollback = true;

  function __construct($db, $user, $pass, $server = '127.0.0.1') {
    if (!is_null(self::$conn)) {
      return;
    }
    $dsn = "mysql:dbname=$db;host=$server";
    try {
      self::$conn = new \PDO($dsn, $user, $pass);
      self::$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); // force execute to throw exception
      self::$conn->exec("set names utf8"); // set utf8 to charset
      // self::$conn->exec("set time_zone='+00:00'"); // always return in UTC, unfortunately this works differently in osx and linux
    } catch (\PDOException $e) {
      throw new \mcc\obj\mccException(array('msg' => 'Database initialization error.',
  'type' => 'sql',
  'previous' => $e));
    }
  }

  /*
   * TRANSACTION SUPPORT
   */

  public function transactionStart() {
    self::$conn->beginTransaction();
    self::$transactionOngoing = true;
  }

  public function transactionCommit() {
    if (self::$transactionOngoing) {
      self::$conn->commit();
      self::$transactionOngoing = false;
    }
  }

  public function transactionRollBack() {
    if (self::$transactionOngoing) {
      self::$conn->rollBack();
      self::$transactionOngoing = false;
    }
  }

  private function transactionRollBackOnError() {
    if (self::$transactionAutorollback) {
      $this->transactionRollBack();
    }
  }

  public function transactionAutorollBack($value = null) {
    if (is_null($value)) {
      return self::$ransactionAutorollback;
    }
    self::$transactionAutorollback = $value;
  }

  /*
   * BASIC QUERIES HELPED
   */

  public function deleteById($table, $id, $field = 'id') {
    $query = null;
    $statement = null;
    try {
      if (is_array($id)) {
        $qMarks = str_repeat('?,', count($id) - 1) . '?';
        $statement = "delete from $table where $field in ($qMarks)";
        $query = $this->prepare($statement);
        $this->bindArrayValueByQuestionMark($query, $id);
      } else {
        $statement = "delete from $table where $field=:id";
        $query = $this->prepare($statement);
        $query->bindValue(':id', $id);
      }
      return $query->execute();
    } catch (\PDOException $e) {
      $this->transactionRollBackOnError();
      throw new \mcc\obj\mccException(array('msg' => 'Error in deleteById.',
  'params' => array('query' => $query, 'statement' => $statement, 'bind' => $id, 'field' => $field),
  'type' => 'sql',
  'previous' => $e));
    }
  }

  public function execute($statement) {
    $query = $this->prepare($statement);
    return $this->executeQuery($query);
  }

  public function executeQuery($query) {
    try {
      return $query->execute();
    } catch (\PDOException $e) {
      $this->transactionRollBackOnError();
      throw new \mcc\obj\mccException(array('msg' => 'Error in executeQuery.',
  'params' => array('query' => $query),
  'type' => 'sql',
  'previous' => $e));
    }
  }

  public function getById($table, $id, $field = 'id', $fields = '*', $getArray = false, $suffix = '') {
    if (is_array($fields)) {
      $fields = implode($fields, ',');
    }
    $query = null;
    $statement = null;
    try {
      if (!is_array($id)) {
        $statement = "select $fields from $table where $field=:id $suffix";
        $query = $this->prepare($statement);
        $query->bindValue(':id', $id);
      } else {
        $statement = "select $fields from $table where " . $this->getPDOwhereStr($id) . " $suffix";
        $query = $this->prepare($statement);
        $this->bindArrayValue($query, $id);
      }
      $query->execute();
      return $this->returnData($query, $getArray);
    } catch (\PDOException $e) {
      $this->transactionRollBackOnError();
      throw new \mcc\obj\mccException(array('msg' => 'Error in getById.',
  'params' => array('query' => $query,
      'bind' => $id,
      'field' => $field,
      'statement' => $statement),
  'type' => 'sql',
  'previous' => $e));
    }
  }

  public function getByIdInSet($table, $ids, $field = 'id', $fields = '*', $suffix = '', $indexColumn = null) {
    if (count($ids) == 0) {
      return array();
    }
    if (is_array($fields)) {
      $fields = implode($fields, ',');
    }
    $ids = array_values($ids); // nullify keys
    if ($ids[0] == intval($ids[0])) { // check if keys are numeric
      $qMarks = str_repeat('?,', count($ids) - 1) . '?';
    } else {
      $qMarks = str_repeat('\'?\',', count($ids) - 1) . '\'?\'';
    }
    $statement = "select $fields from $table where $field in ($qMarks) $suffix";
    $query = $this->prepare($statement);
    $this->bindArrayValueByQuestionMark($query, $ids);
    try {
      $query->execute();
      return $this->returnData($query, true, $indexColumn);
    } catch (\PDOException $e) {
      $this->transactionRollBackOnError();
      throw new \mcc\obj\mccException(array('msg' => 'Error in getByInInSet.',
  'params' => array('query' => $query,
      'bind' => $ids,
      'statement' => $statement),
  'type' => 'sql',
  'previous' => $e));
    }
  }

  public function getByPagination($table, $from = 0, $num = 10000, $value = null, $key = null, $order = array(), $maxid = 0) {
    // so far $maxid is not considered, but database is expected sort of static
    $limit = "limit $from,$num";
    if (count($order) > 0) {
      $limit = ' order by ' . implode(',', $order) . " $limit";
    }
    if (!is_null($value)) {
      $num = $this->getById($table, $value, $key, 'count(*) as amount');
      $data = $this->getById($table, $value, $key, '*', true, $limit);
    } else {
      $num = $this->getData("select count(*) as amount from $table")[0];
      $data = $this->getData("select * from $table $limit");
    }
    $tot = $num['amount'];

    return array('data' => $data, 'tot' => $tot, 'from' => $from, 'max' => $from + count($data));
  }

  public function getColumnNames($table) {
    $statement = "SHOW columns FROM $table";
    $query = $this->prepare($statement);
    try {
      $query->execute();
      return $this->returnDataList($query, 'Field');
    } catch (\PDOException $e) {
      $this->transactionRollBackOnError();
      throw new \mcc\obj\mccException(array('msg' => 'Error in getByInInSet.',
  'params' => array('query' => $query,
      'statement' => $statement),
  'type' => 'sql',
  'previous' => $e));
    }
  }

  public function getData($queryOrStatement, $indexColumn = null, $arrayToBind = null) {
    list($query, $statement) = $this->queryOrStament($queryOrStatement, $arrayToBind);
    try {
      $query->execute();
      return $this->returnData($query, true, $indexColumn);
    } catch (\PDOException $e) {
      $this->transactionRollBackOnError();
      throw new \mcc\obj\mccException(array('msg' => 'Error in getData.',
  'params' => array('query' => $query, 'statement' => $statement),
  'type' => 'sql',
  'previous' => $e));
    }
  }

  public function getDataList($queryOrStatement, $arrayToBind = null) {
    list($query, $statement) = $this->queryOrStament($queryOrStatement, $arrayToBind);
    try {
      $query->execute();
      return $this->returnDataList($query);
    } catch (\PDOException $e) {
      $this->transactionRollBackOnError();
      throw new \mcc\obj\mccException(array('msg' => 'Error in getDataList.',
  'params' => array('query' => $query, 'statement' => $statement),
  'type' => 'sql',
  'previous' => $e));
    }
  }

  public function getDataRow($queryOrStatement, $arrayToBind = null) {
    $value = $this->getData($queryOrStatement, null, $arrayToBind);
    return $value[0];
  }

  public function getLastInsertId() {
    return $this->getConnection()->lastInsertId();
  }

  public function insert($table, $data) {
    $statement = "insert into $table (" . implode(array_keys($data), ',') . ") values " . $this->getPDOinsertStr($data);
    $query = $this->prepare($statement);
    $this->bindArrayValue($query, $data);
    // return $query->execute();
    try {
      return $query->execute();
    } catch (\PDOException $e) {
      $this->transactionRollBackOnError();      
      throw new \mcc\obj\mccException(array('msg' => 'Error in insert.',
  'params' => array('query' => $query,
      'statement' => $statement),
  'type' => 'sql',
  'previous' => $e));
    }
  }

  public function update($table, $newFields, $searchFields) {
    $statement = "update $table set " . $this->getPDOupdateStr($newFields) . " where " . $this->getPDOwhereStr($searchFields, 'Where');
    $query = $this->prepare($statement);
    $this->bindArrayValue($query, array_merge($newFields, $searchFields));
    try {
      return $query->execute();
    } catch (\PDOException $e) {
      $this->transactionRollBackOnError();
      throw new \mcc\obj\mccException(array('msg' => 'Error in update.',
  'params' => array('query' => $query,
      'statement' => $statement),
  'type' => 'sql',
  'previous' => $e));
    }
  }

  public function updateById($table, $data, $id, $field = 'id') {
    $statement = "update $table set " . $this->getPDOupdateStr($data) . " where $field=:id";
    // error_log($statement);
    // error_log(json_encode($data));
    $query = $this->prepare($statement);
    $query->bindValue(':id', $id);
    $this->bindArrayValue($query, $data);    
    // return $query->execute();
    try {
      return $query->execute();
    } catch (\PDOException $e) {
      $this->transactionRollBackOnError();
      throw new \mcc\obj\mccException(array('msg' => 'Error in updateById.',
  'params' => array('query' => $query,
      'statement' => $statement),
  'type' => 'sql',
  'previous' => $e));
    }
  }

  /*
   * BINDERS
   */

  public function bindArrayValue($query, $array, $typeArray = false) {
    foreach ($array as $key => $value) {
      if ($typeArray) {
        $query->bindValue(":$key", $value, $typeArray[$key]);
      } else {
        $param = $this->getBindType($value);
        $query->bindValue(":$key", $value, $param);
      }
    }
  }

  public function bindArrayValueByQuestionMark($query, $array, $typeArray = false) {
    $ind = 1;
    foreach ($array as $key => $value) {
      if ($typeArray) {
        $query->bindValue($ind, $value, $typeArray[$key]);
      } else {
        $param = $this->getBindType($value);
        $query->bindValue($ind, $value, $param);
      }
      $ind++;
    }
  }

  public function getBindType($value) {
    if (is_int($value)) {
      $param = \PDO::PARAM_INT;
    } elseif (is_bool($value)) {
      $param = \PDO::PARAM_BOOL;
    } elseif (is_null($value)) {
      $param = \PDO::PARAM_NULL;
    } elseif (is_string($value)) {
      $param = \PDO::PARAM_STR;
    } else {
      $param = \PDO::PARAM_STR;
    }
  }

  /*
   * GET PARTS OF STATEMENTS
   */

  public function getPDOinsertStr($array) {
    $str = '';
    foreach ($array as $key => $value) {
      $str .= $str == '' ? '(' : ', ';
      $str .= ':' . $key;
    }
    $str .= ')';
    return $str;
  }

  public function getPDOupdateStr($array) {
    $str = '';
    foreach ($array as $key => $value) {
      if ($str != '') {
        $str .= ', ';
      }
      $str .= "$key = :$key";
    }
    return $str;
  }

  public function getPDOwhereStr(&$array, $temp = '') {
    $str = '';
    $newArray = array();
    foreach ($array as $key => $value) {
      if ($str != '') {
        $str .= ' and ';
      }
      $str .= "$key = :$key$temp";
      if ($temp != '') {
        $newArray[$key . $temp] = $value;
      }
    }
    if ($temp != '' && count($array) == count($newArray)) {
      $array = $newArray;
    }
    return $str;
  }

  public function prepare($str) {
    return self::$conn->prepare($str);
  }

  public function getConnection() {
    return self::$conn;
  }

  /*
   * PRIVATE METHODS
   */

  private function queryOrStament($queryOrStatement, $arrayToBind = null) {
    if (is_string($queryOrStatement)) {
      $statement = $queryOrStatement;
      $query = $this->prepare($statement);
    } else {
      $statement = null;
      $query = $queryOrStatement;
    }
    if (!is_null($arrayToBind)) {
      $this->bindArrayValue($query, $arrayToBind);
    }
    return array($query, $statement);
  }

  private function returnData($query, $returnAsArray = false, $indexColumn = null) {
    if (!$returnAsArray) {
      return $query->fetch(\PDO::FETCH_ASSOC);
    } else {
      $ar = array();
      while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
        if (is_null($indexColumn)) {
          array_push($ar, $row);
        } else {
          $ar[$row[$indexColumn]] = $row;
        }
      }
    }
    return $ar;
  }

  private function returnDataList($query, $key = null) {
    $ar = array();
    while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
      if (is_null($key)) {
        array_push($ar, $row[$key]);
      } else {
        array_push($ar, array_pop($row));
      }
    }
    return $ar;
  }

}
