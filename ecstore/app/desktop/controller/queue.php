<?php

 
class desktop_ctl_queue extends desktop_controller{

    var $workground = 'desktop_ctl_system';

    function index(){
        $params = array(
            'title'=>app::get('desktop')->_('队列管理'),
            'actions'=>array(
                array('label'=>app::get('desktop')->_('全部启动'),'submit'=>'index.php?app=desktop&ctl=queue&act=run'),
                array('label'=>app::get('desktop')->_('全部暂停'),'submit'=>'index.php?app=desktop&ctl=queue&act=pause'),
                ),
            );
        $this->finder('base_mdl_queue',$params);
    }

    function run(){
        $this->begin('index.php?app=desktop&ctl=queue&act=index');
        $queue_model = app::get('base')->model('queue');
        foreach((array)$_POST['queue_id'] as $id){
            $item['queue_id'] = $id;
            $item['status'] = 'hibernate';
            $queue_model->save($item);        
        }
        $queue_model->flush();
        $this->end(true,app::get('desktop')->_('启动成功'));
    }
    
    function pause(){
        $this->begin('index.php?app=desktop&ctl=queue&act=index');
        $queue_model = app::get('base')->model('queue');
        foreach((array)$_POST['queue_id'] as $id){
            $item['queue_id'] = $id;
            $item['status'] = 'paused';
            $queue_model->save($item);
        }
        $this->end(true,app::get('desktop')->_('暂停成功'));
    }


}
