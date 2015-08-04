<?php
class cellphoneseller_services_service extends cellphoneseller_cellphoneseller{
    var $store_id = NULL;
    var $store = NULL;
    var $member = NULL;

    public function __construct($app){
        parent::__construct();
        $this->app = $app;

        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);
        $member= $this->get_current_member();
        $this->member = $member;

        if(!$member['member_id']){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
            exit;
        }

        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('b2c')->_('非卖家账号'));
            exit;
        }

        $member_id = $member['member_id'];
        $this->store = $this->get_current_store($member_id);
        $this->store_id = $this->store['store_id'];

    }

    /**
     * complain_list_get 获取店铺投诉信息接口方法
     * @author lufeng
     **/
    public function complain_list_get(){
        $params = $this->params;

        $filter_arr = array('goods_id','complain_id','status','time_from','time_to');
        $status_arr = array('intervene','success','error','cancel');

        $filter = isset($params['filter'])?json_decode($params['filter'],true):array();
        $page = isset($params['page'])?intval($params['page']):1;
        $pagesize = isset($params['pagelimit'])?intval($params['pagelimit']):10;

        if(!empty($filter)){
            foreach($filter as $k=>$v){
                if(!in_array($k,$filter_arr)){
                    $this->send(false,null,app::get('b2c')->_('参数值错误'));
                    exit;
                }

                if($k == 'status' && !in_array($v,$status_arr)){
                    $this->send(false,null,app::get('b2c')->_('状态值错误'));
                    exit;
                }

                if($k == 'time_from' && !empty($v)){
                    $filter['_DTYPE_DATE'][] = 'applyTime[start]';
                    $filter['applyTime']['start'] = $v;
                    unset($filter['time_from']);
                }

                if($k == 'time_to' && !empty($v)){
                    $filter['_DTYPE_DATE'][] = 'applyTime[end]';
                    $filter['applyTime']['end'] = $v;
                    unset($filter['time_to']);
                }
            }
        }

        $obj_complain=kernel::single('complain_seller_complain');
        $aData=$obj_complain->get_list($filter,$this,$page,$pagesize); 

        $this->send(true,$aData,app::get('b2c')->_('success'));
    }

    /**
     * complain_detail_get 查看单个投诉的详细信息接口方法
     * @author lufeng
     **/
    public function complain_detail_get(){
        $params = $this->params;
        //检查应用级必填参数
        $must_params = array(
            'complain_id'=>'投诉ID'
        );

        if(!$params['size']){
            $params['size'] = "CL";
        }

        $this->check_params($must_params);
        $complain_id = $params['complain_id'];
        $subsdf = array('complain_comments'=>'*');
        $complain=app::get('complain')->model('complain')->dump($complain_id, '*', $subsdf);
        $complain['complain_comments'] = array_values($complain['complain_comments']);

        $items = app::get('b2c')->model('order_items');
        $goods_id = $items->getList('goods_id,nums',array('order_id'=>$complain['order_id']));
        $mdl_goods = app::get('b2c')->model('goods');
        foreach($goods_id as $key=>$val){
            $image = $mdl_goods->dump($val['goods_id'],'image_default_id,name,price');
            $complain['goods_info'][$key]['goods_id'] = $val['goods_id'];
            $complain['goods_info'][$key]['nums'] = $val['nums'];
            $complain['goods_info'][$key]['name'] = $image['name'];
            $complain['goods_info'][$key]['price'] = $image['price'];
            $complain['goods_info'][$key]['image_default_id'] = $image['image_default_id'];
            $complain['goods_info'][$key]['image'] = $this->get_img_url($image['image_default_id'],$params['size']);
        }

        $data['complain']=$complain;
        //$data['member_info'] = $this->get_member_info($complain['from_member_id']);
        //$data['store_info'] = $this->get_store_info($complain['store_id']);

        $this->send(true,$data,app::get('b2c')->_('success'));
    }

    /**
     * reports_list_get 获取店铺举报信息接口方法
     * @author qianlei
     **/
    public function reports_list_get(){
        $params = $this->params;
        $filter_arr = array('goods_id','reports_id','cat_id','status','time_from','time_to');
        $status_arr = array('intervene','voucher','success','error','cancel','finish');

        $filter = isset($params['filter'])?json_decode($params['filter'],true):array();
        $page = isset($params['nPage'])?intval($params['nPage']):1;
        $pagesize = isset($params['pagelimit'])?intval($params['pagelimit']):10;

        if(!empty($filter)){
            foreach($filter as $k=>$v){
                if(!in_array($k,$filter_arr)){
                    $this->send(false,null,app::get('b2c')->_('参数值错误'));
                    exit;
                }

                if($k == 'status' && !in_array($v,$status_arr)){
                    $this->send(false,null,app::get('b2c')->_('状态值错误'));
                    exit;
                }

                if($k == 'time_from' && !empty($v)){
                    $filter['_DTYPE_DATE'][] = 'applyTime[start]';
                    $filter['applyTime']['start'] = $v;
                    unset($filter['time_from']);
                }

                if($k == 'time_to' && !empty($v)){
                    $filter['_DTYPE_DATE'][] = 'applyTime[end]';
                    $filter['applyTime']['end'] = $v;
                    unset($filter['time_to']);
                }
            }
        }

        $obj_complain=kernel::single('complain_seller_reports'); 
        $Data=$obj_complain->get_list($filter,$this,$page,$pagesize); 
        $this->send(true,$Data,app::get('b2c')->_('success'));
    }

    /**
     * reports_detail_get 查看单个举报的详细信息接口方法
     * @author qianlei
     **/
    public function reports_detail_get(){
        $params = $this->params;
        //检查应用级必填参数
        $must_params = array(
            'reports_id'=>'举报ID'
        );
        $this->check_params($must_params);
        $reports_id = $params['reports_id'];
        $Data = $this->get_comment($reports_id);
        $this->send(true,$Data,app::get('b2c')->_('success'));
    }

     /**
     * consult_list_get 获取店铺的咨询接口方法
     * @author qianlei
     **/
    public function consult_list_get(){
        $params = $this->params;

        $page = isset($params['nPage'])?intval($params['nPage']):1;
        $pagesize = isset($params['pagelimit'])?intval($params['pagelimit']):10;
        $type = isset($params['type'])?$params['type']:0;
        $type_arr = array(0,1,2);
        $goods_id = $params['goods_id'];

        if(!in_array($type,$type_arr)){
            $this->send(false,null,app::get('b2c')->_('参数值错误'));
            exit;
        }

        $Data = $this->consult_manage($type,$page,$pagesize,$goods_id);

        $this->send(true,$Data,app::get('b2c')->_('success'));
    }

    /**
     * consult_detail_get 获取店铺的单个咨询详情接口方法
     * @author qianlei
     **/
    public function consult_detail_get(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'comment_id'=>'咨询ID'
        );
        $this->check_params($must_params);
        $comment_id = intval($params['comment_id']);

        $oconsult = app::get('b2c')->model('member_comments');
        $oconsult->use_meta();
        $filter = array('comment_id'=>$comment_id,'disabled'=>'false','object_type'=>'ask');

        $Data = $oconsult->dump($filter,'*');
        $ogoods = app::get('b2c')->model('goods');
        $goods = $ogoods->getList('name',array('goods_id'=>$Data['type_id']));
        $Data['name'] = $goods[0]['name'];

        $Data['sub'] = $oconsult->getList('*',array('for_comment_id'=>$comment_id));

        $this->send(true,$Data,app::get('b2c')->_('success'));
    }

    /**
     * consult_count_get 咨询统计
     * @author lufeng
     **/
    public function consult_count_get(){
        $params = $this->params;

        $page = isset($params['page'])?intval($params['page']):1;

        $Data = $this->consult_count();

        $this->send(true,$Data,app::get('b2c')->_('success'));
    }

    /**
     * consult_reply_add 卖家对单个咨询进行回复接口方法
     * @author qianlei
     **/
    public function consult_reply_add(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'comment_id'=>'咨询ID',
            'comment'=>'回复内容'
        );
        $this->check_params($must_params);
        $comment_id = intval($params['comment_id']);
        $comment = $params['comment'];

        $oconsult = app::get('b2c')->model('member_comments');
        $oconsult->use_meta();

        $consult = $oconsult->getList('*',array('comment_id'=>$comment_id,'store_id'=>$this->store_id,'object_type'=>'ask'));
        if(!$consult){
            $this->send(false,NULL,app::get('b2c')->_('该咨询不存在'));exit;
        }
        $consult = $consult[0];
        $member_comments = kernel::single('b2c_message_disask');

        if(app::get('b2c')->getConf('comment.display.ask') == 'reply'){
            $aData = $consult;
            $aData['display'] = 'true';
            $member_comments->save($aData);
        }

        $Data = array();
        $Data['for_comment_id'] = $comment_id;
        $Data['object_type'] = 'ask';
        $Data['author_id'] = $this->member['member_id'];
        $Data['author'] = '店主';
        $Data['time'] = time();
        $Data['last_reply'] = time();
        $Data['to_id'] = $consult['author_id'];
        $Data['comment'] = $comment;
        $Data['title'] = '';
        $Data['contact'] = '';
        $Data['display'] = 'true';
        $Data['to_uname'] = $consult['author'];
        $Data['gask_type'] = $consult['gask_type'];
        $Data['ip'] = $_SERVER["REMOTE_ADDR"];

        if($member_comments->send($Data,'ask')){
             $comments = app::get('b2c')->model('member_comments');
             $mem['member_id'] = $Data['to_id'];
             $comments->fireEvent('gaskreply',$mem,$Data['to_id']);

             $this->send(true,NULL,app::get('b2c')->_('success'));
        }
        else{
              
          $this->send(false,NULL,app::get('b2c')->_('回复失败'));
        }

    }

    /**
     * consult_delete 删除单个咨询接口方法
     * @author qianlei
     **/
    public function consult_delete(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'comment_id'=>'咨询ID'
        );
        $this->check_params($must_params);
        $comment_ids = $params['comment_id'];
        $comment_ids = explode(',',$comment_ids);

        $oconsult = app::get('b2c')->model('member_comments');
        $oconsult->use_meta();

        $count = $oconsult->count(array('comment_id'=>$comment_ids,'store_id'=>$this->store_id,'object_type'=>'ask'));

        if($count != count($comment_ids)){
            $this->send(false,NULL,app::get('b2c')->_('删除失败,有不存在的ID'));exit;
        }

        $consult = $oconsult->getList('comment_id',array('for_comment_id'=>$comment_ids,'object_type'=>'ask'));
        $com_reply = array();
        foreach($consult as $k=>$v){
            $com_reply[] = $v['comment_id'];
        }

        if($oconsult->delete(array('comment_id'=>$comment_ids))){
            $oconsult->delete(array('comment_id'=>$com_reply));
            $this->send(true,NULL,app::get('b2c')->_('success'));
        }else{
            $this->send(false,NULL,app::get('b2c')->_('删除失败'));
        }
    }

    /**
     * storemsg_list_get 获取站内信操作接口方法
     * @author qianlei
     **/
    public function storemsg_list_get(){
        $params = $this->params;

        $page = isset($params['nPage'])?intval($params['nPage']):1;
        $pagesize = isset($params['pagelimit'])?intval($params['pagelimit']):10;
        $type = isset($params['type'])?$params['type']:0;
        $type_arr = array(0,1,2);

        if(!in_array($type,$type_arr)){
            $this->send(false,null,app::get('b2c')->_('参数值错误'));
            exit;
        }

        $Data = $this->store_msg($type,$page,$pagesize);

        $this->send(true,$Data,app::get('b2c')->_('success'));

    }

    /**
     * storemsg_count_get 获取站内信数量接口方法
     * @author lufeng
     **/
    public function storemsg_count_get(){
        $params = $this->params;

        $Data = $this->store_msg_count();

        $this->send(true,$Data,app::get('b2c')->_('success'));

    }

    /**
     * storemsg_delete 删除单个站内信操作接口方法
     * @author qianlei
     **/
    public function storemsg_delete(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'comment_id'=>'站内信ID'
        );
        $this->check_params($must_params);
        $comment_ids = explode(',',$params['comment_id']);
        //echo '<pre>';print_r($comment_ids);die;//qianleidebug
        if($this->del_in_box_msg($comment_ids,$msg)){
            $this->send(true,null,app::get('b2c')->_('success'));
        }else{
            $this->send(false,null,$msg);
        }
    }

    /*----------------------私有方法---------------------*/
    /**
     * 删除所选站内信
     */
     private function del_in_box_msg($comment_id,&$msg){
        $objMsg = kernel::single('b2c_message_msg');
        if($objMsg->check_msg($comment_id,$this->member['member_id'])){
            if($objMsg->delete_msg($comment_id,'inbox')){
                $msg = '删除成功！';
                return true;
            }
            else{
                $msg = '删除失败！';
                return false;
            }
        }else{
            $msg = '参数提交错误！！';
            return false;
        }

    }

    /**
     * 站内信列表显示
     */
    private function store_msg($type=0,$nPage=1,$pagesize=10){
        $oMsg = kernel::single('b2c_message_msg');
        //全部
        if($type == 0){
            $row = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true'));
            $aData['data'] = $row;
            $aData['total'] = count($row);
            $count = count($row);
            $aPage = $this->get_start($nPage,$count,$pagesize);
            $params['data'] = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' =>'true'),$aPage['start'],$pagesize);
            $params['page'] = $aPage['maxPage'];
            $result['message'] = $params['data'];
            $result['total_msg'] = $aData['total'];

            $result['pager'] = array(
                    'current'=>$nPage,
                    'total'=>$params['page'],
                    );
        
        }else if($type == 1){
            //未读
            $row = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
            $aData['data'] = $row;
            $aData['total'] = count($row);
            $count = count($row);
            $aPage = $this->get_start($nPage,$count,$pagesize);
            $params['data'] = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' =>'true','mem_read_status' => 'false'),$aPage['start'],$pagesize);

            $params['page'] = $aPage['maxPage'];
            $result['message'] = $params['data'];
            $result['total_msg'] = $aData['total'];

            $result['pager'] = array(
                    'current'=>$nPage,
                    'total'=>$params['page'],
                    );
        
        }else if($type == 2){
            //已读
            $row = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'true'));
            $aData['data'] = $row;
            $aData['total'] = count($row);
            $count = count($row);
            $aPage = $this->get_start($nPage,$count,$pagesize);
            $params['data'] = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' =>'true','mem_read_status' => 'true'),$aPage['start'],$pagesize);

            $params['page'] = $aPage['maxPage'];
            $result['message'] = $params['data'];
            $result['total_msg'] = $aData['total'];

            $result['pager'] = array(
                    'current'=>$nPage,
                    'total'=>$params['page'],
                    );
        
        }

        //站内信条数显示
        $all = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true'));
        $all = count($all);

        $no_read = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
        $no_read = count($no_read);

        $had_read = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'true'));
        $had_read = count($had_read);

        $result['type'] = $type;
        $result['all'] = $all;
        $result['no_read'] = $no_read;
        $result['had_read'] = $had_read;

        return $result;
    }

    /**
     * 站内信数量获取
     */
    private function store_msg_count(){
        $oMsg = kernel::single('b2c_message_msg');
        
        //站内信条数显示
        $all = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true'));
        $all = count($all);

        $no_read = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
        $no_read = count($no_read);

        $had_read = $oMsg->getList('*',array('to_id' => $this->member['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'true'));
        $had_read = count($had_read);

        $result['all'] = $all;
        $result['no_read'] = $no_read;
        $result['had_read'] = $had_read;

        return $result;
    }

    private function get_start($nPage,$count,$pagesize){
        $maxPage = ceil($count / $pagesize);
        if($nPage > $maxPage) $nPage = $maxPage;
        $start = ($nPage-1) * $pagesize;
        $start = $start<0 ? 0 : $start;
        $aPage['start'] = $start;
        $aPage['maxPage'] = $maxPage;
        return $aPage;
    }

    /**
     * consult_manage 店铺咨询管理列表私有方法
     * @param string type 咨询类型
     */
    private function consult_manage($type=0,$page=1,$pageLimit=20,$goods_id=''){
        $oconsult = app::get('b2c')->model('member_comments');
        $oconsult->use_meta();

        $member_id = $this->member['member_id'];
        $sto= kernel::single("business_memberstore",$member_id);
        $store_id = $sto->storeinfo['store_id'];
        if($goods_id){
            if($type==0){
               $count = $oconsult->count(array('store_id'=>$store_id,'type_id'=>$goods_id,'object_type'=>'ask'));

               $consult = $oconsult->getList('*',array('store_id'=>$store_id,'type_id'=>$goods_id,'object_type'=>'ask'),$pageLimit*($page-1),$pageLimit);
               $Data['type'] = $type;
            }else{
                if($type ==2){
                    $count = $oconsult->count(array('store_id'=>$store_id,'reply_name|noequal'=>'','type_id'=>$goods_id,'object_type'=>'ask'));
                    $consult = $oconsult->getList('*',array('store_id'=>$store_id,'reply_name|noequal'=>'','type_id'=>$goods_id,'object_type'=>'ask'),$pageLimit*($page-1),$pageLimit);
                    $Data['type'] = $type;
                }
                if($type == 1){
                    $count = $oconsult->count(array('store_id'=>$store_id,'filter_sql'=>'reply_name is null','type_id'=>$goods_id,'object_type'=>'ask'));
                    $consult = $oconsult->getList('*',array('store_id'=>$store_id,'filter_sql'=>'reply_name is null','type_id'=>$goods_id,'object_type'=>'ask'),$pageLimit*($page-1),$pageLimit);
                    $Data['type'] = $type;
                }
            }
        }else{
            if($type==0){
               $count = $oconsult->count(array('store_id'=>$store_id,'type_id|noequal'=>'','object_type'=>'ask'));

               $consult = $oconsult->getList('*',array('store_id'=>$store_id,'type_id|noequal'=>'','object_type'=>'ask'),$pageLimit*($page-1),$pageLimit);
               $Data['type'] = $type;
            }else{
                if($type ==2){
                    $count = $oconsult->count(array('store_id'=>$store_id,'reply_name|noequal'=>'','type_id|noequal'=>'','object_type'=>'ask'));
                    $consult = $oconsult->getList('*',array('store_id'=>$store_id,'reply_name|noequal'=>'','type_id|noequal'=>'','object_type'=>'ask'),$pageLimit*($page-1),$pageLimit);
                    $Data['type'] = $type;
                }
                if($type == 1){
                    $count = $oconsult->count(array('store_id'=>$store_id,'filter_sql'=>'reply_name is null','type_id|noequal'=>'','object_type'=>'ask'));
                    $consult = $oconsult->getList('*',array('store_id'=>$store_id,'filter_sql'=>'reply_name is null','type_id|noequal'=>'','object_type'=>'ask'),$pageLimit*($page-1),$pageLimit);
                    $Data['type'] = $type;
                }
            }
        }

        $ogoods = app::get('b2c')->model('goods');
        $Data['pager'] = array(
            'current'=>$page,
            'total'=>ceil($count/$pageLimit)
            );
        $Data['count'] = $this->consult_count();
        foreach($consult as $key=>&$value){
            $goods = $ogoods->getList('name',array('goods_id'=>$value['type_id']));
            $value['name'] = $goods[0]['name'];
            $value['sub'] = $oconsult->getList('*',array('for_comment_id'=>$value['comment_id']));
        }
        
        $Data['data'] = $consult;
        return $Data;
    }

    /**
     * consult_manage 店铺咨询管理条数私有方法
     * @param string type 咨询类型
     */
    private function consult_count(){
        $oconsult = app::get('b2c')->model('member_comments');
        $oconsult->use_meta();

        $member_id = $this->member['member_id'];
        $sto= kernel::single("business_memberstore",$member_id);
        $store_id = $sto->storeinfo['store_id'];

        $count['all'] = $oconsult->count(array('store_id'=>$store_id,'type_id|noequal'=>'','object_type'=>'ask'));
        $count['no_replay'] = $oconsult->count(array('store_id'=>$store_id,'filter_sql'=>'reply_name is null','type_id|noequal'=>'','object_type'=>'ask'));
        $count['had_replay'] = $oconsult->count(array('store_id'=>$store_id,'reply_name|noequal'=>'','type_id|noequal'=>'','object_type'=>'ask'));

        return $count;
    }

    /**
     * get_comment 获取店铺单个举报详细信息私有方法
     * @param string $reports_id 举报id
     */
    private function get_comment($reports_id){
        $subsdf = array('reports_comments'=>'*'); 
        $complain=app::get('complain')->model('reports')->dump($reports_id, '*', $subsdf);
        $complain['reports_comments'] = array_values($complain['reports_comments']);

        //修改留言图片地址
        if(!empty($complain['reports_comments'])){
            foreach($complain['reports_comments'] as $k=>$v){
                for($i=0;$i<5;$i++){
                    if(!empty($v['image_'.$i])){
                        $complain['reports_comments'][$k]['image_'.$i] = $this->get_img_url($v['image_'.$i]);
                    }
                }
            }
        }

         //举报类型
        if ($complain['cat_id']) {
          $objCat=&app::get('complain')->model('reports_cat');
          $catData=  $objCat->getList('cat_name',array('cat_id'=>$complain['cat_id']));
          if($catData){
              $complain['cat_name'] =$catData[0]['cat_name'];
          }

        }
        //商品
        if ( $complain['goods_id']) {
          $objB2c=&app::get('b2c')->model('goods');
          $goodsData=  $objB2c->getList('name,image_default_id,price',array('goods_id'=>$complain['goods_id']));
          if($goodsData){
              $complain['image'] = $this->get_img_url($goodsData[0]['image_default_id']);
              $complain['goods_name'] =$goodsData[0]['name'];
              //$complain['image_default_id'] =$goodsData[0]['image_default_id'];
              $complain['price'] =$goodsData[0]['price'];
          }

        }

        //$Data['member_info'] = $this->get_member_info($complain['member_id']);
        //$Data['store_info'] = $this->get_store_info($complain['store_id']);
        $Data['complain']=$complain;

        return $Data;
    }

    /**
     * get_member_info 获取会员信息私有方法
     * @param string type 咨询类型
     */
    private function get_member_info($member_id){
        $obj_member = app::get('b2c')->model('members');
        $member_info = $obj_member->dump($member_id, '*');
        return $member_info;
    }

    private function get_store_info($store_id){
       $obj_strman = app::get('business')->model('storemanger');
       $store_info=$obj_strman->dump($store_id);
       return $store_info;
    }

    private function get_order_info($member_id,$order_id,$store_id){
        $obj_order=app::get('b2c')->model('orders');
        $filter=array(
            'order_id'=>$order_id,
            'store_id'=>$store_id,
            'member_id'=>$member_id
        );
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $order_info=$obj_order->dump($filter, '*', $subsdf);
        return $order_info;
    }
}