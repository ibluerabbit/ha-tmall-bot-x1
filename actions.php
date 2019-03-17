<?php
require_once __DIR__.'/functions.php';
require_once __DIR__.'/response.php';

function device_discovery($obj, $user){
  $devices = get_device_list($user['user_id']);
  $response = new Response($obj->header->namespace, $obj->header->name.'Response');
  $response->setDiscoveryResponse($devices);
  return $response->send();
}

function  device_query($obj, $user){       
  $device_id=$obj->payload->deviceId;
  $device = get_device($user['user_id'],$device_id);
  $device_obj = get_device_obj($device);
  $states=[];
  $url = $user['ha_url']."/api/states/".$device_id; 
  $query_response = send_curl_request('GET', $url, $user['ha_auth_code'])['response'];
  $entity = json_decode($query_response);
  if (isset($entity->entity_id)){
    $state = $entity->state;
    if($state != 'on' && $state != 'off'){
      $states[]=['name'=>'powerstate','value'=>'on'];
      if ($device_obj->properties){
        foreach($device_obj->properties as $key=>$val)
          $states[]=['name'=>$key, 'value'=>$state];
      }
    } else {
      $states[]=['name'=>'powerstate','value'=>$state];
    }

    $response = new Response($obj->header->namespace, $obj->header->name.'Response');
    $response->setQueryResponse($device_id,$states);
  }else{
    $response = new Response($obj->header->namespace, 'ErrorResponse');
    $response->setErrorResponse("DEVICE_IS_NOT_EXIST","device is not exist");
  }

  return $response->send(); 
}	

function  device_control($obj, $user){
  /* TODO: validate user device */

  $action = '';
  $ha_device = '';
  $device_id=$obj->payload->deviceId;
  $device_type = substr($device_id,0,stripos($device_id,"."));

  $value = isset($obj->payload->value) ? $obj->payload->value : '';

  if (in_array($device_type, ['vacuum','cover','fan','switch','light','media_player','script','climate'])){
      $ha_device=$device_type;
  }

  $action_map = [];
  $post_data = [];
  $post_data['entity_id'] = $device_id;

  //vacuum
  if ($ha_device == 'vacuum'){
    $action_map['Continue']='start_pause';
    $action_map['Pause']='start_pause';
    $action_map['TurnOn']='turn_on';
    $action_map['TurnOff']='return_to_base';
    $action = get_map_value($action_map, $obj->header->name, '');
  }

  //switch,script
  if (in_array($ha_device,['switch', 'script'])){
    $action_map['TurnOn']='turn_on';
    $action_map['TurnOff']='turn_off';
    $action = get_map_value($action_map, $obj->header->name, '');
  }

  //cover
  if ($ha_device == 'cover'){
    $action_map['SetPosition']='set_cover_position';
    $action_map['Continue']='start_cover';
    $action_map['Pause']='stop_cover';
    $action_map['TurnOn']='open_cover';
    $action_map['TurnOff']='close_cover';
    $action = get_map_value($action_map, $obj->header->name, '');
    if ($action=="set_cover_position"){	
      $post_data['position'] = $value;
    }
  }

  //fan
  if ($ha_device == 'fan'){
    $action_map['TurnOn']='turn_on';
    $action_map['TurnOff']='turn_off';
    $action_map['SetWindSpeed'] = 'set_fan_mode';
    $action = get_map_value($action_map, $obj->header->name, '');
    if ($action=="set_fan_mode"){
      $mode_map['max'] = 'High';
      $mode_map['min'] = 'Low';
      $mode_map['3'] = 'High';
      $mode_map['2'] = 'Middle';
      $mode_map['1'] = 'Low';
      $mode = get_map_value($mode_map, $value, 'High');
      $post_data['fan_mode'] = $mode;
    }
  }

  //light
  if ($ha_device == 'light'){
    $action_map['SetColor']='set_color';
    $action_map['SetBrightness']='set_bright';
    $action_map['AdjustUpBrightness']='brightness_up';
    $action_map['AdjustDownBrightness']='brightness_down';
    $action_map['TurnOn']='turn_on';
    $action_map['TurnOff']='turn_off';
    $action = get_map_value($action_map, $obj->header->name, '');
    if ($action=="set_bright"){	
      $action="turn_on";
      $bright_map['min'] = 1;
      $bright_map['max'] = 100;
      $bright = get_map_value($mode_map, strtolower($value), (int)$value);

      $post_data['brightness_pct'] = $bright;
    }
  
    if ($action=="set_color"){	
      $action="turn_on";
      $color_map['Red'] = [255,0,0];
      $color_map['Green'] = [0,128,0];
      $color_map['Yellow'] = [255,200,36];
      $color_map['Blue'] = [0,0,255];
      $color_map['White'] = [255,255,255];
      $color_map['Black'] = [0,0,0];
      $color_map['Cyan'] = [0,255,255];
      $color_map['Purple'] = [28,0,128];
      $color_map['Orange'] = [255,165,0];
      $color = get_map_value($color_map, $value, [100,100,100]);

      $post_data['rgb_color'] = $color;
    }
  }

  //media player
  if ($ha_device == 'media_player'){
    $action_map['Continue']='media_play';
    $action_map['Pause']='media_stop';
    $action_map['AdjustUpVolume']='volume_up';
    $action_map['AdjustDownVolume']='volume_down';
    $action_map['SetVolume']='volume_set';
    $action_map['SetMute']='volume_mute';
    $action_map['CancelMute']='volume_mute';
    $action_map['Next']='media_next_track';
    $action_map['Previous']='media_previous_track';
    $action_map['SelectChannel']='select_source';
    $action_map['SetMode']='volume_mute';
    $action_map['CancelMode'] = 'volume_mute';
    $action = get_map_value($action_map, $obj->header->name, '');

    if ($action == 'volume_mute'){
      $post_data['is_volume_muted'] = $value;
    }

    if ($action == 'volume_set'){
      $post_data['volume_level'] = $value;
    }
  }

  //climate
  if ($ha_device == 'climate'){
    $action_map['TurnOn'] = 'set_operation_mode';
    $action_map['TurnOff'] = 'set_operation_mode';
    $action_map['SetMode'] = 'set_operation_mode';
    $action_map['SetTemperature'] = 'set_temperature';
    $action = get_map_value($action_map, $obj->header->name, '');

    $mode_map['heat'] = 'heat';
    $mode_map['cold'] = 'cool';
    $mode_map['ventilate'] = 'fan_only';
    $mode_map['dehumidification'] = 'dry';
    $mode_map['off'] = 'off';
    $action = get_map_value($action_map, $obj->payload->value, '');

    if ($action == 'set_operation_mode'){
      $post_data['operation_mode'] = $value;
    }
    if ($action=="set_temperature"){	
      $post_data['temperature'] = $value;
    }

  }

  if($action==""&&$ha_device==""){
    $response = new Response($obj->header->namespace, 'ErrorResponse');
    $response->setErrorResponse('DEVICE_NOT_SUPPORT_FUNCTION','device not support');
    return $response->send();
  }

  //send control request
  $post_string = json_encode($post_data);
  $url = $user['ha_url']."/api/services/".$ha_device."/".$action;
  $control_response = send_curl_request('POST', $url, $user['ha_auth_code'], $post_string); 

  //response to aligenie
  $response = new Response($obj->header->namespace, $obj->header->name.'Response');
  $response->setControlResponse($device_id);	

  return $response->send();
}

?>
