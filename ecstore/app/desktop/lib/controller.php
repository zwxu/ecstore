<?php

 
class desktop_controller extends base_controller{

    var $defaultwg;
    function __construct($app){
		header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
		header('Progma: no-cache');
        $this->defaultwg = $this->defaultWorkground;
        parent::__construct($app);
        kernel::single('base_session')->start();

        if($_COOKIE['autologin'] > 0){
            kernel::single('base_session')->set_sess_expires($_COOKIE['autologin']);
        }//如果有自动登录，设置session过期时间，单位：分
        $auth = pam_auth::instance(pam_account::get_account_type('desktop'));
        $account = $auth->account();
        if($_GET['sign']=='site'){           
            $account->type = 'member';
            if(!$account->is_valid()){
                 echo 'error';exit;
            }
        }else{
            if(get_class($this)!='desktop_ctl_passport' && !$account->is_valid()){
                if(get_class($this) != 'desktop_ctl_default')
                    $url = kernel::router()->gen_url($_GET,1);
                else
                    $url = kernel::router()->gen_url(array(),1);
                $url = base64_encode($url);
                $arr_get = $_GET;
                foreach ($arr_get as &$str_get)
                    $str_get = urldecode($str_get);
                $params = urlencode(json_encode($arr_get));
                $_GOTO = 'index.php?ctl=passport&url='.$url.'&params='.$params;
                echo "<script>location ='$_GOTO'</script>";exit;
            }
            $this->user = kernel::single('desktop_user');
            if($_GET['ctl']!="passport"&&$_GET['ctl']!=""){
                $this->status = $this->user->get_status();
                if(!$this->status&&$this->status==0){
                    #echo "未启用";exit;
                    //echo "<script>alert('管理员未启用')</script>";
                    $url = kernel::router()->gen_url(array(),1);
                    $url = base64_encode($url);
                    header('Content-Type:text/html; charset=utf-8');
                    $this->pagedata['link_url'] = 'index.php?ctl=passport&url='.$url;
                    echo $this->fetch('auth_error.html');
                    unset($_SESSION['account']['user_data']);//删除session-user_data数据 add by ql
                    unset($_SESSION[$this->type]);
                    exit;
                }
            }
            ###如果不是超级管理员就查询操作权限
            if(!$this->user->is_super()){
                if(!$this->user->chkground($this->workground)){
                    header('Content-Type:text/html; charset=utf-8');
                    echo app::get('desktop')->_("您无权操作");exit;
                }    
            }
        }
		$obj_model = app::get('desktop')->model('menus');
		//检查链接是否可用
        $obj_model->permissionId($_GET);
        //end
        $this->_finish_modifier = array();
        foreach(kernel::servicelist(sprintf('desktop_controller_content.%s.%s.%s', $_GET['app'],$_GET['ctl'],$_GET['act']))
                as $class_name=>$service){
            if($service instanceof desktop_interface_controller_content){
                if(method_exists($service,'modify')){
                    $this->_finish_modifier[$class_name] = $service;
                }
                if(method_exists($service,'boot')){
                    $service->boot($this);
                }
            }
        }
		//修改tab detail 里的内容
        foreach(kernel::servicelist(sprintf('desktop_controller_content_finderdetail.%s.%s.%s.%s', $_GET['app'],$_GET['ctl'],$_GET['act'],(string)(isset($_GET['finderview'])?$_GET['finderview']:'0')))
                as $class_name=>$service){
            if($service instanceof desktop_interface_controller_content){
                if(method_exists($service,'modify')){
                    $this->_finish_modifier[$class_name] = $service;
                }
                if(method_exists($service,'boot')){
                    $service->boot($this);
                }
            }
        }
        if($this->_finish_modifier){
            ob_start();
            register_shutdown_function(array(&$this,'finish_modifier'));
        }

        $this->url = 'index.php?app='.$this->app->app_id.'&ctl='.$_GET['ctl'];
    }

	

    function __destruct(){

        foreach(kernel::servicelist('desktop_controller_destruct') AS $service){
            if(is_object($service) && method_exists($service, 'destruct')){
                $service->destruct($this);
            }
        }//todo: 析构
    }

    /*
    * 有modifier的处理程序
    */
    function finish_modifier(){
        $content = ob_get_contents();
        ob_end_clean();
        foreach($this->_finish_modifier as $modifier){
            $modifier->modify($content,$this);
        }
        echo $content;
    }
    
