<?php
class SynoDLMSearchNGTorrentLeech {
  private $wurl = 'https://classic.torrentleech.org';
  private $wurl_host = 'https://classic.torrentleech.org';
  private $qurl = '/torrents/browse/index/query/%s/order/desc/orderby/seeders';
  private $lurl = '/user/account/login/';
  private $download_url = 'https://127.0.0.1/tl_download.php';
  private $COOKIE = '/tmp/ng_torrentleech.cookie';
  private $debug = false;

  // auth
  private $username = '';
  private $secret = '';

  private function DebugLog($str) {
    if ($this->debug==true) {
      file_put_contents('/tmp/ng_torrentleech.log',$str."\r\n\r\n",FILE_APPEND);
    }
  }

  public function __construct() {
    $this->qurl = $this->wurl.$this->qurl;
    $this->wurl_host = $this->wurl_host.$this->lurl;
    $this->lurl = $this->wurl.$this->lurl;
  }

  public function prepare($curl, $query, $username, $password) {
    $url = $this->qurl;
    curl_setopt($curl, CURLOPT_URL, sprintf($url, urlencode($query)));
    curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);


    if ($username !== NULL && $password !== NULL) {
      $this->VerifyAccount($username, $password);
      curl_setopt($curl, CURLOPT_COOKIEFILE, $this->COOKIE);
    }

  }

  public function GetCookie()
  {
    return $this->COOKIE;
  }

  public function VerifyAccount($username, $password) {
    $ret = FALSE;

    if (file_exists($this->COOKIE)) {
      unlink($this->COOKIE);
    }

    $PostData = array('username'=>$username,'password'=>$password,'remember_me'=>'Y','login'=>'submit');
    $PostData = http_build_query($PostData);

    $this->username = $username;
    $this->secret = $password;

    $fscurl = curl_init();
    $headers = array
      (
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8',
        'Accept-Language: ru,en-us;q=0.7,en;q=0.3',
        'Accept-Encoding: deflate',
        'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7'
      );
    curl_setopt($fscurl, CURLOPT_HTTPHEADER,$headers);
    curl_setopt($fscurl, CURLOPT_URL, $this->lurl);
    curl_setopt($fscurl, CURLOPT_FAILONERROR, 1);
    curl_setopt($fscurl, CURLOPT_REFERER, $this->lurl);
    curl_setopt($fscurl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($fscurl, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($fscurl, CURLOPT_TIMEOUT, 20);
    curl_setopt($fscurl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
    curl_setopt($fscurl, CURLOPT_POST, 1);
    curl_setopt($fscurl, CURLOPT_COOKIEJAR, $this->COOKIE);
    curl_setopt($fscurl, CURLOPT_COOKIEFILE, $this->COOKIE);
    curl_setopt($fscurl, CURLOPT_POSTFIELDS, $PostData);
    curl_setopt($fscurl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($fscurl, CURLOPT_SSL_VERIFYPEER, false);


    $Result = curl_exec($fscurl);

    if (FALSE !== strpos($Result, 'Welcome back')) {
      $ret = TRUE;
      // Dirty patch to enable downloading direct torrents both with www and non-www links.
      curl_setopt($fscurl, CURLOPT_URL, $this->wurl_host);
      curl_setopt($fscurl, CURLOPT_REFERER, $this->wurl_host);
      curl_exec($fscurl);
    }else{
      $this->DebugLog("Login fail: " . $Result);
    }
    curl_close($fscurl);

    return $ret;
  }


  public function parse($plugin, $response) {
    $cut_start = stripos($response,"<div id=torrents>");
    $cut_end = stripos($response,"<h2>SEARCH");
    $response = substr($response,$cut_start);

    $response  = preg_replace('/(\s+)/is',' ',$response);
    //$this->DebugLog("Response: ". $response);
    $regexp2 = "<tr class=\".*\">(.*)<\/tr>";
    $regexp_title_page_category_date = "<td class=\"name\"><span class=\"title\"><a href=\"(.*)\">(.*)<\/a><\/span><br> Added in <b>(.*)<\/b> on (.*)<\/td>";
    //$regexp_download = "<a href=\"(.*\/download.*)\"><img";
    $regexp_download = "<td\sclass=\"quickdownload\">\s<a href=\".*(\/download.*)\"><img\salt=\"Quick";
    //$regexp_download = "<td\sclass=\"quickdownload\">\s<a href=\"(.*\/download.*)\"><img\salt=\"Quick";
    $regexp_size = "<td>([0-9\,\.]+) (GB|MB|KB|TB)<\/td>";
    $regexp_seeds = "<td class=\"seeders\">([0-9]+)<\/td>";
    $regexp_leechs = "<td class=\"leechers\">([0-9]+)<\/td>";

    $res=0;
    if(preg_match_all("/$regexp2/siU", $response, $matches2, PREG_SET_ORDER)) {
      foreach($matches2 as $match2) {
        $title="Unknown title";
        $download="Unknown download";
        $size=0;
        $datetime="1978-09-28";
        $page="Default page";
        $hash="Hash unknown";
        $seeds=0;
        $leechs=0;
        $category="Unknown category";

        if(preg_match_all("/$regexp_title_page_category_date/siU", $match2[0], $matches, PREG_SET_ORDER)) {
          foreach($matches as $match) {
            $page = $this->purl.$match[1];
            $title =  $match[2];
            $category = $match[3];
            $datetime = $match[4];
            $hash = md5($res.$title);
          }
        }

        if(preg_match_all("/$regexp_download/siU", $match2[0], $matches, PREG_SET_ORDER)) {
          foreach($matches as $match) {
            $download = $this->download_url . '?torrent=' . $match[1] . '&username=' . $this->username . '&password=' . $this->secret;
            $this->DebugLog("rogerio: download var " . $download);
          }
        }

        if(preg_match_all("/$regexp_size/siU", $match2[0], $matches, PREG_SET_ORDER)) {
          foreach($matches as $match) {
            $size = str_replace(",",".",$match[1]);
            switch (trim($match[2])){
            case 'KB':
              $size = $size * 1024;
              break;
            case 'MB':
              $size = $size * 1024 * 1024;
              break;
            case 'GB':
              $size = $size * 1024 * 1024 * 1024;
              break;
            case 'TB':
              $size = $size * 1024 * 1024 * 1024 * 1024;
              break;
            }
            $size = floor($size);
          }
        }

        if(preg_match_all("/$regexp_seeds/siU", $match2[0], $matches, PREG_SET_ORDER)) {
          foreach($matches as $match) {
            $seeds = $match[1];
          }
        }

        if(preg_match_all("/$regexp_leechs/siU", $match2[0], $matches, PREG_SET_ORDER)) {
          foreach($matches as $match) {
            $leechs = $match[1];
          }
        }

        if ($title!="Unknown title") {
          $plugin->addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category);
          $res++;
        }
      }
    }

    return $res;
  }
}
?>
