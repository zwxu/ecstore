<?php

 
class b2c_message_disask extends b2c_message_comment{

    function __construct(&$app){
         
        $this->app = $app;
        parent::__construct($app);
     }
     
     #插入评论/咨询
  function send($aData,$item){
        $sdf['for_comment_id'] = $aData['for_comment_id']?$aData['for_comment_id']:0;
        if($sdf['for_comment_id']){
            $aRes = $this->dump($sdf['for_comment_id']);
            unset($aRes['goods_point']);
            $aRes['lastreply'] = time();
            $aRes['reply_name'] = $aData['author'];
            $this->save($aRes);
        }
      $sdf['type_id'] = $aData['goods_id'];
      $sdf['object_type'] = $item;
      $sdf['author_id'] = $aData['author_id'];
      $sdf['author'] = $aData['author'];
      $sdf['to_id'] = $aData['to_id'];
      $sdf['contact'] = htmlspecialchars($aData['contact']);
      $sdf['title'] = htmlspecialchars($aData['title']);
      $sdf['comment'] = htmlspecialchars($aData['comment']);
      $sdf['time'] = $aData['time'];
      $sdf['lastreply'] = $aData['lastreply'];
      $sdf['ip'] = $aData['ip'];
      $sdf['display'] = $aData['display'];
      if($aData['hidden_name']){
          $addon['hidden_name'] = "YES";
      }
      if($aData['gask_type'] && $item == 'ask'){
          $sdf['gask_type'] = $aData['gask_type'];
      }
      $sdf['addon'] = serialize($addon);

      //咨询添加shop_id-- start
      if($business_shopid=kernel::service("business.addshopid")){
        $business_shopid->setShopId($sdf);
      }//--end

      if($this->save($sdf)){
            if($item == 'discuss' && $aData['goods_point']){
                $goods_point = $this->app->model('comment_goods_point');
                $_pointsdf['comment_id'] = $sdf['comment_id'];
                foreach($aData['goods_point'] as $key=>$val){
                    if($aData['display'] == 'true')
                        $_pointsdf_addon['display'] = 'true';
                    else
                        $_pointsdf_addon['display'] = 'false';
                    $_pointsdf['addon'] = serialize($_pointsdf_addon);
                    $_pointsdf['goods_id'] = $aData['goods_id'];
                    $_pointsdf['goods_point'] = (float)$val['point'];
                    if($_pointsdf['goods_point']<1) $_pointsdf['goods_point']=5;
                    ($_pointsdf['goods_point']<=5) or $_pointsdf['goods_point']=5;
                    $_pointsdf['member_id'] = $aData['author_id'];
                    $_pointsdf['type_id'] = $key;
                    $goods_point->save($_pointsdf);
                    unset($_pointsdf['point_id']);
                }
            }
          return $sdf['comment_id'];
      }
      else{
          return false;
      }
  }
     

  function get_message($comment_id){
          $aData = $this->getList('*',array('object_type' => $this->type,'for_comment_id' => 0,'comment_id' => $comment_id));
          return $aData[0];
  }
  
  ////读取商品评论回复列表
   function getCommentsReply($aId, $display=false){
        if($display)
        {
            $aData = $this->getList('*',array('for_comment_id' => $aId,'display' => 'true'));
        }
        return $aData;
    }
    
    function get_member_comments($member_id,$page){
        $list_listnum = intval($this->app->getConf('comment.list.listnum'));
        if($list_listnum == 0 || $list_listnum == '') return ;
        $this->objComment->type = array('ask','discuss');
        $params = $this->getList('*',array('for_comment_id' => 0,'author_id' => $member_id,'display' => 'true'));
        $count = count($params);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start; 
        $params['data'] = $this->getList('*',array('for_comment_id' => 0,'author_id' => $member_id,'display' => 'true'),$start,$list_listnum);
        #print_r($params['data']);exit;
        foreach($params['data'] as $key=>$v){
            $params['data'][$key]['items'] = $this->get_reply($v['comment_id']);
        }
        $params['page'] = $maxPage;
        return $params;
    }
    
