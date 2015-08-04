<?php
class dev_ctl_project extends desktop_controller{
    
    function index(){
        $actions = array(
                array('label'=>app::get('dev')->_('新建项目'),'href'=>'index.php?app=dev&ctl=project&act=create'),
            );
        $this->finder('dev_mdl_project',array('actions'=>$actions));
    }
    
    function create(){
        $types = array();
        foreach(kernel::servicelist('dev.project_type') as $class=>$type){
            $types[] = array(
                'name'=>$type->get_name(),
                'templates'=>$type->get_templates(),
                'class'=>$class,
                );
        }
        $this->nav[] = array(app::get('dev')->_('创建新项目'));
        $this->pagedata['types'] = $types;
        $this->page('project/create.html');
    }
    
    function docreate(){
        kernel::single('base_shell_webproxy')
            ->exec_command('dev:project create -t '.$_GET['template'].' '.$_GET['id']);
    }

}