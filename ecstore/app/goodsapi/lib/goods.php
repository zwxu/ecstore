<?php

class goodsapi_goods extends goodsapi_goodsapi{

    public function __construct(){
        $this->brand_model = app::get('b2c')->model('brand');
    }

    //图片url转换为image_id
    function get_image_id($image_url){
        if(empty($image_url)) return '';
        $host = defined('IMG_URL') ? IMG_URL : kernel::base_url(1);
        if( $host != substr($image_url,0,strlen($host)) ){
            $url = $image_url;
        }else{
            $url = substr($image_url,strlen($host)+1);
        }
        $image = app::get('image')->model('image')->dump(array('url'=>$url),'image_id');
        if(!$image){
            //远程地址，并且不在系统中存在
            $image_name = substr(strrchr($image_url,'/'),1);
            $image_id = app::get('image')->model('image')->store($image_url,null,null,$image_name);
        }else{
            $image_id =$image['image_id'];
        }
        return $image_id;
    }

    /*
     *根据品牌名称获取到品牌ID
     *
     * */
    function get_brand_id($brand_name){
        $brand = $this->brand_model->dump(array('brand_name'=>$brand_name),'brand_id');
        if( !$brand ){
            return false;
        }
        return $brand['brand_id'];
    }


    /*
     *goods规格值保存
     * */
    function get_spec_values($spec_values){
        $obj_spec = app::get('b2c')->model('specification');
        $obj_spec_value = app::get('b2c')->model('spec_values');
        if(empty($spec_values)) return '';
        $spec_desc = array();
        $spec = array();
        foreach($spec_values as $key=>$spec_value){
            $spec_name = $spec_value['spec_name'];
            $spec_id = $obj_spec->getList('spec_id,spec_type',array('spec_name'=>$spec_name,'alias'=>$spec_value['spec_alias_name']));
            $spec_value_id = $obj_spec_value->getList('*',array('spec_id'=>$spec_id[0]['spec_id'],'spec_value'=>$spec_value['spec_value_name']));

            if(!empty($spec_value['customer_spec_value_name']) && $spec_value['spec_value_name'] != $spec_value['customer_spec_value_name'] ){
                $spec_value_name = $spec_value['customer_spec_value_name'];
            }else{
                $spec_value_name = $spec_value['spec_value_name'];
            }
            $spec_value_image = $spec_value['customer_spec_value_image'];
            if($spec_value_image)
                $spec_value_image = $this->get_image_id($spec_value_image);

            if($spec_value['rela_goods_images'])
                $spec_goods_images = $this->get_image_id($spec_value['rela_goods_images']);

            $private = time().$spec_value_id[0]['spec_value_id'];
            //goods 规格值保存
            $spec[$spec_id[0]['spec_id']] = array(
                'spec_name' => $spec_name,
                'spec_id' => $spec_id[0]['spec_id'],
                'spec_type' => $spec_id[0]['spec_type'],
                'option' => array(
                    $private =>array(
                        'private_spec_value_id' => $private,
                        'spec_value' =>$spec_value_name,
                        'spec_value_id' => $spec_value_id[0]['spec_value_id'],
                        'spec_image' => $spec_value_image,
                        'spec_goods_images' => $spec_goods_images,
                    ),
                ),
            );
            //货品中的规格值保存
            $save_spec_value[$spec_id[0]['spec_id']] = $spec_value_name;
            $save_spec_p[$spec_id[0]['spec_id']] = $private;
            $save_spec_v[$spec_id[0]['spec_id']] = $spec_value_id[0]['spec_value_id'];
        }

        $spec_desc['product']= array(
            'spec_value'=>$save_spec_value,
            'spec_private_value_id'=>$save_spec_p,
            'spec_value_id'=>$save_spec_v,
        );
        $spec_desc['goods'] = $spec;
        return $spec_desc;
    }

    /*
     *goods会员价格保存
     * */
    function get_member_price($member_price){
        $obj_member_lv = app::get('b2c')->model('member_lv');
        if(empty($member_price)){
            return array();
        }
        $member_lv_price = array();
        foreach($member_price as $key=>$value){
            $member_lv = $obj_member_lv->getList('member_lv_id',array('name'=>$value['member_lv_name']));
            $member_lv_price[] = array(
                'level_id'=>$member_lv[0]['member_lv_id'],
                'price' => $value['price'],
            );
        }
        return $member_lv_price;
    }

     function multi_array_merge($array1,$array2){
        if (is_array($array2) && count($array2)){//不是空数组的话
            foreach ($array2 as $k=>$v){
                if (is_array($v) && count($v)){
                    $array1[$k] = $this->multi_array_merge($array1[$k], $v);
                }else {
                    $array1[$k] = $v;
                }
            }
        }else {
            $array1 = $array2;
        }
        return $array1;
    }

    function get_spec_name($spec_name){
        if(empty($spec_name)) return '';
        $arr_spec_name = explode('[',$spec_name);
        $spec['spec_name'] = $arr_spec_name[0];
        $alias = substr($arr_spec_name[1],0,-1);
        if(!empty($alias)){
            $spec['alias'] = $alias;
        }
        return $spec;
    }

}