    function get_member_disask($member_id=null,$page=1,$object_type='ask'){
        if(!$member_id) return null;
        $list_listnum = intval($this->app->getConf('comment.index.listnum')); 
        if($list_listnum == 0 || $list_listnum == '') return ;
        $this->objComment->type = $object_type;
        $params = $this->getList('*',array('for_comment_id' => 0,'author_id' => $member_id,'display' => 'true'));
        $count = count($params);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start; 
        $params['data'] = $this->getList('*',array('for_comment_id' => 0,'author_id' => $member_id,'display' => 'true'),$start,$list_listnum);
        foreach($params['data'] as $key=>$v){
            $params['data'][$key]['items'] = $this->get_reply($v['comment_id']);
        }
        $params['page'] = $maxPage;
        return $params;
    }
    //咨询类型总数 企业版
    function get_ask_total($gid,$type_id,$item){
        $this->objComment->type = $item;
        $aData = $this->getList('*',array('for_comment_id' => 0,'type_id'=>$gid,'display'=>'true','gask_type'=>$type_id));
        $count = count($aData);
        return $count;
    }
    
    //咨询评论回复记录数
    function calc_unread_disask($member_id){
        $this->objComment->type = array('ask','discuss');
        $aData = $this->getList('comment_id',array('author_id' => $member_id));
        $i = 0;
        foreach((array)$aData as $v){
            $row = $this->getList('comment_id',array('for_comment_id' => $v['comment_id']));
            if($row){
                $i++;
            }
        }
        return $i;    
    }
    
    //获取商品咨询和评论包括回复
    function good_all_disask($gid=null,$page=1,$type_id=null){
        if(!$gid) return;
        $aComment['switch']['ask'] = $this->app->getConf('comment.switch.ask');
        $aComment['switch']['discuss'] = $this->app->getConf('comment.switch.discuss');
        $aComment['submit_comment_notice']['discuss'] = $this->app->getConf('comment.submit_comment_notice.discuss');
        $aComment['submit_comment_notice']['ask'] = $this->app->getConf('comment.submit_comment_notice.ask');
        $aComment['goods_discuss_notice'] = $this->app->getConf('comment.goods_discuss_notice');
         foreach($aComment['switch'] as $item => $switchStatus){
            if($switchStatus == 'on'){
                $commentList = kernel::single('business_message_disask')->getGoodsIndexComments($gid,$item,$page,$type_id);
                $aComment['list'][$item] = $commentList['data'];
                $aComment['page']['start'] = $commentList['start'];
                $aComment['page']['end'] = $commentList['end'];
                $aComment[$item.'Count'] = $commentList['total'];
                $aComment[$item.'current'] = $commentList['current_page'];
                $aComment[$item.'totalpage'] = $commentList['page'];
                for($i=0;$i<$commentList['page'];$i++){
                    $aComment[$item.'Page'][] = $i;
                }
                $aId = array();
                if ($commentList['total']){
                    foreach($aComment['list'][$item] as $rows){
                        $aId[] = $rows['comment_id'];
                    }
                    
                    if(count($aId)){
                        $addition = array();
                        $temp = app::get('b2c')->model('member_comments')->getList('comment_id,for_comment_id',array('for_comment_id' => $aId,'comments_type'=>'3','display' => 'true'));
                        foreach((array)$temp as $rows){
                            $aId[] = $rows['comment_id'];
                            $addition[$rows['for_comment_id']][] = $rows['comment_id'];
                        }
                    }
                  
                    if(count($aId)) $aReply = (array)$this->getCommentsReply($aId, true);
                    reset($aComment['list'][$item]);
                    foreach($aComment['list'][$item] as $key => $rows){
                        foreach($aReply as $rkey => $rrows){
                            if($rows['comment_id'] == $rrows['for_comment_id']){
                                $aComment['list'][$item][$key]['items'][] = $aReply[$rkey];
                            }
                           
                            elseif(!empty($addition) && isset($addition[$rows['comment_id']]) && in_array($rrows['for_comment_id'],$addition[$rows['comment_id']])){
                                $aComment['list'][$item][$key]['items'][] = $aReply[$rkey];
                            }
                           
                        }
                        reset($aReply);
                    }
                }else{
                    $aComment['null_notice'][$item] = $this->app->getConf('comment.null_notice.'.$item);;
                }
            }
        }
        return $aComment;
    }
    
