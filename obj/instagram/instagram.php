<?php

namespace mcc\obj\instagram;

class instagram {

  static private $accessToken;
  static private $TMPKEY;

  static public function setAccessToken($accessToken) {
    $mid = array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : 'cli';
    self::$TMPKEY = 'mcc-instagram-' . $mid . '-';
    self::$accessToken = $accessToken;
  }

  static public function getMediaByTag($tag) {

    $key = self::$TMPKEY . 'mediaByTag-' . $tag;
    if (!\mcc\obj\cache\cache::isOlderThan($key, 30)) {
      return \mcc\obj\cache\cache::get($key);
    }

    $ch = curl_init();
    $url = 'https://api.instagram.com/v1/tags/tretrial/media/recent?access_token=' . self::$accessToken;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
        'Accept: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($ch);
    $data = self::parseReply(json_decode($ret, true)['data']);
    \mcc\obj\cache\cache::set($key, $data);
    return $data;
  }

  static private function parseReply($posts) {
    $data = array();
    foreach ($posts as $post) {
      //print_r($post);
      $ar = array(
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
      $ar['html'] = self::formHtml($post,$ar);
      if ($ar['type'] == 'video') {
        $ar['video'] = $post['videos']['standard_resolution'];
      }
      array_push($data, $ar);
    }
    return $data;
  }

  static private function formHtml($post,$ar) {
    $str = ' ' . $ar['text'] . ' ';    
    // links
    $str = preg_replace('@(https?://)(([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1$2" target="blank">$2</a>', $str);
    // users
    $str = preg_replace('#([,\?\.\s])(@)([a-zA-Z0-9\_]*)([,\?\.\s])#', '$1<a href="https://instagram.com/$3" target="instagram">@$3</a>$4', $str);
    
    // dont do this with regular expression, because it might get confused with hashbangs
    foreach ($ar['hashtags'] as $tag) {
      $ht = $tag['text'];
      $str = preg_replace('@(#' . $ht . ')([,\?\s\-])@', '<a href="https://instagram.com/explore/tags/' . $ht . '" target="instagram">#' . $ht . '</a>$2', $str);
    }        

    $str = str_replace("\n",'<br>',$str);
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
