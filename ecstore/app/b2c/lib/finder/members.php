<?php


class b2c_finder_members{
	var $detail_basic;
	var $detail_edit;
	var $detail_advance;
	var $detail_experience;
	var $detail_point;
	var $detail_order;
	var $detail_msg;
	var $detail_remark;
	var $column_editbutton;
    var $pagelimit = 10;

    public function __construct($app)
    {
        $this->app = $app;
        $this->controller = app::get('b2c')->controller('admin_member');

		$this->detail_basic = app::get('b2c')->_('会员信息');
		$this->detail_edit = app::get('b2c')->_('编辑会员');
		$this->detail_advance = app::get('b2c')->_('预存款');
		$this->detail_experience = app::get('b2c')->_('经验值');
		$this->detail_point = app::get('b2c')->_('积分');
		$this->detail_order = app::get('b2c')->_('订单');
		$this->detail_msg = app::get('b2c')->_('站内信');
		$this->detail_remark = app::get('b2c')->_('会员备注');
		$this->column_editbutton = app::get('b2c')->_('操作');
    }

    function detail_basic($member_id){
        $app = app::get('b2c');
        $member_model = $app->model('members');
        $mem = $member_model->dump($member_id);
        $a_mem = $member_model->dump($member_id,'*',array( ':account@pam'=>array('*')));
        $mem_schema = $member_model->_columns();

		$obj_extend_point = kernel::service('b2c.member_extend_point_info');
		if ($obj_extend_point)
		{
			// 当前会员拥有的积分
			$obj_extend_point->get_real_point($member_id, $a_mem['score']['total']);
			// 当前会员实际可以使用的积分
			$obj_extend_point->get_usage_point($member_id, $a_mem['score']['usage']);
		}
        $attr =array();
            foreach($app->model('member_attr')->getList() as $item){
            if($item['attr_show'] == "true" && $item['attr_group']!='defalut') $attr[] = $item; //筛选显示项
        }
        foreach((array)$attr as $key=>$item){
            $sdfpath = $mem_schema[$item['attr_column']]['sdfpath'];
            if($sdfpath){
                $a_temp = explode("/",$sdfpath);
                if(count($a_temp) > 1){
                    $name = array_shift($a_temp);
                    if(count($a_temp))
                    foreach($a_temp  as $value){
                        $name .= '['.$value.']';
                    }
                }
            }else{
                $name = $item['attr_column'];
            }
            #$attr[$key]['attr_value'] = $mem_flat[$sdfpath];
            if($item['attr_group'] == 'defalut'){
             switch($attr[$key]['attr_column']){
                    case 'area':
                    $attr[$key]['attr_value'] = $mem['contact']['area'];
                    break;
                     case 'birthday':
                    $attr[$key]['attr_value'] = $mem['profile']['birthday'];
                    break;
                    case 'name':
                    $attr[$key]['attr_value'] = $mem['contact']['name'];
                    break;
                    case 'mobile':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['mobile'];
                    break;
                    case 'tel':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['telephone'];
                    break;
                    case 'zip':
                    $attr[$key]['attr_value'] = $mem['contact']['zipcode'];
                    break;
                    case 'addr':
                    $attr[$key]['attr_value'] = $mem['contact']['addr'];
                    break;
                    case 'sex':
                    $attr[$key]['attr_value'] = $mem['profile']['gender'];
                    break;
                    case 'pw_answer':
                    $attr[$key]['attr_value'] = $mem['account']['pw_answer'];
                    break;
                    case 'pw_question':
                    $attr[$key]['attr_value'] = $mem['account']['pw_question'];
                    break;
                   }
           }
          if($item['attr_group'] == 'contact'||$item['attr_group'] == 'input'||$item['attr_group'] == 'select'){
              $attr[$key]['attr_value'] = $mem['contact'][$attr[$key]['attr_column']];
              if($item['attr_sdfpath'] == ""){
              $attr[$key]['attr_value'] = $mem[$attr[$key]['attr_column']];
              if($attr[$key]['attr_type'] =="checkbox"){
              $value = unserialize($mem[$attr[$key]['attr_column']]);
              foreach((array)$value as $val){
                  $v.= ($val.';');
                  }
                  if($v == ';') $v = '';
                  $attr[$key]['attr_value'] = $v;
              }
          }
          }

          $attr[$key]['attr_column'] = $name;
          if($attr[$key]['attr_column']=="birthday"){
              $attr[$key]['attr_column'] = "profile[birthday]";
          }
          if($attr[$key]['attr_type'] =="select" || $attr[$key]['attr_type'] =="checkbox"){
              $attr[$key]['attr_option'] = unserialize($attr[$key]['attr_option']);
          }
        }
        $render = $app->render();
        $render->pagedata['attr'] = $attr;
        $render->pagedata['mem'] = $a_mem;
        $render->pagedata['member_id'] = $member_id;
        // 判断是否使用了推广服务
        $is_bklinks = 'false';
        $obj_input_helpers = kernel::servicelist("html_input");
        if (isset($obj_input_helpers) && $obj_input_helpers)
        {
            foreach ($obj_input_helpers as $obj_bdlink_input_helper)
            {
                if (get_class($obj_bdlink_input_helper) == 'bdlink_input_helper')
                {
                    $is_bklinks = 'true';
                }
            }
        }
        $render->pagedata['is_bklinks'] = $is_bklinks;
        return $render->fetch('admin/member/detail.html');
    }