    function redirect($url){
        $arr_url = parse_url($url);
        if($arr_url['scheme'] && $arr_url['host']){
            header('Location: '.$url);
        }else{
            header('Location: '.kernel::router()->app->base_url(1).$url);
        }
        // 
    }
	function location_to(){
        if(kernel::single('base_component_request')->is_ajax()!=true){
            header('Location: index.php#'.$_SERVER['QUERY_STRING']);
        }
    }
    function finder($object_name,$params=array()){
        if($_GET['action']!='to_export'&&$_GET['action']!='to_import'&&$_GET['singlepage']!='true'){
            $this->location_to();
        }
        header("cache-control: no-store, no-cache, must-revalidate");
        $_GET['action'] = $_GET['action']?$_GET['action']:'view';
        $finder = kernel::single('desktop_finder_builder_'.$_GET['action'],$this);

        foreach($params as $k=>$v){
            $finder->$k = $v;
        }
        $app_id = substr($object_name,0,strpos($object_name,'_'));
        $app = app::get($app_id);
        $finder->app = $app;
        $finder->work($object_name);
    }

    function singlepage($view, $app_id=''){
        

        $service = kernel::service(sprintf('desktop_controller_display.%s.%s.%s', $_GET['app'],$_GET['ctl'],$_GET['act']));
        if($service){
            if(method_exists($service, 'get_file'))  $view = $service->get_file();
            if(method_exists($service, 'get_app_id'))   $app_id = $service->get_app_id();
        }

        $page = $this->fetch($view, $app_id);

        $this->pagedata['_PAGE_PAGEDATA_'] = $this->_vars;

        $re = '/<script([^>]*)>(.*?)<\/script>/is';
        $this->__scripts = '';
        $page = preg_replace_callback($re,array(&$this,'_singlepage_prepare'),$page)
            .'<script type="text/plain" id="__eval_scripts__" >'.$this->__scripts.'</script>';

        //后台singlepage页面增加自定义css引入到head标签内的操作--@lujy-start
        $recss = '/<link([^>]*)>/is';
        $this->__link_css = '';
        $page = preg_replace_callback($recss,array(&$this,'_singlepage_link_prepare'),$page);
        $this->pagedata['singleappcss'] = $this->__link_css;
        //--end

        $this->pagedata['statusId'] = $this->app->getConf('b2c.wss.enable');
        $this->pagedata['session_id'] = kernel::single('base_session')->sess_id();
        $this->pagedata['desktop_path'] = app::get('desktop')->res_url;
        $this->pagedata['shopadmin_dir'] = dirname($_SERVER['PHP_SELF']).'/';
        $this->pagedata['shop_base'] = $this->app->base_url();
        $this->pagedata['desktopresurl'] = app::get('desktop')->res_url;
        $this->pagedata['desktopresfullurl'] = app::get('desktop')->res_full_url;


        $this->pagedata['_PAGE_'] = &$page;
        $this->display('singlepage.html','desktop');
    }

    function _singlepage_prepare($match){
        if($match[2] && !strpos($match[1],'src') && !strpos($match[1],'hold')){
            $this->__scripts.="\n".$match[2];
            return '';
        }else{
            return $match[0];
        }
    }

    //处理singlepage页面的css的preg_replace_callback的回调替换函数--@lujy-start
    function _singlepage_link_prepare($matches){
        $this->__link_css .= $matches[0];
        return '';
    }
    //--end

    function _outSplitBegin($key){
       return "<!-----$key-----";
    }
    
    function _outSplitEnd($key){
       return "-----$key----->";
    }
    


    

    function url_frame($url){
        $this->sidePanel();
        echo '<iframe width="100%" scrolling="auto" allowtransparency="true" frameborder="0" height="100%" src="'.$url.'" ></iframe>';
    }

    function page($view='', $app_id=''){
        $this->location_to();
        $_SESSION['message'] = '';
        

        $service = kernel::service(sprintf('desktop_controller_display.%s.%s.%s', $_GET['app'],$_GET['ctl'],$_GET['act']));
        if($service){
            if(method_exists($service, 'get_file'))  $view = $service->get_file();
            if(method_exists($service, 'get_app_id'))   $app_id = $service->get_app_id();
        }


        if(!$view){
            $view = 'common/default.html';
            $app_id = 'desktop';
        }

        ob_start();
        parent::display($view, $app_id);
        $output = ob_get_contents();
        ob_end_clean();        
                
        $output=$this->sidePanel().$output;

        $this->output($output);
    }
    
    

