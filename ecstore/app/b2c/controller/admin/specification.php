<?php
 

class b2c_ctl_admin_specification extends desktop_controller{

    var $workground = 'b2c_ctl_admin_goods';
    function index(){

        $this->finder('b2c_mdl_specification',array(
            'title'=>app::get('b2c')->_('商品规格'),
            'actions' => array(
                array('label'=>'添加规格','icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_specification&act=add','target'=>'dialog::{title:\'添加规格\', width:800, height:420}'),
                array('label'=>'规格图片设置','href'=>'index.php?app=b2c&ctl=admin_specification&act=edit_default_pic','target'=>'dialog::{title:\''.app::get('b2c')->_('规格图片设置').'\'}'),
            ),
            'use_buildin_set_tag' => false
        ));
    }

    function add(){
        $this->display('admin/goods/specification/detail.html');
    }

    function save($arg=null){
        if($arg){
             $this->begin();
        }else{
             $this->begin('index.php?app=b2c&ctl=admin_specification&act=index');
        }
        $oSpec = &$this->app->model('specification');
        if(!$_POST['spec']['spec_value']){
            $this->end(false,app::get('b2c')->_('请输入规格值'));
            exit;
        }
        foreach( $_POST['spec']['spec_value'] as $specValue ){
            if( $specValue['spec_value'] == '' ){
                $this->end(false,app::get('b2c')->_('规格值不能为空'));
                exit;
            }
        }
        $this->end($oSpec->save($_POST['spec']),app::get('b2c')->_('操作成功'));
    }

    function edit( $specId ){
        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header('Progma: no-cache');
        $oSpec = &$this->app->model('specification');
        $subsdf = array(
            'spec_value'=>array('*')
        );
        $this->pagedata['spec'] = $oSpec->dump($specId,'*',$subsdf);
        $this->page('admin/goods/specification/detail.html');
    }

    function check_spec_value_id(){
        $oSpecIndex = &$this->app->model('goods_spec_index');
        if( !$oSpecIndex->dump($_POST) )
            echo "can";
        else
            echo app::get('b2c')->_("该规格值已绑定商品");
    }

    function selSpecDialog($typeId = 0) {
        $aSpec = array();
        if($typeId){
            //$aSpec = $objSpec->getListByTypeId($typeId);
        }else{
            $oSpec = &$this->app->model('specification');
            $aSpec = $oSpec->getList('spec_id,spec_name,spec_memo',null,0,-1);
        }
        $this->pagedata['specs'] = $aSpec;
        $this->display('admin/goods/specification/spec_select.html');
    }

    function previewSpec(){
        $oSpec = &$this->app->model('specification');
        $this->pagedata['spec'] = $oSpec->dump( $_POST['spec_id'], '*',array('spec_value'=>array('*')));
        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');
        $this->display('admin/goods/specification/spec_value_preview.html');
    }

    function edit_default_pic(){
        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header('Progma: no-cache');
         $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');
        $this->pagedata['spec_image_height'] = $this->app->getConf('spec.image.height');
        $this->pagedata['spec_image_width'] = $this->app->getConf('spec.image.width');
        $this->display('admin/goods/specification/spec_default_pic.html');
    }

    function save_default_pic(){
        $this->begin('');
        foreach( $_POST['set'] as $k => $v ){
            $this->app->setConf($k,$v);
        }
        $this->end(true,app::get('b2c')->_('保存成功'));
    }

}
