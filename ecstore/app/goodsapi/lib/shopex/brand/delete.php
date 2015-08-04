<?php

class goodsapi_shopex_brand_delete extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->brand_model = app::get('b2c')->model('brand');
        $this->recycle_model = app::get('desktop')->model('recycle');
    }

    //删除商品品牌接口
    function shopex_brand_delete($params){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'brand_name','is_physical_delete',
        );
        $this->check_params($must_params);

        //得到要删除的品牌id
        $brand_id = $this->brand_model->getList("brand_id",array('brand_name'=>trim($params['brand_name'])));
        if( $brand_id ){
            $brand_id = $brand_id[0]['brand_id'];
        }else{
            $error['code'] = '0x021';
            $this->send_error($error);
        }

        //删除 ，放到回收站
        $delete = kernel::single('desktop_system_recycle')->dorecycle( 'b2c_mdl_brand',array('brand_id'=>array($brand_id)) );
        if( !$delete ){
            $error['code'] = '删除失败';
            $this->send_error($error);
        }else{
            if( $params['is_physical_delete'] == 'true'){
                $insert_id = $this->recycle_model->db->lastinsertid();
                if( !$this->recycle_model->delete(array('item_id'=>$insert_id)) ){
                    $error['code'] = '删除失败';
                    $this->send_error($error);
                }
            }
        }
        $this->send_success();
    }

}
