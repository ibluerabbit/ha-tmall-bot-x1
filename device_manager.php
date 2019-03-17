<?php
require_once __DIR__.'/oauth2/server.php';
require_once __DIR__.'/functions.php';

function get_device_type_list($select){
  require __DIR__.'/device_type_items.php';
  $s = '';
  foreach($item as $key=>$val){
    $selected = ($key==$select ? 'selected' : '');
    $s .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
  }
  return $s;
}

function get_device_name_list($select){
  require __DIR__.'/device_name_items.php';
  $names = json_decode($json, true);
  $options = '';
  foreach($names['data'] as $item){
    $selected = (in_array($select, $item['value']) ? 'selected' :''); 
    $options .= '<option value="'.$item['key'].'" '.$selected.'>'.$item['key'].'</option>';
  }
  return $options;
}

function get_zone_list($select){
  $zones=["门口","客厅","卧室","客房","主卧","次卧","书房","餐厅","厨房","洗手间","浴室","阳台","宠物房","老人房","儿童房","婴儿房","保姆房","玄关","一楼","二楼","三楼","四楼","楼梯","走廊","过道","楼上","楼下","影音室","娱乐室","工作间","杂物间","衣帽间","吧台","花园","温室","车库","休息室","办公室","起居室"];
  $s = '';
  foreach($zones as $zone){
    $selected = ($zone==$select ? 'selected' : '');
    $s .= '<option value="'.$zone.'" '.$selected.'>'.$zone.'</option>';
  }
  return $s;
}

function get_action_list($select){
  require __DIR__.'/action_items.php';
  $s = '';
  foreach($item as $key=>$val){
    $selected = ($select && in_array($key,$select) ? 'checked' : '');
    $s .= '<div><input type="checkbox" id="'.$key.'" name="actions[]" value="'.$key.'" '.$selected.'/>'.$val.'</div>';
  }
  return $s;
}

function get_property_list($select){
  require __DIR__.'/property_items.php';
  $s = '';
  foreach($item as $key=>$val){
    $selected = ($select && in_array($key,$select) ? 'checked' : '');
    $s .= '<div><input type="checkbox" id="'.$key.'" name="properties[]" value="'.$key.'" '.$selected.'/>'.$val.'</div>';
  }
  return $s;
}

function get_device_id_list($user_id, $select){
  global $storage;
  $user = $storage->getUser($user_id);
  $url = $user['ha_url'].'/api/states';
  $response = send_curl_request('GET', $url, $user['ha_auth_code']); 
  $states = json_decode($response['response'], true);
  $entity_ids = [];
  foreach($states as $state){
    $entity_ids[] = $state['entity_id'];
  }
  sort($entity_ids);
  $s = '';
  foreach($entity_ids as $id){
    $selected = ($id==$select ? 'selected' : '');
    $s .= '<option value="'.$id.'" '.$selected.'>'.$id.'</option>';
  }
  return $s;
}

function get_fields_string($data){
  $s = '
<table>
  <tr>
    <th>设备ID</th>
    <td><select id="deviceId" name="deviceId">'.get_device_id_list($data['user_id'], $data['deviceId']).'</select></td>
  </tr>
  <tr>
    <th>设备类型</th>
    <td><select id="deviceType" name="deviceType">'.get_device_type_list($data['deviceType']).'</select></td>
  </tr>
  <tr>
    <th>设备名称</th>
    <!--<td><input type="text" id="deviceName" name="deviceName" value="'.$data['deviceName'].'"></td>-->
    <td><select id="deviceMainName" name="deviceMainName">'.get_device_name_list($data['deviceName']).'</select><select id="deviceName" name="deviceName"></select></td>
  </tr>
  <tr>
    <th>区域</th>
    <td><select id="zone" name="zone">'.get_zone_list($data['zone']).'</select></td>
  </tr>
  <tr>
    <th>品牌</th>
    <td><input type="text" id="brand" name="brand"/ value="'.$data['brand'].'"></td>
  </tr>
  <tr>
    <th>型号</th>
    <td><input type="text" id="model" name="model"/ value="'.$data['model'].'"></td>
  </tr>
  <tr>
    <th>图标</th>
    <td><input type="text" id="icon" name="icon" value="'
       .($data['icon']?$data['icon']:'https://www.home-assistant.io/images/favicon-192x192-full.png').'"/></td>
  </tr>
  <tr>
    <th>属性</th>
    <td>'.get_property_list($data['properties']).'</td>
  </tr>
  <tr>
    <th>动作</th>
    <td>'.get_action_list($data['actions']).'</td>
  </tr>
  <tr>
    <th>扩展</th>
    <td><textarea id="extensions" name="extensions">'.$data['extensions'].'</textarea></td>
  </tr>
</table>
';
  return $s;
}

