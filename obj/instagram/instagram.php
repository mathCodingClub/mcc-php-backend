<?php

namespace mcc\obj\instagram;

class instagram {

  static private $accessToken;
  static private $TMPKEY;

  const CACHE_TIME = 30;

  static public function config($accessToken) {
    $mid = array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : 'cli';
    self::$TMPKEY = 'mcc-instagram-' . $mid . '-';
    self::$accessToken = $accessToken;
  }

  static public function getMediaByTag($tag, $count = 100, $maxid = null) {
    $key = self::$TMPKEY . 'mediaByTag-' . $tag . '-' . $maxid;
    if (!\mcc\obj\cache\cache::isOlderThan($key, self::CACHE_TIME)) {
      return \mcc\obj\cache\cache::get($key);
    }
    $url = 'https://api.instagram.com/v1/tags/tretrial/media/recent?access_token=' . self::$accessToken;
    $url.= "&count=$count";
    $url .= is_null($maxid) ? '' : "&min_tag_id=$maxid";    
    $ret = self::executeGet($url);
    $data = self::parseReply(json_decode($ret, true)['data']);
    \mcc\obj\cache\cache::set($key, $data);
    return $data;
  }

  static public function getMediaByUser($user, $count = 20, $maxid = null) {
    $key = self::$TMPKEY . 'mediaByUser-' . $user . '-' . $maxid;
    if (!\mcc\obj\cache\cache::isOlderThan($key, self::CACHE_TIME)) {
      return \mcc\obj\cache\cache::get($key);
    }
    $url = "https://api.instagram.com/v1/users/$user/media/recent/?access_token=" . self::$accessToken;
    $url.= "&count=$count";
    $url .= is_null($maxid) ? '' : "&max_id=$maxid";
    $ret = self::executeGet($url);    
    $data = self::parseReply(json_decode($ret, true)['data']);
    \mcc\obj\cache\cache::set($key, $data);
    return $data;
  }

  static private function executeGet($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
        'Accept: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    return curl_exec($ch);
  }

  static private function parseReply($posts) {
    $data = array();
    foreach ($posts as $post) {
      //error_log(json_encode($post));      
      //print_r($post);
      //die();
      $ar = array(
          'id' => $post['id'], // should be $post['id'], but max_id does not work in queries
          'link' => $post['link'],
          'type' => $post['type'],
          'image' => $post['images']['standard_resolution']['url'],
          'time' => $post['caption']['created_time'] * 1000,
          'text' => $post['caption']['text'],
          'hashtags' => self::getHashtags($post),
          'user' => array(
              'name' => $post['caption']['from']['full_name'],
              'profile' => $post['caption']['from']['profile_picture'],
              'screenName' => $post['caption']['from']['username'])
      );
      $ar['html'] = self::formHtml($post, $ar);
      if ($ar['type'] == 'video') {
        $ar['video'] = $post['videos']['standard_resolution'];
      }
      array_push($data, $ar);
    }
    return $data;
  }

  static private function formHtml($post, $ar) {
    $str = ' ' . $ar['text'] . ' ';
    // links
    $str = preg_replace('@(https?://)(([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<mcc-link url="$1$2">$2</mcc-link>', $str);
    // users
    $str = preg_replace('#([,\?\.\s])(@)([a-zA-Z0-9\_]*)([,\?\.\s])#', '$1<mcc-link url="https://instagram.com/$3">@$3</mcc-link>$4', $str);

    // dont do this with regular expression, because it might get confused with hashbangs
    foreach ($ar['hashtags'] as $tag) {
      $ht = $tag['text'];
      $str = preg_replace('@(#' . $ht . ')([,\?\s\-])@', '<mcc-link url="https://instagram.com/explore/tags/' . $ht . '">#' . $ht . '</mcc-link>$2', $str);
    }

    $str = str_replace("\n", '<br>', $str);
    return trim($str);
  }

  static private function getHashtags($post) {
    $ar = array();
    if (array_key_exists('tags', $post)) {
      foreach ($post['tags'] as $en) {
        array_push($ar, array('text' => $en));
      }
    }
    \mcc\obj\utils\ar::sortByStringLengthDesc($ar, 'text');
    return $ar;
  }

}
