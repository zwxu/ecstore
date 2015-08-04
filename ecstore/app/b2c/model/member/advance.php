<?php
class b2c_mdl_member_advance extends dbeav_model{

    /**
     * check_account 预存款检查
     *
     * @param mixed $member_id
     * @param mixed $errMsg
     * @param mixed $money
     * @access public
     * @return bool
     */
    function check_account($member_id,&$errMsg,$money){
        $objMember = &$this->app->model('members');
        $row= $objMember->dump($member_id,'advance');
        if($row){
            if(count($row)>0){
                if($money > $row['advance']['total']){
                    $errMsg .= app::get('b2c')->_('预存款帐户余额不足');
                    return 0;
                }else{
                    return $row;
                }
            }else{
                $errMsg .= app::get('b2c')->_('预存款帐户不存在');
                return false;
            }
        }else{
            $errMsg .= app::get('b2c')->_('查询预存款帐户失败');
            return false;
        }
    }
    /**
     * add 预存款充值
     *
     * @param mixed $member_id
     * @param mixed $money
     * @param mixed $message
     * @access public
     * @return void
     */
    function add($member_id,$money,$message,&$errMsg, $payment_id='', $order_id='' ,$paymenthod='' ,$memo='',$type=0,$is_frontend=true){
       if(!$member_id){
            $errMsg .= app::get('b2c')->_('更新预存款账户失败');
            return false;
       }
        if($money){
            $advance = $this->get($member_id);
            if($advance < 0){
                $errMsg .= app::get('b2c')->_('更新预存款账户失败');
                return false;
            }
            $adjmember = &$this->app->model('members');
            $adjmember->db->exec('UPDATE sdb_b2c_members SET advance = advance+'.$money.' where member_id='.$member_id);
            $member_advance = $this->get($member_id);
            $current_shop_advance = $this->get_shop_advance();
            $shop_advance = $current_shop_advance+$money;
            $data = array(
                    'member_id'=>$member_id,
                    'money'=>$money,
                    'message'=>$message,
                    'mtime'=>time(),
                    'payment_id'=>$payment_id,
                    'order_id'=>$order_id,
                    'paymethod'=>$paymenthod,
                    'memo'=>$memo,
                    'import_money'=>$money,
                    'explode_money'=>0,
                    'member_advance'=>$member_advance,
                    'shop_advance'=>$shop_advance,
            );
            if($this->save($data)){
                if (!$type){
                    $data['member_id']=$member_id;
                }

				/** 监听预存款变化 **/
				foreach(kernel::servicelist('member_advance_listener') as $service)
				{
					$arr_params = array(
						'member_id'=>$member_id,
						'doadd'=>true,
						'is_frontend'=>($is_frontend) ? true : false,
					);
					$service->listener_advance($arr_params);
				}
                return true;
            }else{
                $errMsg .= app::get('b2c')->_('更新预存款帐户失败');
                return false;
            }
        }else{
            $errMsg .= app::get('b2c')->_('更新预存款帐户失败');
            return false;
        }
    }


    /**
     * deduct 扣除预存款
     *
     * @param mixed $member_id
     * @param mixed $money
     * @param mixed $message
     * @access public
     * @return void
     */
    function deduct($member_id,$money,$message,&$errMsg, $payment_id='', $order_id='' ,$paymethod='' ,$memo='',$is_frontend=true){
        if(!$member_id){
            $errMsg .= app::get('b2c')->_('更新预存款账户失败');
            return false;
       }
        if($row = $this->check_account($member_id,$errMsg,$money)){
            $adjmember = &$this->app->model('members');
            $adjmember->db->exec('UPDATE sdb_b2c_members SET advance = advance-'.$money.' where member_id='.$member_id.' and advance >='.$money);
            $flag = $adjmember->db->affect_row();
            $member_advance = $this->get($member_id);
            $data['member_id'] = $member_id;
            if($member_advance < 0 || !$flag){
                $errMsg .= app::get('b2c')->_('更新预存款账户失败');
                return false;
            }
            $current_shop_advance = $this->get_shop_advance();
            $shop_advance = $current_shop_advance-$money;
            if($shop_advance < 0){
                $errMsg .= app::get('b2c')->_('更新预存款账户失败');
                return false;
            }
            $data = array(
                    'member_id'=>$member_id,
                    'money'=>$money,
                    'message'=>$message,
                    'mtime'=>time(),
                    'payment_id'=>$payment_id,
                    'order_id'=>$order_id,
                    'paymethod'=>$paymethod,
                    'memo'=>$memo,
                    'import_money'=>0,
                    'explode_money'=>$money,
                    'member_advance'=>$member_advance,
                    'shop_advance'=>$shop_advance,
            );
            if($this->save($data)){
				/** 监听预存款变化 **/
				foreach(kernel::servicelist('member_advance_listener') as $service)
				{
					$arr_params = array(
						'member_id'=>$member_id,
						'doadd'=>false,
						'is_frontend'=>($is_frontend) ? true : false,
					);
					$service->listener_advance($arr_params);
				}
                return true;
            }else{
                $errMsg .= app::get('b2c')->_('更新预存款帐户失败');
                return false;
            }
        }else{
            return false;
        }

    }

    /**
     * getListByMemId 取得现有预存款充值记录
     *
     * @param mixed $member_id
     * @access public
     * @return void
     */
    function get_list_bymemId($member_id){
        return $this->getList('*',array('member_id'=>$member_id));
    }
    /**
     * get 取得现有预存款
     *
     * @param mixed $member_id
     * @access public
     * @return void
     */
    function get($member_id){
        $member = &$this->app->model('members');
        $result = $member->dump($member_id);
        $advance=$result['advance']['total'];
        return $advance;
    }

    /**
     * get_shop_advance 取得现有预存款
     *
     * @param null
     * @access public
     * @return int
    */
    public function get_shop_advance(){
        $row = $this->getList('shop_advance',array(),0,-1,'log_id DESC');
        if(!$row[0]) return 0;
        return $row[0]['shop_advance'];
    }

    function adj_amount($nMemberId,$aAdvanceInfo,&$errMsg='',$is_frontend=true){
    	$user = kernel::single('desktop_user');
    	$username = $user->user_data['account']['login_name'];
        $advance = $aAdvanceInfo['modify_advance'];
        if(!$advance) return ;
        $memo = $aAdvanceInfo['modify_memo'];
        $operator = substr($advance,0,1);
        $operand = substr($advance,0);
        if($operator == '-' && is_numeric($operand) ){
            $message = $username.app::get('b2c')->_('管理员后台扣款');
            return $this->deduct($nMemberId,-$advance,$message,$errMsg, $payment_id='', $order_id='' ,$paymethod='' ,$memo,$is_frontend);
        }elseif(is_numeric($operand)){
        	$message = $username.app::get('b2c')->_('管理员代充值');
            return $this->add($nMemberId,$advance,$message,$errMsg, $payment_id='', $order_id='' ,$paymethod='' ,$memo,$type=0,$is_frontend);
        }
    }

    /**
     * fireEvent 触发事件
     *
     * @param mixed $event
     * @access public
     * @return void
     */
    function fireEvent($action , &$object, $member_id=0){
         $trigger = &$this->app->model('trigger');
         return $trigger->object_fire_event($action,$object, $member_id,$this);
    }
}
?>
