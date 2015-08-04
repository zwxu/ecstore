<?php

 
class site_finder_route_static 
{
    public $addon_cols='static';

    public $column_preview='预览';
    public $column_preview_width='50';
    public function column_preview($row){
        return sprintf('<a href="%s" target="_blank">'.app::get('site')->_('预览').'</a>', app::get('site')->base_url(1) . $row[$this->col_prefix.'static']);
    }

    public $detail_edit = '编辑';
    public function detail_edit($id)
    {
        $render = app::get('site')->render();
        $render->pagedata['data'] = app::get('site')->model('route_statics')->select()->where('id = ?', $id)->instance()->fetch_row();
        return $render->fetch('/admin/route/static/edit.html');
    }//End Function

}//End Class