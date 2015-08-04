<?php


if(!defined('APP_DIR')){
    define('APP_DIR',ROOT_DIR.'/app');
}

if(!defined('ECAE_MODE') && defined('ECAE_SITE_ID') && ECAE_SITE_ID > 0){
    @define('ECAE_MODE', true);
}else{
    @define('ECAE_MODE', false);
}

error_reporting(E_ALL ^ E_NOTICE);

// ego version
if(file_exists(ROOT_DIR.'/app/base/ego.php') && !function_exists('ecos_desktop_finder_find_id')){
    require(ROOT_DIR.'/app/base/ego.php');
}

class kernel{

    static $base_url = null;
    static $url_app_map = array();
    static $app_url_map = array();
    static $console_output = false;
    static private $__online = null;
    static private $__router = null;
    static private $__db_instance = null;
    static private $__singleton_instance = array();
    static private $__request_instance = null;
    static private $__single_apps = array();
    static private $__service_list = array();
    static private $__base_url = array();
    static private $__language = null;
    static private $__service = array();
    static private $__require_config = null;
    static function boot(){
        set_error_handler(array('kernel', 'exception_error_handler'));
        try{
            require(ROOT_DIR.'/config/config.php');
            require(ROOT_DIR.'/config/mapper.php');
            self::$url_app_map = $urlmap;
            foreach(self::$url_app_map AS $flag=>$value){
                self::$app_url_map[$value['app']] = $flag;
            }

            if(get_magic_quotes_gpc()){
                self::strip_magic_quotes($_GET);
                self::strip_magic_quotes($_POST);
            }

            if(!self::register_autoload()){
                require(dirname(__FILE__) . '/autoload.php');
            }

            $pathinfo = self::request()->get_path_info();
            $jump = false;
            if(isset($pathinfo{1})){
                if($p = strpos($pathinfo,'/',2)){
                    $part = substr($pathinfo,0,$p);
                }else{
                    $part = $pathinfo;
                    $jump = true;
                }
            }else{
                $part = '/';
            }

            if($part=='/api'){
                return kernel::single('base_rpc_service')->process($pathinfo);
            }elseif($part=='/openapi'){
                return kernel::single('base_rpc_service')->process($pathinfo);
            }elseif($part=='/app-doc'){
                //cachemgr::init();
                return kernel::single('base_misc_doc')->display($pathinfo);
            }

            if(isset(self::$url_app_map[$part])){
                if($jump){
                    $request_uri = self::request()->get_request_uri();
                    $urlinfo = parse_url($request_uri);
                    $query = $urlinfo['query']?'?'.$urlinfo['query']:'';
                    header('Location: '.$urlinfo['path'].'/'.$query);
                    exit;
                }else{
                    $app = self::$url_app_map[$part]['app'];
                    $prefix_len = strlen($part)+1;
                    kernel::set_lang(self::$url_app_map[$part]['lang']);
                }
            }else{
                $app = self::$url_app_map['/']['app'];
                $prefix_len = 1;
                kernel::set_lang(self::$url_app_map['/']['lang']);
            }

            if(!$app){
                readfile(ROOT_DIR.'/app/base/readme.html');
                exit;
            }

            if(!self::is_online()){
                if(file_exists(APP_DIR.'/setup/app.xml')){
                    if($app!='setup'){
                        //todo:进入安装check
                        setcookie('LOCAL_SETUP_URL', app::get('setup')->base_url(1), 0, '/');
                        header('Location: '. kernel::base_url().'/app/setup/check.php');
                        exit;
                    }
                }else{
                    echo '<h1>System is Offline, install please.</h1>';
                    exit;
                }
            }else{
                require(ROOT_DIR.'/config/config.php');
            }

            date_default_timezone_set(
                defined('DEFAULT_TIMEZONE') ? ('Etc/GMT'.(DEFAULT_TIMEZONE>=0?(DEFAULT_TIMEZONE*-1):'+'.(DEFAULT_TIMEZONE*-1))):'UTC'
            );

            @include(APP_DIR.'/base/defined.php');

            if(isset($pathinfo{$prefix_len})){
                $path = substr($pathinfo,$prefix_len);
            }else{
                $path = '';
            }

            //init cachemgr
            if($app=='setup'){
                cachemgr::init(false);
            }else{
                cachemgr::init();
                cacheobject::init();
            }

            //get app router
            define('CURRENT_LOGIN_APP', $app);
            self::$__router = app::get($app)->router();
            self::$__router->dispatch($path);
        }catch(Exception $e){
            base_errorpage::exception_handler($e);
        }
    }

