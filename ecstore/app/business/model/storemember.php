<?php
class business_mdl_storemember extends dbeav_model {
    var $has_tag = true;
    var $defaultOrder = array('store_id', ' DESC');
   
    var $has_one = array();
   

    public function count_finder($filter = null) {
        $row = $this -> db -> select('SELECT count( DISTINCT attach_id) as _count FROM ' . $this -> table_name(1) . ' WHERE ' . $this -> _filter($filter));
       return intval($row[0]['_count']);
       // return intval('3');
    } 

    public function get_list_finder($cols = '*', $filter = array(), $offset = 0, $limit = -1, $orderType = null) {
        $tmp = $this -> getList('*', $filter, 0, -1, $orderType);
        return array_slice($tmp, $offset, $limit);
    }
    
    //获取店主信息
    function getshopinfo($account_id) {

        $account_id=trim($account_id);
        $sql ="SELECT sdb_business_storemanger.store_id,
                      sdb_business_storemanger.shop_name,
                      sdb_pam_account.login_name,
				      sdb_b2c_members.member_id,
                      sdb_b2c_members.tel,
                      sdb_b2c_members.mobile,
                      sdb_b2c_members.regtime,
                      sdb_b2c_members.pw_question,
                      sdb_b2c_members.pw_answer,
                      sdb_b2c_members.email,
                      sdb_b2c_members.seller,
                      sdb_b2c_members.`name`         
                 FROM sdb_business_storemanger
                      LEFT JOIN sdb_b2c_members ON sdb_b2c_members.member_id =  sdb_business_storemanger.account_id
                      LEFT JOIN sdb_pam_account ON sdb_pam_account.account_id = sdb_b2c_members.member_id
                WHERE sdb_business_storemanger.account_id ='{$account_id}'   
            ORDER BY  sdb_business_storemanger.d_order";

     
       return $this->db->select($sql);

    }
     //根据店主获取店员信息
     function getshopmember($account_id) {

        $account_id=trim($account_id);
        $sql ="SELECT sdb_business_storemanger.store_id,
                      sdb_business_storemanger.shop_name,
                      sdb_pam_account.login_name,
					  sdb_business_storeroles.role_id,
					  sdb_business_storeroles.role_name,
					  sdb_business_storeroles.workground,
				      sdb_b2c_members.member_id,
                      sdb_b2c_members.tel,
                      sdb_b2c_members.mobile,
                      sdb_b2c_members.regtime,
                      sdb_b2c_members.email,
                      sdb_b2c_members.`name`         
                 FROM sdb_business_storemember
					  LEFT JOIN sdb_business_storemanger ON sdb_business_storemember.store_id = sdb_business_storemanger.store_id
                      LEFT JOIN sdb_b2c_members ON sdb_b2c_members.member_id =  sdb_business_storemember.member_id
                      LEFT JOIN sdb_pam_account ON sdb_pam_account.account_id = sdb_b2c_members.member_id
					  LEFT JOIN sdb_business_storeroles ON sdb_business_storeroles.role_id = sdb_business_storemember.roles_id
                WHERE sdb_business_storemanger.account_id ='{$account_id}'   
            ORDER BY  sdb_business_storemember.d_order";
     
       return $this->db->select($sql);

    }

    //根据店员获取店铺信息
     function getmemberstoreinfo($member_id) {

        $member_id=trim($member_id);
        $sql ="SELECT   sdb_business_storemanger.*, 
                        sdb_business_storeroles.role_id,
                        sdb_business_storeroles.role_name,
                        sdb_business_storeroles.workground,
                        sdb_pam_account.login_name,
				        sdb_b2c_members.member_id,
                        sdb_b2c_members.tel,
                        sdb_b2c_members.mobile,
                        sdb_b2c_members.regtime,
                        sdb_b2c_members.email,
                        sdb_b2c_members.`name`   
                  FROM  sdb_business_storemanger
              LEFT JOIN sdb_business_storemember ON sdb_business_storemanger.store_id = sdb_business_storemember.store_id 
              LEFT JOIN sdb_b2c_members ON sdb_business_storemember.member_id = sdb_b2c_members.member_id
              LEFT JOIN sdb_pam_account ON sdb_pam_account.account_id = sdb_b2c_members.member_id
              LEFT JOIN sdb_business_storeroles ON sdb_business_storeroles.role_id = sdb_business_storemember.roles_id
                  WHERE sdb_business_storemember.member_id ='{$member_id}'   
               ORDER BY sdb_business_storemanger.d_order, sdb_business_storemember.d_order";

     
       return $this->db->select($sql);

    }



