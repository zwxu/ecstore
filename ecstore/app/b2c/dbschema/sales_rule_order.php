<?php 
 
$db['sales_rule_order'] = array( 
    'columns' =>
    array (
        'rule_id' =>
        array (
            'type' => 'int(8)', 
            'required' => true,
            'pkey' => true,
            'label' => app::get('b2c')->_('规则id'),
            'editable' => false,
            'extra' => 'auto_increment',
            'in_list' => false, 
            ),
        'name' =>
        array (
            'type' => 'varchar(255)',
            'required' => true,
            'default' => '',
            'label' => app::get('b2c')->_('规则名称'),
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'is_title' => true,
            ),
        'description' =>
        array (
            'type' => 'text',
            'label' => app::get('b2c')->_('规则描述'),
            'required' => false,
            'default' => '',
            'editable' => false,
            'in_list' => true,
            'filterdefault'=>true,
            ),
        'from_time' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('起始时间'),
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            ),
        'to_time' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('截止时间'),
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' =>false,
            'filterdefault'=>true,
            ),
        'member_lv_ids' =>
        array (
            'type' => 'varchar(255)',
            'default' => '',
            'required' => true,
            'label' => app::get('b2c')->_('会员级别集合'),
            'editable' => false,
            ),
        'status' =>
        array (
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('b2c')->_('开启状态'),
            'in_list' => true,
            'editable' => false,
            'filterdefault'=>true,
            'default_in_list' => true,
            ),
        'conditions' =>
        array (
            'type' => 'serialize',
            'default' => '',
            'required' => true,
            'label' => app::get('b2c')->_('规则条件'),
            'editable' => false,
            ),
        'action_conditions' =>
        array (
            'type' => 'serialize',
            'default' => '',
            'label' => app::get('b2c')->_('动作执行条件'),
            'editable' => false,
            ),

        'store_id' =>
        array (
            'type' => 'varchar(255)',
            'default' => '',
            'label' => app::get('b2c')->_('指定店铺'),
            'editable' => false,
            ),
        'stop_rules_processing' =>
        array (
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('b2c')->_('是否排斥'),
            'editable' => true,
            'filterdefault'=>true,
            'in_list' => true,
            'default_in_list' => true,
            ),
        'sort_order' =>
        array (
            'type' => 'int(10) unsigned',
            'default' => '0',
            'required' => true,
            'label' => app::get('b2c')->_('优先级'),
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            ),
        'action_solution' =>
        array (
            'type' => 'serialize',
            'default' => '',
            'required' => true,
            'label' => app::get('b2c')->_('动作方案'),
            'editable' => false,
            ),
        'free_shipping' =>
        array(
            'type' =>array(
                    0=>app::get('b2c')->_('免运费'),
                    1=>app::get('b2c')->_('满足过滤条件的商品免运费'),
                    2=>app::get('b2c')->_('全场免运费')
             ),
            'default' => '0',
            'label' => app::get('b2c')->_('免运费'),
            'editable' => false,
            'filterdefault'=>true,
            'in_list' => false,
            ),
       'rule_type' =>
            array (
            'type' => array (
                'N' => app::get('b2c')->_('普通规则'),
                'C' => app::get('b2c')->_('优惠券规则'),
            ),
            'default' => 'N',
            'required' => true,
            'editable' => false,
            ),
        'c_template' =>
        array(
            'type' => 'varchar(100)',
            'label' => app::get('b2c')->_('过滤条件模板'),
            'editable' => false,
            ),
        's_template' =>
        array(
            'type' => 'varchar(255)',
            'label' => app::get('b2c')->_('优惠方案模板'),
            'editable' => false,
            ),
        ),
    'comment' => app::get('b2c')->_('订单促销规则'),
    );
