<?php

 

class b2c_mdl_member_point extends dbeav_model{
    
    var $defaultOrder = array('addtime', ' DESC');
    //type: 1.订单得积分,2.消费积分,3.无分类
    function getHistoryReason() {

        $aHistoryReason = array(
                            'order_pay_use' => array(
                                                    'describe' => app::get('b2c')->_('订单消费积分'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_b2c_mall_orders',
                                                ),
                            'order_pay_get' => array(
                                                    'describe' => app::get('b2c')->_('订单获得积分.'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_b2c_mall_orders',
                                                ),
                            'order_refund_use' => array(
                                                    'describe' => app::get('b2c')->_('退还订单消费积分'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_b2c_mall_orders',
                                                ),
                            'order_refund_get' => array(
                                                    'describe' => app::get('b2c')->_('扣掉订单所得积分'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_b2c_mall_orders',
                                                ),
                            'order_cancel_refund_consume_gift' => array(
                                                    'describe' => app::get('b2c')->_('Score deduction for gifts refunded for order cancelling.'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_b2c_mall_orders',
                                                ),
                            'exchange_coupon' => array(
                                                    'describe' => app::get('b2c')->_('兑换优惠券'),
                                                    'type' => 3,
                                                    'related_id' => '',
                                                ),
                            'operator_adjust' => array(
                                                    'describe' => app::get('b2c')->_('管理员改变积分.'),
                                                    'type' => 3,
                                                    'related_id' => '',
                                                ),
                            'register_score' => array(
                                                    'describe' => app::get('b2c')->_('注册赠送积分.'),
                                                    'type' => 3,
                                                    'related_id' => '',
                                                ),											
                            'consume_gift' => array(
                                                    'describe' => app::get('b2c')->_('积分换赠品.'),
                                                    'type' => 3,
                                                    'related_id' => 'sdb_b2c_mall_orders',
                                                ),
                            'fire_event' => array(
                                                      'describe' => app::get('b2c')->_('网店机器人触发事件'),
                                                      'type' => 3,
                                                      'related_id' =>'',
                                                ),
                              'comment_discuss' => array(
                                                    'describe' => app::get('b2c')->_('商品评论获得积分'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_b2c_member_comments',
                                                ),
            );
			//扩展积分消费方式
		$obj_menu_point_reason = kernel::servicelist('b2c.member_point_reason');
        if ($obj_menu_point_reason)
        {
            foreach ($obj_menu_point_reason as $obj)
            {
                if (method_exists($obj, 'get_member_point_reason'))
                    $obj->get_member_point_reason($aHistoryReason);
            }
        }
        return $aHistoryReason;
    }   
    
    //检查用户积分是否足够
    function _chgPoint($userId, $nCheckPoint) {
        if ($nCheckPoint<0) {
            $nPoint = $this->getMemberPoint($userId);
            if ($nPoint >= abs($nCheckPoint)) {
                return true;
            }else{
                return false;
            }
        }else {
            return true;
        }
    }
    ##管理员扣除积分
    function adj_amount($nMemberId,$pointInfo,&$msg){
        $objMember = &$this->app->model('members');
        $row = $objMember->dump($nMemberId,'*');
        $falg = 1;
        if(!$pointInfo['modify_point']) return ;
        if($pointInfo['modify_point']) $change_point = $pointInfo['modify_point'];
        if($pointInfo['modify_point']<0){
            if(!($this->app->getConf('site.level_point'))){
                $falg = 0;
            }
            if($row['score']['total']<-$pointInfo['modify_point']){
                $msg =  app::get('b2c')->_("积分扣除超过会员已有积分");
                return false;
            }
        }
        $newValue = $row['score']['total'] + $pointInfo['modify_point'];
        $sdf_member = $objMember->dump($nMemberId,'*');
        $sdf_member['score']['total'] = $newValue;
        
        if(($this->app->getConf('site.level_switch') == 0) && $falg == 1){
        $sdf_member['member_lv']['member_group_id'] = $this->member_lv_chk($sdf_member['member_lv']['member_group_id'],$newValue);
        }
        $objMember->save($sdf_member);
        $point = $pointInfo['modify_point'];
        $reasons = $this->getHistoryReason();
        $reason = $reasons['operator_adjust'];
        $remark = $pointInfo['modify_remark'];
        $sdf_point = array(
                          'member_id'=>$nMemberId,
                          'point'=>$newValue,
                          'change_point'=>$change_point,
                          'addtime'=>time(),
                          'expiretime'=>time() ,
                          'reason'=>$reason['describe'],
                          'remark'=>$remark,
                          'type'=>$reason['related_id'],
                          'type'=>$reason['type'],
                          'operator'=>$operator
                                 );
        if($this->insert($sdf_point)){
            $msg = app::get('b2c')->_("修改成功");
            return true;
        }
        else{
            $msg = app::get('b2c')->_("修改失败");
            return false;
        }

    }
    
    ###通用改变积分方法
    function change_point($nMemberId,$point,&$msg,$reason_type,$type=0,$rel_id,$operator,$remark='pay'){
        if($this->app->getConf('site.get_policy.method') == 1) return true;
        // 判断积分是否已经重复修改.
        if(!is_numeric($point)||strpos($point,".")!==false){
            $msg = app::get('b2c')->_("请输入整数值");
            return false;
        }
		$reasons = $this->getHistoryReason();
		/** 检查是否并发-部分 **/
        $tmp = array();
		if ($rel_id){
			if ($point > 0 && $reason_type != 'operator_adjust')
			{
				$filter = array(
					'member_id' => $nMemberId,
					'related_id' => $rel_id,
					'type'=>$type,
				);
				$tmp = $this->getList('*', $filter);
			}
			
			if ($point < 0 && $reason_type == 'order_pay_use'){
				$filter = array(
					'member_id' => $nMemberId,
					'related_id' => $rel_id,
					'type'=>$type,
					'reason'=>$reasons[$reason_type],
				);
				$tmp = $this->getList('*', $filter);
			}
		}
        
        if ($tmp)
		{
			$msg = app::get('b2c')->_("修改成功");
			return true;
		}
		/** end **/
		$obj_change_point = kernel::service('b2c_change_member_point');
		$memo = ($msg) ? $msg : '';
		if (!$obj_change_point)
		{
			$objMember = &$this->app->model('members');
			$row = $objMember->dump($nMemberId,'*');
			if(!$row) return null;
			$falg = 1;
			if($point<0){
				/*
				if(!($this->app->getConf('site.level_point'))){
					$falg = 0;
				}*/
				if($row['score']['total']<-$point){
					$msg = app::get('b2c')->_("积分扣除超过会员已有积分");return false;
				}
				else
				{
					// 得到所有有效的可用积分记录
					$arr_point_historys = $this->get_usable_point($nMemberId);
					if ($arr_point_historys)
					{
						$discount_point = abs($point);
						foreach ($arr_point_historys as $arr_points)
						{
							// 已经消耗完的积分不在处理.
							if ($arr_points['change_point'] == $arr_points['consume_point'])
								continue;
								
							if ($arr_points['change_point'] >= $arr_points['consume_point'] + $discount_point)
							{
								$arr_points['consume_point'] = $arr_points['consume_point'] + $discount_point;
								$this->update($arr_points, array('id'=>$arr_points['id']));
								break;
							}
							else
							{
								$real_change_point = $arr_points['change_point'] - $arr_points['consume_point'];
								$arr_points['consume_point'] = $arr_points['change_point'];
								$discount_point = $discount_point - $real_change_point;
								$this->update($arr_points, array('id'=>$arr_points['id']));
							}
						}
					}
				}
			}
			if($point) $change_point = $point;
			$newValue = $row['score']['total'] + $point;
			$sdf_member = $objMember->dump($nMemberId,'*');
			$sdf_member['score']['total'] = $newValue;
			
		   // if(($this->app->getConf('site.level_switch')== 0) && $falg == 1){
				
		   // }
			$objMember->save($sdf_member);
			$reason = $reasons[$reason_type];
			$remark = $pointInfo['modify_remark'];
			$sdf_point = array(
				  'member_id'=>$nMemberId,
				  'point'=>$newValue,
				  'change_point'=>$change_point,
				  'addtime'=>time(),
				  'expiretime'=>'0',
				  'reason'=>$reason['describe'],
				  'type'=>$type,
				  'related_id'=>($rel_id) ? $rel_id : 0,
				  'remark'=>$memo ? $memo : '',
			 ); 
			 $arr_reason_types = array('operator_adjust', 'exchange_coupon');
			 if (in_array($reason_type, $arr_reason_types)){
				if ($this->insert($sdf_point)){
					$aMemberLv['member_lv_id'] = $this->member_lv_chk($nMemberId,$sdf_member['member_lv']['member_group_id'],$newValue);
					$memberFilter['member_id'] = $nMemberId;
					$objMember->update($aMemberLv,$memberFilter);
					$msg = app::get('b2c')->_("修改成功");
					return true;
				}else{
					$msg = app::get('b2c')->_("修改失败");
					return false;
				}
			 }else{
				if($this->replace($sdf_point,array('member_id'=>$sdf_point['member_id'],'related_id'=>$sdf_point['related_id'],'type'=>$sdf_point['type']))){
					$aMemberLv['member_lv_id'] = $this->member_lv_chk($nMemberId,$sdf_member['member_lv']['member_group_id'],$newValue);
					$memberFilter['member_id'] = $nMemberId;
					$objMember->update($aMemberLv,$memberFilter);
					$msg = app::get('b2c')->_("修改成功");
					return true;
				}
				else{
					$msg = app::get('b2c')->_("修改失败");
					return false;
				}
			}
		}
		else
		{
			// 接管积分修改过程
			return $obj_change_point->point_change_delegate($nMemberId,$point,$msg,$reason_type,$type,$rel_id,$operator,$remark);
		}
    }
    
    
    ###根据积分修改会员等级
    
    function member_lv_chk($nMemberId,$member_lv_id,$score){
        if($this->app->getConf('site.get_policy.method') == 1) return $member_lv_id;
        $current_member_lv_id = $member_lv_id;
        if(($this->app->getConf('site.level_switch')== 0)){
            if($this->app->getConf('site.point_promotion_method') == 1){
                $score = $this->get_total_cumulation($nMemberId);
            }else{
				$score = $this->get_total_count($nMemberId);
			}
            $objmember_lv = $this->app->model('member_lv');
            $sdf_lv = $objmember_lv->getList('*');
            foreach($sdf_lv as $sdf){
                if($score>=$sdf['point']) {
                    $member_lv_id = $sdf['member_lv_id'];
                }
                else{
                
                }
            }
            if($this->app->getConf('site.point_promotion_method') == 1){
                $current_row = $objmember_lv->getList('point',array('member_lv_id' => $current_member_lv_id));
                $after_row = $objmember_lv->getList('point',array('member_lv_id' => $member_lv_id));
                if($current_row[0]['point']>=$after_row[0]['point'])
                return $current_member_lv_id;
            }
            return $member_lv_id;
        }
        else return $member_lv_id;
    }
    
    
    /**
     * 得到所有有效的获得的积分
     * @param int member id
     * @return array - 有效积分记录
     */
    public function get_usable_point($member_id=0)
    {
        if (!$member_id)
            return array();    
        
        $arr_points = array();
		
		$expired_time = strtotime(date('Y-m-d'));        
        $sql = "SELECT * FROM " . $this->table_name(1) . " WHERE change_point > 0 AND (expiretime > " . $expired_time . " OR expiretime='0') AND member_id = " . $member_id . ' Order by addtime';
        $arr_points = $this->db->select($sql);
        $this->tidy_data($arr_points, '*');        
        
        return $arr_points;
    }
	
	/**
     * 获得当前用户的积分账面数
     * @param int member id
     * @return int 账面数
     */
    public function get_total_count($member_id=0)
    {
        if (!$member_id)
            return 0;
            
        return $this->get_real_history($member_id, '2');
    }
    
    /**
     * 获得积分当前的累积值
     * @param int member id
     * @return int 累积值
     */
    public function get_total_cumulation($member_id=0)
    {
        if (!$member_id)
            return 0;
            
        return $this->get_real_history($member_id);
    }
    
    /**
     * 取到有效的积分历史
     * @param int member id.
     * @param string type 2-积分账面数，1-积分累计值
     * @return int 积分值
     */
    private function get_real_history($member_id, $type='1')
    {
        $real_point = 0;
		$expired_time = strtotime(date('Y-m-d'));
		// 所有未过期的积分
		$sql = "SELECT * FROM " . $this->table_name(1) . " WHERE change_point > 0 AND (expiretime > " . $expired_time . " OR expiretime='0') AND member_id = " . $member_id;
		$rows_unexpired = $this->db->select($sql);
		$this->tidy_data($rows_unexpired, '*');
		if ($rows_unexpired)
		{
			foreach ($rows_unexpired as $arr_row)
			{
				if ($type == '1')
					$real_point += intval($arr_row['change_point']);
				if ($type == '2')
					$real_point += intval($arr_row['change_point'])-intval($arr_row['consume_point']);
			}
		}
        
        return $real_point;
    }
}
