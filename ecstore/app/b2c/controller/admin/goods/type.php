<?php
 

class b2c_ctl_admin_goods_type extends desktop_controller{

    var $workground = 'b2c_ctl_admin_goods';

    function index(){
        $this->finder('b2c_mdl_goods_type',array('actions'=> array(
            array('label'=>app::get('b2c')->_('添加商品类型'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_goods_type&act=add','target'=>'dialog::{ title:\''.app::get('b2c')->_('添加商品类型').'\', width:800, height:300}')
        ),'title'=>app::get('b2c')->_('商品类型'),'base_filter'=>array('is_def'=>'false')));
    }

    function add(){
    	foreach( kernel::servicelist('goods_type_add_html') as $services ) {
    		if ( is_object($services) ) {
    			if ( method_exists($services, 'beforeShow') ) {
    				$services->beforeShow($this);
    			}
    		}
    	}
        $this->display('admin/goods/goods_type/add_type.html');
    }

    function set($typeId = 0){
        if( $typeId ){
            $oType = &$this->app->model('goods_type');
            $this->pagedata['gtype'] = $oType->dump($typeId,'type_id,is_physical,setting');
        }else{
            $this->pagedata['gtype'] = array(
                'is_physical' => 1,
                'setting' => array('use_brand' => 1,'use_props' => 1,)
            );
        }
        // 商品类型增加选择项埋点
        foreach( kernel::servicelist('goods_type_set_option') as $services ) {
        	if ( is_object($services) ) {
        		if ( method_exists($services, 'addOption') ) {
        			$services->addOption($this);
        		}
        	}
        }
        $this->page('admin/goods/goods_type/edit_type_set.html');
    }

    function edit(){
        $gtype = $_POST['gtype'];
        if($gtype['type_id']){
            $oType = &$this->app->model('goods_type');
            $subsdf = array(
                'spec'=>array('*',array('spec:specification'=>array('spec_name,spec_memo'))),
                'brand'=>array('brand_id'),
                'props'=>array('*',array('props_value'=>array('*',null, array( 0,-1,'order_by ASC' ))) )
            );
            $gtype = array_merge($oType->dump($gtype['type_id'],'*',$subsdf),$gtype );
        }
        if(is_array($gtype['props'])){
            foreach($gtype['props'] as $k=>$v){
                if(empty($k)){
                    $gtype['props'] = null;
                }
            }
        }
       
        $brand_info = array();
        foreach((array)$gtype['brand'] as $item){
            $brand_info['linkid'][] = $item['brand_id'];
            $brand_info['info'][] = array('id'=>$item['brand_id']);
        }
        if($brand_info){
            $brand_info['linkid'] = implode(',',$brand_info['linkid']);
            $brand_info['info'] = json_encode($brand_info['info']);
            $gtype['brand'] = $brand_info;
        }
       
        $this->pagedata['gtype'] = $gtype;

        $oBrand = &$this->app->model('brand');
        $this->pagedata['brands'] = $oBrand->getList('brand_id,brand_name',null,0,-1);
        // 商品类型编辑新增tab埋点
        foreach( kernel::servicelist('goods_type_add_tab') as $services ) {
        	if ( is_object($services) ) {
        		if ( method_exists($services, 'addTab') ) {
        			$services->addTab($this,$gtype);
        		}
        	}
        }
        $this->page('admin/goods/goods_type/edit_type_edit.html');
    }

    function check_type(){
        $oGtype = &$this->app->model('goods_type');
        $typeId = current( (array)$oGtype->dump( array( 'name'=>$_POST['name'],'type_id' ) ) );
        if( $typeId && $_POST['id'] != $typeId )
            echo 'false';
        else
            echo 'true';

    }

    function save(){
        $gtype = &$_POST['gtype'];
        $this->begin('index.php?app=b2c&ctl=admin_goods_type&act=index');
        if( !$gtype['name'] ){
            //trigger_error(app::get('b2c')->_('请输入类型名称'),E_USER_ERROR);
            $this->end(false,app::get('b2c')->_('请输入类型名称'));
        }

        $oGtype = &$this->app->model('goods_type');

        $typeId = current( (array)$oGtype->dump( array( 'name'=>$gtype['name'],'type_id' ) ) );
        if( $typeId && $gtype['type_id'] != $typeId ){
            //trigger_error(app::get('b2c')->_('类型名称已存在'),E_USER_ERROR);
            $this->end(false,app::get('b2c')->_('类型名称已存在'));
        }

        //品牌
        if(!$gtype['brand']) $gtype['brand'] = null;
        
       
        if($gtype['brand']['linkid']){
            $brand_info = array_filter(explode(',',$gtype['brand']['linkid']));
            $gtype['brand'] = array();
            foreach((array)$brand_info as $item){
                $gtype['brand'][]['brand_id'] = $item;
            }
        }else $gtype['brand'] = null;
       
        
        //属性
        $this->_preparedProps($gtype);
        //参数
        $this->_preparedParams($gtype,$errorMsg);
        if($errorMsg){
            $this->end(false,$errorMsg);
        }
        //必填参数
        $this->_preparedMinfo($gtype,$errorMsg);
        if($errorMsg) {
            $this->end(false,$errorMsg);
        }
        //规格
        $this->_preparedSpec($gtype);
        // 商品类型扩展项存储 埋点
        foreach( kernel::servicelist('goods_type_addition_item_save') as $services ) {
        	if ( is_object($services) ) {
        		if ( method_exists($services, 'additionItemSave') ) {
        			$services->additionItemSave($gtype);
        		}
        	}
        }
        $this->end($oGtype->save($gtype),app::get('b2c')->_('操作成功'));
    }

    function setPropsValue(){
        reset( $_POST['gtype']['props'] );
        $this->pagedata['props_value'] = current( $_POST['gtype']['props'] );
        $this->pagedata['props_key'] = key( $_POST['gtype']['props'] );
        $this->display('admin/goods/goods_type/set_props_value.html');
    }

    function doSetPropsValue(){
        echo '==';
    }

    function _preparedProps(&$gtype){
        if( !$gtype['props'] ){
            $gtype['props'] = array();
            return;
        }
        $searchType = array(
            '0' => array('type' => 'input', 'search' => 'input'),
            '1' => array('type' => 'input', 'search' => 'disabled'),
            '2' => array('type' => 'select', 'search' => 'nav'),
            '3' => array('type' => 'select', 'search' => 'select'),
            '4' => array('type' => 'select', 'search' => 'disabled'),
        );
        $props = array();
        $inputIndex = 21;
        $selectIndex = 1;

        foreach( $gtype['props'] as $aProps ){
            if( !$aProps['name'] )
                continue;
            if(is_numeric($aProps['type'])) {
                $aProps = array_merge( $aProps,$searchType[$aProps['type']] );
                if($aProps['type'] == 'input') {
                    unset($aProps['options']);
                }
            }
            if( !$aProps['options'] ){
                unset($aProps['options']);
            }else{
                $tAProps = array();
                $aProps['optionIds'] = $aProps['options']['id'];
                foreach( $aProps['options']['value'] as $opk => $opv ){
                    $opv = explode('|',$opv);
                    $tAProps['options'][$opk] = $opv[0];
                    unset($opv[0]);
                    $tAProps['optionAlias'][$opk] = implode('|',(array)$opv);
                }
                $aProps['options'] = $tAProps['options'];
                $aProps['optionAlias'] = $tAProps['optionAlias'];
            }
            $aProps['ordernum']= intval( $aProps['ordernum'] );
            if( $aProps['type'] == 'input' ){
                $propskey = $inputIndex++;
            }else{
                $propskey = $selectIndex++;
            }
            $aProps['goods_p'] = $propskey;
            if(!isset($aProps['show'])){
            	$aProps['show'] = '';
            }
             //前台列表选择方式
            if(!isset($aProps['s_type'])){
            	$aProps['s_type'] = '';
            }
            $props[$propskey] = $aProps;
        }
        if( $inputIndex>51 ){
            //trigger_error(app::get('b2c')->_('输入属性不能超过30项'),E_USER_ERROR);
            $this->end(false,app::get('b2c')->_('输入属性不能超过30项'));
        }
        if( $selectIndex>21 ){
            //trigger_error(app::get('b2c')->_('选择属性不能超过20项'),E_USER_ERROR);
            $this->end(false,app::get('b2c')->_('选择属性不能超过20项'));
        }
        $gtype['props'] = $props;
        $props = null;
    }

    function _preparedParams(&$gtype,&$errorMsg=''){
        if( !$gtype['params'] ){
            $gtype['params'] = array();
            return ;
        }
        $params = array();
        foreach( $gtype['params'] as $aParams ){
            if( !$aParams['name'] ) {
                $errorMsg = app::get('b2c')->_('请为参数表中参数组添加参数名');
                break;
            }
            $paramsItem = array();
            foreach( $aParams['name'] as $piKey => $piName ){
                if(!$piName) {
                    $errorMsg = app::get('b2c')->_('请完成参数表中参数名');
                    break 2;
                }
                $paramsItem[$piName] = $aParams['alias'][$piKey];
            }
            if(!$aParams['group']) {
                $errorMsg = app::get('b2c')->_('请完成参数表中参数组名称');
                break;
            }
            $params[$aParams['group']] = $paramsItem;
        }
        $gtype['params'] = $params;
        $params = null;
    }

    function _preparedMinfo(&$gtype,&$errorMsg=''){
        if(!$gtype['minfo']){
            $gtype['minfo'] = array();
            return;
        }
        $minfo = $gtype['minfo'];
        foreach( $minfo as $minfoKey => $aMinfo ){
            if( !trim( $aMinfo['label'] ) ){
                unset( $gtype['minfo'][$minfoKey] );
                $errorMsg = app::get('b2c')->_('请完成必填信息名称');
                break;
            }
            if( !trim($aMinfo['name']) )
                $gtype['minfo'][$minfoKey]['name'] = 'M'.md5($aMinfo['label']);
            if( $aMinfo['type'] == 'select' )
                $gtype['minfo'][$minfoKey]['options'] = explode(',',$aMinfo['options']);
            else
                unset( $gtype['minfo'][$minfoKey]['options'] );
        }
        $gtype['minfo'] = array_values( $gtype['minfo'] );
    }

    function _preparedSpec(&$gtype){
        if(!$gtype['spec']){
            $gtype['spec'] = array();
            return;
        }
        $spec = array();
        foreach( $gtype['spec']['spec_id'] as $k => $aSpec ){
            $spec[] = array(
                'spec_id'=>$aSpec,
                'spec_style' => $gtype['spec']['spec_type'][$k]
            );
        }
        $gtype['spec'] = $spec;
        $spec = null;
    }



    function fetchProtoTypes($link,$querystring='',$nodeType=''){
        header('Content-Type: text/html;charset=utf-8');
        $net = &kernel::single('base_httpclient');
        $cert = base_certificate::get('certificate_id');
        $token = base_certificate::get('token');
        $sc = md5('goostypefeed'.$cert.$token);
        $url = 'http://feed.shopex.cn/goodstype/'.$link.'?certificate='.$cert.'&sc='.$sc.($querystring?'&'.$querystring:'').($nodeType?'&nodeType='.$nodeType:'');
        $net->http_ver = '1.0';
        $net->defaultChunk = 30000;
        $result = $net->get($url);
        if($result = $net->get($url)){
             $script = '<SCRIPT LANGUAGE="JavaScript">loadLocalBrands();</SCRIPT><script>function checkTypeNameExists(){
                 new Request({url:\'index.php?app=b2c&ctl=admin_goods_type&act=checkTypeNameExists\',method:\'post\',data:\'gtypename=\'+$(\'gtypename\').value,evalScripts:true}).send();
             }
            $("closeftpbutton").getParent("form").store("target",{
                onComplete:function(){
                    $("closeftpbutton").getParent(".dialog").retrieve("instance").close();
                }
             });
            </script>';
            $result = str_replace('ctl=goods/gtype','app=b2c&ctl=admin_goods_type',$result);
            $result = str_replace('required="true"','vtype="required"',$result);
            $result = str_replace('"submit"','"submit" id="closeftpbutton"',$result);
            $result = preg_replace('/<SCRIPT([^>]*)>(.*?)<\/script>/Us',$script,$result);

        }
        if ($link == 'gtype.php') {
        	$result .= '<div class="table-action"><button onclick="javascript:autoFetch();" id="previous_step" class="btn btn-primary" type="button"><span><span>' . __("上一步") . '</span></span></button></div>';
        }
        if($result && false!==substr($result,'shopexfeed')){
            echo $result;
        }else{
            echo '<div style="width:300px;height:80px;"><BR><BR>'.__('因网络连接或其它原因，暂时无法获取系统默认类型信息。<BR>请稍候再试...错误信息').$net->responseCode.'</div><div style="clear:both">';
        }
    }

    function checkTypeNameExists(){
        $o = $this->app->model('goods_type');
        if($o->getList('type_id',array('name'=>$_POST['gtypename']))){
            echo '<script>alert("本类型名在系统中已存在，请更名");</script>';
        }else{
            echo '<script>alert("本类型名在系统中不存在，可正常添加");</script>';
        }
    }

    function fetchSave(){
        $this->begin('index.php?app=b2c&ctl=admin_goods_type&act=index',array(300001=>'index.php?app=b2c&ctl=admin_brand&act=fetchProtoTypes&p[0]=gtype.php&p[1]=id='.$_POST['param_id']));
        $map =  kernel::single('site_utility_xml')->xml2array($_POST['xml']);
        $gtype = $map['goodstype'];
        $gtype['name'] = $_POST['gtypename'];
        if(is_array($_POST['localbrands'])){
            foreach($_POST['localbrands'] as $kp=>$kv){
               $gtype['brand'][]['brand_id'] = $kv;
            }
        }
        $o = &$this->app->model('goods_type');
		$msg = app::get('b2c')->_('类型导入成功');
        $this->end($o->fetchSave($gtype,$msg), $msg);
    }

}
