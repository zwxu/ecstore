<?php

 
    $setting['auther'] = 'ql';
    $setting['name'] = app::get('groupbuy')->_('APP团购列表挂件');
    $setting['version']    = '1.0';
    $setting['catalog']    = app::get('groupbuy')->_('团购挂件');
    $setting['description']    = '';
    $setting['usual']    = '0';
    $setting['stime'] = '2013-7-20';
    $setting['template']=array(
        'default.html'=>app::get('groupbuy')->_('默认')
    );
?>
