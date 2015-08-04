<?php

 
class base_command_queue extends base_shell_prototype{

    var $command_list = '列出所有队列任务';
    function command_list(){
        $rows = app::get('base')->model('queue')->getList('queue_id,queue_title,status,worker');        
        $this->output_table( $rows );
    }
    
    var $command_flush = '立即执行所有队列任务';
    function command_flush(){
        app::get('base')->model('queue')->flush();
    }
    
    var $command_exec = '执行指定的队列任务';
    function command_exec($queue_id){
        app::get('base')->model('queue')->runtask($queue_id);
    }
    
    var $command_clear = '清除所有队列任务';
    var $command_active = '激活某任务';
    var $command_disable = '暂停某任务';

}
