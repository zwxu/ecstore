<?php

 
class dev_command_doc extends base_shell_prototype{

    var $command_update = '执行测试用例';
    var $command_update_options = array(
            'prefix'=>array('title'=>'项目模板','need_value'=>1),
        );
    function command_update(){
    	$args = func_get_args();
    	$options = $this->get_options();
    	if(!$options['prefix']){
    	    echo 'Error: need "--prefix={PATH}", define document root';
    	    return;
    	}
    	foreach($args as $app_id){
    	    kernel::single('dev_docbuilder')->export($app_id,$options['prefix'].'/'.$app_id);
    	}
    }
}