<?php

class site_finder_seo
{
    var $detail_basic = '查看';
    function column_control($row){
        return '<a href="index.php?app=site&ctl=admin_seo&act=seoset&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['id'].'"  target="dialog::{frameable:true, title:\''.app::get('site')->_('添加菜单').'\', width:400, height:280}">'.app::get('site')->_('编辑').'</a>';
    }

    function detail_basic($id){
        $seo = app::get('site')->model('seo')->select()->where('id = ?', $id)->instance()->fetch_row();
        if(is_string($seo['param'])){
            $seo['param'] = unserialize($seo['param']);
        }
        if(is_string($seo['config'])){
            $seo['config'] = unserialize($seo['config']);
        }
        $render = app::get('site')->render();
        $render->pagedata['id'] = $id;
        $render->pagedata['param'] = $seo['param'];
        $render->pagedata['config'] = $seo['config'];//print_R($seo['config']);exit;
        return $render->fetch('admin/seo/base.html');
    }
}//End Class