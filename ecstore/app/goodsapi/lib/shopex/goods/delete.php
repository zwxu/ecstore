<?php

class goodsapi_shopex_goods_delete extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->goods_model = app::get('b2c')->model('goods');
        $this->recycle_model = app::get('desktop')->model('recycle');
    }

    //删除商品信息列表接口
    function shopex_goods_delete(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        if(!isset($params['bns']) || !isset($params['is_physical_delete'])){
            $error['code']  = null;
            $error['msg']  = '必填参数未定义';
            $this->send_error($error);
        }

        //放入回收站
        foreach(explode(',',$params['bns']) as $goods_bn){
            $goods_id = $this->goods_model->dump(array('bn'=>$goods_bn),'goods_id');
            if($goods_id){
                $delete = kernel::single('desktop_system_recycle')->dorecycle( 'b2c_mdl_goods',array('goods_id'=>array($goods_id['goods_id'])));
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
                }//end if
            }
        }//end foreach

        $this->send_success();
    }
}

