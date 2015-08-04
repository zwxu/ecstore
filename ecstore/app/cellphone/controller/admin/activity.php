<?php
class cellphone_ctl_admin_activity extends desktop_controller{

    var $workground = 'cellphone.wrokground.mobile';

    public function __construct($app){
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
        $this->app_list = cellphone_misc_exec::get_actapp();
    }

    function index(){
        $this->finder('cellphone_mdl_activity',array(
            'title'=>app::get('b2c')->_('手机活动'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加活动'),'icon'=>'add.gif','href'=>'index.php?app=cellphone&ctl=admin_activity&act=create','target'=>'_blank'),
            ),
        ));
    }

    function create(){
        $this->pagedata['return_url'] = 'shopamdin?app=cellphone&ctl=admin_activity&act=object_rows';
        $this->singlepage('admin/activity/add.html');
    }

    function get_goods(){
        list($act_id, $app) = explode('_', $_POST['unit_id']);
        $act_id = $act_id?intval($act_id):-1;
        if(!$app || !array_key_exists($app,$this->app_list)){
        }else{
            $actapply = @app::get($app)->model($this->app_list[$app]['m2']);
            if($actapply){
                $this->pagedata['objcet'] = $this->app_list[$app]['m2']."@{$app}";
                $this->pagedata['filter'] = array('aid'=>$act_id,'status'=>'2');
                $this->pagedata['return_url'] = 'shopamdin?app=cellphone&ctl=admin_activity&act=object_rows';
            }
        }
        echo $this->fetch('admin/activity/list.html');
    }

    function object_rows(){
        if($_POST['data']){
            if($_POST['app_id'])
                $app = app::get($_POST['app_id']);
            else
                $app = $this->app;
            $obj = $app->model($_POST['object']);
            $schema = $obj->get_schema();
            $textColumn = $_POST['textcol']?$_POST['textcol']:$schema['textColumn'];
            $textColumn = explode(',',$textColumn);
            $_textcol = $textColumn;
            $textColumn = $textColumn[0];

            $keycol = $_POST['key']?$_POST['key']:$schema['idColumn'];

            //统一做掉了。
            $all_filter = !empty($obj->__all_filter) ? $obj->__all_filter : array();
            $filter = !empty($_POST['filter']) ? $_POST['filter'] : $all_filter;
            $arr_filter = array();
            if( $_POST['data'][0]==='_ALL_' ) {
                if (isset($filter['advance'])&&$filter['advance']){
                    $arr_filters = explode(',',$filter['advance']);
                    foreach ($arr_filters as $obj_filter){
                        $arr = explode('=',$obj_filter);
                        $arr_filter[$arr[0]] = $arr[1];
                    }
                    unset($filter['advance']);
                }
            }else{
                $arr_filter = array_merge($filter,array($keycol=>$_POST['data']));
            }

            $items = $obj->getList('*', $arr_filter);
            $name = $items[0][$textColumn];
            switch($app->app_id){
                case 'timedbuy':
                case 'scorebuy':
                case 'groupbuy':
                case 'spike':
                    $textColumn = 'name@goods@b2c';
                    break;
            }

            if($_POST['type']=='radio'){
                if(strpos($textColumn,'@')!==false){
                    list($field,$table,$app_) = explode('@',$textColumn);
                    if($app_){
                        $app = app::get($app_);
                    }
                    $mdl = $app->model($table);
                    $oschema = $mdl->get_schema();
                    $row = $mdl->getList('*',array($oschema['idColumn']=>$items[0][$keycol]));
                    $name = $row[0][$field];

                }
                echo json_encode(array('id'=>$items[0][$keycol],'name'=>$name));
                exit;
            }else{
                $schema_id = array();
                foreach((array)$items as $key => $row){
                    if($row['gid'])$schema_id[] = $row['gid'];
                }
                $schema_id = empty($schema_id)?array(-1):$schema_id;
                if(strpos($textColumn,'@')!==false){
                    list($field,$table,$app_) = explode('@',$textColumn);
                    if($app_){
                        $app = app::get($app_);
                    }
                    $mdl = $app->model($table);
                    $oschema = $mdl->get_schema();
                    $row = $mdl->getList($oschema['textColumn'].','.$oschema['idColumn'],array($oschema['idColumn']=>$schema_id));
                    $aData = array();
                    foreach((array)$row as $value){
                        $aData[$value[$oschema['idColumn']]] = $value[$oschema['textColumn']];
                    }
                    foreach((array)$items as $key => $value){
                        if($value['gid'])$items[$key][$field] = $aData[$value['gid']];
                    }
                }
            }

            $this->pagedata['_input'] = array('items'=>$items,
                                                'idcol' => $schema['idColumn'],
                                                'keycol' => $keycol,
                                                'textcol' => $textColumn,
                                                '_textcol' => $_textcol,
                                                'name'=>$_POST['name']
                                                );
            $this->pagedata['_input']['view_app'] = 'desktop';
            $this->pagedata['_input']['view'] = $_POST['view'];
            if($_POST['view_app']){
                $this->pagedata['_input']['view_app'] =  $_POST['view_app'];
            }

            if(strpos($_POST['view'],':')!==false){
                list($view_app,$view) = explode(':',$_POST['view']);
                $this->pagedata['_input']['view_app'] = $view_app;
                $this->pagedata['_input']['view'] = $view;

            }
            $this->display('admin/activity/row.html');
        }
    }

