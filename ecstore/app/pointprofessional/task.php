<?php

 
class pointprofessional_task{     

    function post_install($options)
    {
        kernel::log('Initial pointprofessional');
        kernel::single('base_initial', 'pointprofessional')->init();
		
		kernel::log('Register pointprofessional meta');
		$obj_members = app::get('b2c')->model('members');
		
		/**
		 * 会员预获得积分控制
		 */
		$col = array(
          'obtained_point'=>
            array (
			  'type' => 'int(10)',
			  'required' => true,
			  'default' => 0,
			  'editable' => false,
			),
        );
		
		$obj_members->meta_register($col);
		
		/**
		 * 会员的预占积分控制
		 */
		$col = array(
          'freezed_point'=>
            array (
			  'type' => 'int(10)',
			  'required' => true,
			  'default' => 0,
			  'editable' => false,
			),
        );
		
		$obj_members->meta_register($col);
		
		/**
		 * 会员积分累积值
		 */
		$col = array(
          'cumulation_point'=>
            array (
			  'type' => 'int(10)',
			  'required' => true,
			  'default' => 0,
			  'editable' => false,
			),
        );
		
		$obj_members->meta_register($col);
    }
    
    function post_uninstall()
	{
        // 清理相关的数据回到初始值
		$app_b2c = app::get('b2c');
		// 修改kvstroe的值
		$app_b2c->setConf('site.point_expired', false);
		$app_b2c->setConf('site.point_expried_method', '1');
		$app_b2c->setConf('site.point_max_deductible_method', '1');
		$app_b2c->setConf('site.point_max_deductible_value', '');
		$app_b2c->setConf('site.point_deductible_value', '0.01');
		$app_b2c->setConf('site.get_point_interval_time', '0');
		$app_b2c->setConf('site.get_policy.stage', '1');
		$app_b2c->setConf('site.consume_point.stage', '1');
		
		// 修改数据库相关的值
		$obj_mdl_member_lv = $app_b2c->model('member_lv');
		$obj_mdl_member_lv->update(array('expiretime'=>'0'),array());
    }
}
