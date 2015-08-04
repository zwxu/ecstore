<?php

/**
 * 获取购物车信息 处理活动商品
 * $ 2010-04-28 20:29 $
 */
class scorebuy_cart_process_goods implements b2c_interface_cart_process {

     private $app;

    public function __construct(&$app){
        $this->arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
        $this->app = $app;
    }

    
    public function get_order() {
        return 95;
    }

    public function process($aData,&$aResult=array(),$aConfig=array()){
        $this->filter($aData,$aResult,$aConfig);
        app::get('b2c')->model('cart')->count_objects($aResult);

    }

    public function filter($aData,&$aResult=array(),$aConfig=array()){
        $oGoods = app::get('b2c')->model('goods');
        $memLvScoreObj = $this->app->model('memberlvscore');
        foreach($aResult['object']['goods'] as $k=>$v){
            $gid = $v['params']['goods_id'];
            $act_type = $oGoods->getList('act_type',array('goods_id'=>$gid));
            if($act_type[0]['act_type']=='score'){
                $re = $this->checkgoods($gid);
                if($re['isMemLv'] == '1'){
                    $memLvs = $memLvScoreObj->getMemLvScoreByIds($re['id'],$re['gid']);
                }
                if($re){
                    if(!$re['nums']||$re['remainnums']!=0){
                        
                        foreach($v['obj_items']['products'] as $key=>$value){
                            $aResult['object']['goods'][$k]['obj_items']['products'][$key]['price']['price'] = empty($re['last_price'])?0:$re['last_price'];
                            $aResult['object']['goods'][$k]['obj_items']['products'][$key]['price']['buy_price'] = empty($re['last_price'])?0:$re['last_price'];
                            $aResult['object']['goods'][$k]['obj_items']['products'][$key]['price']['buy_score'] = $re['score'];
                            if($re['isMemLv'] == '1'){
                                $aResult['object']['goods'][$k]['obj_items']['products'][$key]['price']['price'] = empty($memLvs[$this->arr_member_info['member_lv']]['last_price'])?0:$memLvs[$this->arr_member_info['member_lv']]['last_price'];
                                $aResult['object']['goods'][$k]['obj_items']['products'][$key]['price']['buy_price'] = empty($memLvs[$this->arr_member_info['member_lv']]['last_price'])?0:$memLvs[$this->arr_member_info['member_lv']]['last_price'];
                                $aResult['object']['goods'][$k]['obj_items']['products'][$key]['price']['buy_score'] = $memLvs[$this->arr_member_info['member_lv']]['score'];
                            }
                        }
                    }
                }
            }
        }
    }

    public function checkgoods($gid){
        $businessactivity = app::get('scorebuy')->model('scoreapply');
        $activity  = app::get('scorebuy')->model('activity');
        $businessInfo = $businessactivity->getList('*',array('gid'=>$gid));
        if(count($businessInfo) > 2){
            $flag = true;
            $result = array();
            foreach($businessInfo as $k=>$v){
                if($v&&$v['status']==2){
                    $activityInfo = $activity->getList('*',array('act_id'=>$v['aid']));
                    $activityInfo = $activityInfo[0];
                    if($activityInfo['act_open']=='true'){
                        $now = time();
                        if($activityInfo['start_time']<$now&&$now<$activityInfo['end_time']){
                            $result = $v;
                        }else{
                            $flag = false;
                        }
                    }else{
                        $flag = false;
                    }
                }else{
                    $flag = false;
                }
            }
            if($flag){
                return $result;
            }else{
                return false;
            }
        }else{
            $businessInfo = $businessInfo[0];
            if($businessInfo&&$businessInfo['status']==2){
                $activityInfo = $activity->getList('*',array('act_id'=>$businessInfo['aid']));
                $activityInfo = $activityInfo[0];
                if($activityInfo['act_open']=='true'){
                    $now = time();
                    if($activityInfo['start_time']<$now&&$now<$activityInfo['end_time']){
                        return $businessInfo;
                    }else{
                        return false;
                    }
                }else{
                    return  false;
                }
            }else{
                return false;
            }
        }

    }
}