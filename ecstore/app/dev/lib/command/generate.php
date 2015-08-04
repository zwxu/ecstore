<?php


class dev_command_generate extends base_shell_prototype{

	var $command_new = '添加一个app等';
	function command_new(){
		/*$args = func_get_args();
		 $options = $this->get_options();
		 if(!$options['controller']){
		 }*/
			
		if(!$app_id = array_shift(func_get_args())){
			echo app::get('dev')->_("用法是： dev:generate new 应用名");
			return false;
		}

		$app_dir = APP_DIR."/".$app_id;
		echo app::get('dev')->_("生成目录结构")."\n";
		@mkdir($app_dir);
		$src = APP_DIR."/dev/demo/skeleton";
		echo app::get('dev')->_("拷贝文件")."\n";
		utils::cp($src,$app_dir);
		echo app::get('dev')->_("更新配置文件")."\n";
		$replace_map = array(
            'APP_ID'=>$app_id,
            'APP_NAME'=>$app_id,
            'APP_DESC'=>app::get('dev')->_("app简短的介绍，请在").$app_dir."/app.xml".app::get('dev')->_("中修改这段信息"),
            'APP_AUTHOR'=>app::get('dev')->_("app作者的信息"),            
            'DEFAULT_CTL'=>'default',    
		);
		utils::replace_in_file("$app_dir/app.xml",$replace_map);
		//echo app::get('dev')->_("安装app")."\n";
		//kernel::single('base_application_manage')->install($app_id);
	}


	var $command_controller = '生成控制器';
	function command_controller(){
		if(!$args_input = array_shift(func_get_args())){
			echo app::get('dev')->_("参数不能为空\n");
			echo app::get('dev')->_("用法是： dev:generate controller app名(必须)：控制器文件夹名：控制器名（必须）：方法名：前台或者后台(site/desktop)(必须)");
			return false;
		}

		$args = explode(':',$args_input);
		if(count($args)!=5){
			echo app::get('dev')->_("为空的值也必须占位，格式强制为：\ndev:generate controller app名(必须)：控制器文件夹名：控制器名（必须）：方法名：前台或者后台(site/desktop)(必须)")."\n";
			return false;
		}
		if(!$app_name = trim($args['0'])){
			echo app::get('dev')->_("查找不到对应的app，请检查app名称输入是否正确")."\n";
			return false;
		}

		if(!$ctl_name = trim($args['2'])){
			echo app::get('dev')->_("控制器名不能为空，请重新输入")."\n";
			return false;
		}
		$ctl_dir = trim(trim(trim($args['1']),'/'));

		if(!$func_name = trim($args['3'])){
            $func_name = 'index';
		}
		if(!$ext_ctl = trim($args['4'])){
			echo app::get('dev')->_("继承类不能为空，请重新输入")."\n";
			return false;
		}
		$replace_map = array(
            '%*APP_NAME*%'=>$app_name,
            '%*CTL_NAME*%'=>$ctl_name,    
            '%*FUNC_NAME*%'=>$func_name,   
		);
		//目录为空的时候以"."代替，防止路径出错
		if($ctl_dir != ''){
			$replace_map['%*CTL_DIR*%'] = str_replace("/","_",$ctl_dir)."_";
		}else{
			$replace_map['%*CTL_DIR*%'] = '.';
		}

		$src = APP_DIR."/dev/demo";
		$dst = APP_DIR."/".$app_name;
		if(!file_exists($dst)){
			echo app::get('dev')->_("找不到 名为  $app_name 的app ");
			return false;
		}

		$src_sample_desktop_path = "$src/sample_desktop.php";   //demo下的后台控制器sample文件路径
		$src_sample_site_path =    "$src/sample_site.php";  //demo下的前台控制器sample文件路径
		$dst_sample_desktop_path = "$dst/controller/$ctl_dir/sample_desktop.php";  //新建的app下的后台控制器sample文件路径
		$dst_sample_site_path =    "$dst/controller/$ctl_dir/sample_site.php";  //新建的app下的前台控制器sample文件路径
		$dst_file_path =           "$dst/controller/$ctl_dir/$ctl_name.php";    //新建的app的控制器文件路径
		
		$src_sample_view_path = "$src/sample.html";   //demo下的后台视图sample文件路径
		$dst_sample_view_path = "$dst/view/$ctl_dir/sample.html";    //新建的app的后台视图sample文件路径
		$dst_view_path =        "$dst/view/$ctl_dir/$func_name.html";    //新建的app的后台视图sample文件路径

		if(file_exists($dst_file_path)){
			echo app::get('dev')->_("控制器  $ctl_name.php 已经存在 ")."\n";
            do{
                $install_confirm = readline('是否覆盖? [Y/n] ');
                switch(strtolower(trim($install_confirm))){
                    case 'y':
                        $install_confirm = true;
                        $command_succ = true;
                    break;
                    case 'n':
                        $install_confirm = false;
                        $command_succ = true;
                    break;
                    default:
                        $command_succ = false;
                }
            }while(!$command_succ);
            if(!$install_confirm){
                return false;
            }
		}

		kernel::log('开始创建控制器...');
		$this->mkdir_r("$dst/controller/$ctl_dir",0755);
		$this->mkdir_r("$dst/view/$ctl_dir",0755);

		switch($ext_ctl){
			case 'desktop':
				//generate controller
				utils::cp($src_sample_desktop_path,$dst_sample_desktop_path);
				rename($dst_sample_desktop_path,$dst_file_path);
				//generate view
				utils::cp($src_sample_view_path,$dst_sample_view_path);
				rename($dst_sample_view_path,$dst_view_path);
				break;
			case 'site':
				utils::cp($src_sample_site_path,$dst_sample_site_path);
				rename($dst_sample_site_path,$dst_file_path);
				utils::cp($src_sample_view_path,$dst_sample_view_path);
				rename($dst_sample_view_path,$dst_view_path);
				break;
//			case 'base':
//				utils::cp($src_sample_base_path,$dst_sample_base_path);
//				rename($dst_sample_base_path,$dst_file_path);
//				break;
		}

		utils::replace_in_file($dst_file_path,$replace_map);
		kernel::log("创建控制器文件  $ctl_name.php");
		kernel::log("创建视图层文件  $func_name.html");
		kernel::log("创建完成！");
	}


