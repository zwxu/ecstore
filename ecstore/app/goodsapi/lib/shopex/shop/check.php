<?php

class goodsapi_shopex_shop_check extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->db = kernel::database();
    }

    //检测店铺接口
    function shopex_shop_check(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        $arr_check = array(
            'goods_cat',     //检测店铺分类是否有重复（同一路径的分类分类名称不允许重复）
            'goods_type',    //类型是否有重复（类型名不允许相同）
            'goods_spec',    //规格是否有重复（规格+规格别名不允许相同）
            'brand',         //品牌是否有重复（品牌名不允许相同）
            'spec_value'    //同一规格下规格值不能相同
        );

        foreach($arr_check as $check_type){
            $this->_check($check_type);
        }

        $this->send_success();

    }//end api

    function _check($type){
        switch($type){
            case "goods_cat":
                $sql = 'SELECT cat_name as name,count(*) as num FROM `sdb_b2c_goods_cat` WHERE 1 group by cat_name,cat_path order by num desc';
                $type_name = '商品分类，';
                $error_msg = '在同一路径下,分类名称有重复';
                break;
            case "goods_type":
                $sql = 'SELECT COUNT(*) as num , name FROM  `sdb_b2c_goods_type`  WHERE 1  GROUP BY name order by num desc';
                $type_name = '商品类型，';
                $error_msg = '类型名称有重复';
                break;
            case "goods_spec":
                $sql = 'SELECT count(*) as num,spec_name as name,alias FROM `sdb_b2c_specification` WHERE 1 group by spec_name,alias order by num desc';
                $type_name = '商品类型规格，';
                $error_msg = '（规格+规格别名）有重复';
                break;
            case "brand":
                $sql = 'SELECT count(*) as num,brand_name as name  FROM `sdb_b2c_brand` WHERE 1 group by brand_name order by num desc';
                $type_name = '商品品牌，';
                $error_msg = '商品品牌有重复';
                break;
            case "spec_value":
                $sql = 'SELECT count(*) as num  FROM `sdb_b2c_spec_values` WHERE 1 group by spec_value,spec_id order by num desc';
                $type_name = '商品类型规格，';
                $error_msg = '规格的规格值有重复';
                break;
        }

        if( $obj = $this->db->SELECT($sql) ){
            foreach($obj as $value){
                if($value['num']>1){
                    if(isset($value['alias'])){
                        $name = '('.$value['name'].') + ('.$value['alias'].')';
                    }else{
                        $name = $value['name'];
                    }
                    $error['code'] = null;
                    $error['msg'] = $error_msg.":".$name;
                    $this->send_error($error);
                }
            }
        }

        return true;
    }
}
