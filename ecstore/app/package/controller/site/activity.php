<?php
class package_ctl_site_activity extends business_ctl_site_member{
    public function __construct(&$app){
        $this->b2c_app=app::get('b2c');
        parent::__construct($this->b2c_app);
        $this->app_current=$app;
        $this->pagedata['gdlytype_filter'] = array('store_id'=>$this->store_id,'dt_status'=>'1','disabled'=>'false');
    }
    
    public function attend($nPage=1, $filter=null){
        $member_id = $this->app->member_id;
        $store_id = $this->store_id;
        $store_region = $this->region_id;
        $storemember = app::get('business')->model('storemember');
        $storemanger = app::get('business')->model('storemanger');
        $this->pagedata['controller'] = 'promotions';

        $oActivity = app::get('package')->model('activity');
        $limit = 10;
        $activityInfo = $oActivity->getList('*',array('act_open'=>'true'),$limit*($nPage-1),$limit);
        $now = time();
        foreach($activityInfo as $k=>$v){
            if($now > $v['end_time'] || empty($v['business_type'])){
                unset($activityInfo[$k]);
                continue;
            }
            $businee_type =array_filter(explode(',',$v['business_type']));
            $ret = array_intersect($businee_type,$store_region);
            if(!$ret){
                unset($activityInfo[$k]);
                continue;
            }
        }
        foreach($activityInfo as $key=>$value){
            $activityInfo[$key]['start_time'] = date('Y-m-d',$value['start_time']);
            $activityInfo[$key]['end_time'] = date('Y-m-d',$value['end_time']);
        }
        
        $this->pagedata['activity'] = $activityInfo;
        $this->pagedata['_PAGE_'] = 'activity.html';
        
        //加载活动tab start 
        $business_activity_cat = kernel::service('business_activity_cat');
        if($business_activity_cat){
            $activityTab = $business_activity_cat->loadActivityCat();
            $this->pagedata['activity_tab'] = $activityTab;
            $this->pagedata['activity_tab_cur'] = 'package';
        }
        //加载活动tab end
        $limitStart = $nPage * $limit;
        $countai = $oActivity->count(array('act_open'=>'true'));
        $total = ceil($countai/$limit);
        $this->pagination($nPage,$total,'attend','','package','site_activity');
        $this->pagedata['img_size'] = app::get('b2c')->getConf('site.big_pic_width').'*'.app::get('b2c')->getConf('site.big_pic_height');
        $this->output('package');
    }
    
    public function toAttend($id){
        $oActivity = app::get('package')->model('activity');
        $this->pagedata['controller'] = 'promotions';
        if(!$this->checkAttendActivity()){
            $this->end(false,'由于您没有及时发货暂停申请捆绑活动！');
        }
        $activityInfo = $oActivity->getList('*',array('act_id'=>$id));
        $activityInfo = $activityInfo[0];
        $activityInfo['start_time'] = date('Y-m-d',$activityInfo['start_time']);
        $activityInfo['end_time'] = date('Y-m-d',$activityInfo['end_time']);
        $this->pagedata['actInfo'] = $activityInfo;
        
        $store_region = $this->region_id;
        $businee_type = array();
        $businee_type =array_filter(explode(',',$activityInfo['business_type']));
        $ret = array_intersect($businee_type,$store_region);
        if($store_region&&!$ret){
            $this->splash('failed', $this->gen_url(array('app' => 'package', 'ctl' => 'site_activity', 'act' => 'attend')), app::get('b2c')->_('你的经营范围跟活动不符'));
        }
        $this->pagedata['_PAGE_'] = 'attend.html';
        $store_id = $this->store_id;
        $this->pagedata['point_mim_get_value'] = app::get('b2c')->getConf('site.point_mim_get_value')*100;//运营商设置的兑换积分的最低比例
        $this->pagedata['point_max_get_value'] = $this->app_b2c->getConf('site.point_max_get_value')*100;//运营商设置的兑换积分的最高比例
        $this->pagedata['current_url'] = app::get('business')->res_url;
        $filter = array('goods_type'=>'normal','marketable'=>'true','store_id'=>$store_id,'act_type'=>'normal');
        $this->pagedata['store_id'] = $store_id;
        $this->pagedata['filter'] = $filter;
        $this->pagedata['return_url'] = $this->gen_url( array('app'=>'package','ctl'=>'site_activity','act'=>'get_goods_info') );
        $this->pagedata['submit_url'] = $this->gen_url( array('app'=>'package','ctl'=>'site_activity','act'=>'saveToAttend') );
        $this->output('package');
    }
    
