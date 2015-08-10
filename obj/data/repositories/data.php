<?php

namespace mcc\obj\data\repositories;

class data extends \mcc\obj\repo\repobase {

  protected $code_;
  protected $content_;
  protected $title_;

  /**
   * @type: timestamp
   */
  protected $created_;

  /**
   * @type: timestamp
   */
  protected $updated_;

  // constant
  const TABLE = 'data';

  public function getTitleData() {
    return array('title' => $this->gettitle(), 
        'code' => $this->getcode(), 
        'id' => $this->getid());
  }

}
