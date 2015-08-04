<?php

class desktop_ctl_crontab extends desktop_controller {

    var $workground = 'desktop_ctl_system';
    function index() {
	$params = array (
	    'title' => app::get('desktop')->_('计划任务管理'),
	    'use_buildin_recycle' => false,
	    'use_buildin_refresh' => true,
	    'actions' => array(
		
		),
	    );
	$this->finder('base_mdl_task', $params);
    }

    function edit($task_id) {
	$model = app::get('base')->model('task');
	$task = $model->dump($task_id);
	
	$this->pagedata['task'] = $task;
	$this->page('crontab/detail.html');

    }

    function save() {
	$this->begin('index.php?app=desktop&ctl=crontab&act=index');
	$model = app::get('base')->model('task');
	if( $model->save($_POST) ) {
	    $this->end(true, '保存成功');
	} else {
	    $this->end(false, '保存失败');
	}
    }

    function exec($task_id) {
	$this->begin('index.php?app=desktop&ctl=crontab&act=index');
	$model = app::get('base')->model('task');
	$task = $model->dump($task_id);
	if(!$task) {
	    $this->end(false, '执行失败');
	}
	$task = new $task['task'];
	$task->exec();
    $model->update(array('last'=>time()),array('task'=>$task_id));
	$this->end(true, '执行成功');
    }

}