<?php


/*
 * @package site
 * @author edwin.lzh@gmail.com
 * @license 
 */
class site_ctl_admin_module extends site_admin_controller 
{
    /*
     * workground
     * @var string
     */
    var $workground = 'site.wrokground.theme';

    /*
     * 列表
     * @public
     */
    public function index() 
    {
        
        $this->finder('site_mdl_modules', array(
           'title' => app::get('site')->_('系统模块'),
           'base_filter' => array(),
            /*
           'actions'=>array(
                array(
                    'label' => app::get('site')->_('添加自定义模块'), 
                    'href' => 'index.php?app=site&ctl=admin_module&act=add', 
                    'target' => 'dialog::{frameable:true, title:\'添加自定义模块\', width:400, height:375}',
                ),
            ),
            */
        ));

    }//End Function

    /*
     * 添加模块
     * @public
     */
    public function add() 
    {
        $this->display('admin/module/edit.html');
    }//End Function

    /*
     * 保存模块
     * @public
     */
    public function save() 
    {
        $this->begin();
        $modules = $this->_request->get_post('modules');
        $modules['enable'] = ($modules['enable'] == 'true') ? 'true' : 'false';
        if(isset($modules['path'])) $this->check_path($modules['path'], $modules['id']);
        if($modules['id'] > 0){
            $id = $modules['id'];
            unset($modules['id']);
            if(app::get('site')->model('modules')->update($modules, array('id'=>$id))){
                $this->end(true, app::get('site')->_('更新成功'));
            }else{
                $this->end(false, app::get('site')->_('更新失败'));
            }
        }else{
            if(app::get('site')->model('modules')->insert($modules)){
                $this->end(true,  app::get('site')->_('添加成功'));
            }else{
                $this->end(false,  app::get('site')->_('添加失败'));
            }
        }
    }//End Function

    private function check_path($path, $id=0) 
    {
        $tmp = preg_replace("/[^0-9a-zA-Z]/isU", "", $path);
        if($path != $tmp){
            $this->end(false,  app::get('site')->_('路径标识只能由字母和数字组成'));
        }
        $obj = app::get('site')->model('modules')->select()->where('path = ?', $path);
        if($id > 0){
            $obj->where('id != ?', $id);
        }
        if($obj->instance()->fetch_one()){
            $this->end(false, app::get('site')->_('路径标识不得重复'));
        }
    }//End Function

}//End Class
