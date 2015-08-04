<?php

 
/**
 * 网店配置模板
 *
 * 版本 $Id: config.sample.php 37482 2009-12-08 10:54:56Z ever $
 */

// ** 数据库配置 ** //
define('DB_USER', ECAE_MYSQL_USER);  # 数据库用户名
define('DB_PASSWORD', ECAE_MYSQL_PASS); # 数据库密码
define('DB_NAME', ECAE_MYSQL_DB);    # 数据库名

# 数据库服务器 -- 99% 的情况下您不需要修改此参数
define('DB_HOST', ECAE_MYSQL_HOST_M);
//define('DB_PCONNECT',1); #是否启用数据库持续连接？

#数据库集群.
//define('DB_SLAVE_NAME',DB_NAME);
//define('DB_SLAVE_USER',DB_USER);
//define('DB_SLAVE_PASSWORD',DB_PASSWORD);
//define('DB_SLAVE_HOST',ECAE_MYSQL_HOST_S);

define('KVSTORE_STORAGE', 'base_kvstore_ecae');
define('CACHE_STORAGE', 'base_cache_ecae');
define('FILE_STORAGER','ecaesystem');


define('WITH_REWRITE',false);

define('STORE_KEY', ''); #密钥
define('DB_PREFIX', 'sdb_');
#define('LANG', '');
define('DEFAULT_TIMEZONE', '8');
define('WITHOUT_CACHE',false);
define('WITHOUT_KVSTORE_PERSISTENT', true);
#启用触发器日志: home/logs/trigger.php
//define ('TRIGGER_LOG',true);
//define ('DISABLE_TRIGGER',true); #禁用触发器

/* 以下为调优参数 */
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
define('DEBUG_JS',false);
define('ROOT_DIR', realpath(dirname(__FILE__).'/../'));

//安全模式启用后将禁用插件
//define('SAFE_MODE',false);

#您可以更改这个目录的位置来获得更高的安全性

if(ECAE_MODE==true){
	$theme_dir = ini_get("upload_tmp_dir");
	$theme_dir = $theme_dir?$theme_dir.'/themes':'/tmp/themes';
	$data_dir = '/tmp';
}else{
	$data_dir = ROOT_DIR.'/data';
	$theme_dir = ROOT_DIR.'/themes';
}


define('DATA_DIR', $data_dir); 
define('THEME_DIR', $theme_dir);
define('PUBLIC_DIR', ROOT_DIR.'/public');  #同一主机共享文件


define('MEDIA_DIR', PUBLIC_DIR.'/images');
define('SECACHE_SIZE','15M'); #缓存大小,最大不能超过1G
//define('TEMPLATE_MODE','database');
define("MAIL_LOG",false);
define('DEFAULT_INDEX','');
define('SERVER_TIMEZONE',8); #服务器时区
//define('APP_ROOT_PHP','index.php'); #iis 5
//define('HTTP_PROXY','127.0.0.1:8888');
@ini_set('memory_limit','32M');
define('WITHOUT_GZIP',false);
define('WITHOUT_STRIP_HTML', true);

# Session 配置
# define('SESS_NAME', 's');   #used as cookie name
# define('SESS_CACHE_EXPIRE', 60);  #expires after n minutes

# 前台禁ip
//define('BLACKLIST','10.0.0.0/24 192.168.0.1/24');

# 确定服务器支持htaccess文件时，可以打开下面两个参数获得加速。
//define ('GZIP_CSS',true);
//define ('GZIP_JS',true);

/* 日志 */
//define('LOG_LEVEL',E_ERROR);

/* 日志保存类型 0=>使用系统日志， 3=>保存文件 */
define('LOG_TYPE', 0);

#使用数据库存放改动过的模板
//define('THEME_STORAGE','db');

/**************** compat functions begin ****************/


/**************** compat functions end ****************/