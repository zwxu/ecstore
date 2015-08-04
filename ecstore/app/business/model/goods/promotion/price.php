<?php

class business_mdl_goods_promotion_price extends dbeav_model{

	/**
	 * 构造方法
	 * @param object model相应app的对象
	 * @return null
	 */
    public function __construct($app){
        parent::__construct($app);
    }
    public function deleteByGID($goods_id){
        $_filter=array('goods_id'=>$goods_id);
        return parent::delete($_filter);
    }
    public function deleteByPID($p_id=0,$p_type=''){
        if(empty($p_type)||$p_id===0){
            return false;
        }
        $_filter=array('ref_id'=>$p_id,'p_type|nequal'=>$p_type);
        return parent::delete($_filter);
    }
    
    public function deleteByPType($p_type=''){
        if(empty($p_type)){
            return false;
        }
        $_filter=array('p_type|nequal'=>$p_type);
        return parent::delete($_filter);
    }
}
