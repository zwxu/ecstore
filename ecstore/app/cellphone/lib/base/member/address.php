<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_member_address extends cellphone_cellphone{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }
//  获得收货地址列表
   function getlist(){
        $params = $this->params;

       //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
       );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
		//$member_id = $params['member_id'] ;
      
        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }

        $mobj_address = app :: get('b2c') -> model('member_addrs');

        $filter = array('member_id'=>$member_id);
      
        if($params['pagelimit']){
            $pagelimit=$params['pagelimit'];
        }else{
            $pagelimit=10;
        }

        if($params['nPage']){
            $nPage=$params['nPage'];
        }else{
            $nPage=1;
        }
		if($params['addr_id']){
		 $filter['addr_id'] = intval($params['addr_id']);
		
		}

        $aData=$mobj_address->getList('addr_id,name,area,addr,zip,mobile,def_addr',$filter,$pagelimit*($nPage-1),$pagelimit);
		$data = array();
        foreach($aData as $key=>$val){
		$pos = strripos(trim($val['area']),':');
		$region_id = intval(substr(trim($val['area']),$pos+1));
		$data[$key]['region_id'] =$region_id;
		$data[$key]['addr_id'] = $val['addr_id'];
		$data[$key]['name'] = $val['name'];
		$data[$key]['addr'] = $val['addr'];
		$data[$key]['mobile'] = $val['mobile'];
		$data[$key]['zip'] = $val['zip'];
		$data[$key]['def_addr'] = $val['def_addr'];
		}

        if($data){
		
		$this->send(true,$data,app::get('b2c')->_('收货地址'));

		}

        else{
		$this->send(true,null,app::get('b2c')->_('没有添加地址'));
		}
        
      

        

       
   
   }
//  保存一条收货地址信息
  function saveaddr(){
  
  //保存地区 格式 mainland:江苏/无锡市/北塘区:1720
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
			 'name'=>'收货人',
			 'mobile'=>'手机号码',
			 'zipcode'=>'邮政编码',
			 'region_id'=>'所在区域',
			 'addr'=>'详细地址',
        );
        $this->check_params($must_params);
        $member = $this->get_current_member();
        $member_id = $member['member_id'];
        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }
		//来接受传过来的数据 
		if(!empty($params['addr_id'])){
        $data['addr_id'] = $params['addr_id'];
		}
		$data['name'] = $params['name'];
        $data['phone']['mobile'] = $params['mobile'];
        $data['zipcode'] = $params['zipcode'];
        $data['addr'] = $params['addr'];
		$data['member_id'] = $member_id;
        $third_region_id = intval($params['region_id']);
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
        
        foreach ($data as $key=>$val){
            if(is_string($val))
            $data[$key] = trim($val);
            if(empty($data[$key])){
                switch ($key){
                case 'name':
                   $this->send(false,null,app::get('b2c')->_('收货人不能为空'));
                
                    break;
                case 'zipcode':
                   $this->send(false,null,app::get('b2c')->_('邮编不能为空'));
                    break;
                case 'area':
                    $this->send(false,null,app::get('b2c')->_('地区不能为空'));
                    break;
                default:
                    break;
                }
            }
        }
 

       if(!preg_match("/^[1-9]\d{5}$/",$data['zipcode'])){
	     $this->send(false,null,app::get('b2c')->_("邮编格式不正确"));
	   }

       if($data['phone']['mobile']){
            if(!preg_match('/^(1[3458])-?\d{9}$/', $data['phone']['mobile'])){
				$this->send(false,null,app::get('b2c')->_("手机格式不正确"));
            }
        }

		$mobj_address = app :: get('b2c')->model('member_addrs');
		if($mobj_address->save($data)){
		$this->send(true,null,app::get('b2c')->_('操作成功'));
		}
		$this->send(false,null,app::get('b2c')->_('操作失败'));
		

  }
// 删除一条收货地址信息
  function deladdr(){
  
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
			'addr_id'=>'地址ID',
        );
        $this->check_params($must_params);

        //$member_id=$params['member_id'];
        //$obj_members = app :: get('b2c') -> model('members');
        //$member=$obj_members->get_member_info($member_id);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        $addr_id = $params['addr_id'];
        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }
		if(empty($addr_id)){
		 $this->send(false,null,app::get('b2c')->_('没有参数'));
		}
        $mobj_address = app :: get('b2c') -> model('member_addrs');

        $filter = array('member_id'=>$member_id,'addr_id'=>$addr_id);
		$result = $mobj_address->delete($filter);
		if($result==true){
		
		$this->send(true,null,app::get('b2c')->_('删除成功'));
		}
        else{
		$this->send(false,null,app::get('b2c')->_('删除失败'));
		}
  
  }
 //将该条地址信息设为用户的默认收货地址
  function setdefault(){
  
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
			'addr_id'=>'地址ID',
        );
        $this->check_params($must_params);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        $addr_id = $params['addr_id'];
        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }
        if(empty($addr_id)){
		 $this->send(false,null,app::get('b2c')->_('没有参数'));
		}
	
        $db = kernel :: database();
        $db->beginTransaction();//事务开始
        $mobj_address = app :: get('b2c') -> model('member_addrs');
		$filter = array('member_id'=>$member_id,'addr_id'=>$addr_id);
        $result1 = $mobj_address->update(array('def_addr'=>0),array('member_id'=>$member_id,'def_addr'=>1));
		$result2= $mobj_address->update(array('def_addr'=>1),$filter);
        
		if($result1 && $result2){
		
	    $db->commit();
		$this->send(true,null,app::get('b2c')->_('设置成功'));
		}
        else{
		$db->rollback();
		$this->send(false,null,app::get('b2c')->_('设置失败'));
		}
      
  }


}