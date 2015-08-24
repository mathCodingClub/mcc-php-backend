<?php

namespace mcc\obj\news\services;

class news extends \mcc\obj\service\base {

  const REPO = '\mcc\obj\news\repositories\news';
  const PAGINATION = 20;

  static public function get($id) {
    $news = self::getById_($id);
    $prev = $news->getPrevious();
    $next = $news->getNext();
    $data = array('news' => $news->get(),
        'news_previous' => is_null($prev) ? null : $prev->getTitleData(),
        'news_next' => is_null($next) ? null : $next->getTitleData());
    return $data;
  }

  static public function getCommentsOf($newsId) {
    $news = self::getbyId_($newsId);
    $comments = $news->getComments();
    return array('comments' => \mcc\obj\utils\ar::convertObjectsToArray($comments, 'get'));
  }

  static public function getTitles($from) {
    $dbHandle = self::getDB();
    $table = \mcc\obj\news\repositories\news::TABLE;
    $tableComments = \mcc\obj\news\repositories\news_comments::TABLE;
    $sum = self::PAGINATION;
    $query = $dbHandle->prepare("select id, title, time, edited, ingress, published from $table order by time desc limit :num, $sum");
    $query->bindValue(':num', (int) trim($from), \PDO::PARAM_INT);
    $data = $dbHandle->getData($query);
    foreach ($data as $key => $value) {
      $data[$key]['time'] = \mcc\obj\utils\time::datetime2timestamp($value['time']) * 1000;
      $data[$key]['edited'] = \mcc\obj\utils\time::datetime2timestamp($value['edited']) * 1000;
      $data[$key]['year'] = date('Y', \mcc\obj\utils\time::datetime2timestamp($value['time']));
      // select count of comments      
      $query = $dbHandle->prepare("select count(*) as numberOfComments from $tableComments where news_id=:news_id");
      $query->bindValue(':news_id', (int) $data[$key]['id'], \PDO::PARAM_INT);
      $temp = $dbHandle->getDataRow($query);
      $data[$key]['numberOfComments'] = $temp['numberOfComments'];
    }
    $query = $dbHandle->prepare("select count(*) as amount from $table");
    $amount = $dbHandle->getDataRow($query);
    // Fetch total number of news
    $news = array('news' => $data,
        'pagination' => array('from' => $from,
            'total' => $amount,
            'isLast' => ($from + count($data)) >= $amount['amount'],
            'next' => $from + count($data),
            'total' => $amount['amount'],
            'perPage' => self::PAGINATION));

    return $news;
  }

  static public function getLatest() {
    $dbHandle = self::getDB();
    // latest added
    $table = \mcc\obj\news\repositories\news::TABLE;
    $query = $dbHandle->prepare("select edited from $table order by edited desc limit 0,1");
    $data = $dbHandle->getDataRow($query);
    $edited = $data['edited'];
    // latest comment
    $table = \mcc\obj\news\repositories\news_comments::TABLE;
    try {
      $query = $dbHandle->prepare("select time from $table order by time desc limit 0,1");
      $data = $dbHandle->getDataRow($query);
      $commented = $data['time'];
    } catch (\Exception $e) {
      $commented = 0;
    }

    return max(array($edited, $commented));
  }

  static public function insertComment($newsId, $contents, $name, $email) {
    $dbHandle = self::getDB();
    $table = \mcc\obj\news\repositories\news_comments::TABLE;
    return $dbHandle->insert($table, array('news_id' => $newsId,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'contents' => $contents, 'name' => $name, 'email' => $email));
  }

  static public function updateComment($data) {
    $obj = new \mcc\obj\news\repositories\news_comments($data['id']);
    $obj->set($data);
    return $obj;
  }

  static public function deleteComment($id) {
    \mcc\obj\news\repositories\news_comments::delete($id);
  }

  static public function setTimeToCurrent($id) {
    $news = self::getById_($id);
    $news->setTimeToCurrent();
  }

}
