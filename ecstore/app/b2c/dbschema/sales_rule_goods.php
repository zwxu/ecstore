<?php 
 
//商品促销规则表
$db['sales_rule_goods'] = array(
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
        'create_time' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('修改时间'),
            'editable' => true,
            'in_list' => true,
            'default_in_list' => false,
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
            'default_in_list' => false,
            'filterdefault'=>true,
            ),
        'member_lv_ids' =>
        array (
            'type' => 'varchar(255)',
            'default' => '',
            'required' => false,
            'label' => app::get('b2c')->_('会员级别集合'),
            'editable' => false,
            ),
            //status 标志是否使用该规则执行预过滤
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
        'stop_rules_processing' =>
        array (
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('b2c')->_('是否排斥'),
            'in_list' => true,
            'editable' => true,
            'filterdefault'=>true,
            'default_in_list' => true,
            ),
        'sort_order' =>
        array (
            'type' => 'int(10) unsigned',
            'default' => '0',
            'required' => true,
            'label' => app::get('b2c')->_('优先级'),
            'in_list' => true,
            'editable' => true,
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
            'type' => 'tinyint(1) unsigned',
            'default' => '0',
            'label' => app::get('b2c')->_('免运费'),
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
            'type' => 'varchar(100)',
            'label' => app::get('b2c')->_('优惠方案模板'),
            'editable' => false,
            ),
        'apply_time' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('预过滤时间'),
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            ),
        ),
    'comment' => app::get('b2c')->_('商品促销规则'),
    );
