<?php
class b2c_goods_detail_promotion{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show( $gid,$siteMember=null,$custom_view){
        $render = $this->app->render();
        $member_data = $this->member_lv_data();

        $blocks = array('promotion'=>array('goods_id'=>$gid));
        foreach(kernel::servicelist('b2c_site_goods_detail_block') as $object){
            $promotionMsg = $object->get_blocks($blocks,$siteMember);   //todo check is right?
        }

        foreach($promotionMsg as $promotionMsgi_key=>$promotionMsg_val){
            $member_lv_ids = explode(',',$promotionMsg_val['member_lv_ids']);
            if(count($member_data) < count($member_lv_ids)){
                //所有会员可以获得优惠
                $promotionMsgArr[$promotionMsgi_key]['name'] = $promotionMsg_val['name'];
            }else{
                $member_name = array();
                foreach($member_lv_ids as $member_id){
                    $member_id == -1?$member_name[] = '非会员':$member_name[] = $member_data[$member_id];
                    $promotionMember = implode(',',$member_name);
                }
                $promotionMsgArr[$promotionMsgi_key]['name'] = $promotionMsg_val['name'];
                $promotionMsgArr[$promotionMsgi_key]['member'] = $promotionMember;
            }
        }
        //统计商品促销数量
        $promotionMsgNum = 0;
        if($promotionMsg){
            $promotionMsgNum = substr_count($promotionMsg,'<br>')+1;
        }
        $render->pagedata['promotionMsg'] = $promotionMsgArr;

        $objSales = $this->app->model('sales_rule_order');
        $pOrderList = $objSales->getList('name,member_lv_ids',array('status'=>'true','from_time|lthan'=>time(),'to_time|than'=>time(),'rule_type'=>'N'));

        foreach($pOrderList as $pk=>$pv){
            $member_lv_ids = explode(',',$pv['member_lv_ids']);
            if(count($member_data) < count($member_lv_ids)){
                $promotionOrder[$pk]['name'] = $pv['name'];
            }else{
                $member_name = array();
                foreach($member_lv_ids as $member_id){
                    $member_id == -1?$member_name[] = '非会员':$member_name[] = $member_data[$member_id];
                    $promotionMember = implode(',',$member_name);
                }
                $promotionOrder[$pk]['name'] = $pv['name'];
                $promotionOrder[$pk]['member'] = $promotionMember;
            }
        }
        //统计订单促销数量
        $promotionOrderNum = count($pOrderList);

        //if($this->app->getConf('site.promotion.display') == 'true')
        $render->pagedata['promotionOrder'] = $promotionOrder;

        $render->pagedata['promotionNum'] = $promotionMsgNum+$promotionOrderNum;

		$file = $custom_view?$custom_view:"site/product/promotion_info.html";
        return $render->fetch($file);
    }


    /**
     * 返回所有的会员等级信息
     */
    function member_lv_data(){
        $objMemberLv = app::get('b2c')->model('member_lv');
        $memberList = $objMemberLv->getList('name,member_lv_id');
        $member_lv = array();
        foreach($memberList as $key => $value){
            $member_lv[$value['member_lv_id']] = $value['name'];
        }
        return $member_lv;
    }


    /**
    *获取订单促销 从KV获取，不存在再读数据库
    */
    /*
    function get_sale_order()
    {
        $time = time();
        if(base_kvstore::instance('b2c_sale_order_info')->fetch('b2c_sale_order_info', $aResult)!== false){
            foreach($aResult as $k=>$v)
            {
                if($v['status'] !='true') continue;
                if($v['rule_type'] !='N') continue;
                if( $v['from_time'] > $time || $time > $row['to_time']) continue; //过滤时间
                 $arr[] = $v;
            }
            return $arr;
        }
        else
        {
            $objSales = $this->app->model('sales_rule_order');
            $pOrderList = $objSales->getList('name',array('status'=>'true','from_time|lthan'=>time(),'to_time|than'=>time(),'rule_type'=>'N'));
            return $pOrderList;
        }
    }
    */


}

