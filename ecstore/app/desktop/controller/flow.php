<?php

 
class desktop_ctl_flow extends desktop_controller{

    var $workground = 'desktop_ctl_dashboard';

    function status(){
        foreach(kernel::servicelist('system_status') as $k=>$f){
            $menus[$k] = $f->get_name();
        }

        if($menus){
            if(!isset($menus[$_GET['menu']]))$_GET['menu'] = key($menus);

            $this->pagedata['menus'] = $menus;
            $this->pagedata['_OUTPUT_'] = kernel::service('system_status',$name)->get_output();
        }
        $this->page();
    }

    function inbox(){        
        $flow_model = $this->app->model('flow');
        $menus = array(
                'unread'=>app::get('desktop')->_('未读'),
                'read'=>app::get('desktop')->_('已读'),
                'starred'=>app::get('desktop')->_('星标'),
            );

        if(!isset($menus[$_GET['menu']]))$_GET['menu'] = key($menus);

        $this->pagedata['menus'] = &$menus;
        $this->pagedata['flow'] = $flow_model->list_flow($this->user,$_GET['menu']);
        $this->page('flow/message.html');
    }

    function msgitem(){
        $flow_model = $this->app->model('flow');
        $item = $flow_model->instance($_GET['item']);

        $flow_model->mark_read($_GET['item'],$this->user->id);

        echo '';
    }

    function page($page=''){
        $get_params = $_GET;
        unset($get_params['menu']);
        $this->pagedata['_QUERY_STRING'] = utils::http_build_query($get_params);
        $this->pagedata['_PAGE_'] = $page;

        if(true){
            $this->pagedata['_ACTIONS_'] = array(
                    'inbox'=>app::get('desktop')->_('事务'),
                    'account'=>app::get('desktop')->_('账号'),
                    'status'=>app::get('desktop')->_('状态'),
                );
        }

        parent::display('flow/page.html');
    }

    function account(){
        $menus = array(
                'account'=>app::get('desktop')->_('账号'),
                'log'=>app::get('desktop')->_('记录'),
                'mobile'=>app::get('desktop')->_('手机访问'),
            );
        if(!isset($menus[$_GET['menu']]))$_GET['menu'] = key($menus);
        $this->pagedata['menus'] = $menus;
        if($_GET['menu']=='mobile'){
            $this->pagedata['base_url'] = $this->app->base_url().'shopadmin/index.php?ctl=flow&act=inbox';
            $this->page('flow/mobile.html');
        }else{
            $this->pagedata['_OUTPUT_'] = 'output';
            $this->page();
        }
    }

    function login(){
        if($_POST['usrname']){
            $oOpt = &$this->app->model('users');//passwd
            $aResult = $oOpt->tryLogin($_POST);
            if($aResult){
                header('Location: index.php?'.$_SERVER['QUERY_STRING']);
            }
        }
        $this->display('flow/login.html');
    }
}
