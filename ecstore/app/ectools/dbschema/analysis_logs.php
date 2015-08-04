<?php 

 
$db['analysis_logs']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'analysis_id' => 
    array (
      'type' => 'number',
      'required' => true,
    ),
    'type' => 
    array (
      'type' => 'number',
      'required' => true,
      'label' => app::get('ectools')->_('类型'),
      'default' => 0,
    ),
    'target' => 
    array (
      'type' => 'number',
      'required' => true,
      'label' => app::get('ectools')->_('指标'),
      'default' => 0,
    ),
    'flag' => 
    array (
      'type' => 'number',
      'required' => true,
      'label' => app::get('ectools')->_('标识'),
      'default' => 0,
    ),
    'value' => 
    array (
      'type' => 'float',
      'required' => true,
      'label' => app::get('ectools')->_('数据'),
      'default' => 0,
    ),
    'time' => 
    array (
      'type' => 'time',
      'required' => true,
      'label' => app::get('ectools')->_('时间'),
    ),
  ),
  'index' => 
      array (
        'ind_analysis_id' => 
        array (
          'columns' => 
          array (
            0 => 'analysis_id',
          ),
        ),
        'ind_type' => 
        array (
          'columns' => 
          array (
            0 => 'type',
          ),
        ),
        'ind_target' => 
        array (
          'columns' => 
          array (
            0 => 'target',
          ),
        ),
        'ind_time' => 
        array (
          'columns' => 
          array (
            0 => 'time',
          ),
        ),
    ),
  'ignore_cache' => true,
);

