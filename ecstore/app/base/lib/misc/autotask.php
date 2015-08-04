<?php

 
class base_misc_autotask{

    function trigger(){
        set_time_limit(0);
        ignore_user_abort(1);

	//检查计划任务有没有增减
        $status = $this->status();
	$service = (array) kernel::servicelist('autotask');
	$servicelist = array_values($service['iterator']->getArrayCopy());
	$exists_class = array_keys($status);
	$diff_class = array_diff($servicelist, $exists_class);

	$add = $delete = array();
	foreach ($diff_class as $class_name)
	{
	    if(in_array($class_name, $exists_class)) {
		//app::get('base')->model('task')->delete('task'=>$class_name);
	    } else {
	
		$class = new $class_name;
		if($class instanceof  base_interface_task) {
		    $data = array(
			'task' => $class_name,
			'description' => $class->description(),
			'rule'=>$class->rule(),
			'last'=>time(),
			);
		    app::get('base')->model('task')->insert($data);
		}
	    }
	}
	
	//根据规则执行计划任务
	$status = $this->status();
	$now = time();
	foreach($status as $cron) {
	    if($cron['enabled'] && $now >= base_crontabparser::parse($cron['rule'], $cron['last']))
	    {
		$cron_class = new $cron['task'];
		$cron_class->exec();
		app::get('base')->model('task')->update( array('last'=>$now), array('task'=>$cron['task']));
		kernel::log('crontab '. $cron['task'] .' run at ' . date('Y-m-d H:m:i', $now));
	    }
	    
	}
	
    }

    function status(){
        $status = array();
        foreach(app::get('base')->model('task')->getlist('*') as $row){
            $status[$row['task']] = $row;
        }
        return $status;
    }

}
