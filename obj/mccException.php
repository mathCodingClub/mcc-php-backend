<?php

namespace mcc\obj;

/*
 * Different types:
 *  general
 *  sql
 *  nodata
 * 
 */

class mccException extends \Exception {

  private $type = 'undefined';
  private $translate = null;
  private $params = array();

  public function __construct($dict = array()) {
    if (!array_key_exists('msg', $dict)) {
      $dict['msg'] = 'mccException';
    }
    if (!array_key_exists('code', $dict)) {
      $dict['code'] = 400;
    }
    if (!array_key_exists('previous', $dict)) {
      $dict['previous'] = null;
    }
    parent::__construct($dict['msg'], $dict['code'], $dict['previous']);
    if (array_key_exists('params', $dict)) {
      $this->params = $dict['params'];
    }
    if (array_key_exists('dict', $dict)) {
      $this->translate = $dict['dict'];
    }
    if (array_key_exists('type', $dict)) {
      $this->type = $dict['type'];
    }
    if ($this->type == 'sql') {
      if (array_key_exists('query', $this->params)) {
        $this->params['debugDumpParams'] = $this->params['query']->debugDumpParams();
        $this->params['errorInfo'] = $this->params['query']->errorInfo();
      }
    }
  }

  public function getParams() {
    return $this->params;
  }

  public function getDictionaryCode() {
    return $this->translate;
  }

  public function getType() {
    return $this->type;
  }

}
