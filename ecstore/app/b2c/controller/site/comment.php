<?php
 
 
class b2c_ctl_site_comment extends b2c_frontpage{

    var $noCache = true;
    function __construct(&$app){
         parent::__construct($app);

    }
    
    //评论验证码
    function gen_dissvcode(){
        $vcode = kernel::single('base_vcode');
        $vcode->length(4);
        $vcode->verify_key('DISSVCODE');
        $vcode->display();
    }
    
    //咨询验证码
    function gen_askvcode(){
        $vcode = kernel::single('base_vcode');
        $vcode->length(4);
        $vcode->verify_key('ASKVCODE');
        $vcode->display();
    }
	
    //回复验证码
    function gen_replyvcode(){
        $vcode = kernel::single('base_vcode');
        $vcode->length(4);
        $vcode->verify_key('REPLYVCODE');
        $vcode->display();
    }
	
    function vocdecheck(){
        $name = trim($_POST['name']);
        if(!base_vcode::verify('REPLYVCODE',intval($name))){
            echo '<span class="font-red">&nbsp;'.app::get('b2c')->_('验证码填写错误,请重新输入').'</span>';
            exit;
        }
    }
	
    function commentlist($goodsid=null, $item=null, $nPage=1){exit;
        if(!$goodsid || !$item){
            $this->splash('failed', 'back', app::get('b2c')->_('参数错误'));
        }
        $objGoods = &$this->app->model('goods');

        $GLOBALS['runtime']['path'] = $objGoods->getPath($goodsid,'');        
        $this->pagedata['goods'] = $objGoods->getList('*',array('goods_id'=>$goodsid));
        if(!$this->pagedata['goods']) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'));
        $this->pagedata['goods'] = $this->pagedata['goods'][0];
        $this->title = $this->pagedata['goods']['name'];
        $member_data = $this->get_current_member();
        if($this->check_login()){
            $this->pagedata['login'] = "YES";
        }
        else{
            $this->pagedata['login'] = "NO";
        }
        $this->pagedata['goods']['setting']['mktprice'] = $this->app->getConf('site.market_price');
        $this->pagedata['goods']['setting']['saveprice'] = $this->app->getConf('site.save_price');
        $this->pagedata['goods']['setting']['buytarget'] = $this->app->getConf('site.buy.target');
        $switchStatus = $this->app->getConf('comment.switch.'.$item);
        if($switchStatus == 'on'){
            $objComment= kernel::single('b2c_message_disask');
            $this->pagedata['base_setting'] = $objComment->get_basic_setting();
            $aComment = $objComment->getGoodsCommentList($goodsid, $item, $nPage);
            $aId = array();
            foreach($aComment['data'] as $rows){
                $aId[] = $rows['comment_id'];
            }
            if(count($aId)) $aReply = (array)($objComment->getCommentsReply($aId, true));
            reset($aComment['data']);
            foreach($aComment['data'] as $key => $rows){
                foreach($aReply as $rkey => $rrows){
                    if($rows['comment_id'] == $rrows['for_comment_id']){
                        $aComment['data'][$key]['items'][] = $aReply[$rkey];
                    }
                }
                reset($aReply);
            }
        }else{
            $this->_response->set_http_response_code(404);
            exit;
        }

        switch($item){
            case 'ask':
            $this->pagedata['askshow'] = $this->app->getConf('comment.verifyCode.ask');
            $this->path[]=array('title'=>app::get('b2c')->_('商品咨询'));
            $pagetitle = app::get('b2c')->_('商品咨询');
            break;
            case 'discuss':
            $this->pagedata['discussshow'] = $this->app->getConf('comment.verifyCode.discuss');
            $this->path[]=array('title'=>app::get('b2c')->_('商品评论'));
            $pagetitle = app::get('b2c')->_('商品评论');
            break;
            case 'buy':
            $this->path[]=array('title'=>app::get('b2c')->_('商品经验'));
            $pagetitle = app::get('b2c')->_('商品经验');
            break;
        }

        $this->pagedata['commentData'] = $aComment['data'];
        $this->pagedata['comment']['total'] = $aComment['total'];
        $this->pagedata['comment']['pagetitle'] = $pagetitle;
        $this->pagedata['comment']['item'] = $item;
        if($item === 'ask'){
            $this->pagedata['title'] =app::get('b2c')->_('[咨询]').$this->pagedata['goods']['name'];
        }
        else{
            $this->pagedata['title'] = app::get('b2c')->_('[评论]').$this->pagedata['goods']['name'];
        }
        $this->pagedata['pager'] = array(
                'current'=> $nPage,
                'total'=> $aComment['page'],
                'link'=> $this->gen_url(array('app'=>'b2c', 'ctl'=>'site_comment','full'=>1,'act'=>'commentlist','args'=>array($goodsid,$item,($tmp = time())))),                 
				'token'=> $tmp);
        $this->page('site/comment/commentlist.html');
    }

