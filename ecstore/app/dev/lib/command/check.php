<?php

 
class dev_command_check extends base_shell_prototype{
    
    var $command_check = '执行代码检查';
    function command_bom(){ #查找包含bom头的文件
        $explorer = kernel::single('dev_explorer');
        $bom = kernel::single('dev_checker_bom');
        $explorer->set_checker($bom);
        $explorer->start(BASE_DIR);
        
        return true;
    }

    function command_webshell(){ #查找webshell
        $explorer = kernel::single('dev_explorer');
        $webshell = kernel::single('dev_checker_webshell');
        $explorer->set_checker($webshell);
        $explorer->start(BASE_DIR);
            
        return true;
    }
    
    function command_view(){
        $app_dir_obj = dir(APP_DIR);
        $explorer = kernel::single('dev_explorer');
        $code = kernel::single('dev_checker_code');
        $explorer->set_checker($code);
        while(($app_file = $app_dir_obj->read()) !== false){
            if(substr($app_file,0,1) == '.') continue;
            $ctl_dir = APP_DIR."/$app_file/controller";
            if(file_exists($ctl_dir)){
                chdir($ctl_dir);
                kernel::log('search in '.$ctl_dir);
                $explorer->start('.');
            }
        }
    }

}