    public function get_goods_info(){
        $data = $_POST['data'];
        if(!$data){
            echo $this->fetch('site/member/input.html','package');exit;
        }
        $arr = app::get('b2c')->model('goods')->getList('goods_id,name,price,store,brief',array('goods_id|in'=>array_values($data)),0,-1);
        $this->pagedata['json_data'] = json_encode($arr);
        $this->pagedata['_input'] = array(
            'items' => $arr,
            'idcol' => 'goods_id',
            'name' => 'gid',
            '_textcol' => 'name',
            'view_app' => 'package',
            'view' => '',
        );
        echo $this->fetch('site/member/input.html','package');
    }
    
    public function saveToAttend(){
       $data = $this->_request->get_post();
       $this->begin();
       $url = $this->gen_url( array('app'=>'package','ctl'=>'site_activity','act'=>'myAttend') );
       if(!$this->checkAttendActivity()){
            $this->end(false,'由于您没有及时发货暂停申请团购活动！');
        }
       if(!$data['gid']){
           $this->end(false,'请选择商品',$url);
       }
       $objGoods = app::get('b2c')->model('goods');
       $goods = $objGoods->getList('goods_id,store,price,weight',array('goods_id|in'=>$data['gid']),0,-1);
       if(!$goods){
          $this->end(false,'请选择商品',$url);
       }
       if(count($goods) == 1){
          $this->end(false,'参加活动商品不得少于两件',$url);
       }
       $data['gid'] = array();
       $goods_id = array();
       $temp['account'] = 0;
       foreach((array)$goods as $items){
          if($data['store']&&$data['store']>$items['store']){
              //$this->end(false,'参加活动的商品数量不能大于库存',$url);
          }
          if(empty($data['store'])){
              $data['store'] = $items['store'];
          }
          $data['store'] = min($data['store'], $items['store']);
          $temp['account'] += $items['price'];
          $data['weight'] += $items['weight']+0.0;
          $data['gid'][] = $items['goods_id'];
          $goods_id[] = $items['goods_id'];
       }
       $up_info = array();
       if($data['id']){
          $objAttend = app::get('package')->model('attendactivity');
          $old_info = $objAttend->getList('gid',array('id'=>intval($data['id'])));
          $old_info = $old_info[0]['gid'];
          $old_info = array_filter(explode(',',$old_info));
          foreach((array)$old_info as $item){
              if(!in_array($item,$goods_id)){
                  $up_info[] = $item;
              }
          }
       }
       if(empty($data['amount'])) $data['amount'] = $temp['account'];
       if(empty($data['presonlimit'])) $data['presonlimit'] = 1;
       $data['gid'] = !empty($data['gid'])?','.implode(',', $data['gid']).',':'';
       $member_id = $this->app->member_id;
       if(!$member_id){
           $this->redirect(array('app'=>'b2c', 'ctl'=>'site_passport', 'act'=>'error'));
       }
       $data['image'] = $data['image_default'];
       unset($data['image_default']);
       $data['member_id'] = $member_id;
       $data['store_id'] = $this->store_id;
       $arr_remove_image = array();
        if( $data['goods']['images'] ){
            $oImage_attach = app::get('image')->model('image_attach');
            $arr_image_attach = $oImage_attach->getList('*',array('target_id'=>$data['id'],'target_type'=>'package'));
            foreach ((array)$arr_image_attach as $_arr_image_attach){
                if (!in_array($_arr_image_attach['image_id'],$data['goods']['images'])){
                    $arr_remove_image[] = $_arr_image_attach['image_id'];
                }
            }
        }
       $object = kernel::single('package_business_activity');
       if($object->addattendactivity($data)){
            $temp = array('act_type' => 'package');
            if($goods_id) $objGoods->update($temp,array('goods_id|in'=>$goods_id));
            $temp = array('act_type' => 'normal');
            if($up_info) $objGoods->update($temp,array('goods_id|in'=>$up_info));
            if( $data['goods']['images'] ){
                $oImage = &app::get('business')->model('image');
                if ($arr_remove_image){
                    foreach($arr_remove_image as $_arr_remove_image)
                        $test = $oImage->delete_image($_arr_remove_image,'package',$this->store_id);
                }
                foreach($data['goods']['images'] as $k=>$v){
                    $test = $oImage->rebuild($v['image_id'],array('S','M','L'),true,$this->store_id,0);
                }
            }
            $data_gdlytype = array_values((array)array_filter((array)$data['gdlytype']));
            $objGoodsDly = app::get('b2c')->model('goods_dly');
            if(is_array($data_gdlytype) && !empty($data_gdlytype)){
                $data_insert = array();
                $data_delete = array();
                $count_new = count($data_gdlytype);
                foreach((array)$objGoodsDly->getList('dly_id',array('goods_id'=>$data['id'],'manual'=>'package'),0,-1) as $key => $rows){
                    if($key < $count_new){
                        $sql = ' update sdb_b2c_goods_dly '.
                            ' set dly_id='.intval($data_gdlytype[$key]).
                            ' where goods_id='.intval($data['id']).' and dly_id='.intval($rows['dly_id']).' and manual=\'package\'';
                        $objGoodsDly->db->exec($sql);
                        unset($data_gdlytype[$key]);
                    }else{
                        $data_delete[] = intval($rows['dly_id']); 
                    }
                }
                foreach((array)$data_gdlytype as $item){
                    if(!empty($item))$data_insert[] = '('.intval($data['id']).','.intval($item).',\'package\')';
                }
                if(count($data_delete)){
                    $objGoodsDly->db->exec('delete from sdb_b2c_goods_dly where goods_id ='.intval($data['id']).' and dly_id in ('.implode(',',$data_delete).') and manual=\'package\'');
                }
                if(count($data_insert)){
                    $objGoodsDly->db->exec('insert into sdb_b2c_goods_dly (goods_id,dly_id,manual) values '.implode(',',$data_insert));
                }
            }else{
                $objGoodsDly->db->exec('delete from sdb_b2c_goods_dly where goods_id ='.intval($data['id']).' and manual=\'package\'');
            }
            $this->end(true,'申请成功',$url);
       }else
       $this->end(false,'申请失败',$url);
    }
    