    //发表评论
    function toComment($goodsid=null, $item='ask'){
        
        if(!$goodsid){
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_('参数错误').'",_:null}';exit;
        }
        $objGoods = &$this->app->Model('goods');
        $good_id = $objGoods->getList('goods_id',array('goods_id'=>$goodsid));
        if(!$good_id) {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_('参数错误').'",_:null}';exit;
        }
        $member_data = $this->get_current_member();
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg'=>$goodsid));
        $objComment = $this->app->model('member_comments');
        if ($this->app->getConf('comment.verifyCode.'.$item)=="on"){
            if($item =="ask"){
                if(!base_vcode::verify('ASKVCODE',intval($_POST['askverifyCode']))){
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"'.app::get('b2c')->_('验证码填写错误').'",_:null}';exit;
                }
            }
            if($item =="discuss"){
                if(!base_vcode::verify('DISSVCODE',intval($_POST['discussverifyCode']))){
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"'.app::get('b2c')->_('验证码填写错误').'",_:null}';exit;
                }
            }
        }
        $objComment = kernel::single('business_message_disask'); // by cam
        if(!$objComment->toValidate($item, $goodsid, $member_data, $message)){
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.$message.'",_:null}';exit;
        }else{
            
            foreach($_POST['point_type'] as $ck => $cv){
                $_POST['point_type'][$ck]['point'] = $cv['point']?$cv['point']:5;
            }
            $aData['comments_type'] = '1';
          
            $aData['hidden_name'] = $_POST['hidden_name'];
            $aData['gask_type'] = $_POST['gask_type'];
            $aData['goods_point'] = $_POST['point_type'];
            $aData['title'] = $_POST['title'];
            $aData['comment'] = $_POST['comment'];
            $aData['goods_id'] = $goodsid;
            $aData['object_type'] = $item;
            $aData['author_id'] = $member_data['member_id'] ? $member_data['member_id']:0;
            $aData['author'] = ($member_data['uname'] ? $member_data['uname'] : app::get('b2c')->_('非会员顾客'));
            $aData['contact'] = ($_POST['contact']=='' ? $member_data['email'] : $_POST['contact']);
            $aData['time'] = time();
            $aData['lastreply'] = 0;
            $aData['ip'] = $_SERVER["REMOTE_ADDR"];
            $aData['display'] = ($this->app->getConf('comment.display.'.$item)=='soon' ? 'true' : 'false');

            $objGoods = &$this->app->model('goods');
            
            if($comment_id = $objComment->send($aData, $item, $message)){
                $objGoods->updateRank($goodsid, $item,1);
                $objGoods->db->exec('update sdb_b2c_goods as g inner join (select avg(goods_point) as point ,goods_id from sdb_b2c_comment_goods_point where goods_id='.intval($goodsid).' group by goods_id) as p on g.goods_id=p.goods_id set g.avg_point = p.point where g.goods_id='.intval($goodsid));
                if($this->app->getConf('comment.display.'.$item) == 'soon' && $item == 'discuss' && $aData['author_id']){
                    $_is_add_point = app::get('b2c')->getConf('member_point');
                    if($_is_add_point){
                        $obj_member_point = $this->app->model('member_point');
                        $obj_member_point->change_point($aData['author_id'],$_is_add_point,$_msg,'comment_discuss',2,$aData['goods_id'],$aData['author_id'],'comment');
                    }
                }

                $member_data = $this->get_current_member();
                $memInfo['member_id'] = $member_data['member_id'];
                if(!$member_data['member_id']){
                    $this->pagedata['login'] = 'nologin';
                }
                

                if($item == 'discuss') {
                   
                    $objPoint = app::get('business')->model('comment_goods_point');
                    $this->pagedata['goods_point'] = $objPoint->get_single_point($goodsid);
                    $this->pagedata['total_point_nums'] = $objPoint->get_point_nums($goodsid);
                    $this->pagedata['comment_goods_type'][] = array('type_id'=>0,'name'=>'商品评分');
                    $this->pagedata['filter'] = $_POST['filter'];
                    $filter = json_decode($_POST['filter'],true);
                    if(is_array($filter)){
                        if($filter['comments_type'] && $filter['comments_type'] == '3') $this->pagedata['toolbar']['append'] = 1;
                        if(isset($filter['comment'])) $this->pagedata['toolbar']['content'] = 1;
                        if($filter['orderb'] == '1') $this->pagedata['toolbar']['orderb'] = '1';
                        else $this->pagedata['toolbar']['orderb'] = '1';
                    }
                   
                    $this->pagedata['discuss_status'] = kernel::single('b2c_message_disask')->toValidate('discuss',$goodsid,$memInfo,$discuss_message);
                    $this->pagedata['discuss_message'] = $discuss_message;
                    $this->pagedata['discussshow'] = $this->app->getConf('comment.verifyCode.discuss');
                }
                else {
                    $this->pagedata['ask_status'] = kernel::single('b2c_message_disask')->toValidate('ask',$goodsid,$memInfo,$ask_message);
                    $this->pagedata['ask_message'] = $ask_message;
                    $this->pagedata['askshow'] = $this->app->getConf('comment.verifyCode.ask');
                }

                echo kernel::single("b2c_goods_description_comments")->show($goodsid,$aGoods,$item);

                /*$objdisask = kernel::single('b2c_message_disask');
                $aComment = $objdisask->good_all_disask($goodsid,1);
                if($item == "ask") {
                    unset($aComment['list']['discuss']);
                    $tpl = "site/product/consult_content.html";
                }
                else if($item == "discuss") {
                    unset($aComment['list']['ask']);
                    $tpl = "site/product/discuss_content.html";
                }
                $this->pagedata['comment'] = $aComment;
                $this->pagedata['base_setting'] = $objdisask->get_basic_setting();
                $this->pagedata['goods']['goods_id'] = $goodsid;
                echo $this->fetch($tpl);*/
                exit;
                //$this->splash('success',$url,$msg,'','',true);

            }
            else{
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.app::get('b2c')->_('发表失败').'",_:null}';exit;
            }

        }
    }
    function csplash($goodsid){
        $this->splash('success', $this->app->mkUrl('product','index', array($goodsid)),app::get('b2c')->_('提交成功！'));
    }
	
    function reply($comment_id=null){exit;
        if(!$comment_id) {
            $this->splash('failed', 'back', app::get('b2c')->_('参数错误'));
        }
        $objComment = kernel::single('b2c_message_disask');
        $aComment = $objComment->dump($comment_id);
        if(!$aComment)  $this->splash('failed', 'back', app::get('b2c')->_('记录为空'));
        $aComment['reply'] = $objComment->get_reply($comment_id);
        $this->pagedata['comment'] = $aComment;
        if($this->check_login()){
            $this->pagedata['login'] = "YES";
        }
        else{
            $this->pagedata['login'] = "NO";
        }
        $this->page('site/product/reply.html');echo '3333';exit;
    }

    //客户回复评论
    function toReply($comment_id=null){
        if(!$comment_id) {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_('参数错误').'",_:null}';exit;
        }
        $member_data = $this->get_current_member();
        
        $objComment = kernel::single('business_message_disask'); // by cam
        $aComment = $objComment->dump($comment_id);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg'=>$aComment['type_id']));
        if(!$aComment)  {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_('记录为空').'",_:null}';exit;
        }
        if ($this->app->getConf('comment.verifyCode.'.'ask')=="on"){
            if(!base_vcode::verify('REPLYVCODE',intval($_POST['replyverifyCode']))) {
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.app::get('b2c')->_('验证码填写错误').'",_:null}';exit;
            }
        }
        if(!$objComment->toValidate($aComment['object_type'], $aComment['goods_id'], $member_data, $message)){
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.$message.'",_:null}';exit;
        }else{
            $aData['comments_type'] = '2'; // by cam
            $aData['comment'] = $_POST['comment'];
            $aData['hidden_name'] = $_POST['hidden_name'];
            $aData['type_id'] = $aComment['type_id'];
            $aData['for_comment_id'] = $comment_id;
            $aData['author_id'] = $member_data['member_id'] ? $member_data['member_id']:0;
            $aData['mem_read_status'] = ($this->member['member_id']==$aComment['author_id'] ? 'false' : 'true');
            $aData['object_type'] = $aComment['object_type'];
            $aData['author'] = ($member_data['uname'] ? $member_data['uname'] : app::get('b2c')->_('非会员顾客'));
            $aData['contact'] = ($_POST['contact']=='' ? $member_data['email'] : $_POST['contact']);
            $aData['to_id'] = $aComment['to_id'];
            $aData['time'] = time();
            $aData['lastreply'] = time();
            $aData['reply_name'] = $aData['author'];
            $aData['display'] = ($this->app->getConf('comment.display.'.$aComment['object_type'])=='soon' ? 'true' : 'false');
            if($objComment->send($aData,$aComment['object_type'])){
                $comments = $this->app->model('member_comments');
                if($aComment['object_type'] == 'discuss') {
                    $comments->fireEvent('discussreply',$aData,$aData['author_id']);
                }elseif($aComment['object_type']=='ask'){
                    $comments->fireEvent('gaskreply',$aData,$aData['author_id']);
                }

                $this->splash('success',$url,app::get('b2c')->_('发表成功！'),'','',true);
            }else{
                  $this->splash('failed','back',app::get('b2c')->_('发表失败！'),'','',true);
            }
        }
    }
    
    public function reply_link(){
        $comment_id = $_POST['comment_id'];
        $url = $this->gen_url(array('app' => 'b2c','ctl' => 'site_comment','act' => 'toReply', 'arg' => $comment_id));
        echo $url;
        exit;
    }
    
     public function ajax_type_ask(){
        $gid = $_POST['goods_id'];
        $type_id = $_POST['type_id'];
        $page = $_POST['page']?$_POST['page']:1;
        if(!$gid) exit;
        $objdisask = kernel::single('b2c_message_disask');
        $aComment = $objdisask->good_all_disask($gid,$page,$type_id);
        unset($aComment['list']['discuss']);
        $this->pagedata['comment'] = $aComment;
        $this->pagedata['base_setting'] = $objdisask->get_basic_setting();
        $this->pagedata['goods']['goods_id'] = $gid;
        echo $this->fetch('site/product/consult_content.html');
        exit;
    }
    
    public function ajax_ask(){
        $gid = $_POST['goods_id'];
        $page = $_POST['page']?$_POST['page']:1;
        $type_id = $_POST['type_id'];
        if(!$gid) exit;
        $objdisask = kernel::single('b2c_message_disask');
        $aComment = $objdisask->good_all_disask($gid,$page,$type_id);
        unset($aComment['list']['discuss']);
        $this->pagedata['comment'] = $aComment;
        $this->pagedata['base_setting'] = $objdisask->get_basic_setting();
        $this->pagedata['goods']['goods_id'] = $gid;
        echo $this->fetch('site/product/consult_content.html');
        exit;
    }
    
    public function ajax_discuss(){
        $gid = $_POST['goods_id'];
        $page = $_POST['page'];
     
        $this->pagedata['filter'] = $_POST['filter'];
        $filter = json_decode($_POST['filter'],true);
        if(!$gid or !$page or !$filter) exit;
        $objdisask = kernel::single('business_message_disask');
        $aComment = $objdisask->good_all_disask($filter,$page);
   
        unset($aComment['list']['ask']);
        $this->pagedata['comment'] = $aComment;
        $this->pagedata['base_setting'] = $objdisask->get_basic_setting();
        $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $this->pagedata['goods']['goods_id'] = $gid;
        echo $this->fetch('site/product/discuss_content.html','b2c');
        exit;
    }
    
   
    public function questionnaire($app='b2c',$ctl='site_comment',$act='questionnaire',$arg=array()){
        $member_data = $this->get_current_member();
        if( !$member_data['member_id'] ){
            $jump_to_url = app::get('site')->router()->gen_url( array('app'=>'b2c','ctl'=>'site_passport','act'=>'login','full'=>'true') );
            kernel::single('base_controller')->splash( 'success',$jump_to_url );exit;
        }
        $this->pagedata['return_url'] = app::get('site')->router()->gen_url( array('app'=>$app,'ctl'=>$ctl,'act'=>$act,'arg'=>$arg,'full'=>'true') );
        $this->page('site/member/questionnaire.html');
    }
    
    public function to_question(){
        $this->begin();
        $url = $this->gen_url( array('app'=>'b2c','ctl'=>'site_member','act'=>'ask') );
        $member_data = $this->get_current_member();
        $item = 'ask';
        if(!base_vcode::verify('ASKVCODE',intval($_POST['askverifyCode']))){
            $this->end(false,'验证码填写错误',$this->gen_url( array('app'=>'b2c','ctl'=>'site_comment','act'=>'questionnaire') ));
        }
        $objComment = kernel::single('business_message_disask');
        $aData['hidden_name'] = $_POST['hidden_name'];
        $aData['gask_type'] = $_POST['gask_type'];
        $aData['goods_point'] = $_POST['point_type'];
        $aData['title'] = $_POST['title'];
        $aData['comment'] = $_POST['comment'];
        $aData['object_type'] = $item;
        $aData['author_id'] = $member_data['member_id'] ? $member_data['member_id']:0;
        $aData['author'] = ($member_data['uname'] ? $member_data['uname'] : app::get('b2c')->_('非会员顾客'));
        $aData['contact'] = ($_POST['contact']=='' ? $member_data['email'] : $_POST['contact']);
        $aData['time'] = time();
        $aData['lastreply'] = 0;
        $aData['ip'] = $_SERVER["REMOTE_ADDR"];
        $aData['display'] = ($this->app->getConf('comment.display.'.$item)=='soon' ? 'true' : 'false');
        header('Content-Type:text/jcmd; charset=utf-8');
        if($comment_id = $objComment->send($aData, $item, $message)){
            $this->end(true,'发表成功',$url);
        }else{
            $this->end(false,'发表失败',$url);
        }
    }
   
}
?>
