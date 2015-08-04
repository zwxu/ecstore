<?php
    interface base_interface_task
    {
	//执行计划任务的方法
        function exec();
	
	//计划任务的默认描述 
        function description();
	
	//规则, 和linux crontab的规是一样一样的
	function rule();
    }
?>
