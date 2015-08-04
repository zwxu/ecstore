<?php
class b2c_service_member_autocomplete{
    function get_data($key,$cols){
        if(!$key) return null;
        $obj_pam = app::get('pam')->model('account');
        $filter['account_type'] = 'member';
        $filter['login_name|head'] = $key;
        $result = $obj_pam->getList('account_id,login_name',$filter);
       /* foreach((array)($result) as $k=>$v){
            $return[$cols[0]][] = $v['login_name'];
            $return[$cols[1]][] = $v['account_id'];
        } */
        
        return $result;
    }
}