    function sidePanel(){
         $menuObj = app::get('desktop')->model('menus');
         $bcdata = $menuObj->get_allid($_GET);
         $output = '';
         if(!$this->workground){
            $this->workground = get_class($this);
         }
         $output.="<script>window.BREADCRUMBS ='".($bcdata['workground_id']?$bcdata['workground_id']:0)
                                                .":"
                                                .($bcdata['menu_id']?$bcdata['menu_id']:0)
                                                ."';</script>";
                                            
         if('desktop_ctl_dashboard'==$this->workground){

             $output .="<script>fixSideLeft('add');</script>";
             return $output;
         }else{
             
             $output .="<script>fixSideLeft('remove');</script>";
         }

        if($_SERVER['HTTP_WORKGROUND'] == $this->workground){
            return $output;
        }

            
        $output.= $this->_outSplitBegin('.side-content');
        $output .= $this->get_sidepanel($menuObj);
        $output .= $this->_outSplitEnd('.side-content');
            
        $output .= '<script>window.currentWorkground=\''.$this->workground.'\';</script>';
 
        return $output;
    }

    public function output(&$output) 
    {
       echo $output;
    }//End Function

   function splash($status='success',$url=null,$msg=null,$method='redirect',$params=array()){
        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
        header('Progma: no-cache');
        $default = array(
                $status=>$msg?$msg:app::get('desktop')->_('操作成功'),
                $method=>$url,
			);
		$arr = array_merge($default, $params ,array('splash'=>true));
        $json = json_encode($arr);

        if($_FILES){
            header('Content-Type: text/html; charset=utf-8');
        }else{
			 header('Content-Type:text/jcmd; charset=utf-8');
		}
		echo $json;
        exit;
    }

    /**
     * jump_to
     *
     * @param string $act
     * @param string $ctl
     * @param array $args
     * @access public
     * @return void
     */
    function jumpTo($act='index',$ctl=null,$args=null){

        $_GET['act'] = $act;
        if($ctl) $_GET['ctl'] = $ctl;
        if($args) $_GET['p'] = $args;

        if(!is_null($ctl)){

            if($pos=strpos($_GET['ctl'],'/')){
                $domain = substr($_GET['ctl'],0,$pos);
            }else{
                $domain = $_GET['ctl'];
            }
            $ctl = $this->app->single(str_replace('/', '-', $ctl));
            $ctl->message = $this->message;
            $ctl->pagedata = &$this->pagedata;
            $ctl->ajaxdata = &$this->ajaxdata;
            call_user_func(array(str_replace('/', '_', $ctl), $act), $args);
        }else{
            call_user_func(array(get_class($this), $act), $args);
        }
    }
    
    function has_permission($perm_id){
        $user = kernel::single('desktop_user');
        return $user->has_permission($perm_id);
    }
    
   function get_sidepanel($menuObj){
        $obj = $menuObj;
        $workground_menus = ($obj->menu($_GET,$this->defaultwg));
        if($workground_menus['nogroup']){
            $nogroup = $workground_menus['nogroup'];            
            unset($workground_menus['nogroup']);

        }
        if(!$workground_menus){
            $dashboard_menu = new desktop_sidepanel_dashboard(app::get('desktop'));
            return $dashboard_menu->get_output();
            
        }
        $workground = array();
        $render = app::get('desktop')->render();
        if($_GET['app']&&$_GET['ctl']){
            $workground = $obj->get_current_workground($_GET);
            $render->pagedata['workground'] = $workground;
        };
        $data_id = $obj->get_allid($_GET);
        //$render->pagedata['dataid'] = $data_id['workground_id'].":".$data_id['menu_id'];
        $render->pagedata['side'] = "leftpanel";
        $render->pagedata['menus_data'] = $workground_menus;
        $render->pagedata['nogroup'] = $nogroup;
        return $render->fetch('sidepanel.html');

    }
    function tags(){
        $ex_p = '&wg='.urlencode($_GET['wg']).'&type='.urlencode($_GET['type']);
        $params = array(
            'title'=>app::get('desktop')->_('标签管理'),
            'actions'=>array(
                array('label'=>app::get('desktop')->_('新建普通标签'),'icon'=>'add.gif','href'=>$this->url.'&act=new_mormal_tag'.$ex_p,'target'=>'dialog::{title:\''.app::get('desktop')->_('新建普通标签').'\'}'),
               // array('label'=>'新建条件标签','href'=>$this->url.'&act=new_filter_tag'.$ex_p,'target'=>'dialog::{title:\'新建条件标签\'}'),
            ),
            'base_filter'=>array(
                'tag_type'=>$_GET['type']
            ),'use_buildin_new_dialog'=>false,'use_buildin_set_tag'=>false,'use_buildin_export'=>false);
        $this->finder('desktop_mdl_tag',$params);
    }

