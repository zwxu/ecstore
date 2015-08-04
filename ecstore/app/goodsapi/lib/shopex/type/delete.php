<?php

class goodsapi_shopex_type_delete extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->type_model = app::get('b2c')->model('goods_type');
        $this->recycle_model = app::get('desktop')->model('recycle');
    }

    //添加商品类型接口
    function shopex_type_delete($params){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'name',
        );
        $this->check_params($must_params);

        //获取到要删除的type_id
        $type = $this->type_model->getList('type_id',array('name'=>$params['name']));
        if(!$type){
            $error['code'] = null;
            $error['msg'] = '要删除的数据不存在，是否是最新数据';
            $this->send_error($error);
        }

        if( $type ){
            $type_id = $type[0]['type_id'];
            //调用desktop中的删除方法
            $delete = kernel::single('desktop_system_recycle')->dorecycle( 'b2c_mdl_goods_type',array('type_id'=>array($type_id)) );
            if( !$delete ){
                $error['code'] = '0x004';
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
        $this->send_success();
    }
}
