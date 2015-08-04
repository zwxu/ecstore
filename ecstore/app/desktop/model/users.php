<?php

 
/**
 * mdl_user
 *
 * @uses modelFactory
 * @package
 * @version $Id: mdl.user.php 1985 2008-04-28 06:36:02Z flaboy $
 * @author Likunpeng <leoleegood@zovatech.com>
 * @license Commercial
 */

class desktop_mdl_users extends dbeav_model{

   var $has_parent = array(
        'pam_account' => 'account@pam'
    );
    var $has_many = array(
        'roles' => 'hasrole:replace',
    );
   var $subSdf = array(
        'default' => array(
            'pam_account:account@pam' => array('*'),
         ),
         'delete' => array(
             'pam_account:account@pam' => array('*'),
             'roles' => array('*'),
         )
    );
   function pre_recycle($data){
        $obj_pam = app::get('pam')->model('account');
        $falg = true;
        $users = kernel::single('desktop_user');
        foreach($data as $val){
            if($users->user_id == $val['user_id']){
                $this->recycle_msg = app::get('desktop')->_('自己不能删除自己');
                $falg = false;
                break;
            }
        }
        return $falg;
   }
   
  function pre_restore(&$data,$restore_type='add'){ 
         if(!($this->check_name($data['pam_account']['login_name']))){
             $data['need_delete'] = true;
             return true;
         }
         else{
             if($restore_type == 'add'){
                    $new_name = $data['pam_account']['login_name'].'_1';
                    while($this->check_name($new_name)){
                        $new_name = $new_name.'_1';
                    }
                    $data['pam_account']['login_name'] = $new_name;
                    $data['need_delete'] = true;
                 return true;    
             }
             if($restore_type == 'none'){
                 $data['need_delete'] = false;
                 return true;
             }
         }
    }
    
    function editUser(&$data){
        if($data['userpass']){
            //$data[':account@pam']['login_password'] = md5(trim($data['userpass']));
           $data[':account@pam']['login_password'] = pam_encrypt::get_encrypted_password(trim($data['userpass']),pam_account::get_account_type($this->app->app_id));
        }
        /*
        else{
            $data[':account@pam']['login_password'] = trim($data['oldpass']);
        }
       */
        $data['pam_account']['account_type'] = pam_account::get_account_type($this->app->app_id); 
        $data['pam_account']['createtime'] = time();

        return parent::save($data); 
    }
    ###
    
    ##检查用户名
    function check_name($login_name){
        $pam = app::get('pam')->model('account');
        $account_type = pam_account::get_account_type($this->app->app_id);
        $aData = $pam->getList('*',array('login_name' => $login_name,'account_type' =>$account_type ));
        $result = $aData[0]['account_id'];
        if($result){
            return true;
        }
        else{
            return false;
        }
    }
    
   ###更新登陆信息
   
   function update_admin($user_id){
       
     $aUser = $this->dump($user_id,'*');
     $sdf[':account@pam']['account_id'] = $user_id;
     $sdf['lastlogin'] = time(); 
     $sdf['logincount'] = $aUser['logincount']+1;
     $this->save($sdf);
   }
   
   ##检查
   function validate($aData,&$msg){
   
        if($aData['pam_account']['login_name']==''||$aData['pam_account']['login_password']==''||$aData['name']==''){
        $msg = app::get('desktop')->_('必填项不能为空');
        return fasle;
        }
        if($aData['pam_account']['login_password']!=$_POST['re_password']){
        $msg = app::get('desktop')->_('两次密码输入不一致');
        return false;
        }
        $result = $this->check_name($aData['pam_account']['login_name']);
        
        if($result){
              $msg = app::get('desktop')->_('该用户名已存在');    
              return false;
              
          } 
         return true;
     }
     
     //获取工作组细分
    function detail_per($check_id,$user_id){
        $roles = $this->app->model('roles');
        $menus =$this->app->model('menus');
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
                    $html.=$obj->$method($user_id);
                }
                $addonmethod[] = $addon['show']; 
            }  
            else{
                echo '';
            } 
        } 
        return $html;
    }
    
    //保存工作组细分
    function save_per($aData){
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
