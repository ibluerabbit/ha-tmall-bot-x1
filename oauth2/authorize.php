<?php
require_once __DIR__.'/server.php';

if (empty($_POST)) {
  echo '
<center>
<form method="post">
用户：<input name="username" type="text"><br>
密码：<input name="password" type="password"><br>
<input type="submit" value="授权">
</form>
</center>
';
}else{
  $username = $_POST['username'];
  $user = $storage->getUser($username);

  if ($user && md5($_POST['password']) == $user['password']){

    $request = OAuth2\Request::createFromGlobals();
    $response = new OAuth2\Response();
    //验证授权请求
    if (!$server->validateAuthorizeRequest($request, $response)) {
      $response->send();
      die();
    }
    //处理授权请求
    $server->handleAuthorizeRequest($request, $response, ($is_authorized=true), $username);
    $response->send();
  }else{
    echo '<center>用户名或密码错误！</center>';
  }
}


?>
