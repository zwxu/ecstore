<?php 
 
//商品与商品促销规则关联表
$db['goods_promotion_ref'] = array(
    'columns' =>
    array (
        'ref_id' =>
        array (
            'type' => 'int(8)',
            'required' => true,
            'pkey' => true,
            'label' => 'id',
            'editable' => false,
            'extra' => 'auto_increment',
            ),
          'goods_id' =>
         array (
            'type' => 'table:goods',
            'default' => 0,
            'required' => true,
            'editable' => false,
         ),
         'rule_id' =>
         array (
            'type' => 'table:sales_rule_goods',
            'default' => 0,
            'required' => true,
            'editable' => false,
         ),
        'description' =>
        array (
            'type' => 'text',
            'label' => app::get('b2c')->_('规则描述'),
            'required' => false,
            'default' => '',
            'editable' => false,
            'in_list' => true,
            ),
        'member_lv_ids' =>
        array (
            'type' => 'varchar(255)',
            'default' => '',
            'required' => false,
            'label' => app::get('b2c')->_('会员级别集合'),
            'editable' => false,
            ),
        'from_time' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('起始时间'),
            'editable' => true,
            'in_list' => true,
            'default'=> 0,
            'default_in_list' => true,
            ),
        'to_time' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('截止时间'),
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            ),
       //预定字段
        'status' =>
        array (
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('b2c')->_('状态'),
            'in_list' => true,
            'editable' => false,
            ),
        'stop_rules_processing' =>
        array (
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('b2c')->_('是否排斥其他规则'),
            'editable' => true,
            ),
        'sort_order' =>
        array (
            'type' => 'int(10) unsigned',
            'default' => '0',
            'required' => true,
            'label' => app::get('b2c')->_('优先级'),
            'editable' => true,
            ),
        'action_solution' =>
        array (
            'type' => 'text',
            'default' => '',
            'required' => true,
            'label' => app::get('b2c')->_('动作方案'),
            'editable' => false,
            ),
        'free_shipping' =>
        array(
            'type' => 'tinyint(1) unsigned',
            'default' => '0',
            'label' => app::get('b2c')->_('免运费'),
            'editable' => false,
            ),
        ),
    'comment' => app::get('b2c')->_('商品与商品促销规则'),
    );