    function save(){
        $this->begin('index.php?app=cellphone&ctl=admin_activity&act=index');
        list($act_id, $app) = explode('_', $_POST['unit_id']);
        if(!$app || !array_key_exists($app,$this->app_list) || !@app::get($app)->model($this->app_list[$app]['m2'])){
            $this->end(false,app::get('b2c')->_('未设置活动或活动不存在'));
        }else{
            $aData['source']['app'] = $app;
            $aData['source']['m1'] = $this->app_list[$app]['m1'];
            $aData['source']['m2'] = $this->app_list[$app]['m2'];
        }
        $aData['act_id'] = $_POST['act_id'];
        $aData['original_id'] = $act_id;
        if(!trim($_POST['act_name'])){
            $this->end(false,app::get('b2c')->_('活动名称不能为空'));
        }
        $aData['act_name'] = $_POST['act_name'];
        if(!trim($_POST['banner'])){
            $this->end(false,app::get('b2c')->_('活动广告图片不能为空'));
        }
        $aData['banner'] = $_POST['banner'];

        /*
        if(!trim($_POST['logo'])){
            $this->end(false,app::get('b2c')->_('活动logo图片不能为空'));
        }

        $aData['logo'] = $_POST['logo'];
        */
        $aData['p_order'] = intval($_POST['p_order']);
        if($_POST['act_info']){
            $_POST['act_info'] = is_array($_POST['act_info'])?$_POST['act_info']:explode(',',$_POST['act_info']);
            $aData['rel'] = array_filter($_POST['act_info']);
        }
        if($this->app->model('activity')->save($aData)){
            $this->end(true,app::get('b2c')->_('保存成功'));
        }else{
            $this->end(false,app::get('b2c')->_('保存失败'));
        }
    }

    function edit($act_id){
        $this->path[] = array('text'=>app::get('b2c')->_('活动编辑'));
        $objAct = &$this->app->model('activity');
        $aData = $objAct->dump($act_id,'*','default');
        $aData['source'] = unserialize($aData['source']);
        $aData['unit_id'] = $aData['original_id'].'_'.$aData['source']['app'];
        foreach((array)$aData['relation'] as $row){
            $aData['act_info'][] = $row['rel_id'];
        }
        $aData['banner_url'] = $this->get_img_url($aData['banner']);
        $this->pagedata['scols'] = "(select name from sdb_b2c_goods where goods_id=".kernel::database()->prefix."{$aData['source']['app']}_{$aData['source']['m2']}.gid) as name,";
        $this->pagedata['actInfo'] = $aData;
        $this->pagedata['objcet'] = "{$aData['source']['m2']}@{$aData['source']['app']}";
        $this->pagedata['filter'] = array('aid'=>$aData['original_id'],'status'=>'2');
        $this->pagedata['return_url'] = 'shopamdin?app=cellphone&ctl=admin_activity&act=object_rows';
        $this->singlepage('admin/activity/add.html');
    }

    public function get_img_url($image_id,$size){
        $url = cellphone_image_storager::image_path($image_id,$size);
        if(empty($url)){
            $imageDefault = app::get('cellphone')->getConf('image.set');
            $url = cellphone_image_storager::image_path($imageDefault[strtoupper($size)]['default_image']);
        }
        return $url;
    }
}