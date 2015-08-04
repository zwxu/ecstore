<?php

 

// ־ģ
class b2c_mdl_order_log extends dbeav_model{

    /**
     * Ϊ
     * @params array - ׼
     * @return null
     */
    public function save(&$sdf)
    {
        //判断用户是买家还是买家
        if($sdf['op_id'] == '0'){
            $sdf['op_name'] = $sdf['op_name'].'(自动)';
        }else{
            $obj_model = app::get('b2c')->model('members');
            $obj_account = app::get('pam')->model('account');
            $seller = $obj_model->dump($sdf['op_id'],'seller');
            $seller_account = $obj_account->dump($sdf['op_id']);

            if($seller['seller'] == 'seller'){
                $sdf['op_name'] = $sdf['op_name'].'(卖家)';
            }else{
                if($seller_account['account_type'] == 'member'){
                    $sdf['op_name'] = $sdf['op_name'].'(买家)';
                }else{
                    $sdf['op_name'] = $sdf['op_name'].'(管理员)';
                }
                
            }
        }
        return parent::save($sdf);
    }
    
    /**
     * ı־Ĳid
     * @params string - id
     * @params string bill type
     * @params string behavior
     * @return boolean
     */
    public function changeResult($rel_id, $bill_type, $behavior='payments', $result='PROCESS')
    {
        $order_log = $this->dump(array('rel_id' => $rel_id, 'sign' => 'CURRENT', 'bill_type' => $bill_type, 'behavior' => $behavior));
        $order_log['result'] = $result;
        
        return $this->save($order_log);
    }
    
     /**
     * õָrel_id, bill_type, behaviorµһ־
     * @params string rel_id
     * @params string pay object
     * @params string behavior
     */
    public function get_latest_orderlist($rel_id, $pay_object, $behavior)
    {
        $sql = "SELECT * FROM ".$this->table_name(1)." WHERE rel_id='". intval($rel_id) . "' AND bill_type='" . $this->db->quote($pay_object) . "' AND behavior='" . $this->db->quote($behavior) . "' ORDER BY alttime DESC";
        
        return $this->db->selectrow($sql);
    }
    
    /**
     * getList
     * @params string - 
     * @params array - 
     * @params ƫʼֵ
     * @params ƫλֵ
     * @params 
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        if ($filter)
            return parent::getList($cols, $filter, $offset, $limit, $orderby);
        else
            return parent::getList($cols, null, $offset, $limit, $orderby);
    }
}
