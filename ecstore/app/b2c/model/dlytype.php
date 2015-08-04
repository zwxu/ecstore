<?php

 

class b2c_mdl_dlytype extends dbeav_model{
    var $has_many = array(
        
    );


    public function getRegionById($parent_id){
        $sql='select r.region_id,r.p_region_id,r.local_name,count(p.region_id) as child_count from sdb_b2c_regions as r
                left join sdb_b2c_regions as p on r.region_id=p.p_region_id
                where r.p_region_id'.($parent_id?('='.intval($parent_id)):' is null').' and r.package=\''.$this->db->quote($this->app->getConf('system.location')).'\'
                group by(r.region_id)
                order by r.ordernum asc,r.region_id';

        return $this->db->select($sql);
    }
    
    public function save(&$data,$mustUpdate = null){
        if (!isset($data['dt_discount']) || !$data['dt_discount'])
            $data['dt_discount'] = '1.00';
                
        if($data['dt_useexp']==0)
        {
            //如果未使用公式则使用默认        
            $data['dt_expressions'] = "{{w-0}-0.4}*{{{".$data['firstunit']."-w}-0.4}+1}*fp*" . $data['dt_discount'] . "+ {{w-".$data['firstunit']."}-0.6}*[(w-".$data['firstunit'].")/".$data['continueunit']."]*cp*".$data['dt_discount']."";
        }
        $tmp_threshold = array();
        if ($data['is_threshold'])
        {
            if ($data['threshold'] && is_array($data['threshold']))
            {
                foreach ($data['threshold'] as $key=>$thres)
                {
                    if ($key-1 < 0)
                    {
                        $tmp_threshold[] = array(
                            'area'=>array(0,$thres),
                            'first_price'=>$data['firstprice'] ? $data['firstprice'] : 0,
                            'continue_price'=>$data['continueprice'] ? $data['continueprice'] : 0,
                        );
                        if ($key+1 > count($data['threshold'])-1)
                        {
                            $tmp_threshold[] = array(
                                'area'=>array($thres,0),
                                'first_price'=>$data['after_firstunit'][$key] ? $data['after_firstunit'][$key] : 0,
                                'continue_price'=>$data['after_continueunit'][$key] ? $data['after_continueunit'][$key] : 0,
                            );
                        }
                        else
                        {
                            $tmp_threshold[] = array(
                                'area'=>array($thres,$data['threshold'][$key+1]),
                                'first_price'=>$data['after_firstunit'][$key] ? $data['after_firstunit'][$key] : 0,
                                'continue_price'=>$data['after_continueunit'][$key] ? $data['after_continueunit'][$key] : 0,
                            );
                        }
                    }
                    elseif ($key+1 > count($data['threshold'])-1)
                        $tmp_threshold[] = array(
                            'area'=>array($thres,0),
                            'first_price'=>$data['after_firstunit'][$key] ? $data['after_firstunit'][$key] : 0,
                            'continue_price'=>$data['after_continueunit'][$key] ? $data['after_continueunit'][$key] : 0,
                        );
                    else
                        $tmp_threshold[] = array(
                            'area'=>array($thres,$data['threshold'][$key+1]),
                            'first_price'=>$data['after_firstunit'][$key] ? $data['after_firstunit'][$key] : 0,
                            'continue_price'=>$data['after_continueunit'][$key] ? $data['after_continueunit'][$key] : 0,
                        );
                }
            }
        }
        $data['threshold'] = $tmp_threshold;
        
        if ($data['protect']) 
            $data['protect_rate'] = $data['protect_rate']/100;
        
        $data['ordernum'] = intval($data['ordernum']);
        
        if($data['area_fee_conf'] && is_array($data['area_fee_conf']))
        {
            foreach ($data['area_fee_conf'] as $key=>$value)
            {
                if ($value['dt_useexp']==0)
                {
                    //如果未使用公式则使用默认
                    if (!isset($value['dt_discount']) || !$value['dt_discount'])
                        $value['dt_discount'] = '1.00';
                    $data['area_fee_conf'][$key]['dt_expressions'] = "{{w-0}-0.4}*{{{".$data['firstunit']."-w}-0.4}+1}*fp*" . $value['dt_discount'] . "+ {{w-".$data['firstunit']."}-0.6}*[(w-".$data['firstunit'].")/".$data['continueunit']."]*cp*".$value['dt_discount']."";
                }
                else
                {
                    $data['area_fee_conf'][$key]['dt_expressions'] = $data['area_fee_conf'][$key]['expressions'];
                }
            }
        }
        
        $return = parent::save($data,$mustUpdate);
        
        return $return;
    }
    
    public function get_shiping_info($shipping_id=0, $cost_item=0)
    {
        if (!$shipping_id)
            return array();
        
        $tmp = $this->getList('*', array('dt_id'=>$shipping_id));
        if ($tmp)
        {
            $arr_dlytype = $tmp[0];
            if ($arr_dlytype['is_threshold'])
            {
                if ($arr_dlytype['threshold'])
                {
                    $arr_dlytype['threshold'] = unserialize(stripslashes($arr_dlytype['threshold']));
                    if (isset($arr_dlytype['threshold']) && $arr_dlytype['threshold'])
                    {
                        foreach ($arr_dlytype['threshold'] as $res)
                        {
                            if ($res['area'][1] > 0)
                            {
                                if ($cost_item >= $res['area'][0] && $cost_item < $res['area'][1])
                                {
                                    $arr_dlytype['firstprice'] = $res['first_price'];
                                    $arr_dlytype['continueprice'] = $res['continue_price'];
                                }
                            }
                            else
                            {
                                if ($cost_item >= $res['area'][0])
                                {
                                    $arr_dlytype['firstprice'] = $res['first_price'];
                                    $arr_dlytype['continueprice'] = $res['continue_price'];
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $arr_dlytype;
    }
    
    /**
     * 判断是否有货到付款的支付方式
     * @param null
     * @return null
     */
    public function shipping_has_cod()
    {
        $dlytype_list = $this->getList('*',array('dt_status'=>'1'));
        if ($dlytype_list)
        {
            foreach ($dlytype_list as $dlytype_info)
            {
                if ($dlytype_info['has_cod'] == 'true')
                    return true;
            }
        }
        return false;
    }
    
    public function get_shipping_name($shipping_id=0)
    {
        if (!$shipping_id)
            return '-';
        
        $tmp = $this->getList('*', array('dt_id'=>$shipping_id));
        if ($tmp)
            $arr_dlytype = $tmp[0];
        
        return $arr_dlytype['dt_name'];
    }
}
