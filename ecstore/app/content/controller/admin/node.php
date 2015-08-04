<?php

/*
 * @package content
 * @subpackage article
 * @author edwin.lzh@gmail.com
 * @license
 */
class content_ctl_admin_node extends site_admin_controller
{
    /*
     * workground
     * @var string
     */
    var $workground = 'site.wrokground.theme';

    /*
     * index
     */


    public function __construct($app){
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    public function index()
    {
        $aList = kernel::single('content_article_node')->get_listmaps();
        if(is_array($aList)) {
            $obj = app::get('site')->router();
            foreach($aList as &$row) {
                if($row['homepage']!='true') {
                    $row['url'] = $obj->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'l', 'arg0'=>$row['node_id']));
                } else {
                    $row['url'] = $obj->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'i', 'arg0'=>$row['node_id']));
                }
            }
        }

        $this->pagedata['list'] = $aList;
        $this->pagedata['tree_number'] = (is_array($this->pagedata['list'])) ? count($this->pagedata['list']) : 0;
        $this->page("admin/node/index.html");
    }//End Function

    /*
     * 添加节点
     */
    public function add()
    {
        $parent_id = $this->_request->get_get('parent_id');
        $homepage =  $this->_request->get_get('node');
        $this->pagedata['node'] = array('parent_id'=>$parent_id, 'ordernum'=>0);
        if($homepage) $this->pagedata['node']['homepage'] = $homepage['homepage'];
        $selectmaps = kernel::single('content_article_node')->get_selectmaps();
        array_unshift($selectmaps, array('node_id'=>0, 'step'=>1, 'node_name'=>app::get('content')->_('---无---')));//echo "<pre>";print_r($selectmaps);die;
        $this->pagedata['selectmaps'] = $selectmaps;
        $this->pagedata['sections'] =
                $sections = array(
                    'single'=>array(
                        'label'=>app::get('content')->_('可视化编辑'),
                        'options'=>'',
                        'file'=>'admin/node/single.html',
                    ),
                );
        $this->page("admin/node/edit.html");
    }//End Function

    /*
     * 编辑节点
     */
    public function edit()
    {
        $node_id = $this->_request->get_get('node_id');
        if(empty($node_id)) $this->splash('error', 'index.php?app=content&ctl=admin_node', app::get('content')->_('错误请求'));
        $this->pagedata['node'] = app::get('content')->model('article_nodes')->get_by_id($node_id);
        
        if(empty($this->pagedata['node']))  $this->splash('error', 'index.php?app=content&ctl=admin_node', app::get('content')->_('错误请求'));
        $selectmaps = kernel::single('content_article_node')->get_selectmaps();
        array_unshift($selectmaps, array('node_id'=>0, 'step'=>1, 'node_name'=>app::get('content')->_('---无---')));
        $this->pagedata['selectmaps'] = $selectmaps;
        $this->pagedata['sections'] =
                $sections = array(
                    'single'=>array(
                        'label'=>app::get('content')->_('可视化编辑'),
                        'options'=>'',
                        'file'=>'admin/node/single.html',
                    ),
                );
        $this->page("admin/node/edit.html");
    }//End Function

    /*
     * 删除节点
     */
    public function remove()
    {
        $this->begin( 'index.php?app=content&ctl=admin_node&act=index' );
        $node_id = $this->_request->get_get('node_id');
        if(empty($node_id)) $this->end(false, app::get('content')->_('错误请求'));
        if(app::get('content')->model('article_nodes')->delete(array('node_id'=>$node_id))){
            $services = kernel::servicelist('content_article_node');
            foreach($services AS $service){
                if($service instanceof content_interface_node){
                    $service->remove($node_id);
                }
            }
            $this->end(true, app::get('content')->_('删除成功'));
        }else{
            $this->end(false, app::get('content')->_('该文章类目存在子类目，不能被删除'));
        }
    }//End Function

    /*
     * 发布
     */
    public function publish()
    {
        $this->begin('index.php?app=content&ctl=admin_node&act=index');
        $node_id = $this->_request->get_get('node_id');
        if(empty($node_id)) $this->end(false, app::get('content')->_('错误请求'));
        $pub = ($this->_request->get_get('pub') == 'true') ? true : false;
        if(app::get('content')->model('article_nodes')->publish($pub, array('node_id'=>$node_id))){
            $this->end(true, ($pub?app::get('content')->_('发布'):app::get('content')->_('取消发布')).app::get('content')->_('成功'));
        }else{
            $this->end(false, ($pub?app::get('content')->_('发布'):app::get('content')->_('取消发布')).app::get('content')->_('失败！请查看父类是否已发布'));
        }
    }//End Function

    /*
     * 保存添加
     */
    public function save()
    {
        $this->begin('index.php?app=content&ctl=admin_node&act=index');
        $post = $this->_request->get_post('node');
        $node_id = $this->_request->get_post('node_id');
        if(empty($post))    $this->end(false, app::get('content')->_('错误请求'));

        if($post['parent_id']) { //存在父类目时，查看父类目是否启用
            $aInfo = kernel::single("content_article_node")->get_node($post['parent_id']);
            if($aInfo['ifpub']=='false' && $post['ifpub']) {
                if($post['ifpub']!=$aInfo['ifpub']) {
                    $post['ifpub'] = $aInfo['ifpub'];
                    $msg = app::get('content')->_('父类目未发布！');
                }
            }
        }

        $post['uptime'] = time();
        $post['homepage'] = $post['homepage'] ? $post['homepage'] : 'false';

        if($node_id > 0){
            $res = app::get('content')->model('article_nodes')->update($post, array('node_id'=>$node_id));
            if($res){
                $services = kernel::servicelist('content_article_node');
                foreach($services AS $service){
                    if($service instanceof content_interface_node){
                        $service->update($node_id, $post);
                    }
                }
                $this->end(true, app::get('content')->_('保存成功!'). $msg);
            }else{
                $this->end(false, app::get('content')->_('保存失败!'). $msg);
            }
        }else{
            $res = app::get('content')->model('article_nodes')->insert($post);
            if($res){
                $services = kernel::servicelist('content_article_node');
                foreach($services AS $service){
                    if($service instanceof content_interface_node){
                        $service->insert($post);
                    }
                }
                $this->end(true, app::get('content')->_('添加成功!'). $msg);
            }else{
                $this->end(false, app::get('content')->_('添加失败!'). $msg);
            }
        }
    }//End Function


    function update() {
        $this->begin('index.php?app=content&ctl=admin_node&act=index');
        $tmp = $_POST['ordernum'];
        is_array($tmp) or $tmp = array();
        $flag = true;
        foreach($tmp as $key => $val) {
            $filter = array('ordernum'=>$val, 'node_id'=>$key);
            $flag = $this->app->model('article_nodes')->save($filter);
            if(!$flag)  $this->end(false, app::get('content')->_('修改失败!'). $msg);
        }
         $this->end(true, app::get('content')->_('修改成功!'). $msg);
    }

}//End Class
