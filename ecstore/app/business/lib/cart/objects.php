<?php

 
/**
 * 获取购物车信息(没有优惠处理的) first
 * $ 2010-04-28 20:29 $
 */
class business_cart_objects {
    private $app;

    public function __construct(&$app){
        $this->app = $app;
    }

    
   public function check_goods_isMy($good_id,&$msg,&$sign){
		$goods_id = $good_id;
		$obj_members = app::get('b2c')->model('members');
		$obj_goods = app::get('b2c')->model('goods');
		$member = $obj_members->get_current_member();
		$store_id = $obj_goods->getList('store_id',array('goods_id'=>$goods_id));
		if($store_id[0]['store_id']){
			if(!$member){
				$sign = true;
			}else{
				$store_info = app::get('business')->model('storemanger')->getList('store_id,account_id',array('store_id'=>$store_id[0]['store_id']));
				$member_id = $member['member_id'];
				if($store_info[0]['account_id']==$member_id){
					$msg = '不能购买自己的商品';
					$sign  = false;
				}else{
					$sign = true;
				}
			}
		}else{
			$msg = '商品数据异常，购买失败';
			$sign  = false;
		}
	}

	public function check_isSeller(&$msg=''){
		$obj_members = app::get('b2c')->model('members');
		$member = $obj_members->get_current_member();
		if($member){
			$member_id = $member['member_id'];
			$seller = $obj_members->getList('member_id,seller',array('member_id'=>$member_id));
			//echo '<pre>';print_r($seller);exit;
			if($seller[0]['seller']&&$seller[0]['seller']=='seller'){
				$msg = '店家不能购买商品，请更换账号！';
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}

    public function check_goods_entity($good_id,$now_goods_id,&$msg,&$sign){
		$goods_id = $good_id;
		$obj_goods = app::get('b2c')->model('goods');
		$goods_kind = $obj_goods->getList('goods_kind,goods_kind_detail',array('goods_id'=>$goods_id));
        $goods_kind_now = $obj_goods->getList('goods_kind,goods_kind_detail',array('goods_id'=>$now_goods_id));
		if(($goods_kind[0]['goods_kind'] == $goods_kind_now[0]['goods_kind']) && ($goods_kind[0]['goods_kind_detail'] == $goods_kind_now[0]['goods_kind_detail'])){
			$sign = true;
        }else{
            $msg = '购物车中包含与当前商品交易流程不同的商品，不能添加当前商品进入购物车，请先结算！';
            $sign  = false;
        }

	}

}

