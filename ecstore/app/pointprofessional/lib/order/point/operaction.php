<?php

 
class pointprofessional_order_point_operaction
{
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
	public function __construct($app)
    {          
        $this->app = $app;
		$this->app_b2c = app::get('b2c');
    }
	
	/**
	 * 处理订单积分的事情
	 * @param array - score_g ,score_u and member_id
	 * @param string - 操作类型 - 订单的所有操作
	 * @return boolean
	 */
	public function gen_member_point($arr_data, $type='create')
	{
		$policy_method = $this->app_b2c->getConf("site.get_policy.method");
		$arr_op = array();
		if ($policy_method > 1)
			$arr_op = $this->get_point_op($arr_data, $type);
		
		$obj_members = $this->app->model('members');
		$is_change = true;
		if (isset($arr_op['freez']) && $arr_op['freez'])
		{
			/**
			 * 冻结这部分使用的积分
			 */
			if ($arr_data['score_u'])
				$is_change = $obj_members->freez($arr_data['member_id'], $arr_data['score_u']);
		}
		
		if (isset($arr_op['unfreez']) && $arr_op['unfreez'])
		{
			/**
			 * 解冻这部分使用的积分
			 */	
			if ($arr_data['score_u'])
				$is_change = $obj_members->unfreez($arr_data['member_id'], $arr_data['score_u']);
		}
		
		if (isset($arr_op['add_obtained']) && $arr_op['add_obtained'])
		{
			/**
			 * 预占积分生成
			 */			
			if ($arr_data['score_g'])
				$is_change = $obj_members->add_obtained($arr_data['member_id'], $arr_data['score_g']);
		}
		
		if (isset($arr_op['reduce_obtained']) && $arr_op['reduce_obtained'])
		{
			/**
			 * 预占积分清除
			 */	
			if ($arr_data['score_g'])
				$is_change = $obj_members->reduce_obtained($arr_data['member_id'], $arr_data['score_g'],$arr_data['rel_id']);
		}
		
		return $is_change;
	}
	
	/**
	 * 根据订单的状态得到对订单积分的不同处理
	 * @param array - 订单状态
	 * @param string - 订单类型
	 * @return array - 返回相应的处理
	 */
	private function get_point_op($arr_data, $type='create')
	{
		if (!$arr_data || !$type)
			return array();
		
		$app_b2c = app::get('b2c');
		$policy_stage_gain = $this->app_b2c->getConf("site.get_policy.stage");
		$policy_stage_consume = $this->app_b2c->getConf("site.consume_point.stage");
		
		switch ($type)
		{
			case 'create':
				$arr_op = array(
					'freez' => true,
					'unfreez' => false,
					'add_obtained' => true,
					'reduce_obtained' => false,
				);
				break;
			case 'pay':
				$arr_op = array(
					'freez' => false,
					'add_obtained' => false,
				);
				if ($policy_stage_gain == '1')
					$arr_op['reduce_obtained'] = true;
				else
					$arr_op['reduce_obtained'] = false;
					
				if ($policy_stage_consume == '1')
					$arr_op['unfreez'] = true;
				else
					$arr_op['unfreez'] = false;
				break;
			case 'delivery':
				$arr_op = array(
					'freez' => false,
					'add_obtained' => false,
				);
				if ($policy_stage_gain == '2')
					$arr_op['reduce_obtained'] = true;
				else
					$arr_op['reduce_obtained'] = false;
					
				if ($policy_stage_consume == '2')
					$arr_op['unfreez'] = true;
				else
					$arr_op['unfreez'] = false;
				break;
			case 'refund':
				$arr_op = array(
					'freez' => false,
					'unfreez' => false,
					'add_obtained' => false,
					'reduce_obtained' => false,
				);				
				break;
			case 'reship':
				$arr_op = array(
					'freez' => false,
					'unfreez' => false,
					'add_obtained' => false,
					'reduce_obtained' => false,
				);
				break;
			case 'finish':
				$arr_op = array(
					'freez' => false,
					'add_obtained' => false,
				);
				if ($policy_stage_gain == '3')
					$arr_op['reduce_obtained'] = true;
				else
					$arr_op['reduce_obtained'] = false;
					
				if ($policy_stage_consume == '3')
					$arr_op['unfreez'] = true;
				else
					$arr_op['unfreez'] = false;
				break;
			case 'cancel':
				$arr_op = array(
					'freez' => false,
					'unfreez' => true,
					'add_obtained' => false,
					'reduce_obtained' => true,
				);
				break;
			default:
				$arr_op = array(
					'freez' => false,
					'unfreez' => false,
					'add_obtained' => false,
					'reduce_obtained' => false,
				);
				break;
		}
		
		return $arr_op;
	}
}