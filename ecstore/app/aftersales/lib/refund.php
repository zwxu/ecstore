<?php

class aftersales_finder_return_refund
{
    /**
     * 构造方法
     * @param object app
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
	
	/**
	 * 生成自己app会员中心的菜单
	 * @param array - 会员中心的菜单数组，引用值
	 * @param array - url 参数
	 * @return boolean - 是否成功
	 */
	public function dorefund($sdf)
	{
		$obj_refund = app::get('aftersales')->model('refunds');
        $flow_id = $obj_refund->db->lastinsertid();
        return $obj_refund->update($sdf);
	}
}