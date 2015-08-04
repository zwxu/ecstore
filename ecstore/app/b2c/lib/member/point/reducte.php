<?php

 
class b2c_member_point_reducte
{
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {        
        $this->app = $app;
    }
    
    /**
     * 增加积分
     * @param string member id
     * @param int score 需要变化的积分值
     * @param string message 引用值
     * @param string useage 用途
     * @param int status 状态值
     * @param string 订单处于的阶段
     * @param string object id - 对象id
     * @param string 原因
     * @param int 操作员id
     */
    public function change_point($member_id=0, $score, &$message, $usage, $status, $stage, $rel_id, $operator, $reason='pay')
    {
        $policy_method = $this->app->getConf("site.get_policy.method");
        $objPoint = $this->app->model('member_point');
		$is_save = true;
        
        if ($policy_method > 1)
        { 
            if (isset($score) && $score != 0)
            {
                // 使用的积分 
                $is_save = $objPoint->change_point($member_id, $score, $message, $usage, $status, $rel_id, $operator, $reason);
                
				if (!$is_save)
					return false;
					
                $obj_order_operations = kernel::servicelist('b2c.order_point_operaction');
                if ($obj_order_operations)
                {
                    $arr_data = array(
                        'member_id' => $member_id,
                        'score_u' => $score,
                        'rel_id' => $rel_id,
                    );
                    foreach ($obj_order_operations as $obj_operation)
                    {
                        $obj_operation->gen_member_point($arr_data, $reason);
                    }
                }
            }
        }
		
		return true;
    }
}