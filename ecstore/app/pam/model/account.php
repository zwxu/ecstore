<?php

/**
* account model类
*/
class pam_mdl_account extends dbeav_model{
	/**
	* 关联MODEL
	* @var array 
	*/
	var $has_many = array(
        'account'=>'auth:append',
    );
	/**
	* dump 等操作的相关联表
	* @var array 
	*/
var $subSdf = array(
        'delete' => array(
            'account:auth' => array('*'),
         )
    );
	
	/**
	 * 得到帐号用户名
	 * @param int $account_id 用户ID
	 * @return string 返回ID对应的用户名
	 */
	public function get_operactor_name($account_id='')
	{
		if ($account_id == '')
			return app::get('pam')->_('未知或无操作员');
		
		$tmp = $this->getList('login_name',array('account_id'=>$account_id));
		if (!$tmp)
		{
			return app::get('pam')->_('未知或无操作员');
		}
		
		return $tmp[0]['login_name'];
	}
}
