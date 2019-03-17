<?php
function get_pdo(){
  $dsn = 'sqlite:/var/www/db/tmall-bot-x1.db';
  $pdo = new PDO($dsn);
  return $pdo;
}

function get_map_value($map, $key, $default){
  if (key_exists($key, $map))
    return $map[$key];
  else
    return $default;
}

function send_curl_request($method, $url, $auth_code='', $post_data=null){
  $ch = curl_init(); 
  if (strtoupper($method)=='POST'){
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  }
  curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json","Authorization: Bearer $auth_code"]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
  curl_setopt($ch, CURLOPT_URL, $url); 
  return ['response'=>curl_exec($ch), 'info'=>curl_getinfo($ch)]; 
}

function uuid(){
  $chars = md5(uniqid(mt_rand(), true));
  $id = substr($chars,0,8) . '-';
  $id .= substr($chars,8,4) . '-';
  $id .= substr($chars,12,4) . '-';
  $id .= substr($chars,16,4) . '-';
  $id .= substr($chars,20,12);
  return $id;
}

function get_row($stmt){
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_rows($stmt){
  $rows = [];
  while( $row = $stmt->fetch(PDO::FETCH_ASSOC) )
    $rows[] = $row;
  return $rows;
}

function get_field_value($stmt, $field){
  $row = get_row($stmt);
  return $row[$field];
}

function get_device($user_id,$deviceId){
  $pdo = get_pdo();
  $stmt = $pdo->prepare("select * from devices where user_id=:user_id and deviceId=:deviceId");
  $stmt->bindParam(":user_id",$user_id,PDO::PARAM_STR);
  $stmt->bindParam(":deviceId",$deviceId,PDO::PARAM_STR);
  $stmt->execute();
  return get_row($stmt);
}

function get_device_list($user_id){
  $pdo = get_pdo();
  $stmt = $pdo->prepare("select * from devices where user_id=:user_id");
  $stmt->bindParam(":user_id",$user_id,PDO::PARAM_STR);
  $stmt->execute();
  return get_rows($stmt);
}	

function exist_device($user_id,$deviceId){
  return get_device($user_id, $deviceId);
}	

function delete_device($user_id,$deviceId){
  $pdo = get_pdo();
  $stmt = $pdo->prepare("delete from devices where user_id=:user_id and deviceId=:deviceId");
  $stmt->bindParam(":user_id",$user_id,PDO::PARAM_STR);
  $stmt->bindParam(":deviceId",$deviceId,PDO::PARAM_STR);
  return $stmt->execute();
}	

function update_device($user_id,$deviceId,$deviceName,$deviceType,
											 $zone, $brand, $model, $icon, $properties, $actions, $extensions){
  $pdo = get_pdo();
  $stmt = $pdo->prepare("update devices set
    deviceName=:deviceName,
		deviceType=:deviceType,
		zone=:zone,
		brand=:brand,
		model=:model,
		icon=:icon,
		properties=:properties,
		actions=:actions,
		extensions=:extensions
		where user_id=:user_id and deviceId=:deviceId");
  $stmt->bindParam(":user_id",$user_id,PDO::PARAM_STR);
  $stmt->bindParam(":deviceId",$deviceId,PDO::PARAM_STR);
  $stmt->bindParam(":deviceName",$deviceName,PDO::PARAM_STR);
  $stmt->bindParam(":deviceType",$deviceType,PDO::PARAM_STR);
  $stmt->bindParam(":zone",$zone,PDO::PARAM_STR);
  $stmt->bindParam(":brand",$brand,PDO::PARAM_STR);
  $stmt->bindParam(":model",$model,PDO::PARAM_STR);
  $stmt->bindParam(":icon",$icon,PDO::PARAM_STR);
  $stmt->bindParam(":properties",$properties,PDO::PARAM_STR);
  $stmt->bindParam(":actions",$actions,PDO::PARAM_STR);
  $stmt->bindParam(":extensions",$extensions,PDO::PARAM_STR);
  return $stmt->execute();
}

function insert_device($user_id,$deviceId,$deviceName,$deviceType,
											 $zone, $brand, $model, $icon, $properties, $actions, $extensions){
  $pdo = get_pdo();
  $stmt = $pdo->prepare("insert into devices (user_id,deviceId,deviceName,deviceType,zone,brand,model,icon,properties,actions,extensions)
    values(:user_id,:deviceId,:deviceName,:deviceType,:zone,:brand,:model,:icon,:properties,:actions,:extensions)");
  $stmt->bindParam(":user_id",$user_id,PDO::PARAM_STR);
  $stmt->bindParam(":deviceId",$deviceId,PDO::PARAM_STR);
  $stmt->bindParam(":deviceName",$deviceName,PDO::PARAM_STR);
  $stmt->bindParam(":deviceType",$deviceType,PDO::PARAM_STR);
  $stmt->bindParam(":zone",$zone,PDO::PARAM_STR);
  $stmt->bindParam(":brand",$brand,PDO::PARAM_STR);
  $stmt->bindParam(":model",$model,PDO::PARAM_STR);
  $stmt->bindParam(":icon",$icon,PDO::PARAM_STR);
  $stmt->bindParam(":properties",$properties,PDO::PARAM_STR);
  $stmt->bindParam(":actions",$actions,PDO::PARAM_STR);
  $stmt->bindParam(":extensions",$extensions,PDO::PARAM_STR);
  $stmt->execute();
}	

function get_device_obj($device){
  $obj['deviceId'] = $device['deviceId'];
  $obj['deviceName'] = $device['deviceName'];
  $obj['deviceType'] = $device['deviceType'];
  $obj['zone'] = $device['zone'];
  $obj['brand'] = $device['brand'];
  $obj['model'] = $device['model'];
  $obj['icon'] = $device['icon'];
  $obj['properties'] = json_decode($device['properties']);
  $obj['actions'] = json_decode($device['actions']);
  $obj['extensions'] = json_decode($device['extensions']);

  $property_values['powerstate']='off';
  $properties = [];
  foreach($obj['properties'] as $property){
    $properties[] = ["name"=> $property,"value"=>get_map_value($property_values,$property,'off')];
  }
  $obj['properties'] = $properties;
  return $obj;
}

?>
