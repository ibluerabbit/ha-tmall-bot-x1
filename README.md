# ha-tmall-bot-x1

## 安装php sqlite3
apt install php7.0 sqlite3

## 创建数据库
执行
sqlite3 /var/www/db/oauth2.db < oauth2.sql
sqlite3 /var/www/db/tmall-bot-x1.db < oauth2.sql

## 设置好数据库和所属文件夹访问权限
chmod 666 XXX

## 下载oauth2-server-php 
git clone https://github.com/bshaffer/oauth2-server-php.git

## 添加用户
insert into oauth_users (username,password,ha_url,ha_auth_code) values ('your_user_id','password_md5','https://your.home.assistant/url','long-lived access tokens')

## 生成密码md5值
php -r "echo md5('yourpassword');"

## 参考链接
- [c1pher 写的 天猫精灵接入HomeAssistant 代码](https://github.com/c1pher-cn/tmall-bot-x1)
- [oauth2-server-php](https://github.com/bshaffer/oauth2-server-php)
- [天猫精灵官方文档](https://doc-bot.tmall.com/docs/doc.htm?spm=0.7629140.0.0.55c417809wAykW&treeId=393&articleId=107674&docType=1)