    public function myAttend($nPage=1, $filter=null){
        $attendactivity = app::get('package')->model('attendactivity');
        $objGoods = app::get('b2c')->model('goods');
        $activity = app::get('package')->model('activity');
        $this->pagedata['controller'] = 'promotions';
        $imageDefault = app::get('image')->getConf('image.set');
        $limit = 10;
        $attendactivityinfo = $attendactivity->getList('*',array('store_id'=>$this->store_id),$limit*($nPage-1),$limit);
        foreach($attendactivityinfo as $k=>$v){
            $actInfo = $activity->getList('*',array('act_id'=>$v['aid']));
            $attendactivityinfo[$k]['actInfo'] = $actInfo[0];
            $goods_id = array_filter(explode(',', $v['gid']));
            if($goods_id){
                $goods_info = $objGoods->getList('goods_id,name,image_default_id,price,udfimg ,thumbnail_pic', array('goods_id|in'=>$goods_id,'store_id'=>$this->store_id),0,-1);
                foreach((array)$goods_info as $key => $value){
                    if($value['udfimg']){
                        $goods_info[$key]['image_id'] = $value['thumbnail_pic']?$value['thumbnail_pic']:$imageDefault['S']['default_image'];
                    }else{
                        $goods_info[$key]['image_id'] = $value['image_default_id']?$value['image_default_id']:$imageDefault['S']['default_image'];
                    }
                    unset($value['udfimg'],$value['thumbnail_pic'],$value['image_default_id']);
                }
                $attendactivityinfo[$k]['goodsInfo'] = $goods_info;
            }
        }
        //加载活动申请tab start 
        $business_activity_cat = kernel::service('business_activity_apply_tag');
        if($business_activity_cat){
            $activityTab = $business_activity_cat->loadActivityApplyTag();
            $this->pagedata['activity_tab'] = $activityTab;
            $this->pagedata['activity_tab_cur'] = 'package';
        }
        //加载活动申请tab end 
        $this->pagedata['busiAct'] = $attendactivityinfo;
        $this->pagedata['store_id'] = $this->store_id;
        $this->pagedata['IMAGE_MAX_SIZE'] = IMAGE_MAX_SIZE;
        $this->pagedata['img_size'] = app::get('b2c')->getConf('site.big_pic_width').'*'.app::get('b2c')->getConf('site.big_pic_height');
        $this->pagedata['current_url'] = app::get('business')->res_url;
        $this->pagedata['_PAGE_'] = 'businessAct.html';
        $limitStart = $nPage * $limit;
        $countai = $attendactivity->count(array('store_id'=>$this->store_id));
        $total = ceil($countai/$limit);
        $this->pagination($nPage,$total,'myAttend','','package','site_activity');
        $this->output('package');
    }

