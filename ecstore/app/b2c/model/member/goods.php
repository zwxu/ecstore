<?php

 

class b2c_mdl_member_goods extends dbeav_model{
    
    //var $defaultOrder = array('goods_id', ' ASC');
    
    ###添加缺货登记
    function add_gnotify($member_id=null,$good_id,$product_id,$email,$cellphone){
       $sdf = array(
       'goods_id' =>$good_id,
       'member_id' =>$member_id,
       'product_id'=>$product_id,
       'email' => $email,
       'cellphone' => $cellphone,
       'status' =>'ready',
       'create_time' => time(),
       'type' =>'sto',
      );
      if($this->save($sdf)){
          return true;
      }
      else{
          return false;
      }
}

//检查邮箱重复登记货品

    function check_gnotify($aData){
        $goods_id = $aData['item'][0]['goods_id'];
        $product_id = $aData['item'][0]['product_id'];
        $email = $aData['email'];
        $cellphone = $aData['cellphone'];
        $filter['goods_id'] = $goods_id;
        $filter['product_id'] = $product_id;
        if($email){
          $filter['email'] = $email;
        }
        if($cellphone){
          $filter['cellphone'] = $cellphone;
        }
        $aData = $this->getList('gnotify_id', $filter);
        if(count($aData)>0){
            return true;
        }
        else{
            return false;
        }
    }


#####根据会员ID获得缺货登记
    function get_gnotify($member_id,$member_lv_id,$page=1){
        $obj_prod = &$this->app->model('products');
        $obj_good = $this->app->model('goods');
        $oGoodsLv = &$this->app->model('goods_lv_price');
        $oMlv = &$this->app->model('member_lv');
        $mlv = $oMlv->db_dump( $member_lv_id,'dis_count' );
        $count = $this->count(array('member_id' => $member_id,'type' =>'sto',object_type=>'goods'),'sto');
        $maxPage = ceil($count / 10);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * 10;
        $start = $start<0 ? 0 : $start;
        $aGid = $this->getList('*',array('member_id' => $member_id,'type' =>'sto','object_type' => 'goods'),$start,10);
        $params['page'] = $maxPage;
        
        foreach($aGid as $val){
            $image = $obj_good->dump($val['goods_id']);
            $aTmp = $obj_prod->dump($val['product_id']);
            if(!$aTmp) $aTmp['product_id'] = $val['product_id'];
            if($member_lv_id){
                $row = $oGoodsLv->getList( 'price',array('product_id'=>$val['product_id'],'level_id'=> $member_lv_id ));
                $aTmp['price']['price']['price'] = $row[0] ? $row[0]['price'] : $aTmp['price']['price']['price'] * $mlv['dis_count'];
                $promotion_price = kernel::single('b2c_goods_promotion_price')->process($val);
                if(!empty($promotion_price['price'])){
                    $aTmp['price']['price']['price'] = $promotion_price['price'];
                    $aTmp['price']['price']['show_button'] = $promotion_price['show_button'];
                    $aTmp['price']['price']['timebuy_over'] = $promotion_price['timebuy_over'];
                }
            }
            if( $aTmp['store'] != '' )
              $aTmp['store'] = $aTmp['store']- $aTmp['freez'];
            else
              $aTmp['store'] = 10000;
            $aTmp['image_default'] = $image['image_default'];
            $aTmp['image_default_id'] =$image['image_default_id'];
            $Pro[] = $aTmp;
        }
        $params['data'] = $Pro;
        return $params;
    }


#####添加商品收藏

    function add_fav($member_id=null,$object_type='goods',$goods_id=null){
        if(!$member_id || !$goods_id) return false;
        $filter['member_id'] = $member_id;
        $filter['goods_id'] = $goods_id;
        $filter['type'] = 'fav';
        if($row = $this->getList('gnotify_id',$filter))
            return true;
        $sdf = array(
           'goods_id' =>$goods_id,
           'member_id' =>$member_id,
           'status' =>'ready',
           'create_time' => time(),
           'type' =>'fav',
           'object_type'=> $object_type,
          );
          if($this->save($sdf)){
             
              $this->db->exec("UPDATE sdb_b2c_goods SET fav_count = fav_count+".intval(1)." WHERE goods_id =".intval($goods_id));
              $this->db->exec("update sdb_b2c_brand set fav_count = fav_count+".intval(1)." where brand_id in (select brand_id from sdb_b2c_goods where goods_id=".intval($goods_id).")");
             
              return true;
          }
          else{
              return false;
          }
	}
	