    function del_rec($store_id,$member_id,&$message) {

         if($store_id && $member_id){
             $filter = array('store_id'=>$store_id,'member_id' => $member_id);
             if($this->delete($filter)){
                  $message = app::get('business')->_("删除成功");
                   return true;
             }
             else{
                 $message = app::get('business')->_("删除失败");
                   return true;
             }

        }else{
            $message = app::get('business')->_("参数有误");
             return false;
        }

    }


    function insertRec($aData,$nMemberId,&$message){ 

        $loginname = strtolower($aData['name']);

        $member_model = &app::get('b2c')->model('members');

        $row = $this->db->select("SELECT sdb_pam_account.account_id from  sdb_pam_account WHERE  sdb_pam_account.login_name='{$loginname}'");
        
         if($row) {
             $member_id =$row[0]['account_id'];
         } else if($aData['user'] !='seller'){
             $message = app::get('business')->_("此用户名不存在");
             return false;
         } else{

             //新注册会员
            $aMember['seller'] =$aData['user'];
            $aMember['pam_account']['login_name'] = $loginname;
            $aMember['contact']['email'] = htmlspecialchars($aData['contact']['commonlyemail']);

            $aMember['pam_account']['account_type'] = pam_account::get_account_type('b2c');
            $aMember['pam_account']['createtime'] = time();

            $use_pass_data['login_name'] = $aMember['pam_account']['login_name'];
            $use_pass_data['createtime'] = $aMember['pam_account']['createtime'];

            $aMember['pam_account']['login_password'] = pam_encrypt::get_encrypted_password(trim($aData['pam_account']['login_password']), pam_account::get_account_type('b2c'), $use_pass_data);

            $aMember['uc_pwd'] = trim($aData['pam_account']['login_password']);

            $aMember['reg_ip'] = base_request::get_remote_addr();
            $aMember['regtime'] = $aMember['pam_account']['createtime'];

                
            $lv_model = &app::get('b2c')->model('member_lv');
            $aMember['member_lv']['member_group_id'] = $lv_model->get_default_lv();
            $arrDefCurrency = app::get('ectools')->model('currency')->getDefault();
            $aMember['currency'] = $arrDefCurrency['cur_code'];
            $aMember['reg_type'] ='username';
         }
         if($aData['store_id']) {
            $row = $this->db->select("SELECT store_id from  sdb_business_storemanger WHERE  sdb_business_storemanger.store_id ={$aData['store_id']}");
         }else{
            $row = $this->db->select("SELECT store_id from  sdb_business_storemanger WHERE  sdb_business_storemanger.account_id ='{$nMemberId}'");
         }
        
         if($row) {
              $store_id =$row[0]['store_id'];
         } else {
             $message = app::get('business')->_("此店铺不存在");
             return false;
         }

        
        if($member_id){
            //$sql =" SELECT * from sdb_business_storemember WHERE   sdb_business_storemember.member_id 
            //                 = '{$member_id}' AND sdb_business_storemember.store_id = '{$store_id}'";
            $sql =" SELECT * from sdb_business_storemember WHERE   sdb_business_storemember.member_id 
                             = '{$member_id}' ";

            $row = $this->db->select($sql);

            if($row) {
                 $message = app::get('business')->_("该用户已经是店员");
                 return false;
            }

            $sql =" SELECT * from sdb_business_storemanger WHERE   sdb_business_storemanger.account_id
                             = '{$member_id}' ";

            $row = $this->db->select($sql);

            if($row) {
                 $message = app::get('business')->_("该用户已经是店主");
                 return false;
            }

            $storemember['member_id']=$member_id;
        }
      	
        $storemember['store_id']=   $store_id ;
       
        //$storemember['shop_password']= md5(trim($aData['pam_account']['login_password']));
        $storemember['roles_id'] =$aData['roles'];


       // print_r( $aMember);exit;
          
        $db = kernel::database();
        $db->beginTransaction();

        if($aData['user']=='seller') {
           //同步到ucenter 
            if( $uc = kernel::service("uc_user_register") ) {
                 $uid = $uc->uc_user_register($aMember['pam_account']['login_name'],trim($aData['pam_account']['login_password']),$aMember['contact']['email'],'','','',$aMember['contact']['phone']['mobile'],null,$aMember['reg_type']);
                if($uid>0){
                    $aMember['foreign_id'] = $uid;
                }else{
                    //echo json_encode(array('status'=>'failed', 'url'=>'back', 'msg'=>'UCenter注册失败,请检查用户名'));
                     $message = app::get('business')->_("UCenter注册失败,请检查用户名");
                     return false;
                }
               
            }
           //同步到ucenter 

           if($member_model->save($aMember)){
                $message = app::get('business')->_("会员注册成功");
                $storemember['member_id'] = $member_model -> db -> lastInsertId();
           }else {
               $db->rollBack();
               $message = app::get('business')->_("会员注册失败");
               return false;
    
           }
             
         }

         if($this->save($storemember)){
             $db->commit();
             $message = app::get('business')->_("保存成功");
             return $member_id;
         }else{
             $db->rollBack();
             $message = app::get('business')->_("保存失败");
             return false;
         }
    }

