<?php


class openid_task{

    function post_install($options)
    {
        kernel::log('Register b2c meta');
        $obj_members = app::get('b2c')->model('members');
        $col = array(
          'trust_name'=>
            array (
                  'type' => 'text',
                  'required' => false,
                  'label' => app::get('b2c')->_('信任登录ID'),
                  'width' => 110,
                  'editable' => false,
                  'in_list' => true,
            ),
        );
        $obj_members->meta_register($col);
		$menu = app::get('desktop')->model('menus');
		$menu->update(array('display'=>'true'),array('menu_type'=>'adminpanel','menu_path'=>'app=desktop&ctl=pam&act=index'));
    }

    function post_uninstall(){
		$obj_members = app::get('b2c')->model('members');
        $obj_members->meta_meta('trust_name');
		$menu = app::get('desktop')->model('menus');
		$menu->update(array('display'=>'false'),array('menu_type'=>'adminpanel','menu_path'=>'app=desktop&ctl=pam&act=index'));
        app::get('pam')->setConf('passport.openid_passport_trust','');
    }

    function post_update($dbinfo){
        $dbver = $dbinfo['dbver'];
        if(empty($dbver) || $dbver == '0.1'){
            $auth_model = app::get('pam')->model('auth');
            $auth = $auth_model->getList('*',array('module'=>'openid_passport_trust'));
            if(!$auth){
                kernel::log('openid version upgrade was successful');
            }
            foreach($auth as $key=>$row){
                $module_uid = end($row['module_uid']);
                $accoutData = $auth_model->getList('account_id',array('module_uid'=>$module_uid));
                if($accoutData){
                    $members_link_table = array(
                            'aftersales'=>'return_product',
                            'b2c'=>'comment_goods_point',
                            'b2c'=>'delivery',
                            'b2c'=>'member_addrs',
                            'b2c'=>'member_comments',
                            'b2c'=>'member_coupon',
                            'b2c'=>'member_goods',
                            'b2c'=>'member_msg',
                            'b2c'=>'member_msg',
                            'b2c'=>'member_point',
                            'b2c'=>'member_pwdlog',
                            'b2c'=>'orders',
                            'b2c'=>'reship',
                            'b2c'=>'sell_logs'
                        );
                    $auth_model->delete(array('module_uid'=>$row['module_uid']));
                    app::get('pam')->model('account')->delete(array('module_uid'=>$row['module_uid']));
                    foreach($members_link_table as $model_app=>$model_name){
                        if( $obj = app::get($model_app)->model($model_name) ){
                            $obj->update(array('member_id'=>$accoutData[0]['account_id']),array('member_id'=>$row['account_id']));
                        }
                    }
                    $old_member_data = app::get('b2c')->model('members')->getList('*',array('member_id'=>$accoutData[0]['account_id']));
                    $new_member_data = app::get('b2c')->model('members')->getList('*',array('member_id'=>$row['account_id']));
                    $members_sdf = $old_member_data[0];
                    $members_sdf['point'] += $new_member_data[0]['point'];//积分
                    $members_sdf['order_num'] += $new_member_data[0]['order_num'];//订单数
                    $members_sdf['advance'] += $new_member_data[0]['advance'];//预存款
                    $members_sdf['advance_freeze'] += $new_member_data[0]['advance_freeze'];
                    $members_sdf['point_freeze'] += $new_member_data[0]['point_freeze'];
                    $members_sdf['point_history'] += $new_member_data[0]['point_history'];
                    $members_sdf['unreadmsg'] += $new_member_data[0]['unreadmsg'];//未读信息
                    $members_sdf['experience'] += $new_member_data[0]['experience'];//经验值
                }else{
                    $auth_model->update(array('module_uid'=>$module_uid),array('account_id'=>$row['account_id']) );
                    app::get('pam')->model('account')->update(array('login_name'=>$module_uid),array('account_id'=>$row['account_id']));
                }
            }
        }

    }//end function

}
