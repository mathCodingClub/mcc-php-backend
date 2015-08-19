<?php

namespace mcc\rest\news;

class pri extends \mcc\obj\slimClass\service {

  private $user;  

  public function middleware() {
    $this->user = \mcc\obj\user\services\user::initByCookie();
  }
  
  /**
   * @route: /:newsId/settocurrenttime
   */
  public function getSetTimeToCurrent($newsId) {
    \mcc\obj\news\services\news::setTimeToCurrent($newsId);
    // $this->sendArrayAsJSON($data);
    $this->sendArrayAsJSON(array(
        'msg' => 'Date changed to this moment successfully.',
        'dict' => 'ACTION_OK'
    ));
  }
  
  public function post() {
    $data = $this->getBodyAsJSON();
    \mcc\obj\news\services\news::create($data);
    $this->response->body(json_encode(array('msg' => 'New article added successfully.',
        'dict' => 'NEWS.ADDED'), JSON_NUMERIC_CHECK));
  }

  public function put() {
    $data = $this->getBodyAsJSON();
    unset($data['edited']);
    unset($data['time']);
    \mcc\obj\news\services\news::save($data);
    $this->response->body(json_encode(array('msg' => 'News modified successfully.',
        'dict' => 'NEWS.MODIFIED'), JSON_NUMERIC_CHECK));
  }

  public function delete($id) {
    \mcc\obj\news\services\news::deleteById($id);
    $this->response->body(json_encode(array('msg' => 'News deleted successfully.',
        'dict' => 'NEWS.DELETED'), JSON_NUMERIC_CHECK));
  }

  public function deleteComment($id) {
    \mcc\obj\news\services\news::deleteComment($id);
    $this->response->body(json_encode(array('msg' => 'Comment deleted successfully.',
        'dict' => 'NEWS.COMMENT_DELETED'), JSON_NUMERIC_CHECK));
  }

  public function putComment() {
    $data = $this->getBodyAsJSON();   
    unset($data['time']);
    \mcc\obj\news\services\news::updateComment($data);
    $this->response->body(json_encode(array('msg' => 'Comment modified successfully.',
        'dict' => 'NEWS.COMMENT_MODIFIED'), JSON_NUMERIC_CHECK));
  }

}
