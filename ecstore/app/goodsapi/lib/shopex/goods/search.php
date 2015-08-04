<?php

class goodsapi_shopex_goods_search extends goodsapi_goodsapi{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->goods_model = app::get('b2c')->model('goods');
        $this->tag_rel_model = app::get('desktop')->model('tag_rel');
        $this->tag_model = app::get('desktop')->model('tag');
        $this->products_model = app::get('b2c')->model('products');
        $this->spec_values_model = app::get('b2c')->model('spec_values');
        $this->sepc_specification_model = app::get('b2c')->model('specification');
    }

    //查找商品信息列表接口
    function shopex_goods_search (){
        $params = $this->params;
        //api 调用合法性检查
       $this->check($params);

        /** 生成过滤条件 **/
        $filter= $this->_filter($params);

        //生成要返回的列
        $columns = '*';
        if( $params['columns']){
            $columns = explode('|',$params['columns']);
        }

        //默认分页码为1,分页大小为20
        $params['page_no'] = isset($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = isset($params['page_size']) ? $params['page_size'] : 20;
        $page_no = intval($params['page_no']) - 1;
        $page_size = intval($params['page_size']);
        $page_offset = $page_no * $page_size;

        //如果分页数为-1 则返回总数
        if($params['page_no'] == -1){
            $rows = $this->goods_model->count($filter);
            $data['item_total'] = $rows;
            $data['goods'] = array();
            $this->send_success($data);
        }

        //得到基本的商品数据
        $rows = $this->goods_model->getList($columns,$filter,$page_offset,$page_size);
        if( !$rows ){
            $error['code'] = null;
            $error['msg']  = '商品数据为空或数据库连接失败';
            $this->send_error($error);
        }

        /**
         * 得到返回的商品数据
         */
        $data_goods = array();
        foreach($rows as $arr_row){
            $data_goods[] = $this->_get_item_detail($arr_row);
        }

        $data['item_total'] = count($rows);
        $data['goods'] = $data_goods;
        $this->send_success($data);

    }

    function _get_item_detail($arr_row){
        if(!$arr_row){
            return  array();
        }
        // 获取到cat_name
        $obj_cat = app::get('b2c')->model('goods_cat');
        $cat = $obj_cat->dump(array('cat_id'=>$arr_row['cat_id']),'cat_name');
        $cat_name = $cat['cat_name'];

        //获取到cat_path
        $path = $obj_cat->getPath($arr_row['cat_id']);
        if( count($path) == 2){
            $cat_path = '';
        }else{
            $count = count($path)-1;
            unset($path[0]);
            unset($path[$count]);
            foreach($path as $k=>$item){
                if($k == 1){
                    $cat_path = $item['title'];
                }else{
                    $cat_path .= '->'.$item['title'];
                }
            }
        }

        //获取到类型名称
        $type = app::get('b2c')->model('goods_type')->dump(array('type_id'=>$arr_row['type_id']),'name');
        $type_name = $type['name'];

        //获取到品牌名称
        $brand = app::get('b2c')->model('brand')->dump(array('brand_id'=>$arr_row['brand_id']),'brand_name');
        $brand_name = $brand['brand_name'];

        //获取到商品默认图片路径
        $has_default_image = 'false';
        if( $arr_row['image_default_id'] ){
            $has_default_image = 'true';
            $default_image_path = kernel::single('base_storager')->image_path($arr_row['image_default_id']);
        }

        //获取会员等级价格列表
        $goods_lv_price = app::get('b2c')->model('goods_lv_price')->getList('*',array('goods_id'=>$arr_row['goods_id']));
        $member_lps = array();
        if( $goods_lv_price ){
            foreach($goods_lv_price as $level_k=>$level_v){
                $member_lv_name = app::get('b2c')->model('member_lv')->dump(array('member_lv_id'=>$level_v['level_id']),'name');
                $member_lv_product = app::get('b2c')->model('products')->dump(array('product_id'=>$level_v['product_id']),'bn');
                $member_lps[$level_k] = array(
                    'member_lv_name' =>$member_lv_name['name'],
                    'price' =>floatval($level_v['price']),
                    'bn'    =>$arr_row['bn'],
                    'bn_code' =>$member_lv_product['bn'],
                    'last_modify' =>time()
                );
            }
        }

        //获取商品关键词信息
        $goods_keywords = app::get('b2c')->model('goods_keywords')->getList('keyword',array('goods_id'=>$arr_row['goods_id']));
        $str_goods_keywords = '';
        $arr_goods_keywords = array();
        if($goods_keywords){
            foreach($goods_keywords as $keywords_k => $keywords_v){
                $arr_goods_keywords[$keywords_k] =  $keywords_v['keyword'];
            }
            $str_goods_keywords = implode('|',$arr_goods_keywords);
        }

        //获取到商品扩展属性信息
        $prop_values = array();
        $goods_type_props = app::get('b2c')->model('goods_type_props');
        $goods_type_props_value = app::get('b2c')->model('goods_type_props_value');
        for ($i=1;$i<=50;$i++){
            //1-20 select 21-50 input
            if ($arr_row['p_'.$i] ){
                $props_value_id = $arr_row['p_'.$i];
                if( $i <= 20){
                    $props = $goods_type_props_value->dump(array('props_value_id'=>$props_value_id),'name,props_id');
                    $prop_value = $props['name'];
                    $props_name = $goods_type_props->dump(array('props_id'=>$props['props_id']),'name');
                }else{
                    $prop_value = $props_value_id;
                    $props_name = $goods_type_props->dump(array('type_id'=>$arr_row['type_id'],'goods_p'=>$i),'name');
                }
                $prop_values[] = array(
                    'key' => $props_name['name'],
                    'value'=>$prop_value,
                    );
            }
        }

        //获取商品详细参数信息
        if($arr_row['params']){
            $params_values = array();
            foreach($arr_row['params'] as $params_key=>$params_value){
                foreach($params_value  as  $p_options_key=>$p_options_value){
                    $params_options[] = array(
                        'key' => $p_options_key,
                        'value' => $p_options_value,
                    );
                }
                $params_values[] = array(
                    'name' => $params_key,
                    'options' => $params_options,
                );
            }
        }

        $tags = array();
        //获取商品tags信息
        $rel_id = $this->tag_rel_model->getList('tag_id',array('rel_id'=>$arr_row['goods_id']));
        foreach($rel_id as $rel_id_k=>$rel_id_v){
            $tag_name = $this->tag_model->getList('tag_name',array('tag_id'=>$rel_id_v['tag_id']));
            $arr_tags[] = $tag_name[0]['tag_name'];
        }
        $tags = implode(',',$arr_tags);

        //获取商品的货品信息
        $products = array();
        $arr_products = $this->products_model->getList('*',array('goods_id'=>$arr_row['goods_id']));
        if(count($arr_products) > 1){
            foreach($arr_products as $product_key=>$product_row){
                //获取货品的规格信息
                if(!empty($product_row['spec_desc'])){
                    $goods_spec = '';
                    foreach( $product_row['spec_desc']['spec_value_id'] as  $spec_key=>$spec_value_id){
                        $spec_private_value_id = $product_row['spec_desc']['spec_private_value_id'][$spec_key];
                        $goods_spec = $arr_row['spec_desc'][$spec_key][$spec_private_value_id];
                        //规格关联商品
                        //print_r($goods_spec);
                        $str_spec_goods_image = '';
                        if($goods_spec['spec_goods_images']){
                            $spec_goods_image = explode(',',$goods_spec['spec_goods_images']);
                            $arr_spec_goods_image = array();
                            foreach ($spec_goods_image as $k => $spec_goods_image_row) {
                                $arr_spec_goods_image[$k] = kernel::single('base_storager')->image_path($spec_goods_image_row).',';
                            }
                            $str_spec_goods_image = implode(',', $arr_spec_goods_image);
                        }

                        $rs_spec_value = $this->spec_values_model->getList('*',array('spec_value_id'=>$spec_value_id));
                        $rs_specfiftion = $this->sepc_specification_model->getList('*',array('spec_id'=>$rs_spec_value[0]['spec_id']));
                        
                        //判断是否有自定义规格值和图片
                        $customer_spec_value_name = '';
                        $customer_spec_value_image = '';
                        if($rs_specfiftion[0]['spec_type'] == 'image'){
                            if($rs_spec_value[0]['spec_image'] != $goods_spec['spec_image']){
                                $customer_spec_value_image = $goods_spec['spec_image'];
                                $customer_spec_value_image = kernel::single('base_storager')->image_path($customer_spec_value_image);
                            }else{
                                $rs_spec_value[0]['spec_image'] = kernel::single('base_storager')->image_path($rs_spec_value[0]['spec_image']);
                            }

                            if($rs_spec_value[0]['spec_value'] != $goods_spec['spec_value']){
                                $customer_spec_value_name = $goods_spec['spec_value'];
                            }
                        }

                        $goods_spec['spec_goods_images'] = kernel::single('base_storager')->image_path($goods_spec['spec_goods_images']);
                        //返回货品规格信息
                        $spec_values[$spec_key] = array(
                            'spec_name'=>$rs_specfiftion[0]['spec_name'],
                            'spec_alias_name'=>$rs_specfiftion[0]['alias'] ? $rs_specfiftion[0]['alias'] : '',
                            'spec_value_name' => $rs_spec_value[0]['spec_value'],
                            'spec_value_image' => $rs_spec_value[0]['spec_image'],
                            'customer_spec_value_name' => $customer_spec_value_name,
                            'customer_spec_value_image' => $customer_spec_value_image,
                            'rela_goods_images' => $goods_spec['spec_goods_images'],
                        );
                    }
                    //print_r($spec_values);
                }
      

                //获取货品的会员价格信息
                if($member_lps){
                    $products_member_lps = array();
                    foreach($member_lps as $member_lps_key=>$member_lps_value){
                        if($member_lps_value['bn_code'] == $product_row['bn']){
                            $products_member_lps[] = $member_lps_value;
                        }
                    }
                }

                //货品是否开启无限库存
                $product_is_unlimit = 'false';
                if( $product_row['store'] === null){
                    $product_is_unlimit = 'true';
                }

                //返回货品信息
                $products[$product_key] = array(
                    'barcode' => $product_row['barcode'],
                    'bn_code' => $product_row['bn'],
                    'price'  => floatval($product_row['price']),
                    'mktprice' =>floatval($product_row['mktprice']),
                    'member_lps' => $products_member_lps,
                    'cost'      => floatval($product_row['cost']),
                    'weight'   => floatval($product_row['weight']),
                    'store'   => intval($product_row['store']),
                    'goods_space' => $product_row['store_place'],
                    'spec_values' => $spec_values,
                    'last_modify' => intval($product_row['last_modify']),
                    'is_unlimit' => $product_is_unlimit,
                );
            }
        }else{
            $bn_code = $arr_products[0]['bn_code'];
        }


        //获取商品的图片信息
        $goods_images = array();
        $image_attach = app::get('image')->model('image_attach')->getList('image_id,last_modified',array('target_id'=>$arr_row['goods_id'],'target_type'=>'goods'));
        if($image_attach){
            foreach($image_attach as $iamge_id){
                $goods_image_source = kernel::single('base_storager')->image_path($iamge_id['image_id']);
                $host = defined('IMG_URL') ? IMG_URL : kernel::base_url(1);
                if( $host == substr($goods_image_source,0,strlen($host)+1) ){
                    $is_remote = 'false';
                }else{
                    $is_remote = 'true';
                }
                $goods_images[] =  array(
                    'is_remote' => $is_remote,
                    'source' => substr($goods_image_source,0,-13),
                    'last_modify' => intval($image_id['last_modify']),
                    'order_by' => 0,
                );
            }
        }

        //该商品是否开启无限库存
        $goods_is_unlimit = 'false';
        if($arr_row['store'] === null){
            $goods_is_unlimit = 'true';
        }

        $goods_url = kernel::single('base_component_request')->get_full_http_host().kernel::single('site_controller')->gen_url(array('app'=>'b2c','ctl'=>'site_product','arg0'=>$arr_row["goods_id"]));
        $data = array(
                'goods_id' => $arr_row['goods_id'],
                'cat_name' => $cat_name,
                'cat_path' => $cat_path,
                'type_name' => $type_name,
                'goods_type' => $arr_row['goods_type'],
                'brand_name' => $brand_name,
                'default_image_path' => substr($default_image_path,0,-13),
                'has_default_image' => $has_default_image,
                'mktprice' => floatval($arr_row['mktprice']),
                'cost'    => floatval($arr_row['cost']),
                'price' => floatval($arr_row['price']),
                'member_lps' => $member_lps,
                'bn' =>$arr_row['bn'],
                'bn_code' => $bn_code,
                'name' => $arr_row['name'],
                'goods_keywords' =>$str_goods_keywords,
                'weight' => floatval($arr_row['weight']),
                'unit' => $arr_row['unit'],
                'store' => intval($arr_row['store']),
                'goods_space' => $arr_row['store_place'],
                'score_setting' => $arr_row['score_setting'],
                'score' => floatval($arr_row['score']),
                'marketable' => $arr_row['marketable'],
                'list_time' => intval($arr_row['uptime']),
                'delist_time' => intval($arr_row['downtime']),
                'disabled'   => 'false',
                'order_by' => intval($arr_row['p_order']),
                'd_order' => intval($arr_row['d_order']),
                'page_title' => $arr_row['seo_info']['seo_title'],
                'brief' => $arr_row['brief'],
                'intro' => $arr_row['intro'],
                'meta_keywords' => $arr_row['seo_info']['seo_keywords'],
                'meta_description' => $arr_row['seo_info']['seo_description'],
                'prop_values' => $prop_values,
                'params_values' => $params_values,
                'tags' => $tags,
                'products' =>$products,
                'is_unlimit' => $goods_is_unlimit,
                'goods_images' => $goods_images,
                'goods_url' =>$goods_url,
                'last_modify' => intval($arr_row['last_modify']),
            );
        return $data;
    }


    // 生成过滤条件
    function _filter($params){
        $condition = "";
        $filter = array();
        if ($params['cat_name']){
            //获取到cat_id
            $cat = app::get('b2c')->model('goods_cat')->getList('cat_id',array('cat_name'=>trim($params['cat_name'])));
            if($cat){
                foreach($cat as $key=>$value){
                    $filter['cat_id'][$key] = $value['cat_id'];
                }
            }
        }

        if ($params['type_name']){
            //获取到type_id
            $type = app::get('b2c')->model('goods_type')->dump(array('name'=>trim($params['type_name'])),'type_id');
            if( $type ){
                $filter['type_id'] = $type['type_id'];
            }
        }

        if ($params['brand_name']){
            //获取到brand_id
            $brand = app::get('b2c')->model('brand')->dump(array('brand_name'=>trim($params['brand_name'])),'brand_id');
            if( $brand ){
                $filter['brand_id'] = $brand['brand_id'];
            }
        }

        if( $params['start_time'] ){
            $filter['last_modify|than'] = $params['start_time'];
        }
        if( $params['end_time']){
            $filter['last_modify|lthan'] = $params['end_time'];
        }
        return $filter;
    }

}

