<?php

 
    $setting['auther'] = 'BBC';
    $setting['name'] = app::get('content')->_('站点栏目及文章');
    $setting['version']    = '2.0';
    $setting['catalog']    = app::get('content')->_('文章挂件');
    $setting['description']    = '';
    $setting['usual']    = '0';
    $setting['stime'] = '2012-8-14';
    $setting['template']=array(
        'default.html'=>app::get('content')->_('默认')
    );
    $setting['limit'] = 5;          //节点下显文章数
    $setting['lv'] = 3;             //深度
    $setting['styleart'] = 0;       //文章样式统一
    $setting['shownode'] = 1;       //是否显示节点名称
    $setting['node_id']  = 1;       //默认节点
    $selectmaps = kernel::single('content_article_node')->get_selectmaps();
    array_unshift($selectmaps, array('node_id'=>0, 'step'=>1, 'node_name'=>app::get('content')->_('---无---')));
    $setting['selectmaps'] = $selectmaps;
    $setting['select_order']['order_type'] = array('pubtime'=>'发布时间');
    $setting['select_order']['order'] = array('asc'=>'升序','desc'=>'降序');
    $setting['showuptime'] = 0; //是否显示文章最后更新时间
    
    //print_r($setting);//exit;
?>
