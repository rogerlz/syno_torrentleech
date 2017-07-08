<?php

$torrent_url = 'https://www.torrentleech.org/'. $_GET['torrent'];
$login_url = 'https://classic.torrentleech.org/user/account/login/';

$cookie = '/tmp/ng_torrentleech_download.cookie';

$PostData = array('username'=>$_GET['username'],'password'=>$_GET['password'],'remember_me'=>'Y','login'=>'submit');
$PostData = http_build_query($PostData);

if (file_exists($cookie)) {
  unlink($cookie);
}

$fscurl = curl_init();

$headers = array
  (
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8',
    'Accept-Language: ru,en-us;q=0.7,en;q=0.3',
    'Accept-Encoding: deflate',
    'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7'
  );
curl_setopt($fscurl, CURLOPT_HTTPHEADER,$headers);
curl_setopt($fscurl, CURLOPT_URL, $login_url);
curl_setopt($fscurl, CURLOPT_FAILONERROR, 1);
curl_setopt($fscurl, CURLOPT_REFERER, $login_url);
curl_setopt($fscurl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($fscurl, CURLOPT_RETURNTRANSFER,1);
curl_setopt($fscurl, CURLOPT_TIMEOUT, 20);
curl_setopt($fscurl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
curl_setopt($fscurl, CURLOPT_POST, 1);
curl_setopt($fscurl, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($fscurl, CURLOPT_COOKIEFILE, $cookie);
curl_setopt($fscurl, CURLOPT_POSTFIELDS, $PostData);
curl_setopt($fscurl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($fscurl, CURLOPT_SSL_VERIFYPEER, false);
curl_exec($fscurl);

curl_setopt($fscurl, CURLOPT_URL, $torrent_url);
curl_setopt($fscurl, CURLOPT_POST, 0);
$response = curl_exec($fscurl);
curl_close($fscurl);

preg_match("/\/.*\/(.*\.torrent)/", $_GET['torrent'], $matches);

$filename = $matches[1];

header( 'Content-Type: application/x-bittorrent' );
header( 'Content-Disposition: inline; filename="' . $filename);
echo $response;
?>