function print_add_form($data){
  echo '<form method="post" action="?action=do_add">';
  echo get_fields_string($data);
  echo '<input type="submit" value="添加">';
  echo '</form>';
}

function print_edit_form($data){
  echo '<form method="post" action="?action=do_edit">';
  echo get_fields_string($data);
  echo '<input type="submit" value="编辑">';
  echo '</form>';
}

function do_add($data){
  insert_device(
    $data['user_id'],
    $data['deviceId'],
    $data['deviceName'],
    $data['deviceType'],
    $data['zone'],
    $data['brand'],
    $data['model'],
    $data['icon'],
    json_encode($data['properties']),
    json_encode($data['actions']),
    $data['extensions']
  );
  header('Location:device_manager.php');
}

function do_edit($data){
  update_device(
    $data['user_id'],
    $data['deviceId'],
    $data['deviceName'],
    $data['deviceType'],
    $data['zone'],
    $data['brand'],
    $data['model'],
    $data['icon'],
    json_encode($data['properties']),
    json_encode($data['actions']),
    $data['extensions']
  );
  header('Location:device_manager.php');
}

function do_del($data){
  delete_device($data['user_id'],$data['deviceId']);
  header('Location:device_manager.php');
}

function print_header(){
  echo '
<!doctype html>
<html>
  <title>Tmall-Bot-X1 设备管理</title>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <style>
body{
  font-size:14px;
}
a{
  color:#888;
  text-decoration:none;
}
h2 > a{
  color:#000;
}
input[type="text"],select,textarea{
  width:300px;
}

table,th,td{
  border-collapse:collapse;
  border:1px dashed #ccc;
}

th{
  width:100px;
  text-align:right;
}

th,td{
  padding:10px;
}

td > div{
  float:left;
  width:200px;
}
    </style>
  </head>
  <body>
<h2><a href="device_manager.php">Tmall-Bot-X1 设备管理</a></h2>
';
}

function print_footer(){
  echo '
  </body>
</html>
';
}
function print_scripts(){
  require __DIR__.'/device_name_items.php';
  echo '
<script>
var device_names='.$json.';
device_main_name = document.getElementById("deviceMainName");
device_name = document.getElementById("deviceName");
device_main_name.onchange=function(){
  while(device_name.length>0) device_name.remove(0);
  for(var i=0; i<device_names.data.length; i++)
    if (device_names.data[i]["key"] == device_main_name.value){
      for(var j=0; j<device_names.data[i]["value"].length; j++){
        option = document.createElement("option");
        option.text = device_names.data[i]["value"][j];
        device_name.add(option);
      }
      break;
    }
}
</script>';
}

