<?php

 

class pointprofessional_member_lv_extends
{
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
	
	/**
	 * 生成自己app会员中心的菜单
	 * @param string 设置的积分过期设置是否开启
	 * @param string 设置的积分的过期的方法
	 * @param array - 会员中心的菜单数组，引用值
	 * @return boolean - 是否成功
	 */
	public function validate($is_point_expired, $point_expired_method, &$data,&$msg='')
	{
		if ($is_point_expired == 'false' || !$is_point_expired)
			return true;
		if (!$data) return true;
		
		/*switch ($point_expired_method){
			case '1':
				//if (strtotime($data['expiretime']) < strtotime(date("Y-m-d",time()))){
					//$msg = app::get('pointprofessional')->_('不能设置今天以前的时间！');
					//return false;
				//}
				break;
			case '2':
				if ($data['expiretime'] < 0){
					$msg = app::get('pointprofessional')->_('过期时间间隔不能设置为负数！');
					return false;
				}
				
				if (!preg_match('/^[0-9]*[1-9][0-9]*$/',$data['expiretime'],$matches)){
					$msg = app::get('pointprofessional')->_('过期时间间隔不能设置为小数！');
					return false;
				}
				break;
		}*/
		
		return true;
	}
}