<?php

 
class dev_command_test extends base_shell_prototype{

    var $command_do = '执行测试用例';
    function command_do(){
    	$args = func_get_args();
        if(count($args) != 2){
            echo app::get('dev')->_("使用方法: dev:test do app的id 测试文件名")."\n".app::get('dev')->_("例子: dev:test do dev sample.php");
            return false;
        }
        $class_name = substr($args[1],0,strpos($args[1],'.'));
        $file = ROOT_DIR."/app/".$args[0]."/testcase/".$args[1];
        if(!file_exists($file)){
            echo app::get('dev')->_("找不到测试文件").$file;
            return false;
        } 
        chdir(realpath(dirname(__FILE__)."/../"));
        echo "\n--  " , $class_name ,'  ', str_repeat('-',66-strlen($class_name)), "\n";
        $_SERVER['argv'] = array(0,$class_name,$file);     
        require 'PHPUnit/TextUI/Command.php';
    }
}

