<?php


namespace mcc\obj\news\repositories;


class news_comments extends \mcc\obj\repo\repobase {

  protected $news_id_;
  protected $contents_;
  protected $name_;
  protected $email_;
  protected $ip_;
  protected $reply_;
  
  /**
   * @type: timestamp
   */
  protected $time_;  
  
   // constant
  const TABLE = 'news_comments';

}
