<?php

 
class desktop_ctl_system extends desktop_controller{

    var $require_super_op = true;
    
    function __construct($app) {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
        $this->app = $app;
    }

    function index(){
        echo $this->app->getConf('shopadminVcode');
    }
    function set_title(){
        if($_POST){
            $this->begin();
            $this->app->setConf('background.title',$_POST['background_title']);
            $this->end(true,app::get('desktop')->_('保存成功'));
        }else{
        echo '<h4 class="head-title" >'.app::get('desktop')->_('标题设置').'</h4>';
            $html = $this->ui()->form_start(array('action'=>'index.php?ctl=system&act=set_title','method'=>'post'));
            $background_title = $this->app->getConf('background.title');
            $html .= $this->ui()->form_input(array('title'=>app::get('desktop')->_('标题：'),'name'=>'background.title','tab'=>'后台设置','value'=>$background_title,'vtype'=>'required'));
            $html.=$this->ui()->form_end();
            echo $html;
        }
    }
    function service()

    {
        echo '<h4 class="head-title" >'.app::get('desktop')->_('系统配置').'</h4>';
       if($_POST){


            $this->app->setConf('shopadminVcode',$_POST['shopamin_vocde']);    
        }
        $services = app::get('base')->model('services');
        $filter = array(
                'content_type'=>'service_category',
                'content_path'=>'select',
                'disabled'=>'true',
            );
        
        $all_category = $services->getList('*', $filter);
        $filter = array(
                'content_type'=>'service',
                'disabled'=>'true',
            );
        $all_services = $services->getList('*', $filter);
        foreach($all_services as $k => $row){
            $vars = get_class_vars($row['content_path']);
            $servicelist[$row['content_name']][$row['content_path']] = $vars['name'];
        }
        $html .= $this->ui()->form_start(array('method'=>'POST'));
        foreach($all_category as $ik => $item){
             if( $item['content_name'] == 'ectools_regions.ectools_mdl_regions' ){
                unset( $all_category[$ik] );
                continue;
            }
           $current_set = app::get('base')->getConf('service.'.$item['content_name']);
            if(@array_key_exists($item['content_name'],$_POST['service'])){
                if($current_set!=$_POST['service'][$item['content_name']]){
                    $current_set = $_POST['service'][$item['content_name']];
                    app::get('base')->setConf('service.'.$item['content_name'], $current_set);
                }
            }
            $form_input = array(
                    'title'=>$item['content_title'],
                    'type'=>'select',
                    'required'=>true,
                    'name'=>"service[".$item['content_name']."]",
                    'tab'=>$tab,
                    'value'=> $current_set,
                    'options'=>$servicelist[$item['content_name']],
            );
            
            $html.=$this->ui()->form_input($form_input);
        }
        $select = $this->app->getConf('shopadminVcode');
        if($select === 'true'){

             $html .="<tr><th><label>".app::get('desktop')->_('后台登陆启用验证码')."</label></th><td>&nbsp;&nbsp;<select name='shopamin_vocde' type='select' ><option value='true' selected='selected'>".app::get('desktop')->_('是')."</option><option value='false' >".app::get('desktop')->_('否')."</option></select></td></tr>";

        }
        else{

             $html .="<tr><th><label>".app::get('desktop')->_('后台登陆启用验证码')."</lable></th><td>&nbsp;&nbsp;<select name='shopamin_vocde' type='select' ><option value='true'>".app::get('desktop')->_('是')."</option><option value='false' selected='selected'>".app::get('desktop')->_('否')."</option></select></td></tr>";

        }
        $html .= $this->ui()->form_end();
        $this->pagedata['_PAGE_CONTENT'] = $html;
        $this->page();
    }

    function licence(){
        $this->sidePanel();
        echo '<iframe width="100%" height="100%" src="'.constant('URL_VIEW_LICENCE').'" ></iframe>';
    }

}

