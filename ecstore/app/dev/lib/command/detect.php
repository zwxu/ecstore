<?php

class dev_command_detect extends base_shell_prototype{

    var $command_ecos = '检测基本环境';
    function command_ecos(){
    	//检测php版本
        $rst = version_compare(PHP_VERSION,'5.0','>=');
        echo  $rst ? app::get('dev')->_("php版本是") . PHP_VERSION . app::get('dev')->_('->符合要求...')."\n" : app::get('dev')->_('php版本是').PHP_VERSION.app::get('dev')->_('->不符合要求，请安装php5以上的版本...')."\n";

        
        //检测目录是否可写        
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
                echo join(',', $unablewrite) . app::get('dev')->_('目录不可写...')."\n";
            }else{
                echo app::get('dev')->_('目录可写性检测通过...')."\n";
            }
        }
        
        
        //检测是否可以解析xml文件
        $rst = function_exists('xml_parse_into_struct');
        echo  $rst ? app::get('dev')->_("php可以解析xml文件...")."\n" : app::get('dev')->_('php不支持解析xml文件...')."\n";

        
        //检测是否安装GD库
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
        echo $rst ? app::get('dev')->_("GD库正常...")."\n" : app::get('dev')->_("GD库不正常 (将影响部分功能)...")."\n";
        

        //检测kvstore是否可以存取
        @$this->app->setConf('dev.test.data','testdata');
        $s = @$this->app->getConf('dev.test.data');
        if(!empty($s)){
            $rst = true;
            @$this->app->setConf('dev.test.data','');
        }else{
            $rst = false;
        }
        echo $rst ? app::get('dev')->_("kvstore存取正常...")."\n" : app::get('dev')->_("kvstore存取不正常...")."\n";
        
        
        //检测mysql函数库是否可用
        $rst = function_exists('mysql_connect') && function_exists('mysql_get_server_info');
        echo  $rst ? app::get('dev')->_("MySQL函数库可用...")."\n" : app::get('dev')->_('MySQL函数库未安装...')."\n";

        
        //检测mysql数据库连接
        if(!$rst){
            echo app::get('dev')->_("MySQL函数库连接出错...");
        }else{
            $rst = false;
            if(defined('DB_HOST')){
                if(defined('DB_PASSWORD')){
                    $rs = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
                }elseif(defined('DB_USER')){
                    $rs = mysql_connect(DB_HOST,DB_USER);
                }else{
                    $rs = mysql_connect(DB_HOST);
                }
                $db_ver = mysql_get_server_info($rs);
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
            if($db_ver == '无法连接'){
                $error_msg = 'Mysql数据库无法连接...';
            }elseif($db_ver == '无法识别'){
                $error_msg = 'Mysql数据库版本无法识别...';
            }else{
                $error_msg = 'Mysql数据库版本是'.$db_ver.'，如果版本过低,请使用高于4.1的版本...';
            }
            echo app::get('dev')->_($error_msg)."\n";
        }
        
        
        //检测mysql数据库是否可写可读
        $db = kernel::database();
        $db->exec('drop table if exists sdb_test')."\n";
        $sql_c = "CREATE TABLE `sdb_test` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`test` char(10) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
		if(@$db->exec($sql_c)){
		    echo app::get('dev')->_("Mysql创建测试表正常...")."\n";
		}else{
		    echo app::get('dev')->_("Mysql创建测试表不正常...")."\n";
		};
		$sql_i = "insert  into `sdb_test`(`id`,`test`) values (1,'test')";
		if(@$db->exec($sql_i)){
		    echo app::get('dev')->_("Mysql插入数据正常...")."\n";
		}else{
		    echo app::get('dev')->_("Mysql插入数据不正常...")."\n";
		};
		$sql_s = "select * from sdb_test";
		if(@$db->exec($sql_s)){
		    echo app::get('dev')->_("Mysql读取数据正常...")."\n";
		}else{
		    echo app::get('dev')->_("Mysql读取数据不正常...")."\n";
		};
		$db->exec('drop table if exists sdb_test')."\n";
       
        
        //检测常用service是否注册成功
		$service_common_arr = array(
			'base_storage_filesystem' => 'file_storage',
			'base_view_compiler' => 'view_compile_helper',
			'base_view_helper' => 'view_helper',
			'base_view_input' => 'html_input',
			'base_rpc_service' => 'openapi.rpc_callback',
			'base_misc_task' => 'autotask',
			'base_rpc_check' => 'openapi.check',
			'base_service_queue' => 'openapi.queue',
			'base_service_cachevary' => 'cachemgr_global_vary',
			'base_service_render' => 'base_render_pre_display',
			'base_charset_default' => 'base_charset',
			'base_status_system' => 'status',
			'dbeav_filter' => 'dbeav_filter',
			'pam_passport_basic' => 'passport',
			'pam_callback' => 'openapi.pam_callback',
			'pam_trust_tao' => 'login_trust',
			'desktop_sidepanel_dashboard' => 'desktop_sidepanel.desktop_ctl_dashboard',
			'desktop_finder_apps' => 'desktop_finder.base_mdl_apps',
			'desktop_application_menu' => 'app_content_detector',
			'desktop_application_workground' => 'app_content_detector',
			'desktop_application_permission' => 'app_content_detector',
			'desktop_application_panelgroup' => 'app_content_detector',
			'desktop_application_adminpanel' => 'app_content_detector',
			'desktop_application_theme' => 'app_content_detector',
			'desktop_application_widgets' => 'app_content_detector',
			'desktop_view_input' => 'html_input',
			'desktop_view_helper' => 'view_helper',
			'desktop_finder_users' => 'desktop_finder.desktop_mdl_users',
			'desktop_finder_roles' => 'desktop_finder.desktop_mdl_roles',
			'desktop_io_type_csv' => 'desktop_io',
			'desktop_finder_pam' => 'desktop_finder.desktop_mdl_pam',
			'desktop_finder_tag' => 'desktop_finder.desktop_mdl_tag',
			'desktop_service_view_menu' => 'desktop_menu',
			'desktop_finder_colset' => 'desktop_task.finder_colset',
			'desktop_finder_favstar' => 'desktop_task.finder_favstar',
			'desktop_finder_recycle' => 'desktop_finder.desktop_mdl_recycle',
			'desktop_finder_magicvars' => 'desktop_finder.desktop_mdl_magicvars',
			'desktop_keyboard_initdata' => 'desktop_keyboard_setting',
			'desktop_service_login' => 'pam_login_listener',
			'desktop_cert_certcheck' => 'product_soft_certcheck',
			'image_finder_image' => 'desktop_finder.image_mdl_image',
			'site_finder_explorers' => 'desktop_finder.site_mdl_explorers',
			'site_finder_link' => 'desktop_finder.site_mdl_link',
			'site_finder_modules' => 'desktop_finder.site_mdl_modules',
			'site_finder_seo' => 'desktop_finder.site_mdl_seo',
			'site_finder_menus' => 'desktop_finder.site_mdl_menus',
			'site_finder_theme' => 'desktop_finder.site_mdl_themes',
			'site_finder_route_static' => 'desktop_finder.site_mdl_route_statics',
			'site_finder_widgets_proinstance' => 'desktop_finder.site_mdl_widgets_proinstance',
			'site_finder_callback_modules' => 'desktop_finder_callback.site_mdl_modules',
			'site_application_explorer' => 'app_content_detector',
			'site_application_module' => 'app_content_detector',
			'site_application_widgets' => 'app_content_detector',
			'site_view_compiler' => 'view_compile_helper',
			'site_view_helper' => 'view_helper',
			'site_service_view_helper' => 'site_view_helper',
			'site_service_cachevary' => 'cachemgr_global_vary',
			'site_service_seo' => 'site_service_seo',
			'site_misc_task' => 'autotask',
			'site_service_view_menu' => 'desktop_menu',
			'site_devtpl_widget' => 'dev.project_type',
			'site_devtpl_theme' => 'dev.project_type',
			'site_application_themewidgets' => 'site_theme_content_detector',
			'site_service_tplsource' => 'tpl_source.site_proinstance',
			'site_service_tpltheme' => 'tpl_source.theme',
			'site_errorpage_display' => 'site_display_errorpage.conf',
			'site_keyboard_initdata' => 'desktop_keyboard_setting'
		);
        $sql = 'select app_id,content_name,content_path from sdb_base_app_content where content_type="service" and disabled!="true"';
        $fsql = $sql.' AND app_id IN ("base","dbeav","desktop","image","pam","site")';
        $rs=$db->select($fsql);
        $service_local_arr = array();
        foreach($rs as $data){
            $service_local_arr[$data['content_path']] = $data['content_name'];
        }
        $unservice = array_diff_key($service_common_arr,$service_local_arr);
        if(count($unservice)){
        	$unservice_output = array();
        	foreach($unservice as $key=>$data1){
        		$app_ids = explode('_',$key);
                $app_id = $app_ids[0];  
                $unservice_output = $data1."({$app_id})";  
                echo "service ｛ " . $unservice_output . app::get('dev')->_(' ｝未注册...')."\n";
        	}
        }else{
            echo app::get('dev')->_('常用service存在，但仍可能有service未注册...')."\n";
        }
        //end
    }


    
}
