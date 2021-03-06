<?php

 
$db['rpcpoll']=array (
  'columns' => array (
        'id'=>array(
            'label'=>app::get('base')->_('序号'),
            'type'=>'varchar(32)',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'process_id'=>array(
            'label'=>app::get('base')->_('进程序号'),
            'type'=>'varchar(32)',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'type'=>array(
            'type'=>array(
                    'request'=>app::get('base')->_('发出请求'),
                    'response'=>app::get('base')->_('接收的请求'),
                ),
            'label'=>app::get('base')->_('类型'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'calltime'=>array(
            'type'=>'time',
            'label'=>app::get('base')->_('请求或被请求时间'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'network'=>array(
            'type'=>'table:network',
            'label'=>app::get('base')->_('连接节点名称'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'method'=>array(
            'type'=>'varchar(100)',
            'label'=>app::get('base')->_('同步的接口名称'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'params'=>array('type'=>'serialize'),
        'callback'=>array(
            'type'=>'varchar(200)',
            'label'=>app::get('base')->_('回调地址'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'callback_params'=>array('type'=>'text'),
        'result'=>array(
            'type'=>'text',
            'label'=>app::get('base')->_('请求响应的结果'),
            'in_list' => true,
            'default_in_list' => true,
         ),
        'fail_times'=>array(
            'type' => 'int(10)',
            'default' => 1,
            'required' => true,
            'label' => app::get('base')->_('失败的次数'),
            'filtertype' => 'number',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'status'=>array(
            'type' => 
              array (
                'succ' => app::get('base')->_('成功'),
                'failed' => app::get('base')->_('失败'),
              ),
              'default' => 'failed',
              'required' => true,
              'label' => app::get('base')->_('交互状态'),
              'editable' => false,
              'in_list' => true,
        ),
    ),
  'index' => 
  array (
    'ind_rpc_task_id' =>
    array (
        'columns' =>
        array (
          0 => 'id',
          1 => 'type',
		  2 => 'process_id',
        ),
        'prefix' => 'unique',
    ),
    'ind_rpc_response_id' =>
    array (
        'columns' =>array(
            0 => 'process_id',
        ),
        'type' => 'hash',
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
  'ignore_cache' => true,
);