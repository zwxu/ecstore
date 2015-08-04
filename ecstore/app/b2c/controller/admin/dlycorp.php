<?php
 
 
class b2c_ctl_admin_dlycorp extends desktop_controller{

    var $workground = 'b2c_ctl_admin_system';
    
    public function __construct($app){
        parent::__construct($app);
        $this->ui = new base_component_ui($this);
        $this->app = $app;
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    public function index(){
        $action = array('label'=>app::get('b2c')->_('添加物流公司'),'href'=>'index.php?app=b2c&ctl=admin_dlycorp&act=addnew','target'=>'dialog::{title:\''.app::get('b2c')->_('添加物流公司').'\',width:500,height:230}');
        $this->finder('b2c_mdl_dlycorp',array('title'=>app::get('b2c')->_('物流公司'),'actions'=>array($action),'use_buildin_new_dialog'=>false,'use_buildin_set_tag'=>false,'use_buildin_recycle'=>true,'use_buildin_export'=>false));
    }

    public function addnew()
    {
        if($_POST)
        {
            $this->begin();
            $dlycorp = $this->app->model('dlycorp');
            $arrdlcorp = $dlycorp->dump(array('name' => trim($_POST['name'])));
            if (!$arrdlcorp)
            {
                if (!$_POST['ordernum'])
                    $_POST['ordernum'] = 50;
                
				$_POST['ordernum'] = intval($_POST['ordernum']);
				if ($_POST['corp_code_other'])
				{
					$_POST['corp_code'] = $_POST['corp_code_copy'];
					unset($_POST['corp_code_other']);
					unset($_POST['corp_code_copy']);
				}
                $result = $dlycorp->save($_POST);
                $this->end($result, app::get('b2c')->_('物流公司添加成功！'));
            }
            else
            {
                $this->end(false, app::get('b2c')->_('该物流公司已经存在！'));
            }            
        }
        else
        {
            /*$html = $this->ui()->form_start();
            $html .= $this->ui()->form_input(array('title'=>'物流公司名称','name'=>'name', 'vtype' => 'required', 'caution' => '请填写公司名称'));
            $html .= $this->ui()->form_input(array('title'=>'网址','name'=>'website'));
            $html .= $this->ui()->form_input(array('title'=>'询件网址','name'=>'request_url'));
            $html .= $this->ui()->form_end();
            echo $html;*/
            $this->display('admin/delivery/dlycorp_new.html');
        }
    }
    
    public function save()
    {
        if($_POST)
        {
            $this->begin();
            $dlycorp = $this->app->model('dlycorp');            
            if (!$_POST['ordernum'])
                $_POST['ordernum'] = 50;
			$_POST['ordernum'] = intval($_POST['ordernum']);
			if ($_POST['corp_code_other'])
			{
				$_POST['corp_code'] = $_POST['corp_code_copy'];
				unset($_POST['corp_code_other']);
				unset($_POST['corp_code_copy']);
			}
            $result = $dlycorp->save($_POST);
            $this->end($result, app::get('b2c')->_('物流公司修改成功！'));            
        }
    }
    
    public function showEdit()
    {
        $dly_corp = $this->app->model('dlycorp');
        $rows = $dly_corp->getList('*', array('corp_id'=>$_GET['corp_id']));
        $row = $rows[0];
        $this->pagedata['dlycrop'] = $row;
        
        $this->display('admin/delivery/dlycrop_edit.html');
    }
}
