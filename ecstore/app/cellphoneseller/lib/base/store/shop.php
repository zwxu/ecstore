<?php

class cellphoneseller_base_store_shop extends cellphoneseller_cellphoneseller{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

    /**
    * 店铺信息
    * @params
    * @return array
    */ 
    function detail_get(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }
        
        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $Data = $sto->storeinfo;

        if(!$params['size']){
            $params['size'] = "CL";
        }

        $aData['store_id'] = $Data['store_id'];
        $aData['shop_name'] = $Data['shop_name'];
        $aData['account_id'] = $Data['account_id'];
        $aData['store_name'] = $Data['store_name'];
        $aData['image_id'] = $Data['image'];

        //获取图片
        $aData['image'] = $this->get_img_url($Data['image'],$params['size']);

        //评分情况
        $objComment =app::get('business')->model('comment_stores_point');
        $store_info = $objComment->getStoreInfo($Data['store_id']);
        $aData['store_point']=$store_info['store_point'];

        if($aData){
            $this->send(true,$aData,'查询成功');
        }else{
            $this->send(false,null,'查询失败');
        }

    }


    /**
    * 发货地址列表获取
    * @params
    * @return array
    */ 
    function address_list_get(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }
        
        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        $filter = array();
        $filter['store_id'] = $store_id;
        $filter['disabled'] = 'false';

        $oObj = app::get('business')->model('dlyaddress');
        $da_info = $oObj->getList('*', $filter, 0, -1, 1);
        
        if($da_info){
            $this->send(true,$da_info,'查询成功');
        }else{
            $this->send(false,null,'查询失败');
        }
    }



    /**
    * 发货地址信息增、改
    * @params
    * @return array
    */ 
    function address_detail_update(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'uname'=>'联系人',
            'address'=>'街道地址',
            'zip'=>'邮编',
            'region_id'=>'所在地'
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $data = $params;

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        $third_region_id = intval($data['region_id']);
        $mobj_regions = app::get('ectools')->model('regions');
        $arr = $mobj_regions->getList('region_path',array('region_id'=>$third_region_id));
		$region_path = $arr[0]['region_path'];
        $region_ids = explode(',',$region_path);
		$local_name = array();
        foreach($region_ids as $val){
            if(!empty($val)){
                $name = $mobj_regions->getList('local_name',array('region_id'=>intval($val)));
                $local_name[] = $name[0]['local_name'];
            }
        }
		
		$area['area_type'] = 'mainland';
        $area['sar'] = $local_name;
        $area['id'] = $third_region_id;
        $data['area'] = $area;
        $data['region'] = $data['area']['area_type'].':'.implode('/',$data['area']['sar']).':'.$data['area']['id'];

        $oObj = app::get('business')->model('dlyaddress');
        if(empty($data['da_id'])){
            unset($data['da_id']);
        }
        $count = $oObj->count(array('store_id'=>$store_id,'consign'=>'true'));
        if(intval($count) > 0){
            if(!isset($data['da_id']))$data['consign'] = 'false';
        }else{
            $data['consign'] = 'true';
        }
        $count = $oObj->count(array('store_id'=>$store_id,'refund'=>'true'));
        if(intval($count) > 0){
            if(!isset($data['da_id']))$data['refund'] = 'false';
        }else{
            $data['refund'] = 'true';
        }
        if($data['mobile']){
            if(!preg_match('/^(1[3458])-?\d{9}$/', $data['mobile'])){
                $this->send(false,null,'请填写正确的手机号！'); 
            }
        }
        if($data['phone']){
            if(!preg_match('/^(0\d{2,3}-?)?[23456789]\d{5,7}(-\d{1,5})?$/',$data['phone'])){
                $this->send(false,null,'请填写正确的电话号码！');
            }
        }
        if($data['zip']){
            if (!preg_match("/^[1-9][0-9]{5}$/",$data['zip'])){
                $this->send(false,null,'请填写正确的邮编！');
            }
        }

        if(empty($data['phone']) && empty($data['mobile'])){
            $this->send(false,null,'联系电话和联系手机必须填写一项！');
        }

        $data['store_id'] = $store_id;

        if(!$oObj->save($data)){
            $this->send(false,null,'地址信息保存失败！');
        }else{
            $this->send(true,1,'地址信息保存成功！');
        }

        

    }


    /**
    * 发货地址信息删除
    * @params
    * @return array
    */ 
    function address_delete(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'da_id'=>'地址ID'
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $da_id = $params['da_id'];

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        if(empty($da_id)){
            $this->send(false,null,'没有可以删除的数据');
        }

        $mobj_address = app::get('business')->model('dlyaddress');

        $filter = array('store_id'=>$store_id,'da_id'=>$da_id);
        $result = $mobj_address->delete($filter);
        if($result){
            $this->send(true,null,app::get('b2c')->_('删除成功'));
        }else{
            $this->send(false,null,app::get('b2c')->_('删除失败'));
        }

    }


    /**
    * 物流公司列表
    * @params
    * @return array
    */ 
    function dlycorp_list_get(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        $objCorp = app::get('business')->model('dlycorp');
        $self = array();
        foreach($objCorp->getList('*', array('store_id'=>$store_id)) as $items){
            $self[] = $items['corp_id'];
        }
        $objCorp = app::get('b2c')->model('dlycorp');
        $all_corp = $objCorp->getList('*', array('disabled'=>'false'));
        if($params['type'] == 'default'){
            foreach($all_corp as $key=>&$item){
                if(count($self)>0 && in_array($item['corp_id'], $self)){
                    $item['default'] = true;
                }else{
                    unset($all_corp[$key]);
                }
            }
            $all_corp = array_values($all_corp);
        }else{
            foreach($all_corp as &$item){
                if(count($self)>0 && in_array($item['corp_id'], $self)){
                    $item['default'] = true;
                }else{
                    $item['default'] = false;
                }
            }
        }
        $aData['corp'] = $all_corp;

        $this->send(true,$aData,'查询成功');

    }

    /**
    * 物流公司设置
    * @params
    * @return array
    */ 
    function dlycorp_default_set(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];
        $corp = json_decode($params['corp'],true);

        if(count($corp['corp'])>5){
            $this->send(false,null,'最多可设置5个默认物流公司');
        }

        $data = array();
        if(isset($corp['corp']) && count($corp['corp'])>0){
            foreach($corp['corp'] as $items){
                $data[] = array('corp_id'=>intval($items),'store_id'=>$store_id);
            }
        }else{
            $this->send(false,null,'没有可以保存的值');
        }

        $objCorp = app::get('business')->model('dlycorp');
        $objCorp->delete(array('store_id' => $store_id));
        
        if ( !$objCorp->save($data) ){
            $this->send(false,null,'设置失败');
        }else{
            $this->send(true,1,'设置成功');
        }

    }

}
