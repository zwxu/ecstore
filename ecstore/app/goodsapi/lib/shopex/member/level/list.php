<?php

class goodsapi_shopex_member_level_list extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->member_lv_model = app::get('b2c')->model('member_lv');
    }

    //获取会员等级列表
    function shopex_member_level_list(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        /*
        //如果当前用户不是系统管理员，检查当前用户操作权限
        if( !$this->is_admin )
            $this->user_permission($this->user_id,'member_lv');
        */

        $obj_member_lv = $this->member_lv_model->getList('*');
        if($obj_member_lv){
            $default_lv = false;
            foreach($obj_member_lv as $key=>$value){
                if($value['default_lv']){
                    $default_lv = true;
                }
                $member_lv[$key] = array(
                    'name' => $value['name'],
                    'dis_count' => floatval($value['dis_count']),
                    'default_lv' =>$default_lv?'true':'false',
                    'more_point' => intval($value['more_point']),
                    'point'   => intval($value['point']),
                    'lv_type' => intval($value['lv_type']),
                    'show_other_price' => $value['show_other_price'],
                    'experience'  => intval($value['experience']),
                    'expiretime'  => intval($value['expiretime']),
                    'last_modify' => time(),
                );
            }
        }

        $data  = $member_lv;
        $this->send_success($data);

    }//end api

}

