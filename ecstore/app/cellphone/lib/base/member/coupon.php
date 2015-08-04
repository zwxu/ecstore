<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_member_coupon extends cellphone_cellphone{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;

    }

    //查找我的优惠券列表接口
    public  function getlist(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);

        //$member_id=$params['member_id'];
        //$obj_members = app :: get('b2c') -> model('members');
        //$member=$obj_members->get_member_info($member_id);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        
        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }

        $mobj_coupon = app :: get('b2c') -> model('member_coupon');

        $filter = array('member_id'=>$member_id);
        $filter['disabled'] = 'false';
        $filter['memc_isvalid'] = 'true';

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

        $aData=$mobj_coupon->_get_list('*',$filter);
        $count = $mobj_coupon->count($filter);

        if ($aData) {
            $valid=array();
            $novalid=array();
            $obj_rule_order=&app :: get('b2c')->model('sales_rule_order');
            $obj_coupons=&app :: get('b2c')->model('coupons');
            $obj_store=&app :: get('business')->model('storemanger');
                    
            foreach ($aData as $k => $item) {
            
            $ocoupons=$obj_coupons->getList('store_id',array('cpns_id'=> $item['cpns_id']));
           
            $aData[$k]['storename']='';
            if($ocoupons[0]){
               $storeid=explode(',', $ocoupons[0]['store_id']);
               
               foreach($storeid as $key => $val) {
                    if ($val == '') unset($storeid[$key]);
                }
        
               $storeid=array_values($storeid);
                
               $oStore= $obj_store->getList('store_name',array('store_id'=> $storeid));
               if($oStore[0]){
                    $aData[$k]['storename']=$oStore[0]['store_name'];
               }
            
            }
          
            //查找优惠内容描述
            $aData[$k]['description']=$this->getdescbyid($obj_rule_order,$item['coupons_info']['rule']['rule_id']);

                if ($item['coupons_info']['cpns_status']==1) {
                    $member_lvs = explode(',',$item['time']['member_lv_ids']);
                    if (in_array($member['member_lv'],(array)$member_lvs)) {
                        $curTime = time();
                        if ($curTime>=$item['time']['from_time'] && $curTime<$item['time']['to_time']) {
                            if ($item['memc_used_times']< app :: get('b2c')->getConf('coupon.mc.use_times')){
                                if ($item['coupons_info']['cpns_status']){
                                     if($item['memc_isactive']=='false'){
                                       $aData[$k]['memc_status'] = app::get('b2c')->_('未激活');
                                       array_push($novalid,$aData[$k]);

                                    }else{
                                       $aData[$k]['memc_status'] = app::get('b2c')->_('可使用');
                                       array_push($valid,$aData[$k]);
                                    }
                                }else{
                                    $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券已作废');
                                    array_push($novalid,$aData[$k]);
                                }
                            }else{
                                $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券次数已用完');
                                array_push($novalid,$aData[$k]);
                            }
                        }else{
                            $aData[$k]['memc_status'] = app::get('b2c')->_('还未开始或已过期');
                            array_push($novalid,$aData[$k]);
                        }
                    }else{
                        $aData[$k]['memc_status'] = app::get('b2c')->_('本级别不准使用');
                        array_push($novalid,$aData[$k]);
                    }
                }else{
                    $aData[$k]['memc_status'] = app::get('b2c')->_('此种优惠券已取消');
                    array_push($novalid,$aData[$k]);
                }
            }
        }else{
            $errmsg=app::get('b2c')->_('没有可用的优惠券');
        }
        
        
        //$pagelimit*($nPage-1),$pagelimit
        
        

        if($params['isinvalid']=='true'){
            $result= array_slice($novalid, $pagelimit*($nPage-1), $pagelimit);
        }else{
            $result= array_slice($valid, $pagelimit*($nPage-1), $pagelimit);
        }
        
        //删除多余的返回数据
        foreach($result  as &$item){
            unset($item['cpns_id'],
                  $item['member_id'],
                  $item['memc_gen_orderid'],
                  $item['memc_source'],
                  $item['memc_enabled'],
                  $item['memc_used_times'],
                  $item['memc_gen_time'],
                  $item['disabled'],
                  $item['memc_isvalid'],
                  $item['memc_isactive'],
                  $item['coupons_info'],
                  $item['time']['member_lv_ids']                  
            );
        }

        
       
       

        $this->send(true,$result,'');
    }

    //根据规则获取优惠券内容描述
    private function  getdescbyid($obj_rule_order,$rule_id){
        $arr = $obj_rule_order->getList( '*',array('rule_id'=>$rule_id));
        $aData['description'] =$arr[0]['description'];
        if($arr[0]['conditions']['conditions'][0]['attribute'] !='coupon'){
            return  $aData;
        }

        $oSOP = kernel::single('b2c_sales_order_process');
        $condi=  $oSOP->getTemplateList();

        //优惠条件
        $strname=$condi[$arr[0]['c_template']]['name'];

        /*
        //>=
        $aryoperator=array( '<'   =>app::get('b2c')->_('小于'),
               '<='  => app::get('b2c')->_('小于等于'),
               '>'   => app::get('b2c')->_('大于'),
               '>='  => app::get('b2c')->_('大于等于')
        );

        $stroperator=$aryoperator[$arr[0]['conditions']['conditions'][1]['conditions'][0]['operator']];
        */

        //100
        $strvalue=$arr[0]['conditions']['conditions'][1]['conditions'][0]['value'];
        
        //优惠方案
        $oSSP = kernel::single('b2c_sales_solution_process');
        $arry=$oSSP->getTemplateList();
        $strtype=$arr[0]['action_solution'][$arr[0]['s_template']]['type'];
        $strpercent=$arr[0]['action_solution'][$arr[0]['s_template']]['percent'];
        if($strpercent){
            $strpercent .='%';
        }
        $stramount=$arr[0]['action_solution'][$arr[0]['s_template']]['total_amount'];
        //$strcpns_id=$arr[0]['action_solution'][$arr[0]['s_template']]['cpns_id'];
        $strsolution=$arry[$strtype][$arr[0]['s_template']];

        $aData['conditions']=str_replace('X',$strvalue,$strname);
        $aData['solutions']=$strsolution;
        $aData['solutions_value']=$strpercent.$stramount.$strcpns_id;

        return $aData;

    }

}