    function getGoodsIndexComments($gid,$item,$page=1,$type_id=null){
        $list_listnum = intval($this->app->getConf('comment.index.listnum'));
        $this->objComment->type = $item;
        $filter['for_comment_id'] = 0;
        $filter['type_id'] = $gid;
        $filter['display'] = 'true';
        if($type_id) $filter['gask_type'] = $type_id;
        $aData = $this->getList('*',$filter);
        $count = count($aData);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start;
        $aData = $this->getList('*',$filter,$start,$list_listnum);
        $data = array();
        $goods_point = $this->app->model('comment_goods_point');
        foreach((array)$aData as $key=>$val){
            if($val['object_type'] == 'discuss'){
                $row = $goods_point->get_comment_point($val['comment_id']);
                $val['goods_point'] = $row;
            }
            $data[] = $val;
        }
        $result['start'] = $start+1;
        $result['end'] = $start+$list_listnum;
        $result['total'] = $count;
        $result['data'] = $data;
        $result['page'] = $maxPage;
        $result['current_page'] = $page;
        return $result;        
    }
    
    function getGoodsCommentList($gid,$item,$page=1){
        $list_listnum = 10;
        $this->objComment->type = $item;
        $aData = $this->getList('*',array('for_comment_id' => 0,'type_id'=>$gid,'display'=>'true'));
        $count = count($aData);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start;
        $data = $this->getList('*',array('for_comment_id' => 0,'type_id'=>$gid,'display'=>'true'),$start,$list_listnum);
        $result['total'] = $count;
        $result['page'] = $maxPage;
        $result['data'] = $data;
        return $result;        
    }
    
