# ha-tmall-bot-x1
实现Home Assistant接入天猫精灵 

## 测试环境
硬件：树莓派3B

系统：Raspbian Strech

## 安装web服务程序
比如
```
sudo apt install apache2
```

## 安装php sqlite3
```
sudo apt install php7.0 sqlite3 php7.0-sqlite3
```

## 下载oauth2-server-php 
```
git clone https://github.com/bshaffer/oauth2-server-php.git
```

## 下载ha-tmall-bot-x1
```
git clone https://github.com/swif-ti/ha-tmall-bot-x1.git
```

## 创建数据库
执行
```
mkdir -p /var/www/db
sqlite3 /var/www/db/oauth2.db < ha-tmall-bot-x1/sql/oauth2.sql
sqlite3 /var/www/db/tmall-bot-x1.db < ha-tmall-bot-x1/sql/tmall-bot-x1.sql
```

## 设置好数据库和所属文件夹访问权限
根据执行web服务程序的用户,设置合适权限
```
chmod 777 /var/www/db
chmod 666 /var/www/db/oauth2.db
chmod 666 /var/www/db/tmall-bot-x1.db
```

## 确认路径是否正确
- ha-tmall-bot-x1/functions.php中tmall-bot-x1.db
- ha-tmall-bot-x1/oauth2/server.php中oauth2.db
- ha-tmall-bot-x1/oauth2/server.php中Autoloader.php

## 配置web服务程序并重启服务
根据实际环境配置,将ha-tmall-bot-x1文件夹设置为web目录

重启服务,比如：
```
sudo systemctl restart apache2
```

## 生成密码md5值
php:
```
php -r "echo md5('yourpassword');"
```
md5sum:
```
md5sum [yourpasswordfile]
```
用md5sum注意密码文件不要有多余的空格或换行符

## 添加OAuth用户和cilent

```
sqlite3 /var/www/db/oauth2.db
INSERT INTO oauth_users (username, password, ha_url, ha_auth_code) VALUES ('your_user_id','password_md5','https://your.home.assistant/url','long-lived access tokens')
INSERT INTO oauth_clients (client_id, client_secret, redirect_uri) VALUES ('testclient', 'testpass', 'https://open.bot.tmall.com/oauth/callback');
```

## AliGenie开发平台设置
[开发平台链接](https://open.aligenie.com/)
服务设置：
- 账户授权连接：https://your.server/oauth2/authorize.php
- Client ID: testclient
- Client Secret: testpass
- 跳转 URL: https://open.bot.tmall.com/oauth/callback
- Access Token URL: https://your.server/oauth2/token.php
- 开发者网关地址: https://your.server/gateway.php

## 使用设备管理页面管理设备
路径根据web服务程序设置确定

比如：
```
http://localhost/device_manager.php
```
## 其他说明
- 以上用户名、密码、HA授权码、主机域名、Client ID、Client Secret等，请根据实际情况更改
- Access Token 的有效时间在 server.php 中设置(access_lifetime),过期后需要登录AliGenie开发平台重新授权
- AliGenie开发平台中填写的url和ha_url要在公网中可以访问
- 没有公网IP，可以使用frp,ngrok之类的内网穿透服务，网上有免费的（稳定性一般），也可以购买ECS自己搭
- 建议使用https加密http通信,推荐使用certbot获取Let's Encrypt证书

## 参考链接
- [c1pher 写的 天猫精灵接入HomeAssistant 代码](https://github.com/c1pher-cn/tmall-bot-x1)
- [oauth2-server-php](https://github.com/bshaffer/oauth2-server-php)
- [天猫精灵官方文档](https://doc-bot.tmall.com/docs/doc.htm?spm=0.7629140.0.0.55c417809wAykW&treeId=393&articleId=107674&docType=1)
