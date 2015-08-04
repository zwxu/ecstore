<?php


/*
 * @package site
 * @author edwin.lzh@gmail.com
 * @license 
 */
class site_ctl_admin_menu extends site_admin_controller 
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
        $this->finder('site_mdl_menus', array(
            'title' => app::get('site')->_('导航菜单'),
            'base_filter' => array(),
            'actions'=>array(
                array(
                    'label' => app::get('site')->_('添加菜单'), 
                    'href' => 'index.php?app=site&ctl=admin_menu&act=add', 
                    'target' => 'dialog::{frameable:true, title:\''. app::get('site')->_('添加菜单').'\', width:400, height:400}',
                ),
            ),
        ));
    }//End Function
    
    /*
     * 添加菜单
     * @public
     */
    public function add() 
    {
        $step = $this->_request->get_get('step');
        switch($step)
        {
            case 3:
                $module = $this->_request->get_get('module');
                if(empty($module))  $this->_error();
                $args = explode(':', $module);
                if(count($args) != 3)   $this->_error();
                $args = array_combine(array('app', 'ctl', 'act'), $args);
                $obj = kernel::service('site_menu.' . sprintf('%s_%s_%s', $args['app'], $args['ctl'], $args['act']));
                $this->pagedata['is_goods_cat'] = array('true'=>'是','false'=>'否');
                if($obj instanceof site_interface_menu){
                    foreach($obj->inputs() as $title=>$input){
                        $tmp['title'] = $title;
                        $tmp['input'] = $this->ui()->input($input);
                        $html[] = $tmp;
                    }
                    $this->pagedata['html'] = $html;
                    $this->pagedata['menu'] = $args;
                    $this->display('admin/menu/edit_app_module.html');
                }else{
                    $this->pagedata['menu'] = $args;
                    $this->display('admin/menu/edit_module.html');
                }                
            break;
            case 2:
                $type = $this->_request->get_get('type');
                if($type == 'module'){
                    $this->pagedata['menus'] = $this->get_module_menus();
                    $this->display('admin/menu/add_module.html');
                }else{
                    $this->pagedata['filter'] = array('parent_id'=>'0');
                    $this->pagedata['is_goods_cat'] = array('true'=>'是','false'=>'否');
                    $this->display('admin/menu/edit_url.html');
                }
            break;
            default:
                $this->pagedata['pre'] = $this->_request->get_get('pre');
                $this->display('admin/menu/add_step_1.html');
        }//End Switch
    }//End Function

    /*
     * 保存APP模块
     * @public
     */
    public function saveappmodule() 
    {
        $this->begin('index.php?app=site&ctl=admin_menu&act=index');
        $get_menu = $this->_request->get_get('menu');
        $id = $get_menu['id'];
        $app = $get_menu['app'];
        $ctl = $get_menu['ctl'];
        $act = $get_menu['act'];

        $obj = kernel::service('site_menu.' . sprintf('%s_%s_%s', $app, $ctl, $act));
        if($obj instanceof site_interface_menu){
            $menu = $this->_request->get_post('menu');
            if(empty($menu['title']))   $this->_error();
            $obj->handle($this->_request->get_post());
            $params = $obj->get_params();
            $config = $obj->get_config();
            $data = array(
                'title' => $menu['title'],
                'app' => $app,
                'ctl' => $ctl,
                'act' => $act,
                'display_order' => ((is_numeric($menu['display_order']) && $menu['display_order'] > 0) ? $menu['display_order'] : 0),
                'hidden' => (($menu['hidden'] == 'true') ? 'true' : 'false'),
                'target_blank' => (($menu['target_blank'] == 'true') ? 'true' : 'false'),
                'params' => $params,
                'config' => $config
            );
            if($id > 0){
                if(app::get('site')->model('menus')->update($data, array('id'=>$id))){
                    $this->end(true, app::get('site')->_('保存成功'));
                }else{
                    $this->end(false, app::get('site')->_('保存失败'));
                }
            }else{
                if(app::get('site')->model('menus')->insert($data)){
                    $this->end(true, app::get('site')->_('添加成功'));
                }else{
                    $this->end(false, app::get('site')->_('添加失败'));
                }
            }
        }else{
            $this->_error();
        }
    }//End Function

    /*
     * 保存普通模块
     * @public
     */
    public function savemodule() 
    {   
        $this->begin('index.php?app=site&ctl=admin_menu&act=index');
        $menu = $this->_request->get_post('menu');
        if(empty($menu) || empty($menu['app']) || empty($menu['ctl']) || empty($menu['act']) || empty($menu['title']))    $this->_error();
        $data = array(
            'title' => $menu['title'],
            'app' => $menu['app'],
            'ctl' => $menu['ctl'],
            'act' => $menu['act'],
            'display_order' => ((is_numeric($menu['display_order']) && $menu['display_order'] > 0) ? $menu['display_order'] : 0),
            'hidden' => (($menu['hidden'] == 'true') ? 'true' : 'false'),
            'target_blank' => (($menu['target_blank'] == 'true') ? 'true' : 'false')
        );
        if($menu['id'] > 0){
            if(app::get('site')->model('menus')->update($data, array('id'=>intval($menu['id'])))){
                $this->end(true, app::get('site')->_('保存成功'));
            }else{
                $this->end(false, app::get('site')->_('保存失败'));
            }
        }else{
            if(app::get('site')->model('menus')->insert($data)){
                $this->end(true, app::get('site')->_('添加成功'));
            }else{
                $this->end(false,  app::get('site')->_('添加失败'));
            }            
        }
    }//End Function

    /*
     * 保存自定义url
     * @public
     */
    public function saveurl() 
    {
        $this->begin('index.php?app=site&ctl=admin_menu&act=index');
        $menu = $this->_request->get_post('menu');
        //print_r($menu);exit;
        if(empty($menu) || empty($menu['title']) || empty($menu['custom_url']))    $this->_error();
        $data = array(
            'title' => $menu['title'],
            'custom_url' => $menu['custom_url'],
            'display_order' => ((is_numeric($menu['display_order']) && $menu['display_order'] > 0) ? $menu['display_order'] : 0),
            'hidden' => (($menu['hidden'] == 'true') ? 'true' : 'false'),
            'target_blank' => (($menu['target_blank'] == 'true') ? 'true' : 'false'),
            'is_goods_cat' => (($menu['is_goods_cat'] == 'true') ? 'true' : 'false'),
            'cat_id' => (($menu['is_goods_cat'] == 'true') ? $menu['cat_id'] : '0'),
        );
        if($menu['id'] > 0){
            if(app::get('site')->model('menus')->update($data, array('id'=>intval($menu['id'])))){
                $this->end(true, app::get('site')->_('保存成功'));
            }else{
                $this->end(false, app::get('site')->_('保存失败'));
            }
        }else{
            if(app::get('site')->model('menus')->insert($data)){
                $this->end(true, app::get('site')->_('添加成功'));
            }else{
                $this->end(false, app::get('site')->_('添加失败'));
            }            
        }
    }//End Function

    /*
     * 保存hidden状态
     * @public
     */
    public function savehidden() 
    {
        $this->begin('index.php?app=site&ctl=admin_menu&act=index');
        $menu_id = $this->_request->get_get('menu_id');
        $menu = $this->_request->get_post('menu');
        $hidden = ($menu['hidden']=='true') ? 'true' : 'false';
        if($menu_id > 0){
            if(app::get('site')->model('menus')->update(array('hidden'=>$hidden), array('id'=>intval($menu_id)))){
                $this->end(true, app::get('site')->_('保存成功'));
            }else{
                $this->end(false, app::get('site')->_('保存失败'));
            }
        }
        $this->end(false, app::get('site')->_('错误的参数'));
    }//End Function

    /*
     * 取得模块菜单信息
     * @private
     */
    private function get_module_menus() 
    {
        $menus = array();
        $app_module = app::get('site')->model('modules')->getList('*');
        if(is_array($app_module)){
            foreach($app_module AS $module){
                $tmp = array();
                if(empty($module['allow_menus']))   continue;
                $tmp['title'] = $module['title'];
                $tmp['app'] = $module['app'];
                $tmp['ctl'] = $module['ctl'];
                $allows = explode('|', $module['allow_menus']);
                foreach($allows AS $allow){
                    $tmp['allow'][] = array('act'=>substr($allow, 0, strpos($allow, ':')), 'title'=>substr($allow, strpos($allow, ':')+1));
                }
                $menus[] = $tmp;
            }
        }
        return $menus;
    }//End Function

    public function detail_edit($id){

        $menu = app::get('site')->model('menus')->select()->where('id = ?', $id)->instance()->fetch_row();

        $render = app::get('site')->render();
        $this->pagedata['filter'] = array('parent_id'=>'0');
        $this->pagedata['is_goods_cat'] = array('true'=>'是','false'=>'否');
        if($menu['is_native']=='true'){
            $render->pagedata['menu'] = $menu;
            echo $render->fetch('admin/menu/edit_native.html');
        }elseif(empty($menu['app'])){
            $render->pagedata['menu'] = $menu;
            echo $render->fetch('admin/menu/edit_url.html');
        }else{
            $obj = kernel::service('site_menu.' . sprintf('%s_%s_%s', $menu['app'], $menu['ctl'], $menu['act']));
            if($obj){
                $config = $menu['config'];
                foreach($obj->inputs($config) as $title=>$input){
                    $tmp['title'] = $title;
                    $tmp['input'] = $render->ui()->input($input);
                    $html[] = $tmp;
                }
                $render->pagedata['menu'] = $menu;
                $render->pagedata['html'] = $html;
                echo $render->fetch('admin/menu/edit_app_module.html');
            }else{
                $render->pagedata['menu'] = $menu;
                echo $render->fetch('admin/menu/edit_module.html');
            }
        }
    }
}//End Class
