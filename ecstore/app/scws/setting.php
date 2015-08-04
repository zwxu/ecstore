<?php

 

$setting = array(
    'dict'=>array(
        'type'=>SET_T_STR, 'default'=>APP_DIR.'/scws/xdb/dict.utf8.xdb', 'desc'=>'分词字典',
    ),
    'rule'=>array(
        'type'=>SET_T_STR, 'default'=>APP_DIR.'/scws/rule/rules.utf8.ini', 'desc'=>'分词规则',
    ),
);
