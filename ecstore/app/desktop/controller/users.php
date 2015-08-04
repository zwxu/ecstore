<?php

 
class desktop_ctl_users extends desktop_controller{
    
    var $workground = 'desktop_ctl_system';

    public function __construct($app)
    {
        parent::__construct($app);
        //$this->member_model = $this->app->model('members');
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    function index(){
        $this->finder('desktop_mdl_users',array(
            'title'=>app::get('desktop')->_('操作员管理'),
            'actions'=>array(
                array('label'=>app::get('desktop')->_('添加管理员'),'href'=>'index.php?ctl=users&act=addnew','target'=>'dialog::{title:\''.app::get('desktop')->_('添加管理员').'\'}'),
            ),'use_buildin_export'=>false
            ));
    }
    
    function addnew(){
        $roles = $this->app->model('roles');
        $users = $this->app->model('users');
        if($_POST){
            $this->begin('index.php?app=desktop&ctl=users&act=index');
            if($users->validate($_POST,$msg)){
                if($_POST['super']==0 && (!$_POST['role'])){
                    $this->end(false,app::get('desktop')->_('请至少选择一个工作组'));
                }
                elseif($_POST['super'] == 0 && ($_POST['role'])){
                    foreach($_POST['role'] as $roles)
                    $_POST['roles'][]=array('role_id'=>$roles);
                }
                $_POST['pam_account']['createtime'] = time();
                $use_pass_data['login_name'] = $_POST['pam_account']['login_name'];
                $use_pass_data['createtime'] = $_POST['pam_account']['createtime'];
                $_POST['pam_account']['login_password'] = pam_encrypt::get_encrypted_password($_POST['pam_account']['login_password'],pam_account::get_account_type($this->app->app_id),$use_pass_data);
                $_POST['pam_account']['account_type'] = pam_account::get_account_type($this->app->app_id);
                if($users->save($_POST)){
                    foreach(kernel::servicelist('desktop_useradd') as $key=>$service){
                        if($service instanceof desktop_interface_useradd){
                            $service->useradd($_POST);
                        }
                    }
                    if($_POST['super'] == 0){   //是超管就不保存
                        $this->save_ground($_POST);
                    }
                    $this->end(true,app::get('desktop')->_('保存成功'));
                }else{
                        $this->end(false,app::get('desktop')->_('保存失败'));
                    }
                
            }
            else{
                $this->end(false,__($msg));
            }   
        }   
        else{
            $workgroup=$roles->getList('*');
            $this->pagedata['workgroup']=$workgroup; 
            $this->display('users/users_add.html');
        }     
    }

      
    ####修改密码
    function chkpassword(){
        $this->begin('index.php?app=desktop&ctl=users&act=index');
        $users = $this->app->model('users');
        if($_POST){
            $sdf = $users->dump($_POST['user_id'],'*',array( ':account@pam'=>array('*'),'roles'=>array('*') ));
            $old_password = $sdf['account']['login_password'];
            $super_row = $users->getList('user_id',array('super'=>'1'));
            $filter['account_id'] = $super_row[0]['user_id'];
            $filter['account_type'] = pam_account::get_account_type($this->app->app_id);
            $super_data = $users->dump($filter['account_id'],'*',array( ':account@pam'=>array('*')));
            $use_pass_data['login_name'] = $super_data['account']['login_name'];
            $use_pass_data['createtime'] = $super_data['account']['createtime'];
            $filter['login_password'] = pam_encrypt::get_encrypted_password(trim($_POST['old_login_password']),pam_account::get_account_type($this->app->app_id),$use_pass_data);
            $pass_row = app::get('pam')->model('account')->getList('account_id',$filter);
            if(!$pass_row){
                $this->end(false,app::get('desktop')->_('超级管理员密码不正确'));         
            }
            elseif($_POST['new_login_password']!==$_POST['pam_account']['login_password']){ // //修改0000!=00000为true的问题@lujy
                $this->end(false,app::get('desktop')->_('两次密码不一致')); 
            }
            else{
                $_POST['pam_account']['account_id'] = $_POST['user_id'];
                $use_pass_data['login_name'] = $sdf['account']['login_name'];
                $use_pass_data['createtime'] = $sdf['account']['createtime'];
                $_POST['pam_account']['login_password'] = pam_encrypt::get_encrypted_password(trim($_POST['new_login_password']),pam_account::get_account_type($this->app->app_id),$use_pass_data);
                $users->save($_POST);
                $this->end(true,app::get('desktop')->_('密码修改成功'));
            }
        }
        $this->pagedata['user_id'] = $_GET['id'];
        $this->page('users/chkpass.html');         

    }
    
    /**
    * This is method saveUser
    * 添加编辑
    * @return mixed This is the return value description
    *
    */
    
    function saveUser(){
        $this->begin();
         $users = $this->app->model('users');
        $roles=$this->app->model('roles');
        $workgroup=$roles->getList('*');
        $param_id = $_POST['account_id'];
        if(!$param_id) $this->end(false, app::get('desktop')->_('编辑失败,参数丢失！'));
        $sdf_users = $users->dump($param_id); 
         if(!$sdf_users) $this->end(false, app::get('desktop')->_('编辑失败,参数错误！'));
          //if($sdf_users['super']==1) $this->end(false, app::get('desktop')->_('不能编辑超级管理员！'));
        if($_POST){
            $_POST['pam_account']['account_id'] = $param_id;
            if($sdf_users['super']==1){
                $users->save($_POST);
                 $this->end(true, app::get('desktop')->_('编辑成功！'));
            }
            elseif($_POST['super'] == 0 && $_POST['role']){
                foreach($_POST['role'] as $roles){
                    $_POST['roles'][]=array('role_id'=>$roles);
                }
                $users->save($_POST);
                $users->save_per($_POST);
                 $this->end(true, app::get('desktop')->_('编辑成功！'));
            }
            else{
                 $this->end(false, app::get('desktop')->_('请至少选择一个工作组！'));
            }
        }
    }
    /**
    * This is method edit
    * 添加编辑
    * @return mixed This is the return value description
    *
    */
         
    function edit($param_id){
        $users = $this->app->model('users');
        $roles=$this->app->model('roles');
        $workgroup=$roles->getList('*');
        $user = kernel::single('desktop_user');
        $sdf_users = $users->dump($param_id); 
        if(empty($sdf_users)) return app::get('desktop')->_('无内容');   
        $hasrole=$this->app->model('hasrole');
        foreach($workgroup as $key=>$group){
            $rolesData=$hasrole->getList('*',array('user_id'=>$param_id,'role_id'=>$group['role_id']));
            if($rolesData){
                $check_id[] = $group['role_id'];
                $workgroup[$key]['checked']="true";
            }
            else{
                $workgroup[$key]['checked']="false";
            }
        }
        $this->pagedata['workgroup'] = $workgroup; 
        $this->pagedata['account_id'] = $param_id;
        $this->pagedata['name'] = $sdf_users['name'];
        $this->pagedata['super'] = $sdf_users['super'];
        $this->pagedata['status'] = $sdf_users['status'];
        $this->pagedata['ismyself'] = $user->user_id===$param_id?'true':'false';
        if(!$sdf_users['super']){
            $this->pagedata['per'] = $users->detail_per($check_id,$param_id);
        }
        $this->page('users/users_detail.html');
            
    }
        
    //获取工作组细分
    function detail_ground(){
        $role_id = $_POST['name'];
        $roles = $this->app->model('roles');
        $menus =$this->app->model('menus');
        $check_id = json_decode($_POST['checkedName']);
        $aPermission =array();
        if(!$check_id) {
            echo '';exit;
        }
        foreach($check_id as $val){
            $result = $roles->dump($val);
            $data = unserialize($result['workground']);
            foreach((array)$data as $row){
                $aPermission[] = $row;
            } 
        }
        $aPermission = array_unique($aPermission);
        if(!$aPermission){
            echo '';exit;
        } 
        $addonmethod = array();
        foreach((array)$aPermission as $val){
            $sdf = $menus->dump(array('menu_type' => 'permission','permission' => $val));
            $addon = unserialize($sdf['addon']);
            if($addon['show']&&$addon['save']){  //如果存在控制  
                if(!in_array($addon['show'],$addonmethod)){
                    $access = explode(':',$addon['show']);
                    $classname = $access[0];
                    $method = $access[1];
                    $obj = kernel::single($classname);
                    $html.=$obj->$method()."<br />";
                }
                $addonmethod[] = $addon['show']; 
            }  
            else{
                echo '';
            } 
        }
        echo $html;
    }
   
    //保存工作组细分
    function save_ground($aData){
        $workgrounds = $aData['role'];
        $menus = $this->app->model('menus');
        $roles =  $this->app->model('roles');
        foreach($workgrounds as $val){
            $result = $roles->dump($val);
            $data = unserialize($result['workground']);
            foreach((array)$data as $row){
                $aPermission[] = $row;
            } 
        }
        $aPermission = array_unique($aPermission);
        if($aPermission){
            $addonmethod = array();
            foreach((array)$aPermission as $key=>$val){
                $sdf = $menus->dump(array('menu_type' => 'permission','permission' => $val));
                $addon = unserialize($sdf['addon']);
                if($addon['show']&&$addon['save']){  //如果存在控制 
                    if(!in_array($addon['save'],$addonmethod)){
                        $access = explode(':',$addon['save']);
                        $classname = $access[0];
                        $method = $access[1];
                        $obj = kernel::single($classname);
                        $obj->$method($aData['user_id'],$aData);
                    }  
                    $addonmethod[] = $addon['save'];
                }   
            }
            }
        }
        
      

}
