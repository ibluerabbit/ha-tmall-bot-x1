<?php
require_once __DIR__.'/oauth2/server.php';
require_once __DIR__.'/actions.php';

$request_str = file_get_contents("php://input");

define('DEBUG', false);
if (DEBUG){
  $request_str = '{
  "header":{
      "namespace":"AliGenie.Iot.Device.Query",
      "name":"Query",
      "messageId":"1bd5d003-31b9-476f-ad03-71d471922820",
      "payLoadVersion":1
   },
   "payload":{
       "accessToken":"access token",
       "deviceId":"switch.ke_ting_dian_deng"
   }
}
';
}
$obj = json_decode($request_str);
error_log('----request----');
error_log($request_str);

$request = OAuth2\Request::createFromGlobals();

if (isset($obj->payload->accessToken)){
  $request->query['access_token'] = $obj->payload->accessToken;
}else{
  if (!DEBUG) die('access_token not found!');
}

if (!$server->verifyResourceRequest($request)) {
  if (!DEBUG) {
    error_log('access denied!');
    $server->getResponse()->send();
    die();
  }
}

$token = $server->getAccessTokenData($request);
if (DEBUG){
  $user_id = 'username';
}else{
  $user_id = $token['user_id'];
}

error_log("user_id: $user_id");
$user = $storage->getUser($user_id);

switch($obj->header->namespace){
  case 'AliGenie.Iot.Device.Discovery':
    $result = device_discovery($obj, $user);
    break;

  case 'AliGenie.Iot.Device.Control':
    $result = device_control($obj, $user);
    break;

  case 'AliGenie.Iot.Device.Query':
    $result = device_query($obj, $user);
    break;
  default:
    $echo = 'error!';
}

error_log('----response----');
error_log($result);

?>
