<?php

class setup_serverinfo 
{
    
    var $allow_change_db = false;
    var $maxLevel = 6;
    var $check_params = array();

    function run($params=array()){
        $this->check_params = $params;
            
        $return=array();

        $totalScore = 0;
        $allow_install = true;
        $func_prefix = (ECAE_MODE) ? 'ecae_' : 'test_';
        foreach(get_class_methods($this) as $func){
            if(substr($func,0,5)==$func_prefix){
                $score = 0;
                $result = $this->$func($score);
                if($result['items']){
                    $group[$result['group']]['type'] = $result['type'];
                    $group[$result['group']]['items'] = array_merge($group[$result['group']['items']]?$group[$result['group']['items']]:array(),$result['items']);
                    if($allow_install && isset($result['allow_install'])){
                        $allow_install = $result['allow_install'];
                    }
                    if($result['key']){
                        $return[$result['key']] = &$group[$result['group']]['items'];
                    }
                }
                $totalScore += $score;
            }
        }

        $score = floor($totalScore/100)+1;
        $rank = min($score,$this->maxLevel+1);
        $level = array('E','D','C','B','A','S');

        $return['data']=$group;
        $return['score']=$totalScore;
        $return['level']=$level[$rank-1];
        $return['rank'] = $rank;
        $return['allow_install'] = $allow_install;
        $return['path_info'] = kernel::request()->get_path_info();

        return $return;
    }

    function ecae_server_req(&$socre) 
    {
        $allow_install = true;
        
        $rst = defined('ECAE_MYSQL_HOST_M');
        $items['Mysql'] = array(
            'value' => $rst ? '已经激活 Mysql' : '请激活 Mysql',
            'result'=> $rst,
        );
        if(!$rst)   $allow_install = false;

        $rst = defined('ECAE_MEMCACHE_HOST');
        $items['Memcache'] = array(
            'value' => $rst ? '已经激活 Memcache' : '请激活 Memcache',
            'result'=> $rst,
        );
        if(!$rst)   $allow_install = false;

        $rst = defined('ECAE_KVDB_ENABLE');
        $items['KVDB'] = array(
            'value' => $rst ? '已经激活 KVDB' : '请激活 KVDB',
            'result'=> $rst,
        );
        if(!$rst)   $allow_install = false;
	
	/*
        $res = ecae_api()->storage_list_group();
	*/
	if (constant("ECAE_MODE")&&ECAE_MODE)
		$buckets = ecae_file_bucket_list();
	$res = array();
	if(is_array($buckets)){
		foreach($buckets AS $bucket){
			$res[] = $bucket['bucket_id'];
		}
	}

        $rst = (is_array($res) && in_array(constant("ECAE_SITE_NAME").'-private', $res)) ? true : false;
        $items['Storage Private'] = array(
            'value' => $rst ? '已经创建 Storage Private' : '请创建 Storage Private',
            'result'=> $rst,
        );
        if(!$rst)   $allow_install = false;

        $rst = (is_array($res) && in_array(constant("ECAE_SITE_NAME").'-public', $res)) ? true : false;
        $items['Storage Public'] = array(
            'value' => $rst ? '已经创建 Storage Public' : '请创建 Storage Public',
            'result'=> $rst,
        );
        if(!$rst)   $allow_install = false;

        $rst = (is_array($res) && in_array(constant("ECAE_SITE_NAME").'-images', $res)) ? true : false;
        $items['Storage Images'] = array(
            'value' => $rst ? '已经创建 Storage Images' : '请创建 Storage Images',
            'result'=> $rst,
        );
        if(!$rst)   $allow_install = false;

        return array('group'=>'基本需求','key'=>'require','items'=>$items,'type'=>'require','allow_install'=>$allow_install);
    }//End Function

