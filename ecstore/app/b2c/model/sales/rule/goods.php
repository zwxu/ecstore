<?php

 
/**
 * 商品促销规则 数据库处理
 * $ 2010-05-16 12:33 $
 */
class b2c_mdl_sales_rule_goods extends dbeav_model
{
    
    
    function __construct($app){
        parent::__construct($app);
        //使用meta系统进行存储
        $this->use_meta();
    }
    
    /**
     * 重写insert方法
     * @param mixed 需要插入记录的数组
     * @return boolean 
     */
    public function insert(&$data)
    {
        $is_inserted = parent::insert($data);
        if ($is_inserted)
        {
            $obj_extends_service = kernel::servicelist('b2c.api_sales_rule_goods_extends_actions');
            if ($obj_extends_service)
            {
                foreach ($obj_extends_service as $obj)
                {
                    $obj->extend_insert($data);
                }
            }
        }
        
        return $is_inserted;
    }
    
    public function pre_recycle($data=array()) {
        $oGPR = $this->app->model('goods_promotion_ref');
        
        if( is_array($data) ) {
            $filter = array();
            foreach ($data as $key => $value) {
                if( !$value['rule_id'] ) continue;
                $filter['rule_id'][] = $value['rule_id'];
            }
            if( $filter['rule_id'] ) {
                $param = array('status'=>'false');
                return $oGPR->update($param, $filter);
            }
            return false;
        }
        return false;
    }
    
    
    
    public function suf_restore($sdf=0) {
        $id = $sdf['rule_id'];
        if(!$id) return false;
        $oGPR = $this->app->model('goods_promotion_ref');
        $param = array('status'=>'true');
        $filter  = array('rule_id'=>$id);
        return $oGPR->update($param, $filter);
    }
    
    
    public function suf_delete($id=0) {
        if(!$id) return false;
        $oGPR = $this->app->model('goods_promotion_ref');
        $filter  = array('rule_id'=>$id);
        return $oGPR->delete($filter);
    }
    
    
}// mdl_goods_rule class end