function list_devices($user_id){
  $devices = get_device_list($user_id);
  echo '<a href="?action=add">添加</a>';
  echo '
<table>
<tr>
<td>用户</td>
<td>设备ID</td>
<td>设备类型</td>
<td>设备名称</td>
<td>区域</td>
<td>品牌</td>
<td>型号</td>
<td>属性</td>
<td>动作</td>
<td>扩展</td>
<td colspan="2">操作</td>
</tr>';
  foreach($devices as $device){
    echo '<tr>';
    echo '<td>'.$device['user_id'].'</td>';
    echo '<td>'.$device['deviceId'].'</td>';
    echo '<td>'.$device['deviceType'].'</td>';
    echo '<td>'.$device['deviceName'].'</td>';
    echo '<td>'.$device['zone'].'</td>';
    echo '<td>'.$device['brand'].'</td>';
    echo '<td>'.$device['model'].'</td>';
    echo '<td>'.$device['properties'].'</td>';
    echo '<td>'.$device['actions'].'</td>';
    echo '<td>'.$device['extensions'].'</td>';
    echo '<td><a href="?action=edit&user_id='.$device['user_id'].'&deviceId='.$device['deviceId'].'">编辑</a></td>';
    echo '<td><a href="?action=do_del&user_id='.$device['user_id'].'&deviceId='.$device['deviceId'].'">删除</a></td>';
    echo '</tr>';
  }
  echo '</table>';
}

function do_login(){
  global $storage;
  $user_id = $_POST['username'];
  $user = $storage->getUser($user_id);

  if ($user && md5($_POST['password']) == $user['password']){
    $_SESSION['login']='ok';
    $_SESSION['user_id']= $user_id;
  }
  header('Location: device_manager.php');
}

session_start();
$user_id=isset($_SESSION['user_id'])?$_SESSION['user_id']:'';
$login=isset($_SESSION['login'])?$_SESSION['login']:'';

$action=isset($_REQUEST['action'])?$_REQUEST['action']:'';


if ($login && $user_id) {

  $deviceId=isset($_REQUEST['deviceId'])?$_REQUEST['deviceId']:'';
  $deviceName=isset($_REQUEST['deviceName'])?$_REQUEST['deviceName']:'';
  $deviceType=isset($_REQUEST['deviceType'])?$_REQUEST['deviceType']:'';
  $zone=isset($_REQUEST['zone'])?$_REQUEST['zone']:'';
  $brand=isset($_REQUEST['brand'])?$_REQUEST['brand']:'';
  $model=isset($_REQUEST['model'])?$_REQUEST['model']:'';
  $icon=isset($_REQUEST['icon'])?$_REQUEST['icon']:'';
  $properties=isset($_REQUEST['properties'])?$_REQUEST['properties']:'';
  $actions=isset($_REQUEST['actions'])?$_REQUEST['actions']:'';
  $extensions=isset($_REQUEST['extensions'])?$_REQUEST['extensions']:'';

  $data = [
    'user_id' => $user_id,
    'deviceId' => $deviceId,
    'deviceType' => $deviceType,
    'deviceName' => $deviceName,
    'zone' => $zone,
    'brand' => $brand,
    'model' => $model,
    'icon' => $icon,
    'properties' => $properties,
    'actions' => $actions,
    'extensions' => $extensions,
  ];

  switch ($action){
    case 'add':
      print_header();
      print_add_form($data);
      print_scripts();
      print_footer();
      break;

    case 'edit':
      print_header();
      $device = get_device($user_id, $deviceId);
      $device['actions']=json_decode($device['actions'],true);
      $device['properties']=json_decode($device['properties'],true);
      print_edit_form($device);
      print_scripts();
      print_footer();
      break;

    case 'do_add':
      do_add($data);
      break;

    case 'do_edit':
      do_edit($data);
      break;

    case 'do_del':
      do_del($data);
      break;

    default:
      print_header();
      list_devices($user_id);
      print_scripts();
      print_footer();
  }

}else{
  if ($action=='do_login'){
    do_login();
  }else{
    echo '
  <center>
  <form method="post" action="device_manager.php?action=do_login">
  用户：<input name="username" type="text"><br>
  密码：<input name="password" type="password"><br>
  <input type="submit" value="登录">
  </form>
  </center>
  ';
  }
}

