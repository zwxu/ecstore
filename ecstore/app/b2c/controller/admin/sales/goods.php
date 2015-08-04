<?php
 
 
//*******************************************************************
//  商品促销规则控制器
//  $ 2010-04-13 16:27 $
//*******************************************************************
class b2c_ctl_admin_sales_goods extends desktop_controller{

    public $workground = 'b2c_ctl_admin_sales_coupon';

    public function index(){
        $this->finder('b2c_mdl_sales_rule_goods',array(
            'title'=>app::get('b2c')->_('商品促销规则'),
            'actions'=>array(
                            array('label'=>app::get('b2c')->_('添加规则'),'href'=>'index.php?app=b2c&ctl=admin_sales_goods&act=add','target'=>'_blank'),
                            array('label'=>app::get('b2c')->_('应用所有选中'), 'icon'=>'del.gif','confirm'=>app::get('b2c')->_('确定应用所有选中项吗？'),'submit'=>'index.php?app=b2c&ctl=admin_sales_goods&act=applyAll&p[0]=1'),
                            #array('label'=>'删除','icon'=>'del.gif','confirm'=>'确定删除选中项？删除后可进入回收站恢复','submit'=>$this->url.'&action=dorecycle');
                            array('label'=>app::get('b2c')->_('取消所有选中'), 'icon'=>'del.gif','confirm'=>app::get('b2c')->_('确定取消所有选中项吗？'),'submit'=>'index.php?app=b2c&ctl=admin_sales_goods&act=applyAll&p[0]=0')
                        )
            ));
    }

    /**
     * 添加新规则
     */
    public function add() {
        $this->pagedata['rule']['sort_order'] = 50;
        $this->_editor();
    }

    /**
     * 修改规则
     *
     * @param int $rule_id
     */
    public function edit($rule_id) {
        $oGoodsPromotion = $this->app->model("sales_rule_goods");
        $aRule = $oGoodsPromotion->dump($rule_id,'*','default');

        $aRule['member_lv_ids'] = empty($aRule['member_lv_ids'])? null :explode(',',$aRule['member_lv_ids']);

        ///////////////////////////// 过滤条件 ////////////////////////////
        $aRule['conditions'] = empty($aRule['conditions'])? null : ($aRule['conditions']);
        $html = kernel::single('b2c_sales_goods_process')->getTemplate($aRule['c_template'],$aRule['conditions']);
        $this->pagedata['conditions'] = $html;

        ///////////////////////////// 优惠方案 ////////////////////////////
        $aRule['action_solution'] = empty($aRule['action_solution'])? null : ($aRule['action_solution']);
        $html = kernel::single('b2c_sales_solution_process')->getTemplate($aRule['s_template'],$aRule['action_solution'], 'goods');
        $this->pagedata['action_solution'] = $html;
        
        $this->pagedata['rule'] = $aRule;

        $this->_editor( $rule_id );
    }

    /**
     * add & edit 公共部分
     *
     */
    private function _editor( $rule_id=0 ) {
        
        //排斥状态显示优先级项  默认加载 addtime:14:09 2010-8-19
        $time = time();
        $filter = array('from_time|sthan'=>$time, 'to_time|bthan'=>$time, 'status'=>'true', 'rule_type'=>'N');
        if( $rule_id ) $filter['rule_id|noequal'] = $rule_id;
        $arr = $this->app->model('sales_rule_goods')->getList( 
                                                        'name,sort_order', 
                                                        $filter,
                                                        0,-1,'sort_order ASC'
                                                    );
        $this->pagedata['sales_list'] = $arr;
        $arr = null;
        //end  
        
        
        $this->pagedata['promotion_type'] = 'goods'; // 规则类型 用于公用模板

        ////////////////////////////  模块  ////////////////////////////////
        $this->pagedata['sections'] = $this->_sections();

        //////////////////////////// 会员等级 //////////////////////////////
        $oMemberLevel = &$this->app->model('member_lv');
        $this->pagedata['member_level'] = $oMemberLevel->getList('member_lv_id,name', array(), 0, -1, 'member_lv_id ASC');

        //////////////////////////// 过滤条件模板 //////////////////////////////
        $oSGP = kernel::single('b2c_sales_goods_process');
        $this->pagedata['pt_list'] = $oSGP->getTemplateList();

        //////////////////////////// 优惠方案模板 //////////////////////////////
        $this->pagedata['perfilter'] = true;
        $oSSP = kernel::single('b2c_sales_solution_process');
        $this->pagedata['stpl_list'] = $oSSP->getTemplateList(false);

        //出去相应的优惠方案 
        unset($this->pagedata['stpl_list']['goods']['progetcoupon_promotion_solutions_getcoupon']);
        unset($this->pagedata['stpl_list']['goods']['gift_promotion_solutions_gift']);

        $this->pagedata['solution_type'] = 'goods';
        
        
        
        header("Cache-Control:no-store");
        $this->singlepage('admin/sales/promotion/frame.html');
    }