	var $command_model = '生成模型层类';
	function command_model(){
		if(!$args_input = array_shift(func_get_args())){
			echo app::get('dev')->_("参数不能为空\n");
			echo app::get('dev')->_("用法是： dev:generate model app名(必须)：模型层文件夹名：模型层类名（必须）：方法名");
			return false;
		}

		$args = explode(':',$args_input);
		if(count($args)!=4){
			echo app::get('dev')->_("为空的值也必须占位，格式强制为：\n dev:generate model app名(必须)：模型层文件夹名：模型层类名（必须）：方法名：")."\n";
			return false;
		}
		if(!$app_name = trim($args['0'])){
			echo app::get('dev')->_("app名不能为空，请重新输入")."\n";
			return false;
		}
		$mdl_dir = trim(trim(trim($args['1']),'/'));
		if(!$mdl_name = trim($args['2'])){
			echo app::get('dev')->_("模型层类名不能为空，请重新输入")."\n";
			return false;
		}

		if(!$func_name = trim($args['3'])){
            $func_name = 'default';
		}

		$replace_map = array(
            '%*APP_NAME*%'=>$app_name,
            '%*MDL_NAME*%'=>$mdl_name,    
            '%*FUNC_NAME*%'=>$func_name,   
		);
		//目录为空的时候以"."代替，防止路径出错
		if($mdl_dir != ''){
			$replace_map['%*MDL_DIR*%'] = str_replace("/","_",$mdl_dir)."_";
		}else{
			$replace_map['%*MDL_DIR*%'] = '.';
		}

		$src = APP_DIR."/dev/demo";
		$dst = APP_DIR."/".$app_name;
		if(!file_exists($dst)){
			echo app::get('dev')->_("找不到 名为  $app_name 的app ");
			return false;
		}

		$src_sample_mdl_path = "$src/sample_mdl.php";   //demo下的后台控制器sample文件路径
		$dst_sample_mdl_path = "$dst/model/$mdl_dir/sample_mdl.php";  //新建的app下的后台控制器sample文件路径
		$dst_mdl_path =        "$dst/model/$mdl_dir/$mdl_name.php";    //新建的app的控制器文件路径
		
		if(file_exists($dst_mdl_path)){
			echo app::get('dev')->_("模型层类  $mdl_name.php 已经存在 ")."\n";
            do{
                $install_confirm = readline('是否覆盖? [Y/n] ');
                switch(strtolower(trim($install_confirm))){
                    case 'y':
                        $install_confirm = true;
                        $command_succ = true;
                    break;
                    case 'n':
                        $install_confirm = false;
                        $command_succ = true;
                    break;
                    default:
                        $command_succ = false;
                }
            }while(!$command_succ);
            if(!$install_confirm){
                return false;
            }
		}

		kernel::log('开始创建模型层...');
		$this->mkdir_r("$dst/model/$mdl_dir",0755);
        utils::cp($src_sample_mdl_path,$dst_sample_mdl_path);
        rename($dst_sample_mdl_path,$dst_mdl_path);
		utils::replace_in_file($dst_mdl_path,$replace_map);
		kernel::log("创建模型层文件  $mdl_name.php");
		kernel::log("创建完成！");
	}

	//make recursive dir
	function mkdir_r($dirName, $rights=0755){
		$dirs = explode('/', $dirName);
		$dir='';
		foreach ($dirs as $part) {
			$dir.=$part.'/';
			if (!is_dir($dir) && strlen($dir)>0){
				@mkdir($dir, $rights);
				kernel::log("创建目录  $dir");
			}
		}
	}


}

