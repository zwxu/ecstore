<?php

class goodsapi_shopex_goods_cat_add extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

    //获取商品分类列表接口
    function shopex_goods_cat_add($params){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'cat_name','order_by','type_name'
        );
        $this->check_params($must_params);

        $obj_cat = app::get('b2c')->model('goods_cat');
        if(empty($params['cat_path'])){
            $parent_id = 0;
            $cat_path = ',';
        }else{
            $cat_path = array();
            $cat_id = 0;
            foreach( explode( '->',$params['cat_path'] ) as $cat_name ){
                $cat = $obj_cat->dump(array('cat_name'=>$cat_name,'parent_id'=>$cat_id),'cat_id');
                if( $cat )
                    $cat_id = $cat['cat_id'];
                else
                    $cat_id = 0;
            }
            $parent_id = $cat_id;
            $cat_path = ','.$cat_id.',';
        }

        $type = app::get('b2c')->model('goods_type')->dump(array('type_name'=>$params['type_name']),'type_id');
        if(!$type){
            $this->send_error(array('msg'=>'关联商品类型不存在'));
        }

        $filter = array(
            'parent_id'=> $cat_id,
            'cat_name' => $params['cat_name'],
            'cat_path' => $cat_path,
            'p_order'  => $params['order_by'],
            'type_id'  => $type['type_id']
        );

        if($obj_cat->save($filter)){
            $data['last_modify'] = time();
            $this->send_success($data);
        }else{
            $this->send_error(array('code'=>'0x004'));
        }
    }
}
