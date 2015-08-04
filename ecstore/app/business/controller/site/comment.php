<?php
class business_ctl_site_comment extends b2c_frontpage{
    
    function __construct(&$app){
        $this->app_current = $app;
        $this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c);
        $current_member = $this->get_current_member();
        $this->app_b2c->member_id = $current_member['member_id'];
        $this->app_current->member_id = $current_member['member_id'];
    }
    
    function discuss($order_id=0){
        //$order_id = '20101026134778';
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('评论宝贝'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;
        $this->pagedata['comment_goods_type'][] = array('type_id'=>0,'name'=>'商品评分');
        $objCommentType = $this->app_b2c->model('comment_goods_type');
        $comment_type = $objCommentType->getList('*');
        if(!$comment_type){
            $sdf['type_id'] = 1;
            $sdf['name'] = app::get('b2c')->_('宝贝与描述相符');
            $addon['is_total_point'] = 'on';
            $addon['description'] = array(5 => '质量非常好，与卖家描述的完全一致，非常满意',
                            4 => '质量不错，与卖家描述的基本一致，还是挺满意的',
                            3 => '质量一般，没有卖家描述的那么好',
                            2 => '部分有破损，与卖家描述的不符，不满意',
                            1 => '差的太离谱，与卖家描述的严重不符，非常不满');
            $sdf['addon'] = serialize($addon);
            $objCommentType->insert($sdf);
            $comment_type = $objCommentType->getList('*');
        }
        $comment_des = array();
        foreach($comment_type as $rows){
            $sdf['addon'] = unserialize($rows['addon']);
            $comment_des[$rows['type_id']] = $sdf['addon']['description'];
        }
        $this->pagedata['comment_store_des'] = json_encode($comment_des);
        $this->pagedata['comment_store_type'] = $comment_type;
        $goods_point_status = app::get('b2c')->getConf('goods.point.status');
        $this->pagedata['point_status'] = $goods_point_status ? $goods_point_status: 'on';
        
        $objOrder = $this->app_b2c->model('orders');
        $objOrderItems = $this->app_b2c->model('order_items');
        $objGoods = $this->app_b2c->model('goods');
        $objComment = $this->app_b2c->model('member_comments');
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['discussshow'] = $this->app_b2c->getConf('comment.verifyCode.discuss');
        $day_1 = app::get('b2c')->getConf('site.comment_original_time');
        $day_1 = intval($day_1)?intval($day_1):30;
        
        //$order_info = $objOrder->getList('order_id,createtime,comments_count', array('order_id'=>$order_id,'member_id'=>$this->app_b2c->member_id,'status'=>'finish'), 0, -1, 'createtime desc');]
        $sql  = " select o.order_id,o.createtime,o.comments_count from sdb_b2c_orders as o 
                  left join sdb_business_comment_orders_point as p on p.order_id = o.order_id 
                  where o.order_id='{$order_id}' and o.member_id='".$this->app_b2c->member_id."' and o.status='finish' 
                  and ifnull(o.comments_count,0)=0 and DATE_SUB(CURDATE(),INTERVAL {$day_1} DAY)<=from_unixtime(o.createtime) 
                  and p.order_id is null 
                  order by createtime desc ";
        $order_info = $objOrder->db->select($sql);
        foreach($order_info as $rows){
            //if(intval($rows['comments_count']) > 0 || intval($rows['createtime']) < strtotime("-1 month")) continue;
            $order_item = $objOrderItems->getList('order_id,goods_id,product_id',array('order_id' => $rows['order_id']));
            $data = array();
            foreach($order_item as $items){
                $data[] = $items['goods_id'];
            }
            $goods_info[$rows['order_id']] = $objGoods->getList('goods_id,name,thumbnail_pic,udfimg,image_default_id',array('goods_id' => $data),0,-1);
        }
        $this->pagedata['order_info'] = $goods_info;
        $this->pagedata['border_id'] = $order_id;
        $this->page('site/goods/discuss.html',false,'business');
    }
    
    function toComment($item='ask', $order_id, $type=0){
        if($type==0){
            $act = 'discuss';
        }elseif($type==3){
            $act = 'addition';
        }
        if($act && $order_id){
            $url = app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_comment','act'=>$act,'arg'=>$order_id));
        }else{
            $url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'orders'));
        }
        if(empty($_POST['order_id'])){
            $this->splash('failed',$url,app::get('b2c')->_('参数错误'),'','',false);
        }
        if ($this->app_b2c->getConf('comment.verifyCode.'.$item)=="on"){
            if($item =="ask"){
                if(!base_vcode::verify('ASKVCODE',intval($_POST['askverifyCode']))){
                    $this->splash('failed',$url,app::get('b2c')->_('验证码填写错误'),'','',false);
                }
            }
            if($item =="discuss"){
                if(!base_vcode::verify('DISSVCODE',intval($_POST['discussverifyCode']))){
                    $this->splash('failed',$url,app::get('b2c')->_('验证码填写错误'),'','',false);
                }
            }
        }
        $member_data = $this->get_current_member();
        $objBGoods = $this->app_current->model('goods');
        $order_info = $objBGoods->get_order_info($_POST['order_id'], $member_data['member_id']);
        if(!$order_info){
            $this->splash('failed',$url,app::get('b2c')->_('参数错误'),'','',false);
        }
        $objComment = kernel::single('business_message_disask');
        $objGoods = $this->app_current->model('goods');
        $aData = array();
        $aData['comments_type'] = $type?$type:'1';
        $aData['gask_type'] = $_POST['gask_type'];
        $aData['title'] = $_POST['title'];
        $aData['object_type'] = $item;
        $aData['author_id'] = $member_data['member_id'] ? $member_data['member_id']:0;
        $aData['author'] = ($member_data['uname'] ? $member_data['uname'] : app::get('b2c')->_('非会员顾客'));
        $aData['contact'] = ($_POST['contact']=='' ? $member_data['email'] : $_POST['contact']);
        $aData['time'] = time();
        $aData['lastreply'] = 0;
        $aData['ip'] = $_SERVER["REMOTE_ADDR"];
        $aData['display'] = ($this->app_b2c->getConf('comment.display.'.$item)=='soon' ? 'true' : 'false');
        $order_ids = array();
        foreach($order_info as $rows){
            $order_ids[$rows['order_id']] = $rows['store_id'];
            if(is_array($_POST['goods_id']) && isset($_POST['goods_id'][$rows['order_id']])){
                foreach($_POST['goods_id'][$rows['order_id']] as $gid){
                    if($gid != $rows['goods_id']) continue;
                    $temp = $aData;
                    $temp['order_id'] = $rows['order_id'];
                    $temp['goods_id'] = $gid;
                    if($temp['comments_type'] == '3'){
                        $temp['for_comment_id'] = $_POST['comment_id'][$rows['order_id']][$gid];
                    }
                    $temp['hidden_name'] = $_POST['hidden_name'][$rows['order_id']][$gid];
                    foreach($_POST['goods_point'][$rows['order_id']]['goods'][$gid] as $ck => $cv){
                        $temp['goods_point'][$ck]['point'] = $cv?$cv:5;
                    }
                    $temp['comment'] = $_POST['comment'][$rows['order_id']][$gid];
                    if($comment_id = $objComment->send($temp, $item)){
                        $single_order[$rows['order_id']] = $rows['order_id'];
                        //$objGoods->updateRank($gid, $item,1);
                        $objGoods->db->exec('update sdb_b2c_goods as g inner join (select avg(goods_point) as point ,goods_id,count(point_id) as comments_count from sdb_b2c_comment_goods_point where goods_id='.intval($gid).' group by goods_id) as p on g.goods_id=p.goods_id set g.avg_point = p.point,g.comments_count=p.comments_count where g.goods_id='.intval($gid));
                        if($this->app_b2c->getConf('comment.display.'.$item) == 'soon' && $item == 'discuss' && $aData['author_id']){
                            $_is_add_point = app::get('b2c')->getConf('member_point');
                            if($_is_add_point){
                                $obj_member_point = $this->app_b2c->model('member_point');
                                $obj_member_point->change_point($aData['author_id'],$_is_add_point,$_msg,'comment_discuss',2,$aData['goods_id'],$aData['author_id'],'comment');
                            }
                        }
                    }else{
                        $error_info[] = array('订单号：'.$rows['order_id'].'|商品号：'.$rows['bn']);
                    }
                }
            }
        }
        $objCommentType = $this->app_b2c->model('comment_goods_type');
        $comment_type = $objCommentType->getList('*');
        $exp_type = '';
        foreach($comment_type as $rows){
            $sdf['addon'] = unserialize($rows['addon']);
            if($sdf['addon']['is_total_point'] == 'on'){
                $exp_type = $rows['type_id'];
                break;
            }
        }
        $obj_store = app::get('business')->model('storemanger');
        if(count($order_info) > 0 && isset($single_order)){
            $objBOrderPoint = $this->app_current->model('comment_orders_point');
            foreach($_POST['order_id'] as $rows){
                $aData = array();
                $aData['store_id'] = $order_ids[$rows];
                $aData['member_id'] = $member_data['member_id'] ? $member_data['member_id']:0;
                $aData['order_id'] = $rows;
                foreach($_POST['point_type'][$rows]['store'] as $ck => $cv){
                    $temp = $aData;
                    $temp['point'] = $cv?$cv:5;
                    $temp['type_id'] = $ck;
                    $objBOrderPoint->save($temp);
                    if($exp_type && $exp_type == $ck){
                        $exper = 0;
                        switch(intval($temp['point'])){
                            case 1:
                            case 2:
                            $exper = -1;
                            break;
                            case 4:
                            case 5:
                            $exper = 1;
                            break;
                            default:
                            $exper = 0;
                            break;
                        }
                        $obj_store->change_experience($aData['store_id'],$exper,$msg,$aData['member_id'],'1',$rows,'discuss');
                    }
                }
            }
        }
        if(isset($error_info) && count($error_info)>0){
            $this->splash('failed',$url,implode(',',$error_info),'','',false);
        }else{
            foreach($single_order as $rows){
                $objGoods->updateOrderRank($rows, $item,1);
            }
            $url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'orders'));
            $this->splash('success',$url,app::get('b2c')->_('评论成功'),'','',false);
        }
    }
    
    function selfdiscuss($nPage=1){
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的评论'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        
        $objDisask = kernel::single('business_message_disask');
        $objGoods = $this->app_current->model('goods');
        $objPoint = $this->app_current->model('comment_goods_point');
        $aData = $objDisask->get_member_disask($this->app_b2c->member_id,$nPage,'discuss');
        $comment=array();
        $imageDefault = app::get('image')->getConf('image.set');
        $oImage = app::get('image')->model('image');
        foreach((array)$aData['data'] as $k => $v){
            $goods_data = $objGoods->getList('name,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$v['type_id']));
            if(!$goods_data) continue;
            if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['image_default_id']))){
                $goods_data[0]['image_default_id'] = $imageDefault['S']['default_image'];
            }
            if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['thumbnail_pic']))) {
                $goods_data[0]['thumbnail_pic'] = $imageDefault['S']['default_image'];
            }
            $v['goods_point'] = $objPoint->get_single_point($v['type_id']);
            $v['name'] = $goods_data[0]['name'];
            $v['thumbnail_pic'] = $goods_data[0]['thumbnail_pic'];
            $v['udfimg'] = $goods_data[0]['udfimg'];
            $v['image_default_id'] = $goods_data[0]['image_default_id'];
            $comment[] = $v;
        }
        $this->pagedata['commentList'] = $comment;
        $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagination($nPage,$aData['page'],'selfdiscuss','','business',$ctl='site_comment');
        $this->pagedata['_PAGE_'] = 'sdiscuss.html';
        $this->output('business');
    }
    
    function pagination($current,$totalPage,$act,$arg='',$app_id='b2c',$ctl='site_member'){ //本控制器公共分页函数
        if (!$arg)
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>array(($tmp = time())))),
                'token'=>$tmp,
                );
        else
        {
            $arg = array_merge($arg, array(($tmp = time())));
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>$arg)),
                'token'=>$tmp,
                );
        }
    }
    
    function set_show(){
        $comment_id = $_GET['arg0'];
        $object_type = $_GET['arg1'];
        if(!$comment_id) return ;
        $comment = kernel::single('business_message_disask');
        $comment->type = $object_type;
        $reply_data = $comment->getList('comment_id',array('comment_id' => $comment_id));
        foreach($reply_data as $v){
            $comment->setShowed($v['comment_id'],'true');
        }
    }
    
    function set_noshow(){
        $comment_id = $_GET['arg0'];
        $object_type = $_GET['arg1'];
        if(!$comment_id) return ;
        $comment = kernel::single('business_message_disask');
        $comment->type = $object_type;
        $reply_data = $comment->getList('comment_id',array('comment_id' => $comment_id));
        foreach($reply_data as $v){
            $comment->setShowed($v['comment_id'],'false');
        }
    }
    
    function set_read($comment_id=null,$object_type='ask'){
        if(!$comment_id) return ;
        $comment = kernel::single('business_message_disask');
        $comment->type = $object_type;
        $reply_data = $comment->getList('comment_id',array('for_comment_id' => $comment_id, 'comments_type|noequal'=>'3'));
        foreach($reply_data as $v){
            $comment->setReaded($v['comment_id']);
        }
    }
    
    function set_reply(){
        $this->pagedata['comment_id'] = $_POST['comment_id'];
        $this->display('site/member/discuss_reply.html', 'business');
    }
    
    function to_reply(){
        $comment_id = $_POST['comment_id'];
        $comment = $_POST['reply_content'];
        if($comment_id&&$comment){
            $member_data = $this->get_current_member();
            $member_comments = kernel::single('business_message_disask');
            $row = $member_comments->dump($comment_id);
            $author_id = $row['author_id'];
            unset($row['goods_point']);
            if($this->app_b2c->getConf('comment.display.discuss') == 'reply'){
                $aData = $row;
                $aData['display'] = 'true';
                $goods_point = $this->app_b2c->model('comment_goods_point');
                $goods_point->set_status($comment_id,'true');
                $_is_add_point = app::get('b2c')->getConf('member_point');
                if($_is_add_point && $author_id){
                    $obj_member_point = $this->app_b2c->model('member_point');
                    $obj_member_point->change_point($author_id,$_is_add_point,$_msg,'comment_discuss',2,$row['type_id'],$author_id,'comment');
                }
                $member_comments->save($aData);
            }
            $objComment = $this->app_b2c->model('member_comments');
            $store_id = $objComment->getList('store_id',array('comment_id'=>$comment_id));
            $sdf['store_id'] = $store_id[0]['store_id'];
            $sdf['comments_type'] = '0';
            $sdf['comment_id']= '';
            $sdf['for_comment_id'] = $comment_id;
            $sdf['object_type'] = "discuss";
            $sdf['to_id'] = $author_id;
            $sdf['author_id'] = $member_data['member_id'];
            $sdf['author'] = $member_data['uname'];
            $sdf['title'] = '';
            $sdf['contact'] = '';
            $sdf['display'] = 'true';
            $sdf['time'] = time();
            $sdf['comment'] = $comment;
            if($member_comments->send($sdf,'discuss')){
                $comments = $this->app_b2c->model('member_comments');
                $data['member_id'] = $author_id;
                $comments->fireEvent('discussreply',$data,$author_id);
                $params['items'] = $member_comments->get_reply($comment_id);
                //$params['items'] = array('author' => 'deeemo','comment'=>'abceee');
                echo json_encode($params['items']);exit;
            }else{
                echo 0;exit;
            }
        }
        else{
            echo 0;exit;
        }
    }
    
    function addition($order_id=0){
        //$order_id = '20101026134778';
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('追加评论'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;
        $objOrder = $this->app_b2c->model('orders');
        $objOrderItems = $this->app_b2c->model('order_items');
        $objGoods = $this->app_current->model('goods');
        $objComment = $this->app_b2c->model('member_comments');
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['discussshow'] = $this->app_b2c->getConf('comment.verifyCode.discuss');
        $day_2 = app::get('b2c')->getConf('site.comment_additional_time');
        $day_2 = intval($day_2)?intval($day_2):90;
        
        //$order_info = $objOrder->getList('order_id,createtime,comments_count', array('order_id'=>$order_id,'member_id'=>$this->app_b2c->member_id,'status'=>'finish'), 0, -1, 'createtime desc');
        $sql  = " select o.order_id,o.createtime,o.comments_count from sdb_b2c_orders as o 
                  left join sdb_business_comment_orders_point as p on p.order_id = o.order_id 
                  where o.order_id='{$order_id}' and o.member_id='".$this->app_b2c->member_id."' and o.status='finish' 
                  and ifnull(o.comments_count,0)=1 and DATE_SUB(CURDATE(),INTERVAL {$day_2} DAY)<=from_unixtime(o.createtime) 
                  and p.order_id is not null 
                  order by createtime desc ";
        $order_info = $objOrder->db->select($sql);
        foreach($order_info as $rows){
            //if(intval($rows['comments_count']) > 1 || intval($rows['createtime']) < strtotime("-3 month")) continue;
            $order_item = $objOrderItems->getList('order_id,goods_id,product_id',array('order_id' => $rows['order_id']));
            $data = array();
            foreach($order_item as $items){
                $data[] = $items['goods_id'];
            }
            $goods_info[$rows['order_id']] = $objGoods->get_comment_goods($data, $rows['order_id']);
        }
        $this->pagedata['order_info'] = $goods_info;
        $this->pagedata['border_id'] = $order_id;
        $this->page('site/goods/discuss_addition.html',false,'business');
    }
    
    protected function output($app_id='business'){
        $this->pagedata['member'] = $this->member;
        $this->pagedata['cpmenu'] = $this->get_cpmenu();
        $this->pagedata['top_menu'] = $this->get_headmenu();
        $this->pagedata['current'] = $this->action;
        if( $this->pagedata['_PAGE_'] ){
            $this->pagedata['_PAGE_'] = 'site/member/'.$this->pagedata['_PAGE_'];
        }else{
           $this->pagedata['_PAGE_'] = 'site/member/'.$this->action_view;
        }
        foreach(kernel::servicelist('member_index') as $service){
            if(is_object($service)){
                if(method_exists($service,'get_member_html')){
                    $aData[] = $service->get_member_html();
                }
            }
        }
        $this->pagedata['app_id'] = $app_id;
        $this->pagedata['_MAIN_'] = 'site/member/main.html';
        $this->pagedata['get_member_html'] = $aData;
        $member_goods = $this->app->model('member_goods');
        $this->pagedata['sto_goods_num'] = $member_goods->get_goods($this->app->member_id);
        $this->set_tmpl('member');
        $this->page('site/member/main.html');
    }
}