    private function _sections() {
       $arr = array(
                 'basic'=> array(
                             'label'=>app::get('b2c')->_('基本信息'),
                             'options'=>'',
                             'file'=>'admin/sales/promotion/basic.html',
                             'app' => 'b2c',
                           ), // basic
               'conditions'=> array(

                                'label'=>app::get('b2c')->_('优惠条件'),
                                'options'=>'',
                                'file'=>'admin/sales/promotion/conditions.html',
                                'app' => 'b2c',
                              ), // conditions
               'solution'=> array(
                              'label'=>app::get('b2c')->_('优惠方案'),
                              'options'=>'',
                              'file'=>'admin/sales/promotion/solution.html',
                              'app' => 'b2c',
                            ), // solutions
            );
             
        $extend = array();
        foreach( kernel::servicelist("b2c_sales_goods_section_extenion") as $object ) {
            if( !method_exists($object,'get_section') ) continue;
            $arr = array_merge($arr,$object->get_section());
            
            
            //设置页面信息 
            if( !method_exists($object,'set_page_data') ) continue;
            $object->set_page_data( $this );
        }
        
        return $arr;
    }

    public function toAdd() {
        $this->begin();
        $aData = $this->_prepareRuleData($_POST);
        $oGoodsPromotion = $this->app->model("sales_rule_goods");
        if( $aData['status']=='false' ) {
            if( $aData['rule_id'] ) {
                kernel::single('b2c_sales_goods_process')->clear((int)$aData['rule_id']);
            }
        } else {
            
        }
        
        $bResult = $oGoodsPromotion->save($aData);

        $this->end($bResult,app::get('b2c')->_('操作成功,'));
    }
    
    
    /**
     * 这个MS可以放入model里处理
     */
    private function _prepareRuleData($aData) {
        ///////////////////////////// 基本信息 //////////////////////////////////
        $aResult = $aData['rule'];
        
        if( !$aResult['name'] ) $this->end( false,'促销名称不能为空！' );

        // 开始时间&结束时间
        foreach ($aData['_DTIME_'] as $val) {
            $temp['from_time'][] = $val['from_time'];
            $temp['to_time'][] = $val['to_time'];
        }
        $aResult['from_time'] = strtotime($aData['from_time'].' '. implode(':', $temp['from_time']));
        $aResult['to_time'] = strtotime($aData['to_time'].' '. implode(':', $temp['to_time']));
        if( $aResult['to_time']<=$aResult['from_time'] ) $this->end( false,'结束时间不能小于或等于开始时间！' );

        // 会员等级
        $aResult['member_lv_ids'] = empty($aResult['member_lv_ids'])? null : implode(',',$aResult['member_lv_ids']);

        // 创建时间 (修改时不处理)
        //if(empty($aResult['rule_id'])) 
        $aResult['create_time'] = time();

        ////////////////////////////// 过滤规则 //////////////////////////////////
        $aResult['conditions'] = empty($aData['conditions'])? null : ($aData['conditions']);
        //自定义商品促销模板,商品属性的修改时间条件的时间处理@wuwei
        if(!empty($aData['conditions'])){            
            foreach($aResult['conditions']['conditions'] as $key=>$value){
                if($value['attribute'] == 'goods_last_modify'){
                    $aResult['conditions']['conditions'][$key]['value'] = strtotime( $value['value'].' '.$aData['_DTIME_']['H']['conditions[conditions'][$key]['value'].':'.$aData['_DTIME_']['M']['conditions[conditions'][$key]['value']);
                }
            }
        }//end if
        
        //if(is_null($aResult['conditions'])) $aResult['c_template'] = null;

        ////////////////////////////// 优惠方案 //////////////////////////////////
        $aResult['action_solution'] = empty($aData['action_solution'])? null : ($aData['action_solution']);
        //$aResult['action_solution'] = empty($aData['action_solution'])? null : ($aData['action_solution']);
        
        if( empty($aResult['sort_order']) && $aResult['sort_order']!==0 )
            $aResult['sort_order'] = 50;
        if( $aResult['sort_order'] ) $aResult['sort_order'] = (int)$aResult['sort_order'];
		if( isset($aData['action_solution']['progetcoupon_promotion_solutions_getcoupon']['cpns_id']) && !is_array($aData['action_solution']['progetcoupon_promotion_solutions_getcoupon']['cpns_id']))
		$this->end( false, app::get('b2c')->_('请选择优惠卷!'));
		/** 
		 * 校验删选相应的表单元素
		 */

		if(is_null($aData['rule']['c_template'])){
                     $this->end(false,'优惠条件必选一项');
                }

                if(is_null($aData['rule']['s_template'])){
                     $this->end(false,'优惠方案必选一项');
                }

		$obj_rule_c_template = kernel::single($aData['rule']['c_template']);
		if ($obj_rule_c_template){
			if (method_exists($obj_rule_c_template, 'verify_form'))
				if (!$obj_rule_c_template->verify_form($aData,$msg)){
					$this->end( false, $msg);
				}
		}
		
		$obj_rule_s_template = kernel::single($aData['rule']['s_template']);
		if ($obj_rule_s_template){
			if (method_exists($obj_rule_s_template, 'verify_form'))
				if (!$obj_rule_s_template->verify_form($aData,$msg)){
					$this->end( false, $msg);
				}
			
		}

        return $aResult;
    }

