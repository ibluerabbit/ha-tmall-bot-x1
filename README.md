# ha-tmall-bot-x1

## 安装web服务程序
比如
`apt install apache2`

## 安装php sqlite3
`apt install php7.0 sqlite3 php7.0-sqlite3`

## 下载oauth2-server-php 
`git clone https://github.com/bshaffer/oauth2-server-php.git`

## 下载ha-tmall-bot-x1
`git clone https://github.com/swif-ti/ha-tmall-bot-x1.git`

## 创建数据库
执行
```
mkdir -p /var/www/db
sqlite3 /var/www/db/oauth2.db < oauth2.sql
sqlite3 /var/www/db/tmall-bot-x1.db < oauth2.sql
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
根据实际环境配置
###重启服务
比如：
`systemctl restart apache2`

## 生成密码md5值
php:
`php -r "echo md5('yourpassword');"`
md5sum:
`md5sum [yourpasswordfile]`
用md5sum注意密码文件不要有多余的空格或换行符

## 添加用户

```
sqlite3 /var/www/db/oauth2.db
insert into oauth_users (username,password,ha_url,ha_auth_code) values ('your_user_id','password_md5','https://your.home.assistant/url','long-lived access tokens')
```

## AliGenie平台设置
###主要页面文件名称
- 开发网关：gateway.php
- 帐户授权：authorize.php
- Access Token：token.php

## 使用设备管理页面管理设备
路径根据web服务程序设置确定
比如：
`http://localhost/device_manager.php`

## 参考链接
- [c1pher 写的 天猫精灵接入HomeAssistant 代码](https://github.com/c1pher-cn/tmall-bot-x1)
- [oauth2-server-php](https://github.com/bshaffer/oauth2-server-php)
- [天猫精灵官方文档](https://doc-bot.tmall.com/docs/doc.htm?spm=0.7629140.0.0.55c417809wAykW&treeId=393&articleId=107674&docType=1)
