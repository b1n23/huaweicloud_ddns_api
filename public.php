<?php
require 'signer.php';
$signer = new Signer();
//参数区 开始
if (isset($_GET['ak']) && isset($_GET['sk']) && isset($_GET['domain_name'])) {  
    $ak = $_GET['ak'];  
    $sk = $_GET['sk'];  
    $domain_name = $_GET['domain_name'];
} else {
  echo "缺少必要的参数。";
  exit();
}

//$endpoint = "https://dns.cn-east-3.myhuaweicloud.com"; //华东-上海一
$endpoint = "https://dns.ap-southeast-1.myhuaweicloud.com"; //中国-香港
//参数区 结束
$record_value = get_client_ip();

if (is_ipv4($record_value)) {
  $record_type = "A";
} elseif (is_ipv6($record_value)) {
  $record_type = "AAAA";
} else {
  echo "Invalid IP";
  exit();
}

$signer = new Signer();
$signer->Key = $ak;
$signer->Secret = $sk;

$getRecordId_response = getRecordId($signer, $domain_name, $record_type);

$zoneId = $getRecordId_response['zoneId'];
$recordId = $getRecordId_response['recordId'];
$domain_name = $getRecordId_response['name'];

UpdateRecordSet($signer, $zoneId, $recordId, $domain_name, $record_type, $record_value);

function is_ipv4($ip){
  return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
}
function is_ipv6($ip){
  return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
}
function get_client_ip()
{
  $ip = '';
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    //$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim($ips[0]);
  } else {
    $ip = $_SERVER['REMOTE_ADDR'];
  }
  return $ip;
}
function getRecordId($signer, $domain_name, $record_type)
{
  global $endpoint;
  $request_url = $endpoint . '/v2/recordsets?search_mode=equal&type=' . $record_type . '&name=' . $domain_name . '&limit=1';
  $req = new Request('GET', $request_url);
  $req->body = '';
  $curl = $signer->Sign($req);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

  $response = curl_exec($curl);
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  if ($status == 0) {
    echo curl_error($curl);
    curl_close($curl);
    exit();
  } else {
    curl_close($curl);
    echo $response;
    $data = json_decode($response, true);
    $recordId = $data['recordsets'][0]['id'];
    $zoneId = $data['recordsets'][0]['zone_id'];
    $name = $data['recordsets'][0]['name'];
    return array("recordId" => $recordId, "zoneId" => $zoneId, "name" => $name);
  }
}

function UpdateRecordSet($signer, $zoneId, $recordId, $domain_name, $record_type, $record_value)
{
  global $endpoint;
  $request_url = $endpoint . '/v2/zones/' . $zoneId . '/recordsets/' . $recordId;
  $req = new Request('PUT', $request_url);
  $req->headers = array(
    'content-type' => 'application/json',
  );
  $RecordSet_body = array(
    "name" => $domain_name,
    "type" => $record_type,
    "records" => array($record_value)
  );
  $req_body = json_encode($RecordSet_body);

  $req->body = $req_body;
  $curl = $signer->Sign($req);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

  $response = curl_exec($curl);
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  if ($status == 0) {
    echo curl_error($curl);
  } else {
    echo $response;
  }
  curl_close($curl);
}