     function toValidate($item, $gid, $memInfo, &$message){
        if($this->app->getConf('comment.switch.'.$item) != 'on'){
            return false;
        }

        if($this->app->getConf('comment.power.'.$item) != 'null' && !isset($memInfo['member_id'])){

            $message = app::get('b2c')->_('请<a href="'.app::get('site')->router()->gen_url(array('app' => 'b2c', 'ctl' => 'site_passport', 'act' => 'login', 'arg' =>'')).'">登陆</a>后再留言<br>如果您不是会员请<a href="'.app::get('site')->router()->gen_url(array('app' => 'b2c','ctl' => 'site_passport', 'act' => 'signup', 'arg' =>'')).'">注册</a>!');
            return false;
            exit;
        }

        $this->db = kernel::database();
        if($this->app->getConf('comment.power.'.$item) == 'buyer' && $memInfo['member_id']){
            /*
             * 已购买的定义：订单做过发货动作，即销售记录表有相应数据
             */
            $aRet = $this->db->selectrow('SELECT count(log_id) AS countRows FROM sdb_b2c_sell_logs WHERE member_id='. intval($memInfo['member_id']) .' AND goods_id='.intval($gid));
            
            if($aRet['countRows'] == 0){
                $message = app::get('b2c')->_('未购买过该商品不能发表!');
                return false;
                exit;
            }
        }
    
        if($item=="discuss" && $memInfo['member_id']){
            $aRet = $this->db->selectrow('SELECT count(*) AS countRows FROM sdb_b2c_member_comments where type_id='.intval($gid).' and object_type="discuss" and for_comment_id=0 and author_id='.intval($memInfo['member_id']));
            if($aRet['countRows'] != 0){
                $message = app::get('b2c')->_('您已对此商品发表过评论。');
                return false;
                exit;
            }
        
        }
        return true;
    }
    #获取商店商品评论
    function getTopComment($limit=10,$item='discuss'){
        $this->objComment->type = $item;
        $goods = $this->app->model('goods');
        $row = $this->getList('*',array('for_comment_id' => 0,'display'=>'true'),0,$limit);
        $data = array();
        foreach($row as $v){
            $row_ = $goods->getList('name,thumbnail_pic,udfimg,image_default_id',array('goods_id' => $v['type_id']));
            $v['name'] = $row_[0]['name'];
            $v['thumbnail_pic'] = $row_[0]['thumbnail_pic'];
            $v['udfimg'] = $row_[0]['udfimg'];
            $v['image_default_id'] = $row_[0]['image_default_id'];
            $data[] = $v;
        }
        return $data;
    }
     #获取设置
   function get_setting($item){
        $aOut['switch'][$item] = $this->app->getConf('comment.switch.'.$item);
        $aOut['display'][$item] = $this->app->getConf('comment.display.'.$item);
        $aOut['power'][$item] = $this->app->getConf('comment.power.'.$item);
        $aOut['null_notice'][$item] = $this->app->getConf('comment.null_notice.'.$item);
        $aOut['submit_display_notice'][$item] = $this->app->getConf('comment.submit_display_notice.'.$item);
        $aOut['submit_hidden_notice'][$item] = $this->app->getConf('comment.submit_hidden_notice.'.$item);
        $aOut['submit_comment_notice'][$item] = $this->app->getConf('comment.submit_comment_notice.'.$item);
        $aOut['goods_discuss_notice'] = $this->app->getConf('comment.goods_discuss_notice');
        $aOut['index'] = intval($this->app->getConf('comment.index.listnum')?$this->app->getConf('comment.index.listnum'):5);
        $aOut['list'] = intval($this->app->getConf('comment.list.listnum'));
        $aOut['verifyCode'][$item] = $this->app->getConf('comment.verifyCode.'.$item);
        return $aOut;
    }

#设置

   function to_setting($item,$aData){
        $this->app->setConf('comment.switch.'.$item, $aData['switch'][$item]);
        #$this->app->setConf('comment.display.'.$item, $aData['display'][$item]);
        $this->app->setConf('comment.power.'.$item, $aData['power'][$item]);
        $this->app->setConf('comment.null_notice.'.$item, $aData['null_notice'][$item]);
        $this->app->setConf('comment.submit_display_notice.'.$item, $aData['submit_display_notice'][$item]);
        $this->app->setConf('comment.submit_hidden_notice.'.$item, $aData['submit_hidden_notice'][$item]);
         $this->app->setConf('comment.submit_comment_notice.'.$item, $aData['submit_comment_notice'][$item]);
        if($item == 'discuss')$this->app->setConf('comment.goods_discuss_notice', $aData['goods_discuss_notice']);
       // $this->app->setConf('comment.list.listnum', $aData['listnum']);
       // $this->app->setConf('comment.verifyCode.'.$item, $aData['verifyCode'][$item]);
   }
   
   #获取基本设置
    function get_basic_setting(){
        $aOut['switch_reply'] = $this->app->getConf('comment.switch_reply') ? $this->app->getConf('comment.switch_reply'):'off';
        $aOut['display_lv'] = $this->app->getConf('comment.display_lv') ?$this->app->getConf('comment.display_lv'):'off' ;
        $aOut['display'] = $this->app->getConf('comment.display.discuss') ? $this->app->getConf('comment.display.discuss'): 'reply';
        $aOut['index'] = intval($this->app->getConf('comment.index.listnum'));
        $aOut['verifyCode'] = $this->app->getConf('comment.verifyCode.discuss')? $this->app->getConf('comment.verifyCode.discuss'):'on';
        return $aOut;
    }
}