<?php

namespace mcc\obj\news\repositories;

class news extends \mcc\obj\repo\repobase {

  // database variables (end with _ and are protected)
  protected $title_;
  protected $ingress_;
  protected $contents_;
  protected $published_;

  /**
   * @type: timestamp
   */
  protected $time_;

  /**
   * @type: timestamp
   */
  protected $edited_;

  // constant
  const TABLE = 'news';

  public function getComments() {
    return $this->getChildrennews_comments()['obj'];
  }

  public function getNext() {
    $db = self::getDB();
    $table = self::TABLE;
    $query = $db->prepare("select * from $table where time > '{$this->time_}' and published=1 order by time limit 0,1");
    try {
      $next = $db->getDataRow($query);
    } catch (\Exception $e) {
      return null;
    }
    $news = new self();
    $news->initByData($next);
    return $news;
  }

  public function getPrevious() {
    $db = self::getDB();
    $table = self::TABLE;
    $query = $db->prepare("select * from $table where time < '{$this->time_}' and published=1 order by time desc limit 0,1");
    try {
      $prev = $db->getDataRow($query);
    } catch (\Exception $e) {
      return null;
    }
    $news = new self();
    $news->initByData($prev);
    return $news;
  }

  public function getTitleData() {
    return array('id' => $this->id_, 'title' => $this->title_);
  }
  
  public function setTimeToCurrent(){
    $db = self::getDB();
    $table = self::TABLE;    
    $db->execute("update $table set time=CURRENT_TIMESTAMP where id={$this->id_}");
  }

}
