<?php


#商品
class operatorlog_goods
{
    /**
     *
     * 删除前获取提交过来的商品编号信息
     * @param unknown_type $params
     */
    public function logDelInfoStart($params)
    {
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        $o = app::get('b2c')->model('goods');
        $rows = $o->getList('bn',$params);
        $this->info=$rows;
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
    }//End Function
    /**
     *
     * Enter description here ...
     * @param unknown_type $delflag 是否被删除标识
     */
    public function logDelInfoEnd($delflag=false)
    {
        if($delflag==true){
            $bntmp='';
            foreach($this->info as $value){
                $bntmp.= $value['bn'].',';
            }
            $bn=rtrim($bntmp,',');
            $memo='商品编号('.$bn.')';
            kernel::single('operatorlog_logs')->inlogs($memo, '删除商品', 'goods');
        }
    }//End Function

    function trimvalue(&$value,$key){
        $value = trim($value);
    }

    function logGoodsStart($goods_id){
        $objGoods = app::get('b2c')->model('goods');
        $original_info = $objGoods->dump($goods_id,'*','default');

        array_walk_recursive($original_info, array($this,'trimvalue'));
        $this->goodsOri = $original_info;
    }

    /**
     *
     * 获取添加编辑的商品信息
     * @param array $params 提交过来的商品信息
     */
    function logGoodsEnd($goodsNew){
        array_walk_recursive($goodsNew,array($this,'trimvalue'));
        if(!empty($goodsNew['goods_id'])){
            $objGoods = app::get('b2c')->model('goods');
            $goodsOri = $this->goodsOri;

            $memo_lv = '';//会员价信息记录
            $memo='将ID为('.$goodsNew['goods_id'].')的商品  ';

            if($goodsOri['category']['cat_id']!=$goodsNew['category']['cat_id']){
                $objCat = app::get('b2c')->model('goods_cat');
                $catNameOri = $objCat->getList('cat_name',array('cat_id'=>$goodsOri['category']['cat_id']));
                $catNameNew = $objCat->getList('cat_name',array('cat_id'=>$goodsNew['category']['cat_id']));
                $memo .= '商品分类由('.$catNameOri['0']['cat_name'].')改为('.$catNameNew['0']['cat_name'].'),';
                $modify_flag = true;
            }
            if($goodsOri['type']['type_id']!=$goodsNew['type']['type_id']){
                $objType = app::get('b2c')->model('goods_type');
                $typeNameOri = $objType->getList('name',array('type_id'=>$goodsOri['type']['type_id']));
                $typeNameNew = $objType->getList('name',array('type_id'=>$goodsNew['type']['type_id']));
                $memo .= '商品类型由('.$typeNameOri['0']['name'].')改为('.$typeNameNew['0']['name'].'),';
                $modify_flag = true;
            }
            if($goodsOri['name'] != $goodsNew['name']){
                $memo .= '商品名称由('.$goodsOri['name'].')改为('.$goodsNew['name'].'),';
                $modify_flag = true;
            }
            if($goodsOri['bn']!=$goodsNew['bn']){
                $memo .= '商品编码由('.$goodsOri['bn'].')改为('.$goodsNew['bn'].'),';
                $modify_flag = true;
            }
            if($goodsOri['brand']['brand_id']!=$goodsNew['brand']['brand_id']){
                $objBrand = app::get('b2c')->model('brand');
                $brandNameOri = $objBrand->getList('brand_name',array('brand_id'=>$goodsOri['brand']['brand_id']));
                $brandNameNew = $objBrand->getList('brand_name',array('brand_id'=>$goodsNew['brand']['brand_id']));
                $memo .= '商品品牌由('.$brandNameOri['0']['brand_name'].')改为('.$brandNameNew['0']['brand_name'].'),';
                $modify_flag = true;
            }
            if($goodsOri['brief']!=$goodsNew['brief']){
                $memo .= '修改了商品简介,';
                $modify_flag = true;
            }
            if($goodsOri['status']!=$goodsNew['status']){
                $arr=array('false'=>'否','true'=>'是');
                $memo .= '商品上下架状态由('.$arr[$goodsOri['status']].')改为('.$arr[$goodsNew['status']].'),';
                $modify_flag = true;
            }
            if($goodsOri['nostore_sell']!=$goodsNew['nostore_sell']){
                $arr=array('0'=>'否','1'=>'是');
                $memo .= '商品无库存销售由('.$arr[$goodsOri['nostore_sell']].')改为('.$arr[$goodsNew['nostore_sell']].')';
                $modify_flag = true;
            }
            if($goodsOri['description']!=$goodsNew['description']){
                $memo .= '修改了商品详情,';
                $modify_flag = true;
            }
            //多规格商品，基础页面下列信息不记录
            if($goodsNew['product']){
                if($goodsOri['price']!=$goodsNew['product']['0']['price']['price']['price']){
                    $memo .= '商品价格由('.$goodsOri['price'].')改为('.$goodsNew['product']['0']['price']['price']['price'].'),';
                    $modify_flag = true;
                }
                if($goodsOri['store']!=$goodsNew['store']){
                    $memo .= '商品库存由('.$goodsOri['store'].')改为('.$goodsNew['product']['0']['store'].'),';
                    $modify_flag = true;
                }
                if($goodsOri['cost']!=$goodsNew['product']['0']['price']['cost']['price']){
                    $memo .= '商品成本价由('.$goodsOri['cost'].')改为('.$goodsNew['product']['0']['price']['cost']['price'].'),';
                    $modify_flag = true;
                }
                if($goodsOri['product'][key($goodsOri['product'])]['price']['mktprice']['price']!=$goodsNew['product']['0']['price']['mktprice']['price']){
                    $memo .= '商品市场价由('.$goodsOri['product'][key($goodsOri['product'])]['price']['mktprice']['price'].')改为('.$goodsNew['product']['0']['price']['mktprice']['price'].'),';
                    $modify_flag = true;
                }
                if($goodsOri['weight']!=$goodsNew['product']['0']['weight']){
                    $memo .= '商品重量由('.$goodsOri['weight'].')改为('.$goodsNew['product']['0']['weight'].'),';
                    $modify_flag = true;
                }
    
                //系统会员等级基础信息
                $member_lvinfo = app::get('b2c')->model('member_lv')->getList('member_lv_id,name,dis_count');
                $member_lv=array();
                $lv_name=array();
                $lv_discount=array();
                foreach($member_lvinfo as $keylv=>$vallv){
                    $member_lv[$vallv['member_lv_id']]=$vallv['member_lv_id'];//会员等级
                    $lv_name[$vallv['member_lv_id']]=$vallv['name'];//会员名称
                    $lv_discount[$vallv['member_lv_id']]=$vallv['dis_count'];//会员价折扣
                }
    
                //保存前会员价信息重组
                $lv_price_ori = $goodsOri['product'][key($goodsOri['product'])]['price']['member_lv_price'];//保存前的会员价数据
                $lv_price_ori_rec = array();
                if(!empty($lv_price_ori)){
                    foreach($lv_price_ori as $k_lpo=>$v_lpo){
                        $lv_price_ori_rec[$v_lpo['level_id']] = $v_lpo['price'];
                    }
                }
                $diff_lv_ori = array_diff_key($member_lv, $lv_price_ori_rec);
                if(!empty($diff_lv_ori)){
                    foreach($diff_lv_ori as $k_dlo=>$v_dlo){
                        $lv_price_ori_rec[$k_dlo]=$goodsOri['price']*$lv_discount[$k_dlo];
                    }
                }
    
                //保存后会员价信息重组
                $lv_price_new = $goodsNew['product']['0']['price']['member_lv_price'];//保存后的会员价数据
                $lv_price_new_rec = array();
                if(!empty($lv_price_new)){
                    foreach($lv_price_new as $k_lpn=>$v_lpn){
                        $lv_price_new_rec[$v_lpn['level_id']] = $v_lpn['price'];
                    }
                }
                $diff_lv_new = array_diff_key($member_lv, $lv_price_new_rec);
                if(!empty($diff_lv_new)){
                    foreach($diff_lv_new as $k_dln=>$v_dln){
                        $lv_price_new_rec[$k_dln]=$goodsNew['product']['0']['price']['price']['price']*$lv_discount[$k_dln];
                    }
                }
    
                //判断会员价是否修改
                foreach($lv_price_ori_rec as $k_lpor=>$v_lpor){
                    if($v_lpor!=$lv_price_new_rec[$k_lpor]){
                        $memo_lv .= $lv_name[$k_lpor].'价由('.$v_lpor.')改为('.$lv_price_new_rec[$k_lpor].'), ';
                        $modify_flag = true;
                    }
                }
            }

            $memo = trim($memo.$memo_lv);
            if($modify_flag){
                kernel::single('operatorlog_logs')->inlogs(rtrim($memo,','), '修改商品', 'goods');
            }
        }
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $value
     */
    function logproducts($product){
        //echo "<pre>";print_r($product);exit;
        $member_lvinfo = app::get('b2c')->model('member_lv')->getList('member_lv_id,name,dis_count');
        $lv_name=array();
        $lv_discount=array();
        foreach($member_lvinfo as $keylv=>$vallv){
            $lv_name[$vallv['member_lv_id']]=$vallv['name'];//会员名称
            $lv_discount[$vallv['member_lv_id']]=$vallv['dis_count'];//会员价折扣
        }
        $mkt_name = array('true'=>'是','false'=>'否');
        $memo  = '修改货品ID为('.$product['product_id'].')的,';
        $memo .= '货品编码为('.$product['bn'].'),';
        $memo .= '货品库存为('.$product['store'].'),';
        $memo .= '货品销售价为('.$product['price']['price']['price'].'),';
        $memo .= '货品成本价为('.$product['price']['cost']['price'].'),';
        $memo .= '货品市场价为('.$product['price']['mktprice']['price'].'),';
        $memo .= '货品上下架状态为('.$mkt_name[$product['status']].'),';
        $memo .= '货品重量为('.$product['weight'].'),';
        $memo_lv='';
        foreach($product['price']['member_lv_price'] as $key=>$val){
            if($val['price']==''){
                $val['price'] = $product['price']['price']['price']*$lv_discount[$val['level_id']];
            }
            $memo_lv .= $lv_name[$val['level_id']].'价改为('.$val['price'].'),';
            
        }
        $memo=rtrim($memo.$memo_lv,',');
        kernel::single('operatorlog_logs')->inlogs($memo, '修改货品(只记录保存后的数据信息)', 'goods');
    }


}//End Class
