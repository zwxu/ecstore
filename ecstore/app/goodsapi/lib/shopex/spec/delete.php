<?php

class goodsapi_shopex_spec_delete extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->spec_model = app::get('b2c')->model('specification');
        $this->recycle_model = app::get('desktop')->model('recycle');
    }

    //删除商品规格接口
    function shopex_spec_delete(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'spec_name','alias',
        );
        $this->check_params($must_params);

        //获取到要删除的规格的id
        $spec = $this->spec_model->dump(array('spec_name'=>trim($params['spec_name']),'alias'=>trim($params['alias'])),'spec_id');
        if( !$spec ){
            //要删除的规格不存在，是否已经是最新数据
            $error['code'] = null;
            $error['msg'] = '要删除的规格不存在';
            $this->send_error($error);
        }

        $spec_id = $spec['spec_id'];
        //将数据放到回收站,删除
        $delete = kernel::single('desktop_system_recycle')->dorecycle('b2c_mdl_specification',array('spec_id'=>array($spec_id)) );
         if( !$delete ){
            $error['code'] = null;
            $error['msg'] = '删除失败';
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

        $this->send_success();
    }
}
