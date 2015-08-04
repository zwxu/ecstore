<?php
//投诉管理
class complain_ctl_site_seller_complain extends business_ctl_site_member
{
	/**
	 * 构造方法
	 * @param object application
	 */
	public function __construct(&$app)
	{
        $this->b2c_app=app::get('b2c');
        parent::__construct($this->b2c_app);
        $this->app_current=$app;
        $this->mdl_complain=$this->app_current->model('complain');
         $this->pagedata['isIE']=(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false);
         //设置不读缓存 
        $GLOBALS['runtime']['nocache']=microtime();
    }
    public function addComplain($store_id=0,$order_id=0){
         
    }
    public function show_comment($complain_id){
        $subsdf = array('complain_comments'=>'*');
        $complain=$this->mdl_complain->dump($complain_id, '*', $subsdf);
        $this->get_order_info($complain['from_member_id'],$complain['order_id'],$complain['store_id']);
        $this->get_member_info($complain['to_member_id']);
        $this->get_store_info($complain['store_id']);
        $this->pagedata['complain']=$complain;
        
        //echo '<pre>';print_r($complain);echo '</pre>';
        $this->_output();
    }
    public function save_comment(){
        
        //echo '<pre>';print_r($_FILES);echo '</pre>';exit;
        $url = $this->gen_url(array('app' => 'complain','ctl' => 'site_seller_complain', 'act'=>'show_comment','arg0'=>$_POST['complain_id']));
        $isBigImage=false;
        foreach($_FILES['image']['size'] as $key=>$v){
            if(intval($v)>5242880){
                $isBigImage=true;
                break;
            }
        }
        if($isBigImage){
        $this->splash('failed',$url,app::get('b2c')->_('图片不能大于5M。'),'','',false);
        }
        $mdl_img=app::get('image')->model('image');
        if($_FILES['image']['tmp_name'][0]){
            $image_name = $_FILES['image']['name'][0];
            $image_0  = $mdl_img->store($_FILES['image']['tmp_name'][0],null,null,$image_name,false);
            $mdl_img->rebuild($image_0,array('S'),true); 
        }
        if($_FILES['image']['tmp_name'][1]){
            $image_name = $_FILES['image']['name'][1];
            $image_1  = $mdl_img->store($_FILES['image']['tmp_name'][1],null,null,$image_name,false);
            $mdl_img->rebuild($image_1,array('S'),true); 
        }
        if($_FILES['image']['tmp_name'][2]){
            $image_name = $_FILES['image']['name'][2];
            $image_2  = $mdl_img->store($_FILES['image']['tmp_name'][2],null,null,$image_name,false);
            $mdl_img->rebuild($image_2,array('S'),true);
        }
        if(!$image_0&&!$image_1&&!$image_2) {
            $this->splash('failed',$url,app::get('b2c')->_('图片上传失败，请查看格式和大小是否符合要求。'),'','',false);
        }
        $data_item=array();
        $minfo=$this->get_current_member();
        $data_item['complain_id']=$_POST['complain_id'];
        $data_item['author_id']=$_POST['to_member_id'];
        $data_item['author']=$minfo['uname'];
        $data_item['comment']=$_POST['comment'];
        $data_item['source']=$_POST['source'];
        $data_item['image_0']=$image_0;
        $data_item['image_1']=$image_1;
        $data_item['image_2']=$image_2;
        $data_item['last_modified']=time();
        $mdl_comments=$this->app_current->model('complain_comments');
        $result=$mdl_comments->save($data_item);
        if($result){
            $this->splash('success',$url,app::get('b2c')->_('留言成功，请等待处理'),'','',false);
        }else{        
            $this->splash('failed',$url,app::get('b2c')->_('留言失败'),'','',false);
            
        } 
    }
    public function main($npage=1){
        $obj_complain=kernel::single('complain_seller_complain');
        $aData=$obj_complain->get_list($_POST,$this,$nPage); 
        $this->pagedata['complain_list']=$aData['data']; 
        
       //echo '<pre>';print_r($aData['data']);echo '</pre>';
        $this->pagination($nPage,$aData['page'],'main','','complain',$ctl='site_seller_complain');
        $this->action_view='index.html';
        $this->_output();
    }
    public function get_order_info($member_id,$order_id,$store_id){
        $obj_order=$this->b2c_app->model('orders');
        $filter=array(
            'order_id'=>$order_id,
            'store_id'=>$store_id,
            'member_id'=>$member_id
        );
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $order_info=$obj_order->dump($filter, '*', $subsdf);
        $this->get_order_detail_item($order_info,'member_order_detail');
        $this->pagedata['order']=$order_info;
    }
    public function get_member_info($member_id){
        $obj_member=$this->b2c_app->model('members');
        $member_info=$obj_member->dump($member_id, '*');
        $minfo=$this->get_current_member();
        $member_info['uname']=$minfo['uname'];
        $this->pagedata['member_info']=$member_info;
    }
    public function get_store_info($store_id){
       $obj_strman = app::get('business')->model('storemanger');
       $store_info=$obj_strman->dump($store_id);       
       $this->pagedata['store_info']=$store_info;
       //echo '<pre>';print_r($store_info);echo '</pre>';
    }
    public function _output(){
        $this->action_view='seller/complains/'.$this->action_view;
        $this->output('complain');
    }
} 