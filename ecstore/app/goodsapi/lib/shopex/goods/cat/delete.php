<?php

class goodsapi_shopex_goods_cat_delete extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

    //删除商品分类列表接口
    function shopex_goods_cat_delete(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'cat_name','is_physical_delete',
        );
        $this->check_params($must_params);

        $parent_id = $this->_get_cat_id($params['cat_path']);
        $obj_cat = app::get('b2c')->model('goods_cat');
        //获取到要删除的分类
        $cat_id = $this->obj_cat->dump(array('parent_id'=>$parent_id,'cat_name'=>$params['cat_name']),'cat_id');
        if( $cat_id ){
            $cat_id = $cat_id['cat_id'];
        }else{
            $error['code'] = null;
            $error['msg']  = '要删除的分类不存在';
            $this->send_error($error);
        }

        //如果是物理删除
        if($params['is_physical_delete'] == 'true' ){
            if(!$obj_cat->toRemove($cat_id)){
                $error['code'] = null;
                $error['msg']  = '删除失败';
                $this->send_error($error);
            }
        }else{ //如果不是物理删除
            $delete = kernel::single('desktop_system_recycle')->dorecycle( 'b2c_mdl_goods_cat',array('cat_id'=>array($cat_id)) );
            if(!$delete){
                $error['code'] = null;
                $error['msg']  = '删除失败';
                $this->send_error($error);
            }
        }
        $data['last_modify'] = time();
        $this->send_success($data);
    }

    //根据cat_path获取到对应的父分类的id
    function _get_cat_id(){
        $obj_cat = app::get('b2c')->model('goods_cat');
        if(empty($cat_path)){
            $parent_id = 0;
        }else{
            $cat_id = 0;
            foreach( explode( '->',$cat_path ) as $cat_name ){
                $cat = $obj_cat->dump(array('cat_name'=>$cat_name,'parent_id'=>$cat_id),'cat_id');
                if( $cat ){
                    $cat_id = $cat['cat_id'];
                }
            }
        }
        return $cat_id;
    }
}