    static function exception_error_handler($errno, $errstr, $errfile, $errline )
    {
        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            break;

            case E_STRICT:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:
                //do nothing
            break;
        }
        return true;
    }//End Function

    static function router(){
        return self::$__router;
    }

	static function openapi_url($openapi_service_name,$method='access',$params=null){
        if(substr($openapi_service_name,0,8)!='openapi.'){
            trigger_error('$openapi_service_name must start with: openapi.');
            return false;
        }
        $arg = array();
        foreach((array)$params as $k=>$v){
            $arg[] = urlencode($k);
            $arg[] = urlencode(str_replace('/','%2F',$v));
        }
        return kernel::base_url(1).kernel::url_prefix().'/openapi/'.substr($openapi_service_name,8).'/'.$method.'/'.implode('/',$arg);
    }

    static function request(){
        if(!isset(self::$__request_instance)){
            self::$__request_instance = kernel::single('base_request',1);
        }
        return self::$__request_instance;
    }

    static function url_prefix(){
        return (defined('WITH_REWRITE') && WITH_REWRITE === true)?'':'/index.php';
    }

    static function this_url($full=false){
        return self::base_url($full).self::url_prefix().self::request()->get_path_info();
    }

    static function log($message,$keepline=false){
        if(self::$console_output){
            if($keepline){
                echo $message;
            }else{
                echo $message = $message."\n";
            }
        }else{
            //modify by edwin.lzh@gmail.com 2010/6/10
            $message = sprintf("%s\t%s\n", date("Y-m-d H:i:s"), $message);
            switch(LOG_TYPE)
            {
                case 3:
                    if(defined('LOG_FILE')){
                        $logfile = str_replace('{date}', date("Ymd"), LOG_FILE);
                        $ip = ($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
                        $ip = str_replace(array('.', ':'), array('_', '_'), $ip);
                        $logfile = str_replace('{ip}', $ip, $logfile);
                    }else{
                        $logfile = DATA_DIR . '/logs/all.php';
                    }
                    if(!file_exists($logfile)){
                        if(!is_dir(dirname($logfile)))  utils::mkdir_p(dirname($logfile));
                        file_put_contents($logfile, (defined(LOG_HEAD_TEXT))?LOG_HEAD_TEXT:'<'.'?php exit()?'.">\n");
                    }
                    @error_log($message, 3, $logfile);
                break;
                case 0:
                default:
                    @error_log($message, 0);
            }//End Switch
        }
    }

    static function base_url($full=false){
        $c = ($full) ? 'true' : 'false';
        if(!isset(self::$__base_url[$c])){
            if(defined('BASE_URL')){
                if($full){
                    self::$__base_url[$c] = constant('BASE_URL');
                }else{
                    $url = parse_url(constant('BASE_URL'));
                    if(isset($url['path'])){
                        self::$__base_url[$c] = $url['path'];
                    }else{
                        self::$__base_url[$c] = '';
                    }
                }
            }else{
                if(!isset(self::$base_url)){
                    self::$base_url = self::request()->get_base_url();
                }

                if(self::$base_url == '/'){
                    self::$base_url = '';
                }

                if($full){
                    self::$__base_url[$c] = strtolower(self::request()->get_schema()).'://'.self::request()->get_host().self::$base_url;
                }else{
                    self::$__base_url[$c] = self::$base_url;
                }
            }
        }
        return self::$__base_url[$c];
    }

    static function set_online($mode){
        self::$__online = $mode;
    }

    static function is_online(){
        if(self::$__online===null){
            if(ECAE_MODE){
                if(file_exists(ROOT_DIR.'/config/config.php') && $__require_config === null ){
                    require(ROOT_DIR.'/config/config.php');
                    $__require_config = true;
                }else{
                    self::$__online = false;
                    return self::$__online;
                }
                $ecos_install_lock = app::get('base')->getConf('ecos.install.lock');
                empty($ecos_install_lock)?self::$__online=false:self::$__online=true;
            }else{
                self::$__online = file_exists(ROOT_DIR.'/config/config.php' );
            }
        }
        return self::$__online;
    }

    static function single($class_name,$arg=null){
        if($arg===null){
            $p = strpos($class_name,'_');
            if($p){
                $app_id = substr($class_name,0,$p);
                if(!isset(self::$__single_apps[$app_id])){
                    self::$__single_apps[$app_id] = app::get($app_id);
                }
                $arg = self::$__single_apps[$app_id];
            }
        }
        if(is_object($arg)){
            $key = get_class($arg);
            if($key==='app'){
                $key .= '.' . $arg->app_id;
            }
            $key = '__class__' . $key;
        }else{
            $key = md5('__key__'.serialize($arg));
        }
        if(!isset(self::$__singleton_instance[$class_name][$key])){
            self::$__singleton_instance[$class_name][$key] = new $class_name($arg);
        }
        return self::$__singleton_instance[$class_name][$key];
    }

    static function database(){
        if(!isset(self::$__db_instance)){
            $classname = defined('DATABASE_OBJECT') ? constant('DATABASE_OBJECT') : 'base_db_connections';
            $obj = new $classname;
            if($obj instanceof base_interface_db){
                self::$__db_instance = $obj;
            }else{
                trigger_error(DATABASE_OBJECT.' must implements base_interface_db!', E_USER_ERROR);
                exit;
            }
        }
        return self::$__db_instance;
    }

    static function service($srv_name,$filter=null){
        $defined_service = app::get('base')->getConf('server.'.$srv_name);
        if($defined_service && $defined_service = kernel::single($defined_service)){
            return $defined_service;
        }
        return self::servicelist($srv_name,$filter)->current();
    }

    static function servicelist($srv_name,$filter=null){
	    if(self::is_online()){
			if(!isset(self::$__service[$srv_name])){
				if(base_kvstore::instance('service')->fetch($srv_name,$service_define)){
					self::$__service[$srv_name] = new service($service_define,$filter);
					return self::$__service[$srv_name];
				}
			}else{
				return self::$__service[$srv_name];
			}
		}
        return new ArrayIterator(array());
	}

    static function strip_magic_quotes(&$var){
        foreach($var as $k=>$v){
            if(is_array($v)){
                self::strip_magic_quotes($var[$k]);
            }else{
                $var[$k] = stripcslashes($v);
            }
        }
    }

    static function register_autoload($load=array('kernel', 'autoload'))
    {
        if(function_exists('spl_autoload_register')){
            return spl_autoload_register($load);
        }else{
            return false;
        }
    }

    static function unregister_autoload($load=array('kernel', 'autoload'))
    {
        if(function_exists('spl_autoload_register')){
            return spl_autoload_unregister($load);
        }else{
            return false;
        }
    }

    static function autoload($class_name)
    {
        $p = strpos($class_name,'_');

        if($p){
            $owner = substr($class_name,0,$p);
            $class_name = substr($class_name,$p+1);
            $tick = substr($class_name,0,4);
            switch($tick){
            case 'ctl_':
                if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$owner.'/controller/'.str_replace('_','/',substr($class_name,4)).'.php')){
                    $path = CUSTOM_CORE_DIR.'/'.$owner.'/controller/'.str_replace('_','/',substr($class_name,4)).'.php';
                }else{
                    $path = APP_DIR.'/'.$owner.'/controller/'.str_replace('_','/',substr($class_name,4)).'.php';
                }
                if(file_exists($path)){
                    return require_once $path;
                }else{
                    throw new exception('Don\'t find controller file');
                    exit;
                }
            case 'mdl_':
                if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$owner.'/model/'.str_replace('_','/',substr($class_name,4)).'.php')){
                    $path = CUSTOM_CORE_DIR.'/'.$owner.'/model/'.str_replace('_','/',substr($class_name,4)).'.php';
                }else{
                    $path = APP_DIR.'/'.$owner.'/model/'.str_replace('_','/',substr($class_name,4)).'.php';
                }
                if(file_exists($path)){
                    return require_once $path;
                }elseif(file_exists(APP_DIR.'/'.$owner.'/dbschema/'.substr($class_name,4).'.php') || file_exists(CUSTOM_CORE_DIR.'/'.$owner.'/dbschema/'.substr($class_name,4).'.php')){
                    $parent_model_class = app::get($owner)->get_parent_model_class();
                    eval ("class {$owner}_{$class_name} extends {$parent_model_class}{ }");
                    return true;
                }else{
                    throw new exception('Don\'t find model file "'.$class_name.'"');
                    exit;
                }
            default:
                if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$owner.'/lib/'.str_replace('_','/',$class_name).'.php')){
                    $path = CUSTOM_CORE_DIR.'/'.$owner.'/lib/'.str_replace('_','/',$class_name).'.php';
                }else{
                    $path = APP_DIR.'/'.$owner.'/lib/'.str_replace('_','/',$class_name).'.php';
                }
                if(file_exists($path)){
                    return require_once $path;
                }else{
                    throw new exception('Don\'t find lib file "'.$class_name.'"');
                    return false;
                }
            }
        }elseif(file_exists($path = APP_DIR.'/base/lib/static/'.$class_name.'.php')){
            if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/base/lib/static/'.$class_name.'.php')){
                 $path = CUSTOM_CORE_DIR.'/base/lib/static/'.$class_name.'.php';
            }
            return require_once $path;
        }else{
            throw new exception('Don\'t find static file "'.$class_name.'"');
            return false;
            //exit;
        }
    }

    static public function set_lang($language)
    {
        self::$__language = trim(strtolower($language));
    }//End Function

    static public function get_lang()
    {
        return  self::$__language ? self::$__language : ((defined('LANG')&&constant('LANG')) ? LANG : 'zh-cn');
    }//End Function
}

function __($str){
    return $str;
}