    function detail_edit($member_id){
        $app = app::get('b2c');
        $member_model = $app->model('members');

        if($_POST){
            $_POST['member_id'] = $member_id;

            foreach($_POST as $key => $val){
                if(strpos($key,"box:") !== false){
                    $aTmp = explode("box:", $key);
                    $_POST[$aTmp[1]] = serialize($val);
                }
            }

           
            if($member_model->is_exists_email($_POST['contact']['email'], $member_id)){
                $msg = app::get('b2c')->_('邮箱已经存在!');
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"' . $msg . '",_:null}';
                exit;
            }

            //当手机号码不为空时，验证手机号码是否正确，并检查手机号码是否已存在 
            if($_POST['contact']['phone']['mobile'] != ''){
                if(!$member_model->check_mobile($_POST['contact']['phone']['mobile'], $msg, $member_id)){
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"' . $msg . '",_:null}';
                    exit;
                }

                if($member_model->is_exists_mobile($_POST['contact']['phone']['mobile'], $member_id)){
                    $msg = app::get('b2c')->_('手机号码已经存在!');
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"' . $msg . '",_:null}';
                    exit;
                }
            }
            // end

			//同步到ucenter yindingsheng
			if( $member_object = kernel::service("uc_user_edit")) {
				if(!$member_object->uc_user_edit($_POST)){
					$msg = app::get('b2c')->_('第三方同步失败!请核对信息');
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"' . $msg . '",_:null}';
                    exit;
				}
			}
			//同步到ucenter yindingsheng

            if(!$member_model->save($_POST)){
                if($_GET['target'] == "_blank"){
                    $msg = app::get('b2c')->_('保存失败!');
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"' . $msg . '",_:null}';
                    exit;
                }
            }else{
                //增加会员同步 2012-05-15
                #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                if($obj_operatorlogs = kernel::service('operatorlog.b2c_mdl_members')){
                    if(method_exists($obj_operatorlogs, 'logMemberModifyInfo')){
                        $obj_operatorlogs->logMemberModifyInfo($_POST);
                    }
                }
                #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

                if($member_rpc_object = kernel::service("b2c_member_rpc_sync")){
                    $member_rpc_object->modifyActive($member_id);
                }
            }
            
        }

        $mem = $member_model->dump($member_id);
        $a_mem = $member_model->dump($member_id,'*',array( ':account@pam'=>array('*')));
        $member_lv=$app->model("member_lv");
        foreach($member_lv->getMLevel() as $row){
            $options[$row['member_lv_id']] = $row['name'];
        }
        $a_mem['lv']['options'] = is_array($options) ? $options : array(app::get('b2c')->_('请添加会员等级')) ;
        $a_mem['lv']['value'] = $a_mem['member_lv']['member_group_id'];
        $mem_schema = $member_model->_columns();
        $attr =array();
            foreach($app->model('member_attr')->getList() as $item){
            if($item['attr_show'] == "true" || $item['attr_group'] == 'defalut') $attr[] = $item; //筛选显示项
        }
        foreach((array)$attr as $key=>$item){
            $sdfpath = $mem_schema[$item['attr_column']]['sdfpath'];
            if($sdfpath){
                $a_temp = explode("/",$sdfpath);
                if(count($a_temp) > 1){
                    $name = array_shift($a_temp);
                    if(count($a_temp))
                    foreach($a_temp  as $value){
                        $name .= '['.$value.']';
                    }
                }
            }else{
                $name = $item['attr_column'];
            }
            if($item['attr_group'] == 'defalut'){
             switch($attr[$key]['attr_column']){
                    case 'area':
                    $attr[$key]['attr_value'] = $mem['contact']['area'];
                    break;
                     case 'birthday':
                    $attr[$key]['attr_value'] = $mem['profile']['birthday'];
                    break;
                    case 'name':
                    $attr[$key]['attr_value'] = $mem['contact']['name'];
                    break;
                    case 'mobile':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['mobile'];
                    break;
                    case 'tel':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['telephone'];
                    break;
                    case 'zip':
                    $attr[$key]['attr_value'] = $mem['contact']['zipcode'];
                    break;
                    case 'addr':
                    $attr[$key]['attr_value'] = $mem['contact']['addr'];
                    break;
                    case 'sex':
                    $attr[$key]['attr_value'] = $mem['profile']['gender'];
                    break;
                    case 'pw_answer':
                    $attr[$key]['attr_value'] = $mem['account']['pw_answer'];
                    break;
                    case 'pw_question':
                    $attr[$key]['attr_value'] = $mem['account']['pw_question'];
                    break;
                   }
           }
          if($item['attr_group'] == 'contact'||$item['attr_group'] == 'input'||$item['attr_group'] == 'select'){
              $attr[$key]['attr_value'] = $mem['contact'][$attr[$key]['attr_column']];
              if($item['attr_sdfpath'] == ""){
              $attr[$key]['attr_value'] = $mem[$attr[$key]['attr_column']];
              if($attr[$key]['attr_type'] =="checkbox"){
              $attr[$key]['attr_value'] = unserialize($mem[$attr[$key]['attr_column']]);
              }
          }
          }

          $attr[$key]['attr_column'] = $name;
          if($attr[$key]['attr_column']=="birthday"){
              $attr[$key]['attr_column'] = "profile[birthday]";
          }
          if($attr[$key]['attr_type'] =="select" || $attr[$key]['attr_type'] =="checkbox"){
              $attr[$key]['attr_option'] = unserialize($attr[$key]['attr_option']);
          }
        }
        $render = $app->render();
        $render->pagedata['attr'] = $attr;
        $render->pagedata['mem'] = $a_mem;
        $render->pagedata['member_id'] = $member_id;
        return $render->fetch('admin/member/edit.html');
    }


    function detail_advance($member_id=null){
        if(!$member_id) return null;
        $nPage = $_GET['detail_advance'] ? $_GET['detail_advance'] : 1;
        $singlepage = $_GET['singlepage'] ? $_GET['singlepage']:false;
        $app = app::get('b2c');
        $member = $app->model('members');
        $mem_adv =  $app->model('member_advance');
        if($_POST){
            if(!$mem_adv->adj_amount($member_id,$_POST,$msg,false)){
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"'.$msg.'",_:null}';
                    exit;
            }
        }

       // $items_adv = $mem_adv->get_list_bymemId($member_id );
        $data = $member->dump($member_id,'*',array('advance/event'=>array('*',null,array($this->pagelimit*($nPage-1),$this->pagelimit))));
        $items_adv = $data['advance']['event'];
        //后台会员列表，详细栏的预存款,支付方式改为显示中文-@lujy-start
        foreach($items_adv as $key=>$item){
            if(!empty($item['paymethod'])){
               $oPayName = app::get('ectools')->model('payment_cfgs');
               $items_adv[$key]['paymethod'] = $oPayName->get_app_display_name($item['paymethod']);
            }
        }
        //--end
        if($member_id){
             $row = $mem_adv->getList('log_id',array('member_id' => $member_id));
             $count = count($row);
        }
        $render = $app->render();
        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_advance';
        $this->controller->pagination($nPage,$count,$_GET);
        $render->pagedata['items_adv'] = $items_adv;
        $render->pagedata['member'] = $member->dump($member_id,'advance');
        return $render->fetch('admin/member/advance_list.html');
    }


    function detail_experience($member_id){
        $app = app::get('b2c');
        $member = $app->model('members');
        $aMem = $member->dump($member_id,'*',array('contact'=>array('*')));
            if($_POST){
               if(!$member->change_exp($member_id,$_POST['experience'],$msg)){
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"'.$msg.'",_:null}';
                    exit;
               }
            }
            $aMem = $member->dump($member_id,'*',array('contact'=>array('*')));
            $render = $app->render();
            $render->pagedata['mem'] = $aMem;
            return $render->fetch('admin/member/experience.html');
    }


    function detail_point($member_id=null){
        if(!$member_id) return null;
        $nPage = $_GET['detail_point'] ? $_GET['detail_point'] : 1;
        $singlepage = $_GET['singlepage'] ? $_GET['singlepage']:false;
        $app = app::get('b2c');
        $member = $app->model('members');
        $mem_point = $app->model('member_point');
        $obj_user = kernel::single('desktop_user');

        if($_POST){
            $change_point = $_POST['modify_point'];
            $msg = $_POST['modify_remark'];
            if($mem_point->change_point($member_id,$change_point,$msg,'operator_adjust',3,0,$obj_user->user_id,'charge')){
            }
            else{
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"'.$msg.'",_:null}';
                    exit;
            }
        }
        if($member_id){
             $row = $mem_point->getList('id',array('member_id' => $member_id));
             $count = count($row);
        }
        $data = $member->dump($member_id,'*',array('score/event'=>array('*',null,array($this->pagelimit*($nPage-1),$this->pagelimit))));
        $accountObj = app::get('pam')->model('account');
        //获取日志操作管理员名称@lujy--start--
        foreach($data['score']['event'] as $key=>$val){
            $operatorInfo = $accountObj->getList('login_name',array('account_id' => $val['operator']));
            $data['score']['event'][$key]['operator_name'] = $operatorInfo['0']['login_name'];
        }
        //--end--
       //echo $nPage;
        $render = $app->render();
		$obj_extend_point = kernel::service('b2c.member_extend_point_info');
		if ($obj_extend_point)
		{
			// 当前会员拥有的积分
			$obj_extend_point->get_real_point($member_id, $data['score']['total']);
			// 当前会员实际可以使用的积分
			$obj_extend_point->get_usage_point($member_id, $data['score']['usage']);
		}
		else
		{
			$data['score']['total'] = $mem_point->get_total_count($member_id);
			$data['score']['usage'] = $mem_point->get_total_count($member_id);
		}
        $render->pagedata['member'] = $data;
        $render->pagedata['event'] = $data['score']['event'];

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_point';
        $this->controller->pagination($nPage,$count,$_GET);
        return $render->fetch('admin/member/point_list.html');
    }


    function detail_order($member_id=null){
        if(!$member_id) return null;
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('b2c');
        $member = $app->model('members');
         $orders = $member->getOrderByMemId($member_id,$this->pagelimit*($nPage-1),$this->pagelimit);
         $order =  $app->model('orders');
         if($member_id){
             $row = $order->getList('order_id',array('member_id' => $member_id));
             $count = count($row);
         }
         foreach($orders as $key=>$order1){
             $orders[$key]['status'] = $order->trasform_status('status',$orders[$key]['status']);
             $orders[$key]['pay_status'] = $order->trasform_status('pay_status',$orders[$key]['pay_status'] );
             $orders[$key]['ship_status'] = $order->trasform_status('ship_status', $orders[$key]['ship_status']);
         }

         $render = $app->render();
         $render->pagedata['orders'] = $orders;
         if($_GET['page']) unset($_GET['page']);
         $_GET['page'] = 'detail_order';
         $this->controller->pagination($nPage,$count,$_GET);
         return $render->fetch('admin/member/order.html');
    }


    function detail_msg($member_id){
        if(!$member_id) return null;
		$member_id = intval($member_id);
        $nPage = $_GET['detail_msg'] ? $_GET['detail_msg'] : 1;
        $app = app::get('b2c');
        $obj_msg = kernel::single('b2c_message_msg');
        $this->db = kernel::database();
        $_count_row = $this->db->select('select * from sdb_b2c_member_comments where has_sent="true" and object_type="msg" and (to_id ='.$this->db->quote($member_id).' or author_id='.$this->db->quote($member_id).')');
        $row = $this->db->select('select * from sdb_b2c_member_comments where has_sent="true" and object_type="msg" and (to_id ='.$this->db->quote($member_id).' or author_id='.$this->db->quote($member_id).') limit '.$this->pagelimit*($nPage-1).','.$this->pagelimit);
        $count = count($_count_row);
        $render = $app->render();
        $render->pagedata['msgs'] =  $row;
        if($_GET['page']) unset($_GET['page']);
         $_GET['page'] = 'detail_msg';
         $this->controller->pagination($nPage,$count,$_GET);
        return $render->fetch('admin/member/member_msg.html');
    }


    function detail_remark($member_id){
        $app = app::get('b2c');
        $member = $app->model('members');
        if($_POST){
            $sdf['remark'] = $_POST['remark'];
            $sdf['remark_type'] = $_POST['remark_type'];
            if(!$member->update($sdf,array('member_id' => $member_id))){
                    $msg = app::get('b2c')->_('保存失败!');
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"'.$msg.'",_:null}';
                    exit;
            }
            if($_GET['singlepage']=='true'){
                 $msg = app::get('b2c')->_('保存成功!');
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{success:"'.$msg.'",_:null}';
                    exit;
            }
        }
        $remark = $member->getRemarkByMemId($member_id);
        $render = $app->render();
        $render->pagedata['remark_type'] = $remark['remark_type'];
        $render->pagedata['remark'] =  $remark['remark'];
        $render->pagedata['res_url'] = $app->res_url;
        return $render->fetch('admin/member/remark.html');
    }


    public function column_editbutton($row)
    {
        $render = $this->app->render();
        $arr = array(
            'app'=>$_GET['app'],
            'ctl'=>$_GET['ctl'],
            'act'=>$_GET['act'],
            'finder_id'=>$_GET['_finder']['finder_id'],
            'action'=>'detail',
            'finder_name'=>$_GET['_finder']['finder_id'],
        );

        $arr_link = array(
            'info'=>array(
                'detail_edit'=>array(
					'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_edit&id='.$row['member_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('b2c')->_('编辑会员信息'),
					'target'=>'tab',
                ),
            ),
            'finder'=>array(
                'detail_advance'=>array(
					'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_advance&id='.$row['member_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('b2c')->_('预存款'),
                    'target'=>'tab',
                ),
                'detail_experience'=>array(
					'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_experience&id='.$row['member_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('b2c')->_('经验值'),
                    'target'=>'tab',
                ),
                'detail_point'=>array(
					'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_point&id='.$row['member_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('b2c')->_('积分'),
                    'target'=>'tab',
                ),
                'detail_remark'=>array(
					'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_remark&id='.$row['member_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('b2c')->_('会员备注'),
                    'target'=>'tab',
                ),
            ),
        );

        //增加编辑菜单权限@lujy
        $permObj = kernel::single('desktop_controller');
        if(!$permObj->has_permission('editadvance')){
            unset($arr_link['finder']['detail_advance']);
        }
        if(!$permObj->has_permission('editexp')){
            unset($arr_link['finder']['detail_experience']);
        }
        if(!$permObj->has_permission('editadvance')){
            unset($arr_link['finder']['editscore']);
        }


        $site_get_policy_method = $this->app->getConf('site.get_policy.method');
        if ($site_get_policy_method == '1')
        {
            unset($arr_link['finder']['detail_point']);
        }

        $render->pagedata['arr_link'] = $arr_link;
        $render->pagedata['handle_title'] = app::get('b2c')->_('编辑');
        $render->pagedata['is_active'] = 'true';
        return $render->fetch('admin/actions.html');
    }
}
