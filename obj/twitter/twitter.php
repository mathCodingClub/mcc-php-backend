<?php

namespace mcc\obj\twitter;

class twitter {

  static private $settings;
  static private $TMPKEY;
  const CACHE_TIME = 30;
  
  
  static public function config($setting) {
    $mid = array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : 'cli';
    self::$TMPKEY = 'mcc-twitter-' . $mid . '-';
    self::$settings = $setting;
  }

  
  static public function fetch($url,$getfield){
    $requestMethod = 'GET';
    $twitter = new \TwitterAPIExchange(self::$settings);
    return $twitter->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest();    
  }
  
  static public function search($query, $count = 100, $maxid = null) {
    $key = self::$TMPKEY . 'search-' . $query . '-' . $maxid;    
    if (!\mcc\obj\cache\cache::isOlderThan($key, self::CACHE_TIME)) {
      return \mcc\obj\cache\cache::get($key);
    }
    $url = 'https://api.twitter.com/1.1/search/tweets.json';
    $getfield = "?q=$query&include_entities=true&count=$count";
    $getfield .= is_null($maxid) ? '' : "&max_id=$maxid";
    $str = self::fetch($url,$getfield);
    $data = self::parseReply($str);
    \mcc\obj\cache\cache::set($key, $data);
    return $data;
  }

  static public function userTimeline($user, $count = 100, $maxid = null) {
    $key = self::$TMPKEY . 'userTimeline-' . $user . '-' . $maxid;
    if (!\mcc\obj\cache\cache::isOlderThan($key, self::CACHE_TIME)) {
      return \mcc\obj\cache\cache::get($key);
    }
    $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    $getfield = "?screen_name=$user&count=$count";
    $getfield .= is_null($maxid) ? '' : "&max_id=$maxid";    
    $str = self::fetch($url,$getfield);
    $ar = json_decode($str, true);
    $data = self::parseTweets($ar);
    \mcc\obj\cache\cache::set($key, $data);
    return $data;
  }

  static public function homeTimeline($count = 100, $maxid = null) {
    $key = self::$TMPKEY . 'homeTimeline-' . $maxid;
    if (!\mcc\obj\cache\cache::isOlderThan($key, self::CACHE_TIME)) {
      return \mcc\obj\cache\cache::get($key);
    }
    $url = 'https://api.twitter.com/1.1/statuses/home_timeline.json';    
    $getfield = "?&count=$count";
    $getfield .= is_null($maxid) ? '' : "&max_id=$maxid";    
    $str = self::fetch($url,$getfield);
    $ar = json_decode($str, true);
    if (array_key_exists('errors', $ar)) {
      throw new \mcc\obj\mccException(array(
  'dict' => 'TWITTER.RATE_LIMIT_EXCEEDED',
  'param' => array('code' => $ar['errors'][0]['code'],
      'reply' => json_encode($ar, JSON_PRETTY_PRINT)),
  'msg' => $ar['errors'][0]['message']));
    }
    $data = self::parseTweets($ar);
    \mcc\obj\cache\cache::set($key, $data);
    return $data;
  }

  /*
   * PRIVATE
   */

  static private function parseReply($str) {
    $ar = json_decode($str, true);
    //print_r($ar['statuses'][0]);
    //die();    
    return self::parseTweets($ar['statuses']);
  }

  static private function parseTweets($tweetsFetched) {
    $tweets = array();
    foreach ($tweetsFetched as $tweet) {
      $tw = array(
          'id' => $tweet['id'],
          'link' => 'https://twitter.com/' . $tweet['user']['screen_name'] . '/status/' . $tweet['id'],
          'text' => $tweet['text'],
          'time' => strtotime($tweet['created_at']) * 1000,
          'hashtags' => self::getHashtags($tweet),
          'urls' => self::getUrls($tweet),
          'images' => self::getImages($tweet),
          'user' => self::getUser($tweet),
          'userMentions' => self::getUserMentions($tweet)
      );

      $tw['html'] = self::formHtml($tw);

      array_push($tweets, $tw);
    }
    return $tweets;
  }

  static private function formHtml($tweet) {
    $str = $tweet['text'];


    foreach ($tweet['hashtags'] as $tag) {
      $ht = $tag['text'];
      $str = str_replace('#' . $ht, '<mcc-link url="https://twitter.com/hashtag/' . $ht . '?src=hash">#' . $ht . '</mcc-link>', $str);
    }
    foreach ($tweet['urls'] as $url) {
      $str = str_replace($url['urlShort'], '<mcc-link url="' . $url['url'] . '" target="blank">' . $url['text'] . '</mcc-link>', $str);
    }
    foreach ($tweet['userMentions'] as $user) {
      $str = str_replace('@' . $user['screenName'], '<mcc-link url="https://twitter.com/' . $user['screenName'] . '">@' . $user['screenName'] . '</mcc-link>', $str);
    }
    $str = preg_replace('@(http://t.co/)([a-zA-Z0-9]*)$@', '', trim($str));
    return $str;
  }

  static private function getHashtags($tweet) {
    $ar = array();
    if (array_key_exists('hashtags', $tweet['entities'])) {
      foreach ($tweet['entities']['hashtags'] as $en) {
        array_push($ar, array('text' => $en['text']));
      }
    }
    return $ar;
  }

  static private function getImages($tweet) {
    $ar = array();
    if (array_key_exists('media', $tweet['entities'])) {
      foreach ($tweet['entities']['media'] as $en) {
        if ($en['type'] == 'photo') {
          array_push($ar, array('src' => $en['media_url']));
        }
      }
    }
    return $ar;
  }

  static private function getUrls($tweet) {
    $ar = array();
    if (array_key_exists('urls', $tweet['entities'])) {
      foreach ($tweet['entities']['urls'] as $en) {
        array_push($ar, array('url' => $en['expanded_url'],
            'urlShort' => $en['url'],
            'text' => $en['display_url']));
      }
    }
    return $ar;
  }

  static private function getUser($tweet) {
    return array(
        'screenName' => $tweet['user']['screen_name'],
        'name' => $tweet['user']['name'],
        'profile' => $tweet['user']['profile_image_url']
    );
  }

  static private function getUserMentions($tweet) {
    $ar = array();
    if (array_key_exists('user_mentions', $tweet['entities'])) {
      foreach ($tweet['entities']['user_mentions'] as $en) {
        array_push($ar, array('name' => $en['name'],
            'screenName' => $en['screen_name']));
      }
    }
    return $ar;
  }

}
