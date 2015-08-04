<?php


class site_ctl_admin_route_static extends site_admin_controller
{

    function __construct(&$app)
    {
        parent::__construct($app);
        $this->statics_route_limit = 100;
    }//End Function

    public function index()
    {
        $this->finder('site_mdl_route_statics', array(
            'title' => app::get('site')->_('自定义URL'),
            'base_filter' => array(),
            'actions'=>array(
                array(
                    'label' => app::get('site')->_('添加规则'),
                    'href' => 'index.php?app=site&ctl=admin_route_static&act=add',
                    'target' => 'dialog::{frameable:true, title:\''.app::get('site')->_('添加规则').'\', width:537, height:200}',
                ),
            ),
        ));
    }//End Function

    public function add()
    {
        $this->pagedata['close_win'] = 1;
        $this->page('admin/route/static/edit.html');
    }//End Function

    public function save()
    {
        $statics = $this->_request->get_post('statics');
        $this->begin();
        $count = app::get('site')->model('route_statics')->count();
        if($count >= $this->statics_route_limit){
            $this->end(false, app::get('site')->_('静态路由最高设置为'.$this->statics_route_limit.'条，请调整现有路由设置'));
        }
        if($row = app::get('site')->model('route_statics')->has_static($statics['static'])){
            if($row['id']!=$statics['id']) $this->end(false, app::get('site')->_('静态规则已经存在'));
        }
        if($row = app::get('site')->model('route_statics')->has_url($statics['url'])){
            if($row['id']!=$statics['id']) $this->end(false, app::get('site')->_('目标链接已经存在'));
        }
        if($statics['id'] > 0){
            $id = $statics['id'];
            unset($statics['id']);
            if(app::get('site')->model('route_statics')->update($statics, array('id'=>$id))){
                $this->end(true, app::get('site')->_('保存成功'));
            }else{
                $this->end(false, app::get('site')->_('保存失败'));
            }
        }else{
            if(app::get('site')->model('route_statics')->insert($statics)){
                $this->end(true, app::get('site')->_('添加成功'));
            }else{
                $this->end(false, app::get('site')->_('添加失败'));
            }
        }
    }//End Function

}//End Class