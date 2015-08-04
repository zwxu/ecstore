<?php

class goodsapi_shopex_goods_cat_update extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->obj_cat = app::get('b2c')->model('goods_cat');
    }

    //获取商品分类列表接口
    function shopex_goods_cat_update($params){
        $parmas = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'cat_name','new_cat_path','new_cat_name'
        );
        $this->check_params($must_params);

        //获取到要更新的项的父类id
        $parent_id = $this->_get_cat_id($params['cat_path']);
        $parent_id = $parent_id['cat_id'];

        //获取到要更新的项
        $cat_id = $this->obj_cat->dump(array('parent_id'=>$parent_id,'cat_name'=>$params['cat_name']),'cat_id');
        if( $cat_id ){
            $cat_id = $cat_id['cat_id'];
        }else{
            $error['code'] = null;
            $error['msg']  = '要更新的分类不存在';
            $this->send_error($error);
        }

        //获取要更新后的父类id 和 cat_path
        $new_cat = $this->_get_cat_id($params['new_cat_path']);
        $new_parent_id = $new_cat['cat_id'];
        $new_cat_path = $new_cat['cat_path'];

        //获取到要更新的type_name 的id
        $type = app::get('b2c')->model('goods_type')->dump(array('type_name'=>$params['type_name']),'type_id');
        if(!$type){
            $error['code'] = null;
            $error['msg']  = '关联类型名称不存在';
            $this->send_error($error);
        }else{
           $type_id = $type['type_id'];
        }

        //更新数据
        $data = array(
            'parent_id'=> $new_parent_id,
            'cat_name' => $params['new_cat_name'],
            'cat_path' => $new_cat_path,
            'p_order'  => $params['order_by'],
            'type_id'  => $type_id,
            //'last_modify' => $params['last_modify'],
        );

        if($this->obj_cat->update($data,array('cat_id'=>$cat_id))){
            $data['last_modify'] = time();
            $this->send_success($data);
        }else{
            $this->send_error(array('code'=>'0x004'));
        }
    }

    function _get_cat_id($cat_path){
        if(empty($cat_path)){
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
                    $this->send_error(array('code'=>'0x003'));
                }
                $new_cat_path .= $cat_id.',';
            }
        }
        $cat['cat_id'] = $cat_id;
        $cat['cat_path'] = $new_cat_path;
        return $cat;
    }
}