	function get_member_fav($member_id=null){
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

###删除收藏商品

     function delFav($member_id,$gid,&$page=null,$num=10){
        $is_delete = false;
		$is_delete = $this->delete(array('goods_id' => $gid,'member_id' => $member_id,'type' => 'fav'));
    
    if(!is_array($gid)) $gid = array(intval($gid));
    if(!empty($gid))
    $this->db->exec("UPDATE sdb_b2c_goods SET fav_count = fav_count-".intval(1)." WHERE goods_id in(".implode(',',$gid).") and fav_count>0");
   
		/** 得到当前会员分页数 **/
		$count = $this->count(array('member_id'=>$member_id));
		$page = ceil($count / $num);

		return $is_delete;
     }
	 
	 function count($filter=null,$type=null){
		if (!$filter || !$filter['member_id']) return 0;
		
		$oGood = &$this->app->model('goods');
		$count = $this->db->selectrow("SELECT COUNT(member_goods.`goods_id`) AS num 
									FROM ".$this->table_name(1)." AS member_goods
									INNER JOIN ".$oGood->table_name(1)." AS goods ON member_goods.`goods_id`=goods.`goods_id` 
									WHERE member_goods.`member_id`=".intval($filter['member_id'])." AND member_goods.`type`='". $type ."' AND goods.`marketable`='true'");
		
		return $count['num'];
	 }
     
     function delAllFav($member_id){
       
        $oGood = &$this->app->model('goods');
        $sql = "update ".$oGood->table_name(1)." set fav_count = fav_count-".intval(1)." where goods_id in (select goods_id from ".$this->table_name(1)." where member_id=".intval($member_id)." and type='fav') and fav_count>0";
        $this->db->exec($sql);
        
        return $this->delete(array('member_id' => $member_id,'type' => 'fav'));
     }

####根据会员ID获得该会员收藏的商品

    function get_favorite($member_id,$member_lv_id,$page=1,$num=10,$type){
        $count = $this->count(array('member_id'=>$member_id),'fav');
        if( !$num ) $num = 10;
        $maxPage = ceil($count / $num);
        if($page > $maxPage) return array();
        $start = ($page-1) * $num;
        $start = $start<0 ? 0 : $start;
        //$aGid = $this->getList('goods_id',array('member_id' => $member_id,'type' =>'fav'), $start, $num, $orderType='create_time DESC');
        if(!$type){
            $aGid = $this->select()->columns(array('goods_id'))
                    ->where('member_id=?',$member_id)
                    ->where('type=?','fav')->order(array('create_time DESC'))->limit($start,$num)->instance()->fetch_all();
        }else{
            if($type == 'pmt'){
                $Mgoods = $this->app->model('member_goods');
                $oGoods = $this->app->model('goods');
                $pmt_goods = $Mgoods->getList('goods_id',array('type'=>'fav','member_id'=>$member_id));

                foreach($pmt_goods as $v){
                    $p_goods[] = $v['goods_id'];
                }

                $aGid = $oGoods->getList('goods_id',array('act_type|noequal'=>'normal','goods_id|in'=>$p_goods));
            }else{
                $aGid = $this->select()->columns(array('goods_id'))
                    ->where('member_id=?',$member_id)
                    ->where('is_change=?',$type)
                    ->where('type=?','fav')->order(array('create_time DESC'))->limit($start,$num)->instance()->fetch_all();
            }
        }
        
        $agid = array();
        foreach($aGid as $val){
            $agid[]= $val['goods_id'];
            $params['data'][$val['goods_id']] = array();
        }

        $oGood = &$this->app->model('goods');
        $aTmp = $oGood->getList('udfimg,thumbnail_pic,image_default_id,goods_id,price,name,type_id,fav_count',array('goods_id' => $agid));

        if(is_array($agid)&&$agid){
                $oMlv = &$this->app->model('member_lv');
                $mlv = $oMlv->select()->columns(array('dis_count'))->where('member_lv_id=?',$member_lv_id)->instance()->fetch_row();
                //$mlv = $oMlv->db_dump( $member_lv_id,'dis_count' );
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
                $params['page'] = $maxPage;
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

    //定时获取收藏商品价格变化 
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
