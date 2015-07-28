<?php

namespace mcc\rest\news;

class pub extends \mcc\obj\slimClass\service {

  public function get($id) {    
    $news = \mcc\obj\news\services\news::get($id);
    $this->sendArrayAsJSON($news);
  }

  public function getTitles($from = 0) {    
    $titles = \mcc\obj\news\services\news::getTitles($from);
    $this->sendArrayAsJSON($titles);
  }

  public function getComments($newsId) {    
    $comments = \mcc\obj\news\services\news::getCommentsOf($newsId);
    $this->sendArrayAsJSON($comments);
  }

  public function getLatestTimestamp() {
    $timestamp = \mcc\obj\news\services\news::getLatest();
    $this->sendArrayAsJSON(array('timestamp' => $timestamp));    
  }

  public function postComment($newsId) {
    $data = $this->getBodyAsJSON();
    if (trim($data['contents']) == '' || trim($data['name']) == '') {
      throw new \mcc\obj\mccException(array('msg' => 'Fill in contents and name', 
          'code' => 500, 
          'params' => array('errors' => array('name','contents')),
          'dict' => 'NEWS.COMMENT_MISSING_FIELDS'));
    }
    \mcc\obj\news\services\news::insertComment($newsId, $data['contents'], $data['name'], $data['email']);
    $this->sendArrayAsJSON(array('msg' => 'Comment added successfully.', 'dict' => 'NEWS.COMMENT_ADDED'));
  }

}
