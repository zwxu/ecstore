<?php



class b2c_api_ocs_1_0_sales_promotion
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
     * api 接口，增加促销规则
     * @param array sdf request data
     * @param mixed object service handle
     */
    public function add_rule(&$data,&$obj)
    {
        if(!(isset($data['type']) && $data['type']))
        {
            $obj->send_user_error('促销类型不能为空','');
        }

        if(!in_array($data['type'],array('goods','order')))
        {
            $obj->send_user_error('促销类型不正确','');
        }

        if(!(isset($data['id']) && $data['id']))
        {
            $obj->send_user_error('促销编号不能为空','');
        }

        if(!(isset($data['title']) && $data['title']))
        {
            $obj->send_user_error('促销名称不能为空','');
        }

        $db = &kernel::database();
        $table_name = "sdb_b2c_sales_rule_".$data['type'];
        $col_name = "outer_".$data['type']."_pmtid";
        if ($db->selectrow("SELECT mr.mr_id FROM sdb_dbeav_meta_register mr JOIN sdb_dbeav_meta_value_varchar mv ON mr.mr_id=mv.mr_id WHERE mr.tbl_name='".$table_name."' AND mr.col_name='".$col_name."' AND mv.value='".$data['id']."'"))
        {
            $obj->send_user_error('促销id已经存在','');
        }

        $createtime = time();
        if ($data['type'] == 'goods')
        {
            $sdf = array(
                'name'=>$data['title'],
                'from_time'=>strtotime($data['start_time']),
                'to_time'=>strtotime($data['end_time']),
                'description'=>$data['detail'],
                'status'=>'false',
                'conditions'=>array (
                    'type' => 'b2c_sales_goods_aggregator_combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'conditions' => array (
                        0 => array (
                            'type' => 'b2c_sales_goods_item_goods',
                            'attribute' => 'goods_goods_id',
                            'operator' => '>=',
                            'value' => '0',
                        ),
                    ),
                ),
                'stop_rules_processing'=>'false',
                'sort_order'=>50,
                'action_solution'=>array (
                    'gift_promotion_solutions_gift' => array (
                        'gain_gift' => '',
                    ),
                ),
                'c_template'=>'b2c_promotion_conditions_goods_allgoods',
                's_template'=>'gift_promotion_solutions_gift',
                'create_time'=>$createtime
            );
        }
        else{
            $sdf = array(
                'name'=>$data['title'],
                'from_time'=>strtotime($data['start_time']),
                'to_time'=>strtotime($data['end_time']),
                'description'=>$data['detail'],
                'status'=>'false',
                'conditions'=>array (
                    'type' => 'b2c_sales_order_aggregator_combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'conditions' => array (
                        0 => array (
                            'type' => 'b2c_sales_order_item_order',
                            'attribute' => 'order_subtotal',
                            'operator' => '>=',
                            'value' => '0',
                        ),
                    ),
                ),
                'action_conditions'=>array (
                    'type' => 'b2c_sales_order_aggregator_combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'conditions' => array (
                        0 => array (
                            'type' => 'b2c_sales_order_item_order',
                            'attribute' => 'order_subtotal',
                            'operator' => '>=',
                            'value' => '0',
                        ),
                    ),
                ),
                'stop_rules_processing'=>'false',
                'sort_order'=>50,
                'action_solution'=>array (
                    'gift_promotion_solutions_gift' => array (
                        'type' => 'order',
                        'gain_gift' => '',
                    ),
                ),
                'c_template'=>'b2c_promotion_conditions_order_allorderallgoods',
                's_template'=>'gift_promotion_solutions_gift',
                'create_time'=>$createtime
            );
        }

        if ($data['type'] == "goods")
        {
            $sdf['outer_goods_pmtid'] = $data['id'];
        }
        else
        {
            $sdf['outer_order_pmtid'] = $data['id'];
        }
        $obj_promotion = $this->app->model(substr($table_name,8));
        $obj_promotion->use_meta();

        if(!$obj_promotion->insert($sdf))
            $obj->send_user_error('数据库出错','');

        $returndata = array(
            'promotion_id'=>$data['id'],
        );
        return $returndata;
    }
}