    /**
     * 获取指定模板(ajax)
     */
    public function template(){
        $oSGP = kernel::single('b2c_sales_goods_process');
        // 只载入模板 这里只是选择模板
        //$_POST['template']['storeid_filter']='sfafdsa';
        $html = $oSGP->getTemplate($_POST['template']);
        if(empty($html)) die("<b align=\"center\">".app::get('b2c')->_("模板生成失败")."</b>");

        $this->pagedata['conditions'] = $html;
        $this->display('admin/sales/promotion/goods_rule.html');
    }
    
    
    /**
     * 用于优惠方案获取模板
     */
    public function solution() {
        $oSSP = kernel::single('b2c_sales_solution_process');
        // 只载入模板 这里只是选择模板
        $html = $oSSP->getTemplate($_POST['template'], array(), $_POST['type']);
        if(empty($html)) die("<b align=\"center\">".app::get('b2c')->_("模板生成失败")."</b>");

        $this->pagedata['conditions'] = $html;
        $this->display('admin/sales/promotion/goods_rule.html');
    }
    

    /**
     * 选择条件 里面的操作有必在封装在model里
     */
    public function conditions(){
        // 传入的值为空的处理
        if(empty($_POST)) exit;

        // vpath
        $_POST['path'] .= '[conditions]['.$_POST['position'].']';
        $_POST['level'] += 1;

        $oSGP = kernel::single('b2c_sales_goods_process');
        echo $oSGP->makeCondition($_POST);
    }
    
    
    public function clear($rule_id) {
        $this->begin('index.php?app=b2c&ctl=admin_sales_goods&act=index');
        $oSGP = kernel::single('b2c_sales_goods_process');
        if($oSGP->clear((int)$rule_id)) 
            $this->end(true, app::get('b2c')->_('取消应用成功'));
        else 
            $this->end(true, app::get('b2c')->_('取消应用失败'));
    }

