<?php

 
class pointprofessional_mdl_member_point extends b2c_mdl_member_point
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
     * 重写count方法
     * @param array filter
     * @return int 计数
     */
    public function count($filter=null)
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
        return parent::count($filter);
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
	
	/**
     * 取到有效的积分历史 - 重写原来的方法
     * @param int member id.
     * @param string type 2-积分账面数，1-积分累计值
     * @return int 积分值
     */
    private function get_real_history($member_id, $type='1')
    {
        $site_point_expired = $this->app->getConf('site.point_expired');
		if ($site_point_expired == 'true')
			$expired_time = strtotime($this->app->getConf('site.point_expired_value'));
		else
			$expired_time = 0;
        $real_point = 0;
        
        if ($site_point_expired == 'true')
        {
            $obj_member = $this->app->model('members');
			$sql = "SELECT member_lv_id FROM " . $obj_member->table_name(1) . " WHERE member_id = " . $member_id;
			$arr_tmp = $this->db->select($sql);
            $this->tidy_data($arr_tmp, '*');
            $arr_member = array();
            
            if ($arr_tmp)
                $arr_member = $arr_tmp[0];
            
            $arr_member_lv = array();
            $obj_member_lv = $this->app->model('member_lv');
            $arr_tmp = $obj_member_lv->getList('expiretime', array('member_lv_id'=>$arr_member['member_lv_id']));
            
            if ($arr_tmp)
                $arr_member_lv = $arr_tmp[0];
            if ($arr_member_lv['expiretime'])
				$expired_time = $arr_member_lv['expiretime'];
        }
        
		$expired_time = strtotime(date('Y-m-d'));
		// 所有未过期的积分
		$sql = "SELECT * FROM " . $this->table_name(1) . " WHERE change_point > 0 AND (expiretime > " . $expired_time . " OR expiretime='0') AND member_id = " . $member_id;
		$rows_unexpired = $this->db->select($sql);
		$this->tidy_data($rows_unexpired, '*');
		if ($rows_unexpired)
		{
			foreach ($rows_unexpired as $arr_row)
			{
				if ($type == '2')
					$real_point += intval($arr_row['change_point']) - intval($arr_row['consume_point']);
				if ($type == '1')
					$real_point += intval($arr_row['change_point']);
			}
		}
        
        return $real_point;
    }
}