<?php

class goodsapi_shopex_goods_update extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->obj_goods = kernel::single('goodsapi_goods');
        $this->goods_model = app::get('b2c')->model('goods');
        $this->obj_cat = app::get('b2c')->model('goods_cat');;
    }

    //更新商品信息列表接口
    function shopex_goods_update (){
        $params = $this->params;
        /** 处理api调用权限**/
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'cat_name','cat_path','type_name','price','bn','name','store','marketable',
            'list_time','delist_time','disabled','intro','is_unlimit'
        );

        foreach($must_params as $must_params_v){
            if(!isset($params[$must_params_v])){
                $error['code'] = null;
                $error['msg']  = '必填参数未定义';
                $this->send_error($error);
            }
        }

        /*
        //如果当前用户不是系统管理员，检查当前用户操作权限
        if( !$this->is_admin )
            $this->user_permission($this->user_id,'goods');
        */
        /**  end   **/
        if(!$params['name']){
            //商品名称不能为空
            $error['msg'] = '商品名称不能为空';
            $this->send_error($error);
        }

        if($params['brief']&&strlen($params['brief'])>210){
            //简短的商品介绍,请不要超过70个字！
            $error['msg'] = '简短的商品介绍,请不要超过70个字!';
            $this->send_error($error);
        }

        //获取到更新的goods_id
        if($params['goods_id']){
            $old_goods_id = $params['goods_id'];
        }else{
            $goods_id = $this->goods_model->db->select('select goods_id from sdb_b2c_goods where bn="'.trim($params['bn'].'"'));
            if( !empty($goods_id) ){
               $old_goods_id = $goods_id[0]['goods_id'];
            }else{
                $error['code'] = null;
                $error['msg']  = '更新商品不在网店存在';
                $this->send_error($error);
            }
        }

        /** 处理商品参数  **/
        //获取到要更新的项的父类id
        $parent_id = $this->_get_cat_id($params['cat_path']);
        $parent_id = $parent_id['cat_id'];
        $cat_id = $this->obj_cat->dump(array('parent_id'=>$parent_id,'cat_name'=>$params['cat_name']),'cat_id');
        if( $cat_id ){
            $cat_id = $cat_id['cat_id'];
        }else{
            $cat_id = $parent_id;
        }

        //获取到商品类型ID 没有则不是最新数据
        $type = app::get('b2c')->model('goods_type')->dump(array('name'=>$params['type_name']),'type_id');
        $type_id = $type['type_id'];
        //获取到商品品牌ID  没有则不是最新数据
        //$brand_id = $this->get_goods_brand_id($params['brand_name']);
        if(!empty($params['brand_name'])){
            $brand_id = app::get('b2c')->model('brand')->getList("brand_id",array('brand_name'=>trim($params['brand_name'])));
            if( $brand_id ){
                $brand_id = $brand_id[0]['brand_id'];
            }else{
                $error['code'] = '0x021';
                $this->send_error($error);
            }

        }

        //将默认图片地址转换为 图片ID
        if( $params['has_default_image'] ){
            $image_default_id = $this->obj_goods->get_image_id($params['default_image_path']);
        }

        //处理商品关键字
        if( $params['goods_keywords'] ){
            foreach( explode( '|', $params['goods_keywords']) as $keyword ){
                $goods['keywords'][] = array(
                    'keyword' => $keyword,
                    'res_type' => 'goods'
                );
            }
        }

        //处理seo_info信息
        $seo_info = array(
            'seo_title' =>$params['page_title']? $params['page_title']:null,
            'seo_keywords' =>$params['meta_keywords']?$params['meta_keywords']:null,
            'seo_description' => $params['meta_description']?$params['meta_description']:null,
        );

        //处理商品扩展属性信息
       if($params['prop_values']){
            $arr_prop_values = json_decode($params['prop_values'],true);
            $type_props_model = app::get('b2c')->model('goods_type_props');
            foreach($arr_prop_values['items'] as  $prop_value_key=>$prop_value){
                $subSdf = array('props_value'=>array('*'));
                $type_props = $type_props_model->dump(array('type_id'=>$type_id,'name'=>$prop_value['key']),'props_id,goods_p',$subSdf);
                if($type_props){
                    if($type_props['goods_p'] <= 20){
                        foreach($type_props['props_value'] as $props_value){
                            if($props_value['name'] == $prop_value['value']){
                                $props['p_'.$type_props['goods_p']] = array('value'=>$props_value['props_value_id']);
                            }
                        }
                    }else{
                        $props['p_'.$type_props['goods_p']] = array('value'=>$prop_value['value']);
                    }
                }
            }//end foreach
            $save_props = $props;
        }

        //处理商品详细参数信息
        if($params['params_values']){
            $arr_params_values = json_decode($params['params_values'],true);
            foreach($arr_params_values['params_values'] as $obj_params_values){
                foreach( $obj_params_values['options'] as $obj_params_options){
                    $params_options[$obj_params_options['key']] = $obj_params_options['value'];
                }
                $goods_params[$obj_params_values['name']] = $params_options;
            }
        }

        //处理标签
        if($params['tags']){
            $tag_model = app::get('desktop')->model('tag');
            foreach(explode(',',$params['tags']) as $tags_name){
                $tag_id = $tag_model->dump(array('tag_name'=>$tags_name),'*');
                $save_tag[] =  array(
                    'tag' =>array(
                        'tag_id'=>$tag_id['tag_id']
                    ),
                    'app_id' => 'b2c',
                    'tag_type' => 'goods',
                );
            }
        }
        //处理商品图片
        $json_images = json_decode($params['goods_images'],true);
        if($json_images['images']){
            foreach($json_images['images'] as $arr_image){
                $iamge_id = $this->obj_goods->get_image_id($arr_image['source']);
                $save_images[] = array(
                    'target_type' => 'goods',
                    'image_id' => $iamge_id
                );
            }
        }

        //处理货品信息
        $arr_products = json_decode($params['products'],true);
        if(empty($arr_products['products'])){
            $arr_mebmer_lps = json_decode($params['member_lps'],true);
            $save_member_lv_price = $this->obj_goods->get_member_price($arr_mebmer_lps['member_lps']);
            $product_status = true;
            if(!$params['marketable'])$product_status = false;
            $save_product = array(array(
                'status' =>$product_status,
                'barcode' => $params['barcode'],
                'price' => array(
                    'price' =>array('price'=>$params['price']),
                    'member_lv_price' =>$save_member_lv_price,
                    'cost' =>array('price'=>$params['cost']),
                    'mktprice'=>array('price'=>$params['mktprice']),
                ),
                'bn'=>$params['bn_code'],
                'weight' => $params['weight'],
                'store' =>$params['is_unlimit'] == 'true'? null:$params['store'],
                'store_place' =>$params['goods_space'],
                'unit' => $params['unit'],
                'default' =>1,
            ));
        }else{
            $product_status = true;
            if(!$params['marketable']) $product_status = false;
            $save_goods_spec = array();
            foreach($arr_products['products'] as $product_key=>$obj_product){
                $save_spec_desc = $this->obj_goods->get_spec_values($obj_product['spec_values']);
                $save_member_lv_price = $this->obj_goods->get_member_price($obj_product['member_lps']);
                $save_product['new_'.$product_key] = array(
                    'barcode' => $obj_product['barcode'],
                    'bn' => $obj_product['bn_code'],
                    'store' => $obj_product['is_unlimit']=='true'?null:$obj_product['store'],
                    'weight' => $obj_product['weight'],
                    'cost' => $obj_product['cost'],
                    'store_place' => $obj_product['goods_space'],
                    'status' => $product_status,
                    'price' =>array(
                        'member_lv_price' => $save_member_lv_price,
                        'price' => array('price'=>$obj_product['price']),
                        'cost'=> array('price'=> $obj_product['cost']),
                        'mktprice'=>array('price'=>$obj_product['mktprice']),
                    ),
                    'spec_desc'=>$save_spec_desc['product'],
                    'unit' => $params['unit'],
                );
                $save_goods_spec = $this->obj_goods->multi_array_merge($save_goods_spec,$save_spec_desc['goods']);
            }
        }//end save_prodcut

        $goods_status = true;
        if(!$params['marketable'])$goods_status = false;

        $save_data = array(
            'goods_id' => $old_goods_id,
            'tag' => $save_tag,
            'category' => array('cat_id'=>$cat_id),
            'type'  => array('type_id'=>$type_id),
            'name' =>  $params['name'],
            'bn'  => $params['bn'],
            'brand' => array('brand_id'=>$brand_id),
            'brief' => $params['brief'],
            'goods_setting' =>$params['goods_setting'],
            'images'  => $save_images,
            'product' => $save_product,
            'image_default_id' => $image_default_id,
            'props'  =>$save_props,
            'params' => $goods_params,
            'spec'  => $save_goods_spec,
            'seo_info' => $seo_info,
            'spec' => $save_goods_spec,
            'goods_type' => $params['goods_type']?$params['goods_type']:'normal',
            'keywords' => $goods['keywords'],
            'gain_score' => floatval($params['score']),
            'unit' => $params['unit'],
            'status' => $goods_status,
            'description' => $params['intro']
        );

        if ( !$this->goods_model->save($save_data) ){
            $error['code'] = null;
            $error['msg']  =  '更新失败';
            $this->send_error($error);

        }

        $data['last_modify']  = time();
        $this->send_success($data);
    }

    function _get_cat_id($cat_path){
        if(empty($cat_path) || $cat_path == '->'){
            $cat_id = 0;
            $cat_path = ',';
        }else{
            $new_cat_path = ',';
            $cat_id = 0;
            foreach( explode( '->',$cat_path ) as $cat_name ){
                $cat = $this->obj_cat->dump(array('cat_name'=>$cat_name,'parent_id'=>$cat_id),'cat_id');
                if( $cat ){
                    $cat_id = $cat['cat_id'];
                }else{
                    $this->send_error('0x003');
                }
                $new_cat_path .= $cat_id.',';
            }
        }
        $cat['cat_id'] = $cat_id;
        $cat['cat_path'] = $new_cat_path;
        return $cat;
    }

}