   public function apply($rule_id){
       
       $oSGP = kernel::single('b2c_sales_goods_process');

       if($_POST) {
           $this->begin('index.php?app=b2c&ctl=admin_sales_goods&act=index');
           if(!isset($_POST['status'])) $this->end(false, app::get('b2c')->_('应用失败'));
           if($oSGP->apply($_POST['rule_id'])) {
                $this->end(true, app::get('b2c')->_('应用成功'));
           } else {
               $this->end(false, app::get('b2c')->_('应用失败'));
           }
       }

       $mSRG = $this->app->model("sales_rule_goods");
       $aRule = $mSRG->dump($rule_id);

       $this->pagedata['rule'] = $aRule;
       $this->pagedata['rule_id'] = $rule_id;
       $this->pagedata['num'] = $oSGP->getApplyNum($rule_id);
       $this->display('admin/sales/promotion/apply.html');
   }

   public function applyAll($bFlag = true) {
       $this->begin('index.php?app=b2c&ctl=admin_sales_goods&act=index');

       $arr_rule_id = $_POST['rule_id'];
       if( !$arr_rule_id ) {
           if( $_POST['isSelectedAll']=='_ALL_' ) {
                $arr = $this->app->model('sales_rule_goods')->getList( 'rule_id' );
                $arr_rule_id = array_map('current',$arr);
           }
       }
       $oSGP = kernel::single('b2c_sales_goods_process');
       // 先全部全部取消应用
       $oSGP->clearAll( $arr_rule_id );

       // 如果是全部取消到这结束
       if(!$bFlag) $this->end(true, app::get('b2c')->_('取消成功'));

       // todo 这里可能得做任务处理 多条的处理促销规则,暂时取所有的商品规则进行处理  2010-04-19 10:45 wubin
       $mSRG = $this->app->model('sales_rule_goods');
       //$aGPR = $mSRG->getList('rule_id',array('status'=>'true'),0,9999); // 默认20条件 现在取9999 应该取完了
       foreach( $arr_rule_id as $rule_id ) {
           $oSGP->apply( $rule_id );
       }

       $this->end(true, app::get('b2c')->_('应用成功'));

   }
   
   
    public function applyAllSelect($bFlag = true) {
        
       $oSGP = kernel::single('b2c_sales_goods_process');
       // 先全部全部取消应用
       $oSGP->clearAll();

       // 如果是全部取消到这结束

       if(!$bFlag) die(app::get('b2c')->_("取消成功"));


       // todo 这里可能得做任务处理 多条的处理促销规则,暂时取所有的商品规则进行处理  2010-04-19 10:45 wubin
       $mSRG = $this->app->model('sales_rule_goods');
       $aGPR = $mSRG->getList('rule_id',array('status'=>'true'),0,9999); // 默认20条件 现在取9999 应该取完了
       foreach($aGPR as $key=>$row) {
           $oSGP->apply($row['rule_id']);
       }

       die(app::get('b2c')->_("应用成功"));

   }
   
   
   