    function new_mormal_tag(){
        $ex_p = '&wg='.urlencode($_GET['wg']).'&type='.urlencode($_GET['type']);
       if($_POST){
            $this->begin();
            $tagmgr = app::get('desktop')->model('tag');
            $data = array(
                    'tag_name'=>$_POST['tag_name'],
                    'tag_abbr'=>$_POST['tag_abbr'],
                    'tag_type'=>$_REQUEST['type'],
                    'app_id'=>$this->app->app_id,
                    'tag_mode'=>'normal',
                    'tag_bgcolor'=>$_POST['tag_bgcolor'],
                    //'tag_fgcolor'=>$_POST['tag_fgcolor'],
                );
            if($_POST['tag_id']){
                $data['tag_id'] = $_POST['tag_id'];
            }//print_r($data);exit;
            $tagmgr->save($data);
            $this->end();
        }else{
            $html = $this->ui()->form_start(array(
                'action'=>$this->url.'&act=new_mormal_tag'.$ex_p,
                'id'=>'form_settag',
                'method' => 'post',
                ));
            $html .= $this->ui()->form_input(array('title'=>app::get('desktop')->_('标签名'),'vtype'=>'required','name'=>'tag_name'));
            $html .= $this->ui()->form_input(array('title'=>app::get('desktop')->_('标签备注'),'name'=>'tag_abbr'));
            $html .= $this->ui()->form_input(array('title'=>app::get('desktop')->_('标签颜色'),'type'=>'color','name'=>'tag_bgcolor'));
            //$html .= $this->ui()->form_input(array('title'=>app::get('desktop')->_('标签字体景色'),'type'=>'color','name'=>'tag_fgcolor'));
            $html.=$this->ui()->form_end();
            $___infomation=app::get('desktop')->_('如果新建的标签已经存在，则此操作变为编辑原标签');

echo <<<EOF
<div style="margin: 5px;" class="notice">
$___infomation
</div>
{$html}
<script>

   \$('form_settag').store('target',{
        
     
        onComplete:function(){
        
            if(window.finderGroup['{$_GET['finder_id']}'])
            window.finderGroup['{$_GET['finder_id']}'].refresh();
                         
            $('form_settag').getParent('.dialog').retrieve('instance').close();
             
        }
   
   });

</script>
EOF;
        }
    }

    function tag_edit($id){
        $this->url = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'];
       $render =  app::get('desktop')->render();
        //return $render->fetch('admin/tag/detail.html',$this->app->app_id);
        $mdl_tag = app::get('desktop')->model('tag');
        $tag = $mdl_tag->dump($id,'*');
        $ui = new base_component_ui(null,app::get('desktop'));
        $html = $ui->form_start(array(
                        'action'=>$this->url.'&act=new_mormal_tag'.$ex_p,
                        'id'=>'tag_form_add',
                        'method' => 'post',
                        ));
            $html .= $ui->form_input(array('title'=>app::get('desktop')->_('标签名'),'name'=>'tag_name','value'=>$tag['tag_name']));
            $html .= $ui->form_input(array('title'=>app::get('desktop')->_('标签备注'),'name'=>'tag_abbr','value'=>$tag['tag_abbr']));
            $html .= $ui->form_input(array('title'=>app::get('desktop')->_('标签颜色'),'type'=>'color','name'=>'tag_bgcolor','value'=>$tag['tag_bgcolor']));
            //$html .= $ui->form_input(array('title'=>app::get('desktop')->_('标签字体色'),'type'=>'color','name'=>'tag_fgcolor','value'=>$tag['tag_fgcolor']));
            $html .= '<input type="hidden" name="tag_id" value="'.$id.'"/>';
            $html .= '<input type="hidden" name="app_id" value="'.$tag['app_id'].'"/>';
            $html .= '<input type="hidden" name="type" value="'.$tag['tag_type'].'"/>';
            $html.=$ui->form_end();
            echo $html;
echo <<<EOF
<script>
window.addEvent('domready', function(){
    $('tag_form_add').store('target',{
        onComplete:function(){
            
           if(window.finderGroup['{$_GET['finder_id']}'])
            window.finderGroup['{$_GET['finder_id']}'].refresh();
            
            if($('tag_form_add').getParent('.dialog'))
            $('tag_form_add').getParent('.dialog').retrieve('instance').close();
        }
    });
});
</script>
EOF;
    }
    public function pre_display(&$content){
		parent::pre_display($content);
		
        if($this->_ignore_pre_display === false){
            foreach(kernel::servicelist('desktop_render_pre_display') AS $service){
                if(method_exists($service, 'pre_display')){
                    $service->pre_display($content);
                }
            }
        }
    }

}
