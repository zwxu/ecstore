<?php
class business_ctl_admin_storeregion extends desktop_controller{
    var $workground = 'business.wrokground.store';
    var $pagelimit = 10;
    var $member_model ;
    public function __construct($app)
    {
        parent::__construct($app);
        $this->member_model = $this->app->model('storemanger');
        header("cache-control: no-store, no-cache, must-revalidate");
    }
  function index(){
 
        $this->finder('business_mdl_storeregion',array(
        'title'=>'经营范围列表',
        'allow_detail_popup'=>true,
        'use_buildin_export'=>true,
        'use_buildin_set_tag'=>true,
        'use_buildin_filter'=>true,
        'use_view_tab'=>true,
        'actions'=>array(
                        array('label'=>"添加经营范围",'href'=>'index.php?app=business&ctl=admin_storeregion&act=add_page','target'=>'_blank'),
                        array('label'=>"批量操作",
                        'icon'=>'batch.gif',
                        'group'=>array(
                                array('label'=>app::get('business')->_('排序'),'icon'=>'download.gif','submit'=>'index.php?app=business&ctl=admin_storeregion&act=singleBatchEdit&p[0]=storeregiondorder','target'=>'dialog'),
                                ),
                        ),
                        ),
            'object_method' => array('count'=>'count_finder','getlist'=>'get_list_finder'),
        ));
            
    }


    function singleBatchEdit($editType=''){

        $newFilter = $_POST;
        unset($newFilter['app']);
        unset($newFilter['ctl']);
        unset($newFilter['act']);
        unset($newFilter['_finder']);
        unset($newFilter['marketable']);
        unset($newFilter['_DTYPE_BOOL']);

        if($_POST['isSelectedAll'] == '_ALL_')
            $_POST['region_id'][0] = '_ALL_';
        if(count($_POST['region_id']) == 0 && $_POST['_finder']['select'] != 'multi' && !$_POST['_finder']['id'] && !$_POST['filter']){
            echo __('请选择记录');
            exit;
        }
        if($_POST['filter']){
            $_POST['_finder'] = unserialize($_POST['filter']);
            $editType = $_POST['updateAct'];
        }

        //选中记录的ID经序列化后保存于页面
        $this->pagedata['filter'] =serialize($_POST['region_id']);
        $this->pagedata['editInfo']=array('count'=>count($_POST['region_id']));

        $this->display('admin/store/batch/batchEdit'. $editType .'.html');

       
    }

    function saveBatchEdit(){
         
        $this->begin('');
        $filter = unserialize($_POST['filter']);

        $objGoods = &$this->app->model('storeregion');

        $haserror = false;

        foreach($filter as $key=>$row){

            $storegrade['region_id'] =$row;

            //保存批量操作:排序
            $storegrade['d_order'] = $_POST['set']['dorder'];
           
            $objGoods-> save($storegrade);

        }

        ini_set('track_errors','1');
        restore_error_handler();

        if(!$haserror){
           $this->end(true, app::get('business')->_('批量排序'));
           
        }else{
            echo $GLOBALS['php_errormsg'];
        }

    }



    function add_page(){
        $this->pagedata['title'] = "添加经营范围";
        $this->singlepage('admin/store/storeregionnew.html');
    }


    public function toAdd() {
        $this->begin('');
        $aData = $_POST['storeregion'];
        
        /*
        foreach( $aData as $key => $val ) {
            if( $val=='' ) unset($aData[$key]);
        }
        */

        $o = $this->app->model('storeregion');

        if($flag = $o->save($aData)) {
            if(! $aData['region_id'] ) {
                    $this->end(true,app::get('business')->_('保存成功！'. '新增ID为：' . $aData['region_id']));
            } else {
                    $this->end(true,app::get('business')->_('更新成功！'. '更新ID为：' . $aData['region_id']));
            }
        } else {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{false:"'. app::get('business')->_('失败: 操作失败！') .'",_:null,region_id:"'.$aData['region_id'].'"}';
        }
    }


    public function edit() {

        if(($id=$_GET['region_id'])) {

            $filter = array(
                'region_id'   => $id,
            );

            $arr_info = $this->app->model('storeregion')->dump($filter,'*','default');
            
            //后台编辑 无商品信息时提示数据错误
            if( !$arr_info ) {
                exit('操作失败！相关商品信息为空！数据异常！！');
            } else {

                $this->pagedata['storeregion'] = $arr_info;
                $this->add_page();
            }
        } else {
            exit('操作失败！相关信息为空！经营范围id不能为空！');
        }
    }

}