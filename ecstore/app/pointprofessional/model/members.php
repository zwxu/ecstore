<?php

 
class pointprofessional_mdl_members extends b2c_mdl_members
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
        //使用meta系统进行存储
        $this->use_meta();
    }
    
    /**
     * 冻结订单预扣除积分
     * @param int member_id - 会员id
     * @param int point - 预冻结积分值
     * @return boolean - 成功与否
     */
    public function freez($member_id, $point)
    {
        if (!$member_id || !$point)
            return false;
        
        $arr_member = $this->dump($member_id,'freezed_point');
        $arr_data = array(
            'freezed_point' => intval($arr_member['freezed_point']) + intval($point),
        );
        $filter = array(
            'member_id' => $member_id,
        );
        return $this->update($arr_data, $filter);
    }
	
	/**
	 * 得到被冻结的积分
	 * @param int member id
	 * @return int point - 冻结的积分
	 */
	public function get_freez_point($member_id=0)
	{
		if (!$member_id)
			return 0;
		
		$arr_member = $this->dump($member_id,'freezed_point');
		return intval($arr_member['freezed_point']);
	}
    
    /**
     * 获取当前用户的有效积分
     * @param int member id
     * @return int 有效积分
     */
    public function get_real_point($member_id=0)
    {
        if (!$member_id)
            return 0;
            
        $real_point = 0;
        
        // 得到所有有效的可用积分记录
        $obj_member_point = $this->current_app->model('member_point');
        $arr_point_historys = $obj_member_point->get_usable_point($member_id);
        if ($arr_point_historys)
        {
            $discount_point = abs($point);
            foreach ($arr_point_historys as $arr_points)
            {
				if ($arr_points['change_point'] > 0)
					$real_point += $arr_points['change_point'] - $arr_points['consume_point'];
            }
        }
        
        return $real_point;
    }
    
    /**
     * 解冻订单预扣除的积分
     * @param int member_id - 会员id
     * @param int point - 预解冻积分值
     * @return boolean - 成功与否
     */
    public function unfreez($member_id, $point,$rel_id='')
    {
        if (!$member_id || !$point)
            return false;  
        $arr_member = $this->dump($member_id,'freezed_point');
        $arr_data = array(
            'freezed_point' => intval($arr_member['freezed_point']) - abs($point),
        );
        $filter = array(
            'member_id' => $member_id,
        );
        return $this->update($arr_data, $filter);
    }
    
    /**
     * 生成预冻结积分量 - 将要获得的积分
     * @param int member_id - 会员id
     * @param int point - 预获得积分值
     * @return boolean - 成功与否
     */
    public function add_obtained($member_id, $point)
    {
        if (!$member_id || !$point)
            return false;
            
        $arr_member = $this->dump($member_id,'obtained_point');
        $arr_data = array(
            'obtained_point' => intval($arr_member['obtained_point']) + intval($point),
        );
        $filter = array(
            'member_id' => $member_id,
        );
        return $this->update($arr_data, $filter);
    }
    
    /**
     * 扣除预冻结的积分量 - 将要获得的积分
     * @param int member_id - 会员id
     * @param int point - 预获得积分值
     * @return boolean - 成功与否
     */
    public function reduce_obtained($member_id, $point,$rel_id='')
    {
        if (!$member_id || !$point)
            return false;
        $member_point  = $this->app->model('member_point');
        $filter['member_id'] = $member_id;
        $filter['related_id'] = $rel_id;
        $filter['type'] = 2;
        $row = $member_point->getList('id',$filter);
        if($row) return true;
        $arr_member = $this->dump($member_id,'obtained_point');
        $arr_data = array(
            'obtained_point' => intval($arr_member['obtained_point']) - intval($point),
        );
        $filter = array(
            'member_id' => $member_id,
        );
        return $this->update($arr_data, $filter);
    }
    
    /**
     * 得到会员预获得的冻结量 - 将要获得的量
     * @param int member_id - 会员id
     * @return int obtained_point - 预获得的积分量
     */
    public function get_obtained($member_id)
    {
        if (!$member_id)
            return false;
        
        $arr_member = $this->dump($member_id,'obtained_point');
        return intval($arr_member['obtained_point']);
    }
}