<?php

 
$datatypes = array(
    'money'=>array(
        'sql'=>'decimal(20,3)',
        'searchparams'=>array('than'=>app::get('base')->_('大于'),'lthan'=>app::get('base')->_('小于'),'nequal'=>app::get('base')->_('等于'),'sthan'=>app::get('base')->_('小于等于'),'bthan'=>app::get('base')->_('大于等于'),'between'=>app::get('base')->_('介于')),
        'match'=>'[0-9]{1,18}(\.[0-9]{1,3}|)',
    ),
    'email'=>array(
        'sql'=>'varchar(255)',
        'searchparams'=>array('has'=>app::get('base')->_('包含'),'tequal'=>app::get('base')->_('等于'),'head'=>app::get('base')->_('开头等于'),'foot'=>app::get('base')->_('结尾等于'),'nohas'=>app::get('base')->_('不包含')),
    ),
    'bn'=>array(
        'sql'=>'varchar(255)',
        'searchparams'=>array('has'=>app::get('base')->_('包含'),'tequal'=>app::get('base')->_('等于'),'nohas'=>app::get('base')->_('不包含')),
    ),
    'html'=>array(
        'sql'=>'text',
    ),
    'bool'=>array(
        'sql'=>'enum(\'true\',\'false\')',
        'searchparams'=>array('has'=>app::get('base')->_('包含'),'nohas'=>app::get('base')->_('不包含')),
    ),
    'time'=>array(
        'sql'=>'integer(10) unsigned',
        'searchparams'=>array('than'=>app::get('base')->_('晚于'),'lthan'=>app::get('base')->_('早于'),'nequal'=>app::get('base')->_('是'),'between'=>app::get('base')->_('介于')),
    ),
    'cdate'=>array(
        'sql'=>'integer(10) unsigned',
    ),
    'intbool'=>array(
        'sql'=>'enum(\'0\',\'1\')',
    ),
    'region'=>array(
        'sql'=>'varchar(255)',
    ),
    'password'=>array(
        'sql'=>'varchar(32)',
    ),
    'tinybool'=>array(
        'sql'=>'enum(\'Y\',\'N\')',
    ),
    'number'=>array(
        'sql'=>'mediumint unsigned',
        'searchparams'=>array('than'=>app::get('base')->_('大于'),'lthan'=>app::get('base')->_('小于'),'nequal'=>app::get('base')->_('等于'),'sthan'=>app::get('base')->_('小于等于'),'bthan'=>app::get('base')->_('大于等于'),'between'=>app::get('base')->_('介于')),
    ),
    'float'=>array(
        'sql'=>'float',
        'searchparams'=>array('than'=>app::get('base')->_('大于'),'lthan'=>app::get('base')->_('小于'),'nequal'=>app::get('base')->_('等于'),'sthan'=>app::get('base')->_('小于等于'),'bthan'=>app::get('base')->_('大于等于'),'between'=>app::get('base')->_('介于')),
    ),
    'gender'=>array(
        'sql'=>'enum(\'male\',\'female\')',
    ),
    'ipaddr'=>array(
        'sql'=>'varchar(20)',
    ),
    'serialize'=>array(
        'sql'=>'longtext',
    ),
    'last_modify'=>array(
        'sql'=>'integer(10) unsigned',
        'searchparams'=>array('than'=>app::get('base')->_('大于'),'lthan'=>app::get('base')->_('小于'),'nequal'=>app::get('base')->_('等于')),
    ),
);
