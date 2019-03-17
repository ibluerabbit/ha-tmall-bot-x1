<?php
require_once __DIR__.'/functions.php';

class Response{
  public $namespace;
  public $name;
  public $deviceId;
  public $properties;
  public $errorCode;
  public $message;
  public $response;

  public function __construct($namespace, $name){
    $this->response = [];
    $this->namespace= $namespace;
    $this->name = $name;
    $this->setHeader($namespace, $name);
  }

  public function setHeader($namespace, $name){
    $this->response['header']['namespace'] =$namespace;
    $this->response['header']['name'] = $name;
    $this->response['header']['messageId'] = uuid();
    $this->response['header']['payLoadVersion'] = 1;
  }

  public function setDiscoveryResponse($devices){
    $data=[];
    foreach($devices as $device){
      $data[] = get_device_obj($device);
    }
    $this->response['payload']['devices'] = $data;
  }

  public function setQueryResponse($deviceId,$properties) { 
    $this->deviceId = $deviceId;
    $this->properties=$properties;

    $this->response['payload']['deviceId'] = $this->deviceId;
    $this->response['properties']=$this->properties;
  } 

  public function setControlResponse($deviceId) { 
    $this->deviceId = $deviceId;
    $this->response['payload']['deviceId'] = $this->deviceId;
  } 

  public function setErrorResponse($errorCode, $message){
    $this->errorCode = $errorCode;
    $this->message = $message;
    $this->response['payload']['errorCode'] = $this->errorCode;
    $this->response['payload']['message'] = $this->message;
  }

  public function send(){
    $response_str = json_encode($this->response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo($response_str);
    return $response_str;
  }
}

?>
