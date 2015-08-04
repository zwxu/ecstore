<?php

 

class b2c_mdl_goods_view_history extends dbeav_model{

#####添加商品

    function add_history($member_id=null,$goods_id=null){
        if(!$member_id || !$goods_id) 
            return false;
        $sdf = array(
           'goods_id' =>$goods_id,
           'member_id' =>$member_id,
           'last_modify'=>time()
          );
          if($this->save($sdf)){
              return true;
          }
          else{
              return false;
          }
	}
	
	function get_history($member_id=null){
		if(!$member_id) return null;
		$oGood = &$this->app->model('goods');
		$fav = $this->db->select("SELECT member_goods.`goods_id`, goods.fav_count 
									FROM ".$this->table_name(1)." AS member_goods
									INNER JOIN ".$oGood->table_name(1)." AS goods ON member_goods.`goods_id`=goods.`goods_id` 
									WHERE member_goods.`member_id`=".intval($member_id)." AND member_goods.`type`='fav' AND goods.`marketable`='true'");
        $result = implode(',',(array)array_map('current',$fav));
        if($result) $result = ','.$result;
        return $result;
	}

###删除浏览历史商品

     function del_history($member_id,$gid){
        $is_delete = false;
		$is_delete = $this->delete(array('goods_id' => $gid,'member_id' => $member_id));
		return $is_delete;
     }
	 
	 function count($filter=null,$type=null){
		if (!$filter || !$filter['member_id']) return 0;
		
		$oGood = &$this->app->model('goods');
		$count = $this->db->selectrow("SELECT COUNT(member_goods.`goods_id`) AS num 
									FROM ".$this->table_name(1)." AS member_goods
									INNER JOIN ".$oGood->table_name(1)." AS goods ON member_goods.`goods_id`=goods.`goods_id` 
									WHERE member_goods.`member_id`=".intval($filter['member_id'])." AND goods.`marketable`='true'");
		
		return $count['num'];
	 }
     
     function delAllHistory($member_id){
        return $this->delete(array('member_id' => $member_id));
     }

####根据会员ID获得该会员浏览历史的商品

    function get_view_history($member_id,$member_lv_id,$page=1,$num=10){
        if (!$member_id || !$member_lv_id) return array();
        $count = $this->count(array('member_id'=>$member_id));
        if( !$num ) $num = 10;
        $maxPage = ceil($count / $num);
        if($page > $maxPage)
        $page=$maxPage;
        $start = ($page-1) * $num;
        $start = $start<0 ? 0 : $start;
        $aGid = $this->select()->columns(array('goods_id'))
            ->where('member_id=?',$member_id)->order(array('last_modify DESC'))->limit($start,$num)->instance()->fetch_all();
        
        $atgid = array();
        foreach($aGid as $val){
            $atgid[]= $val['goods_id'];
            $params['data'][$val['goods_id']] = array();
        }

        $oGood = &$this->app->model('goods');
        $aTmp = $oGood->getList('udfimg,thumbnail_pic,image_default_id,goods_id,price,name,type_id,fav_count',array('goods_id' => $atgid));

        if(is_array($atgid)&&$atgid){
                $oMlv = &$this->app->model('member_lv');
                $mlv = $oMlv->select()->columns(array('dis_count'))->where('member_lv_id=?',$member_lv_id)->instance()->fetch_row();
                $oImage = app::get('image')->model('image');
                $objProduct = $this->app->model('products');
                $oGoodsLv = &$this->app->model('goods_lv_price');
                $aProduct = $aTmp;
                if($aProduct){
                    foreach ($aProduct as &$val) {
                        // 判断图片是否存在
                        $image_default_id = $oImage->select()->columns(array('image_id'))
                                           ->where('image_id=?',$val['image_default_id'])->instance()->fetch_one();
                        if (empty($image_default_id)) {
                            $val['image_default_id'] = '';
                        }
                        $thumbnail_pic = $oImage->select()->columns(array('image_id'))
                                                  ->where('image_id=?',$val['thumbnail_pic'])->instance()->fetch_one();
                        if (empty($thumbnail_pic)) {
                            $val['thumbnail_pic'] = '';
                        }

                        $temp = $objProduct->getList('product_id, spec_info, price, freez, store, goods_id',array('goods_id'=>$val['goods_id'],'marketable'=>'true'),$offset=0, $limit=-1,$orderType='price DESC');
                        if( $member_lv_id ){
                            //货品会员价
                            $tmpGoods = array();
                            $goodsLvPrice = $oGoodsLv->select()->columns('product_id,price')
                                                    ->where('goods_id=?',$val['goods_id'])
                                                    ->where('level_id=?',$member_lv_id)->instance()->fetch_all();
                            foreach($goodsLvPrice as $k => $v ){
                                $tmpGoods[$v['product_id']] = $v['price'];
                            }

                            foreach( $temp as &$tv ){
                                $tv['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$tv['price'] ));
                            }

                            $val['price'] = $tv['price'];
                            $promotion_price = kernel::single('b2c_goods_promotion_price')->process($val);
                            if(!empty($promotion_price['price'])){
                                $val['price'] = $promotion_price['price'];
                                $val['show_button'] = $promotion_price['show_button'];
                                $val['timebuy_over'] = $promotion_price['timebuy_over'];
                            }
                        }
                        $val['spec_desc_info'] = $temp;

                        $params['data'][$val['goods_id']] = $val;
                    }
                }
                //$params['data'] = $aProduct;
                $params['data'] = array_filter($params['data']);
                $params['totalpage'] = $maxPage;
                $params['curentPage'] = $page;
                return $params;
        }else{
            return false;
        }
    }
    
    /**
     * get_goods
     * 获取到货记录
     * 
     * @access public
     * @return int
     */
    public function get_goods($member_id=null){
        if(!$member_id) return 0;
        $obj_product = $this->app->model('products');
        $aProduct = $this->getList('product_id',array('member_id' => $member_id, 'type' => 'sto'));
        $i = 0;
        foreach((array)$aProduct as $key => $v){
            if(!$v['product_id']) continue;
            $row = $obj_product->getList('store',array('product_id' => $v['product_id']));
            if($row[0]['store']>0) $i++;
        }
        return $i;
    }

    //定时获取浏览历史商品价格变化 
    function changePrice(){

        $Mgoods = $this->app->model('member_goods');
        $goods = $this->app->model('goods');
        $goods_price = $Mgoods->getList('gnotify_id,member_id,goods_id,money',array('type'=>'fav'));

        foreach($goods_price as $key=>&$v){
          $price = $goods->getlist('price',array('goods_id'=>$v['goods_id']));
          if($v['money'] == '0'){
             $v['money'] = $price[0]['price'];
          }else{
              if($v['money']<$price[0]['price']){
                 $v['is_change'] = 'up';
                 $v['change_money'] = $price[0]['price']-$v['money'];
              }elseif($v['money']>$price[0]['price']){
                 $v['is_change'] = 'down';
                 $v['change_money'] = $v['money']-$price[0]['price'];
              }else{
                 $v['change_money'] = $price[0]['price']-$v['money'];
              }
                 $v['money'] = $price[0]['price'];
          }
          
          $Mgoods->save($v);
        }
    }//end

}