    public function editAttend($id){
        $attendactivity = app::get('package')->model('attendactivity');
        $attendactivityinfo = $attendactivity->getList('*',array('id'=>$id));
        $attendactivityinfo = $attendactivityinfo[0];
        $this->pagedata['goods'] = array();
        $this->pagedata['goods']['image_default_id'] = $attendactivityinfo['image'];
        $attendactivityinfo['gdlytype'] = array();
        foreach((array)app::get('b2c')->model('goods_dly')->getList('dly_id',array('goods_id'=>$id,'manual'=>'package'),0,-1) as $items){
            $attendactivityinfo['gdlytype'][] = $items['dly_id'];
        }
        $this->pagedata['businessAct'] = $attendactivityinfo;//end 申请信息
        if($attendactivityinfo['status']!=3){
            //$this->splash('failed', $this->gen_url(array('app' => 'package', 'ctl' => 'site_activity', 'act' => 'myAttend')), app::get('b2c')->_('该申请暂不能编辑'));
        }
        if($this->store_id != $attendactivityinfo['store_id']){
            $this->splash('failed', $this->gen_url(array('app' => 'package', 'ctl' => 'site_activity', 'act' => 'myAttend')), app::get('b2c')->_('只能编辑本店铺的申请'));
        }
        $oActivity = app::get('package')->model('activity');
        $activityInfo = $oActivity->getList('*',array('act_id'=>$attendactivityinfo['aid']));
        $activityInfo = $activityInfo[0];
        $activityInfo['start_time'] = date('Y-m-d',$activityInfo['start_time']);
        $activityInfo['end_time'] = date('Y-m-d',$activityInfo['end_time']);
        $this->pagedata['actInfo'] = $activityInfo;//end 活动信息

        $attendactivityinfo['gid'] = array_filter(explode(',', $attendactivityinfo['gid']));
        if($attendactivityinfo['gid']){
            $goods = app::get('b2c')->model('goods')->getList('price,store',array('goods_id'=>$attendactivityinfo['gid']),0,-1);
            foreach((array)$goods as $key => $value){
                if(!$this->pagedata['goods']['store']){
                    $this->pagedata['goods']['store'] = intval($value['store']);
                }
                $this->pagedata['goods']['store'] = min($this->pagedata['goods']['store'], intval($value['store']));
                $this->pagedata['goods']['price'] += $value['price']+0.0;
            }
        }
        
        $this->pagedata['goods']['images'] = app::get('image')->model('image_attach')->getList('image_id',array('target_type'=>'package','target_id'=>$id),0,-1);
        //end 商品信息
        
        $this->pagedata['_PAGE_'] = 'attend.html';
        $store_id = $this->store_id;
        $this->pagedata['point_mim_get_value'] = app::get('b2c')->getConf('site.point_mim_get_value')*100;//运营商设置的兑换积分的最低比例
        $this->pagedata['point_max_get_value'] = $this->app_b2c->getConf('site.point_max_get_value')*100;//运营商设置的兑换积分的最高比例
        $this->pagedata['current_url'] = app::get('business')->res_url;
        $filter = array('goods_type'=>'normal','marketable'=>'true','store_id'=>$store_id,'act_type'=>'normal');
        $temp = app::get('b2c')->model('goods')->getList('goods_id',$filter,0,-1);
        $filter = array('goods_id'=>array_merge($attendactivityinfo['gid']));
        foreach((array)$temp as $item){
            $filter['goods_id'][] = $item['goods_id'];
        }
        $this->pagedata['store_id'] = $store_id;
        $this->pagedata['filter'] = $filter;
        $this->pagedata['return_url'] = $this->gen_url( array('app'=>'package','ctl'=>'site_activity','act'=>'get_goods_info') );
        $this->pagedata['submit_url'] = $this->gen_url( array('app'=>'package','ctl'=>'site_activity','act'=>'saveToAttend') );
        $this->output('package');
    }

