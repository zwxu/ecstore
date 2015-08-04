<?php


class site_finder_modules
{
    
    public $detail_edit = '编辑';
    public function detail_edit($id){

        $modules = app::get('site')->model('modules')->select()->where('id = ?', $id)->instance()->fetch_row();

        if($modules['is_native'] == 'true'){
            $render = app::get('site')->render();
            $render->pagedata['modules'] = $modules;
            return $render->fetch('admin/module/edit_native.html');
        }else{
            $render = app::get('site')->render();
            $render->pagedata['modules'] = $modules;
            return $render->fetch('admin/module/edit.html');
        }
    }
}//End Class