    function test_server_req(&$score){

        $rst = version_compare(PHP_VERSION,'5.0','>=');
        $items['PHP5以上'] = array(
            'value'=>$rst?'您的php版本是'.PHP_VERSION:'您的php版本是'.PHP_VERSION.'不符合要求，请安装php5以上的版本',
            'result'=>$rst,
        );
        if(!$rst){
            $allow_install = false;
        }

        $rst = !get_cfg_var('zend.ze1_compatibility_mode');
        $items['zend.ze1_compatibility_mode 关闭'] = array(
            'value'=>$rst?'zend.ze1_compatibility_mode 已关闭':'您的zend.ze1_compatibility_mode 需关闭',
            'result'=>$rst,
        );
        if(!$rst){
            $allow_install = false;
        }
        
        $deploy = base_setup_config::deploy_info();
        $writeable_dir = $deploy['installer']['writeable_check']['dir'];
        if(is_array($writeable_dir)){
            $unablewrite = array();
            foreach($writeable_dir AS $dir){
                $file = ROOT_DIR . '/' . $dir . '/test.html';
                if($fp = @fopen($file, 'w')){
                    @fclose($fp);
                    @unlink($file);                   
                }else{
                    $unablewrite[] = $dir;
                }
            }
            if(count($unablewrite)){
                $items['目录可写性检测'] = array(
                    'value'=> join(',', $unablewrite) . '目录不可写',
                    'result'=>false,
                );
                $allow_install = false;
            }else{
                $items['目录检测'] = array(
                    'value'=> '全部检测通过',
                    'result'=>true,
                );
            }
        }
		$tmpfname = tempnam(ROOT_DIR . "/data/cache", "foo");
        $handle = fopen($tmpfname, "w");
        $rst = flock($handle,LOCK_EX);
        fclose($handle);
        unlink($tmpfname);
        $items['支持文件锁(flock)'] = array(
            'value'=>$rst?'支持文件锁(flock)':'您不支持文件锁(flock)',
            'result'=>$rst,
        );
        if(!$rst){
            $allow_install = false;
        }

        $rst = function_exists('xml_parse_into_struct');
        $items['php可以解析xml文件'] = array(
            'value'=>$rst?'php可以解析xml文件':'您的php不支持解析xml文件',
            'result'=>$rst,
        );
        if(!$rst){
            $allow_install = false;
        }

        $rst = function_exists('mysql_connect') && function_exists('mysql_get_server_info');
        $items['MySQL函数库可用'] = array(
            'value'=>$rst?'您的MySQL函数库是'.mysql_get_client_info():'您的MySQL函数库未安装',
            'result'=>$rst,
        );
        if(!$rst){
            $allow_install = false;
        }else{
            $rst = false;
            if(isset($this->check_params['mysql_host'])){
                define('DB_HOST',$this->check_params['mysql_host']);
            }elseif(defined('DB_HOST')){
                if(defined('DB_PASSWORD')){
                    $rs = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
                }elseif(defined('DB_USER')){
                    $rs = mysql_connect(DB_HOST,DB_USER);
                }else{
                    $rs = mysql_connect(DB_HOST);
                }
                $db_ver = mysql_get_server_info($rs);
            }elseif($db_ver = @mysql_get_server_info()){
                define('DB_HOST','');
            }else{
                $sock = get_cfg_var('mysql.default_socket');
                if(PHP_OS!='WINNT' && file_exists($sock) && is_writable($sock)){
                    define('DB_HOST',$sock);
                }else{
                    $host = ini_get('mysql.default_host');
                    $port = ini_get('mysql.default_port');
                    if(!$host)$host = '127.0.0.1';
                    if(!$port)$port = 3306;
                    define('DB_HOST',$host.':'.$port);
                }
            }
            if(!$db_ver){
                if(substr(DB_HOST,0,1)=='/'){
                    $fp = @fsockopen("unix://".DB_HOST);
                }else{
                    if($p = strrpos(DB_HOST,':')){
                        $port = substr(DB_HOST,$p+1);
                        $host = substr(DB_HOST,0,$p);
                    }else{
                        $port = 3306;
                        $host = DB_HOST;
                    }
                    $fp = @fsockopen("tcp://".$host, $port, $errno, $errstr,2);
                }
                if (!$fp){
                    $db_ver = '无法连接';
                } else {
                    fwrite($fp, "\n");
                    $db_ver = fread($fp, 20);
                    fclose($fp);
                    if(preg_match('/([2-8]\.[0-9\.]+)/',$db_ver,$match)){
                        $db_ver = $match[1];
                        $rst = version_compare($db_ver,'4.0','>=');
                    }else{
                        $db_ver = '无法识别';
                    }
                }
            }else{
                $rst = version_compare($db_ver,'4.1','>=');
            }
            $this->db_ver = $db_ver;

            $mysql_key = '数据库Mysql 4.1以上&nbsp;<i style="color:#060">'.DB_HOST.'</i>';
            if($this->allow_change_db){
                $mysql_key.='<form method="get" action="" style="margin:0;padding:0"><table><tr><td><label for="db_host">MySQL主机</label></td><td>&nbsp;</td></tr><tr><td><input id="db_host" value="'.DB_HOST.'" name="db_host" style="width:100px;" type="text" /></td><td><input type="submit" value="连接"></td></tr></table></form>';
            }
            if($db_ver == '无法连接'){
                $error_msg = '您的Mysql数据库无法连接';
            }elseif($db_ver == '无法连接'){
                $error_msg = '您的Mysql数据库版本无法识别';
            }else{
                $error_msg = '您的Mysql数据库版本是'.$db_ver.'，版本过低请使用高于4.1的版本.';
            }
            $items[$mysql_key] = array(
                'value'=>$rst?'您的Mysql数据库版本是'.$db_ver:$error_msg.'<br>自定义MySQL地址:<input type="text" name="installer_check[mysql_host]" value="" />',
                'result'=>$rst,
            );
            if(!$rst){
                $allow_install = false;
            }
        }
        
        /*
        if(ini_get('safe_mode')){
            $rst = is_callable('ftp_connect');
            if(!$rst){
                $allow_install = false;
            }
            $items['当安全模式开启时,ftp函数可用'] = array(
                'value'=>$rst?'可用':'不可用',
                'result'=>$rst,
            );
        }
        */
        
        if(is_callable('imagecreatetruecolor')){
            try{
                $image = imagecreatetruecolor(100, 200);
                imagedestroy($image);
                $rst = true;
            }catch(Exception $e){
                $rst = false;
            }
        }else{
            $rst = false;
        }
        $items['是否正常安装GD库'] = array(
            'value'=>$rst?'成功':'失败 (将影响部分功能)',
            'result'=>$rst,
        );

        $rst = preg_match('/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/',gethostbyname('www.example.com'));
        $items['DNS配置完成,本机上能通过域名访问网络'] = array(
            'value'=>$rst?'成功':'失败 (将影响部分功能)',
            'result'=>$rst,
        );

        return array('group'=>'基本需求','key'=>'require','items'=>$items,'type'=>'require','allow_install'=>$allow_install);
    }

}//End Class