    public function quitActivity($id){
        $attendactivity = app::get('package')->model('attendactivity');
        $this->begin($this->gen_url( array('app'=>'package','ctl'=>'site_activity','act'=>'myAttend') ));
        
        if(!$id){
            $this->end(false,'请选择一个申请！');
        }
        $objGoods = app::get('b2c')->model('goods');
        foreach((array)$attendactivity->getList('gid',array('id'=>$id),0,-1) as $k=>$v){
            $garr = array('act_type'=>'normal');
            $v['gid'] = array_filter(explode(',',$v['gid']));
            if($v['gid'])
            $objGoods->update($garr,array('goods_id'=>$v['gid']));
        }
        $re = $attendactivity->delete(array('id'=>$id));
        if($re){
            app::get('b2c')->model('goods_dly')->delete(array('goods_id'=>$id,'manual'=>'package'));
            $this->end(true,'删除成功！');
        }
        $this->end(false,'删除失败！');
    }
    
    public function goods_list(){
        $id = $_POST['id'];
        $attendactivity = app::get('package')->model('attendactivity');
        $goods_id = $attendactivity->getList('gid',array('store_id'=>$this->store_id,'id'=>$id));
        if(empty($goods_id)){
            echo '{error:"'.app::get('b2c')->_('参数错误').'",_:null}';exit;
        }
        $goods_id = $goods_id[0]['gid'];
        $goods_id = array_filter(explode(',', $goods_id));
        $goods_info = array();
        if(empty($goods_id)){
            echo '{error:"'.app::get('b2c')->_('没有捆绑的商品信息').'",_:null}';exit;
        }else{
            $objGoods = app::get('b2c')->model('goods');
            $goods_info = $objGoods->getList('goods_id,name,image_default_id,price,udfimg ,thumbnail_pic', array('goods_id|in'=>$goods_id),0,-1);
            if(empty($goods_info)){
                echo '{error:"'.app::get('b2c')->_('没有捆绑的商品信息').'",_:null}';exit;
            }
        }
        $imageDefault = app::get('image')->getConf('image.set');
        foreach((array)$goods_info as $key => $value){
            if($value['udfimg']){
                $goods_info[$key]['image_id'] = $value['thumbnail_pic']?$value['thumbnail_pic']:$imageDefault['S']['default_image'];
            }else{
                $goods_info[$key]['image_id'] = $value['image_default_id']?$value['image_default_id']:$imageDefault['S']['default_image'];
            }
            unset($value['udfimg'],$value['thumbnail_pic'],$value['image_default_id']);
        }
        $this->pagedata['goods'] = $goods_info;
        $this->pagedata['packid'] = $id;
        $str_html = $this->fetch('site/member/goods_list.html','package');
        echo '{success:"'.app::get('b2c')->_('成功加载商品！').'",_:null,data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'",reload:null}';
    }
    
    private function checkAttendActivity(){
        $object = app::get('business')->model('activity');
        $nowTime = time();
        $filter = array(
                      'act_type'=>'package',
                      'store_id'=>$this->store_id,
                      'start_time|sthan'=>$nowTime,
                      'end_time|bthan'=>$nowTime
                    );
        $rs = $object->getList('*',$filter);
        if(empty($rs)){
            return true;
        }

        return false;
    }
}