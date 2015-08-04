<?php
class business_ctl_site_webcall extends business_ctl_site_member{

    public function manage($type='',$custom_cat_id=''){
        $member_id = $this->app_b2c->member_id;
        $sto= kernel::single("business_memberstore",$member_id);
        $store_id = $sto->storeinfo['store_id'];
        
        $objCustomer = app::get('business')->model('customer_service');
        $member_info = $objCustomer->getList('*', array('store_id'=>$store_id));
        $this->pagedata['data'] = $member_info;

        if($type == 'edit'){
            $info = $objCustomer->getList('*', array('items_id'=>$custom_cat_id));
            $this->pagedata['info'] = $info['0'];
            $this->pagedata['edit'] = 1;
        }
        
        $this->pagedata['_PAGE_'] = 'customer_service.html';
        $this->output('business');
    }

    function toAddservice(){
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_webcall','act'=>'manage'));
        if($_POST){
            $member_id = $this->app->member_id;
            $sto= kernel::single("business_memberstore",$member_id);
            $store_id = $sto->storeinfo['store_id'];
            $_POST['store_id'] = $store_id;
            $data = $_POST;
            if($_POST['edit'] == '1'){
                $data['items_id'] = $_POST['items_id'];
            }
            $objCat = app::get('business')->model('customer_service');
            $is_null = $objCat->getList('items_id',array('store_id'=>$store_id));
            if(count($is_null) == 0){
                $data['is_defult'] = '1';
            }
            if($objCat->save($data))
                $this->splash('success',$url,app::get('business')->_('操作成功'),'','',true);
            else
                $this->splash('failed',$url,app::get('business')->_('操作失败'),'','',true);

        }else{
            $this->splash('failed',$url,app::get('business')->_('缺少参数'),'','',true);
        }
    }

    function toRemove(){
        $filter['items_id']=$_POST['item_id'];
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_webcall','act'=>'manage'));

        $objCat = app::get('business')->model('customer_service');

        if($objCat->delete($filter)){
            $arr=json_encode(array('status'=>'success','message'=>'已删除'));
        }else{
           $arr=json_encode(array('status'=>'success','message'=>'删除失败'));
        }
        
         echo $arr;
    }

    function defult_edit($items_id){
        $member_id = $this->app->member_id;
        $sto= kernel::single("business_memberstore",$member_id);
        $store_id = $sto->storeinfo['store_id'];

        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_webcall','act'=>'manage'));

        $objCat = app::get('business')->model('customer_service');
        $items = $objCat->getList('items_id',array('store_id'=>$store_id,'is_defult'=>'1'));
        $success = $objCat->update(array('is_defult'=>'2'),array('items_id'=>$items['0']['items_id']));
        if($success){
            $result = $objCat->update(array('is_defult'=>'1'),array('items_id'=>$_POST['item_id']));
        }
        if($result){
            $arr=json_encode(array('status'=>'success','message'=>'设置成功'));
        }else{
            $arr=json_encode(array('status'=>'success','message'=>'设置失败'));
        }
        
         echo $arr;
    }
    
    public function children(){
        $params = array();
        $params['member_id'] = $_POST['member_id'];
        $params['email'] = $_POST['email'];
        $params['pwd'] = $_POST['pwd'];
        $this->children_manage($params);
    }
    
    function children_manage($params){
        $host = defined('WEBCALL_HOST')?WEBCALL_HOST:'';
        //$reg_url = "{$host}/invite.aspx?email=".$params['email']."&accountid=shopex&pwd=".$params['pwd'];
        $url = app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_webcall','act'=>'manage'));
        if(!isset($params['member_id']) || empty($params['member_id'])){
            $this->splash('failed',$url , app::get('b2c')->_('店员为空，自动开通客服帐号失败'),'','',false);
        }
        $objMember = app::get('b2c')->model('members');
        $objStore = app::get('business')->model('storemanger');
        $objStoreM = app::get('business')->model('storemember');
        $objAccount = app::get('pam')->model('account');
        $store_member = $objStoreM->getList('store_id,member_id', array('member_id'=>$params['member_id']));
        $store_member = $store_member[0];
        if($store_member['store_id'] != $this->store_id) $this->splash('failed',$url , app::get('b2c')->_('非法操作，自动开通客服帐号失败'),'','',false);
        if(!$store_member['member_id']){
            $this->splash('failed',$url , app::get('b2c')->_('店员不存在，自动开通客服帐号失败'),'','',false);
        }
        $main_account = $objMember->db->select("select m.im_webcall,m.name from {$objMember->table_name(1)} as m join {$objStore->table_name(1)} as s on m.member_id=s.account_id and s.store_id='{$store_member['store_id']}'");
        $main_account = $main_account[0];
        if(!$main_account['im_webcall']){
            $this->splash('failed',$url , app::get('b2c')->_('店主没有开通主客服账号，自动开通客服帐号失败'),'','',false);
        }
        $reg_url = "{$host}/addSubAccount.aspx?mainAccount=".urlencode($main_account['im_webcall'])."&email=".urlencode($params['email'])."&accountid=B2B2C.szmall.com&pwd=".urlencode($params['pwd'])."&name=".urlencode(($name = substr($main_account['name'], 0, 20))?$name:'客服');
        //$reg_url = urlencode($reg_url);

        //初始化
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $reg_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        //执行
        $return = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);
        if(intval($return)>0){
            $flag = $objMember->db->exec("update {$objMember->table_name(1)} set im_webcall = '{$params['email']}' where member_id = {$params['member_id']}");
            if (!$flag)
            {
                $this->splash('failed',$url , app::get('b2c')->_('自动开通客服帐号失败'),'','',false);
            }
            $this->splash('success',$url , app::get('b2c')->_('自动开通客服帐号成功'),'','',false);
        }else{
            $this->splash('failed',$url , app::get('b2c')->_('客服帐号已被占用，请检查！'),'','',false);
        }
    }
}