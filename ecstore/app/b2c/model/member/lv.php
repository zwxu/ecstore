<?php



class b2c_mdl_member_lv extends dbeav_model{
    var $defaultOrder = array('point', ' ASC');
    function save($aData){
        $default_lv_id = $this->get_default_lv();
        $site_point_expried_method = $this->app->getConf('site.point_expried_method');
        if ($site_point_expried_method == '1')
            $aData['expiretime'] = 0;
        /*
        if( $aData['member_lv_id'] && $default_lv_id && $aData['member_lv_id'] != $default_lv_id ){
            $this->unset_default_lv($default_lv_id);
        }*/
        if(isset($aData['point'])) $aData['experience'] = $aData['point'];
        if(isset($aData['experience'])) $aData['point'] = $aData['experience'];
        return parent::save($aData);
    }

    /**
     * @获取会员等级列表信息
     * @access public
     * @param $cols 查询字段
     * @param $filter 查询过滤条件
     * @return void
     */
    public function getMLevel($cols='*', $filter=array()){
        $rows = $this->getList($cols,$filter);
        return  $rows ? $rows : array() ;
    }

    function get_default_lv(){
        $ret = $this->getList('member_lv_id',array('default_lv'=>1));
        return $ret[0]['member_lv_id'];
    }


    function unset_default_lv($default_lv_id){
        $sdf['member_lv_id'] = $default_lv_id;
        $sdf['default_lv'] = 0;
        $this->save($sdf);
    }

    function validate(&$data,&$msg){
       $fag = 1;
       if($data['name']==''){
             $msg = app::get('b2c')->_('等级名称不能为空！');
             $fag = 0;
        }
        $ret = $this->getList('member_lv_id',array('name'=>$data['name']));
        $member_lv_id = $ret[0]['member_lv_id'];
        $lv = $this->getList('*',array('default_lv'=>1));
        if(isset($data['point'])){
            $data['point'] = intval($data['point']);
            $filter = array('point' => $data['point']);
            $levelSwitch = app::get('b2c')->_("积分");
            $exist = $this->getList('*',$filter);
            $default_lv = $lv[0]['name'];
            if($exist && ($exist[0]['member_lv_id'] != $data['member_lv_id'])){
                $msg = app::get('b2c')->_('已存在').$levelSwitch.app::get('b2c')->_('相同的会员等级');
                $fag = 0;
            }
        }
        if(isset($data['experience'])){
            $data['experience'] = intval($data['experience']);
            $filter = array('experience' => $data['experience']);
            $levelSwitch = app::get('b2c')->_("经验值");
            $exist = $this->getList('*',$filter);
            $default_lv = $lv[0]['name'];
            if($exist && ($exist[0]['member_lv_id'] != $data['member_lv_id'])){
                $msg = app::get('b2c')->_('已存在').$levelSwitch.app::get('b2c')->_('相同的会员等级');
                $fag = 0;
            }
        }
        
        if( $member_lv_id && $member_lv_id != $data['member_lv_id']){
             $msg = app::get('b2c')->_('同名会员等级存在！');
             $fag = 0;
        }
        if(($data['default_lv'] == 1 && $default_lv)&&$data['member_lv_id'] !=$lv[0]['member_lv_id']){
             $msg = $default_lv.app::get('b2c')->_('  已是默认等级，请先取消！！');
             $fag = 0;
        }
        if($data['dis_count'] < 0 or $data['dis_count'] > 1){
             $msg = app::get('b2c')->_('会员折扣率不是有效值！');
             $fag = 0;
        }
        if($data['point'] < 0 || $data['experience'] < 0){
            $msg = $levelSwitch.app::get('b2c')->_('不能为负！');
            $fag = 0;
        }
        if($data['dis_count'] == 0){
            $data['dis_count'] = "0.0";
        }
		
		// validate service
		$site_point_expired = $this->app->getConf('site.point_expired');
		$site_point_expried_method = $this->app->getConf('site.point_expried_method');
		foreach (kernel::servicelist('member_lv_extends_validate') as $k=>$o){
			if (method_exists($o,'validate') && $fag){
				$fag = $o->validate($site_point_expired,$site_point_expried_method,$data,$msg);
			}
		}
        return $fag;
    }

     function pre_recycle($data){
       $members = $this->app->model('members');
       foreach($data as $val){
          $aData = $members->getList('member_id',array('member_lv_id' => $val['member_lv_id']));
          if($aData){
              $this->recycle_msg = app::get('b2c')->_('该等级下存在会员,不能删除');
               return false;
           }

       }
       return true;
   }

   function pre_restore(&$data,$restore_type='add'){
         if(!($this->is_exists($data['name']))){
             $data['need_delete'] = true;
             return true;
         }
         else{
             if($restore_type == 'add'){
                    $new_name = $data['name'].'_1';
                    while($this->is_exists($new_name)){
                        $new_name = $new_name.'_1';
                    }
                    $data['name'] = $new_name;
                    $data['need_delete'] = true;
                 return true;
             }
             if($restore_type == 'none'){
                 $data['need_delete'] = false;
                 return true;
             }
         }
    }

    function is_exists($name){
        $row = $this->getList('member_lv_id',array('name' => $name));
        if(!$row) return false;
        else return true;
    }

    /**
     * get_member_lv_switch
     * 会员等级升级提示信息
     *
     * @access public
     * @return array
     */
   public function get_member_lv_switch($member_lv_id=null){
           if(!$member_lv_id) return null;
           $switch_type = $this->app->getConf('site.level_switch');
           if($switch_type == 0){
               $arr_member_lv = $this->getList('member_lv_id,name,point,experience',array(),0,-1,'point ASC');
               foreach($arr_member_lv as $k => $v){
                   if($v['member_lv_id'] == $member_lv_id){
                       $i = $k+1;
                       break;
                   }
               }
               $result['show'] = ($i>=count($arr_member_lv))? 'NO' : 'YES';
               $result['lv_name'] = $arr_member_lv[$i]['name'];
               $result['lv_data'] = $arr_member_lv[$i]['point'];
               $result['switch_type'] = $switch_type;
           }
           else{
               $arr_member_lv = $this->getList('member_lv_id,name,point,experience',array(),0,-1,'experience ASC');
               foreach($arr_member_lv as $k => $v){
                   if($v['member_lv_id'] == $member_lv_id){
                       $i = $k+1;
                       break;
                   }
               }
               $result['show'] = ($i>=count($arr_member_lv))? 'NO' : 'YES';
               $result['lv_name'] = $arr_member_lv[$i]['name'];
               $result['lv_data'] = $arr_member_lv[$i]['experience'];
               $result['switch_type'] = $switch_type;
           }
           return $result;
   }

    /**
     * @获取某一会员等级详细信息
     * @access public
     * @param $cols 查询字段
     * @param $sLv 会员等级id
     * @return void
     */
    function getMLevelById($cols='*',$sLv)
    {
        $aTemp = $this->getList($cols,array('member_lv_id'=>$sLv));
        return $aTemp;
    }


}