   public function goods_dialog($html=true, $aData=array(), $table_info=array()) {
        
        
        if(empty($table_info) || !is_array($table_info)) {
            $table_info = $_GET['arg0'];
            $table_info = @unserialize(urldecode($table_info));
        }
        
        $serialize_table_info = @serialize($table_info);
        
        if(empty($table_info['table'])) return false;
        
        
        $filter = array();
        if($this->app->getConf("site.storage.enabled")=='true') {
            $filter['marketable'] = 'true';
        }
        
        

        //每页条数
        $pageLimit = 10;
        
        //当前页
        $page = $_GET['page'];
        $page or $page=1;
        
        if(is_array($table_info['table'])) {
            $db = kernel::database();
            foreach($table_info['table'] as $row) {
                $position = strpos($row, ':');
                if($position===false) return false;
                $de_table[] = substr($row, 0, $position);
                $table[] = $db->prefix . substr($row, 0, $position);
                $join_columns[] = $db->prefix . str_replace(':', '.', $row);
            }
            if(empty($table) || empty($join_columns)) return false;
            if(!is_array($table_info['columns']))  return false;
            foreach($table_info['columns'] as $key => $val) {
                $val = str_replace($de_table, $table, $val);
                //$val = str_replace(':', '.', $val);
                
                if(strpos($val, ':')) {
                    $columns[] = 'CONCAT('. str_replace(':', ',"   ", ', $val) .") AS $key";
                } else {
                    $columns[] = "$val as $key";
                }
                if($key=='id') {
                    $default_column = $val;
                }
            }
            
            if($aData && is_array($aData) && !empty($aData)) {
                $filter[$default_column] = $aData;
            }
            
            
            
            //print_r($columns);
            $sql_table = implode(',', $table);
            $sql_join = implode('=', $join_columns);
            $sql_where = 1;
            if($filter && is_array($filter)) {
                $sql_where = array(1);
                foreach($filter as $key => $val) {
                    $prefix = (strpos($key, $table[0])===false) ? $table[0].'.' : '';
                    if(is_array($val)) {
                        $sql_where[] = $prefix . "$key IN (". implode(',', $val) . ')';
                    } else {
                        $sql_where[] = $prefix . "$key='$val'";
                    }
                }
                
                $sql_where = implode(' AND ', $sql_where);
            }
            $sql = "SELECT count(*) AS count FROM ". $sql_table . " WHERE ". $sql_join .' AND '.  $sql_where;

            $count = $db->select($sql);
            $count = $count[0]['count'];
            $limit = ' LIMIT '. $pageLimit*($page-1) . ", $pageLimit";
            $sql = 'SELECT '. implode(',', $columns) .' FROM '. $sql_table . " WHERE ". $sql_join .' AND '.  $sql_where . $limit;
            $arr_products = $db->select($sql);
        } else {
            
            $table_name = explode('_', ltrim($table_info['table'], '_'));
            $app = array_shift($table_name);
            if(empty($table_name)) return false;
            $obj = app::get($app)->model(implode('_', $table_name));
            
            if(!is_array($table_info['columns']))  return false;
            foreach($table_info['columns'] as $key => $val) {
                $val = str_replace($table_info['table'], kernel::database()->prefix . $table_info['table'], $val);
                $val = str_replace(':', '.', $val);
                $columns[] = "$val AS $key";
                if($key=='id') {
                    if(strpos($val, '.')!==false) {
                        $default_column = substr($val, strpos($val, '.') + 1);
                    } else {
                        $default_column = $val;    
                    }
                }
                
            }
            
            
            if($aData && is_array($aData) && !empty($aData)) {
                $filter[$default_column] = $aData;
            }
            if(empty($columns)) return false;
            //总数
            $count = $obj->count($filter);
            $arr_products = $obj->getList(implode(',', $columns), $filter, $pageLimit*($page-1),$pageLimit);      
        }
        
        
        
        
          
        
        //标识用于生成url
        $token = md5("page{$page}");
        $this->pagedata['pager'] = array(
                'current'=>$page,
                'total'=>ceil($count/$pageLimit),
                'link'=>app::get('desktop')->router()->gen_url(array('app'=>'b2c', 'ctl'=>'admin_sales_goods', 'act'=>'goods_dialog', 'page'=>$token, 'arg0'=>$serialize_table_info)),
                'token'=>$token
            );

        //$arr_products = $obj->getList('name, goods_id', $filter);
        $this->pagedata['arr_products'] = $arr_products;
        if($html) 
            $this->display('admin/sales/dialog/goods.html');
   }
   
   
   public function get_defaine_dialog($aData=array(), $table_info=array()) {
        $aData['default'] = $aData['default'] ? $aData['default'] : array('false');
        $this->goods_dialog(false, $aData['default'], $table_info);
        $this->pagedata['name'] = $aData['name'];
        $this->display('admin/sales/dialog/goods_item.html');
        //$this->display('admin/sales/dialog.html');
   }
   

}
