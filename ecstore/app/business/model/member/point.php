<?php

 
class business_mdl_member_point extends b2c_mdl_member_point
{
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {        
        $this->app = app::get('b2c');
        $this->current_app = $app;
        parent::__construct( $this->app );
    }
	
	 /**
     * 重写getList方法
     * @param string column
     * @param array filter
     * @param int offset
     * @param int limit
     * @param string order by
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        if ($this->app->getConf('site.point_expired') == 'true')
        {
            $special_filter = array(
				'filter_sql'=>'change_point > 0 AND (expiretime > ' . strtotime(date('Y-m-d')) . ' OR expiretime=0)',
            );
        }
        else
        {
            $special_filter = array(
				'change_point|than'=>'0',
			);
        }
        
        $filter = array_merge($special_filter, $filter);        
        return parent::getList($cols, $filter, $offset, $limit, $orderType);
    }
    
	/**
	 * 得到所有的积分历史(包括过期的)
	 * @param string column
     * @param array filter
     * @param int offset
     * @param int limit
     * @param string order by
	 */
	public function get_all_list($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
		return parent::getList($cols, $filter, $offset, $limit, $orderType);
	}
	
}