    function  getshopmemberbyid($store_id,$member_id) {

        $sql = "SELECT sdb_business_storemember.store_id,
                       sdb_business_storemember.member_id,
                       sdb_business_storemember.attach_id,
                       sdb_business_storemember.roles_id,
					   sdb_business_storeroles.role_name,
                       sdb_pam_account.login_name,
                       sdb_b2c_members.`name`
                  from sdb_business_storemember
                       LEFT JOIN sdb_pam_account ON sdb_pam_account.account_id = sdb_business_storemember.member_id
                       LEFT JOIN sdb_b2c_members  ON  sdb_b2c_members.member_id  = sdb_business_storemember.member_id
                       LEFT JOIN sdb_business_storeroles ON sdb_business_storeroles.role_id = sdb_business_storemember.roles_id
                 WHERE sdb_business_storemember.store_id = '{$store_id}' AND sdb_business_storemember.member_id ='{$member_id}'";

       $row = $this->db->select($sql);

       return $row;


    }

    function getmemberbyname($name){
        $sql ="SELECT *  FROM  sdb_b2c_members   LEFT JOIN sdb_pam_account ON sdb_pam_account.account_id = sdb_b2c_members.member_id
                WHERE   sdb_pam_account.login_name ='{$name}'";
        $row = $this->db->select($sql);
       return $row;

    }

   


     function save_security($aData,&$msg){
            $nMemberId = $aData['member_id'];

            $aMem = app::get('b2c')->model('members')->dump($nMemberId,'*',array(':account@pam'=>array('*')));
            if(!$aMem){
                $msg=app::get('business')->_('无效的用户Id');
                return false;
            }

            $rows = $this->getshopmemberbyid( $aData['store_id'], $aData['member_id']);

             if(!$rows){
                $msg=app::get('business')->_('无效的店员');
                return false;
            }
 
            if(md5($aData['old_passwd']) != $rows[0]['shop_password']){
                 $msg=app::get('business')->_('输入的旧密码与原密码不符！');
                return false;
            }
           
            if($aData['passwd'] != $aData['passwd_re']){
                $msg=app::get('b2c')->_('两次输入的密码不一致！');
                return false;
            }


            if( strlen($aData['passwd']) <  4 ){
                 $msg=app::get('business')->_('密码长度不能小于4');
                 return false;
             }

             if( strlen($aData['passwd']) > 20 ){
                 $msg=app::get('business')->_('密码长度不能大于20');
                 return false;
             }
            $storemember['attach_id'] =$aData['attach_id'];
            $storemember['store_id'] =$aData['store_id'];
            $storemember['member_id'] =$aData['member_id'];
            $storemember['shop_password'] =md5($aData['passwd']);
            if($this->save($storemember)){
                $msg = app::get('business')->_("密码修改成功");
                 return true;
            }else{
                $msg=app::get('business')->_('密码修改失败！');
                return false;
            }
        
     }
     // 数据权限过滤
     function _filter($filter=array(),$tbase=''){
        
        foreach(kernel::servicelist('business_mdl_storemember.filter') as $k=>$obj_filter){
            if(method_exists($obj_filter,'extend_filter')){
                $obj_filter->extend_filter($filter);
            }
        }
        return parent::_filter($filter,$tbase);
     }
       function save_roles($aData,&$msg){


            $nMemberId = $aData['member_id'];

            $aMem = app::get('b2c')->model('members')->dump($nMemberId,'*',array(':account@pam'=>array('*')));
            if(!$aMem){
                $msg=app::get('business')->_('无效的用户Id');
                return false;
            }

            $rows = $this->getshopmemberbyid( $aData['store_id'], $aData['member_id']);

             if(!$rows){
                $msg=app::get('business')->_('无效的店员');
                return false;
            }

            $storemember['attach_id'] =$aData['attach_id'];
            $storemember['store_id'] =$aData['store_id'];
            $storemember['member_id'] =$aData['member_id'];
            $storemember['roles_id'] =$aData['roles_id'];
            if($this->save($storemember)){
                $msg = app::get('business')->_("角色修改成功");
                 return true;
            }else{
                $msg=app::get('business')->_('角色修改失败！');
                return false;
            }




       }





      
} 
