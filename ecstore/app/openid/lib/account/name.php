<?php
class openid_account_name{

    public function get_login_name($pam_account=null){
        if(!$pam_account) return '';
        if(app::get('pam')->model('auth')->getList('*',array('account_id' => $pam_account['account_id']))){
             $members_model = app::get('b2c')->model('members');
             $data = $members_model->getList('*',array('member_id' => $pam_account['account_id']));
             if(!$data){
                   return $pam_account['login_name'];
             }else{
                   return $data[0]['name'];
             }
        }else{
            return $pam_account['login_name'];
        }
    }
}
?>
