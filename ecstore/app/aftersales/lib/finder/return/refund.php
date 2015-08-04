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

	public function dorefund($sdf)
	{
		$obj_refund = app::get('ectools')->model('refunds');
        $data['refund_type'] = $sdf['refund_type'];
        return $obj_refund->update($data,array('refund_id'=>$sdf['refund_id']));
	}
}