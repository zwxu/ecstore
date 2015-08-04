<?php
//投诉管理
class complain_ctl_site_buyer_reports extends b2c_ctl_site_member
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
        $this->mdl_reports=$this->app_current->model('reports');
        $this->pagedata['isIE']=(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false);

    }
    public function reports_main($nPage=1){ 
        $obj_complain=kernel::single('complain_buyer_reports');

      
        $aData=$obj_complain->get_list($_POST,$this,$nPage); 
        $this->pagedata['complain_list']=$aData['data'];

        
       // echo '<pre>';print_r($aData['data']);echo '</pre>';
        $this->pagination($nPage,$aData['page'],'main','','complain',$ctl='site_buyer_reports');
        $this->action_view='index.html';
        $this->_output();
    }
    public function _output(){
        $this->action_view='buyer/reports/'.$this->action_view;
        $this->output('complain');
    }


    public function add($goods_id,$store_id){

         //$goods_id='3'; $store_id='3'; 

          $member_id = $this->b2c_app->member_id;
       
          //判断商品是否是此店铺
          $aryGoods=&app::get('b2c')->model('goods')->getList('*',array('store_id'=>$store_id,'goods_id'=>$goods_id));

          if(empty($aryGoods)){
               $this->splash('failed',$_SERVER['HTTP_REFERER'],app::get('b2c')->_('此店铺无此商品'),'','',false);
          }

          //$reports_cat
          $aryreports_cat=&app::get('complain')->model('reports_cat')->getList('cat_id,cat_name',array('disabled'=>'false'));
          $this->pagedata['reports_cat']= $aryreports_cat;
          $this->pagedata['goods_id']=$goods_id;
          $this->pagedata['goods_name']=$aryGoods[0]['name'];
          $this->get_member_info($member_id);
          $this->get_store_info($store_id);
          $this->_output();
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


     public function addSave(){ 
    
        $error_url = $this->gen_url(array('app' => 'complain','ctl' => 'site_buyer_reports', 'act'=>'add','arg0'=>$_POST['goods_id'],'arg1'=>$_POST['store_id']));
        $success_url=$this->gen_url(array('app'=>'complain','ctl'=>'site_buyer_reports','act'=>'reports_main'));

        //同一件商品如果已被其他会员举报，系统会提示您不必重复举报；
        $aryGoods=$this->mdl_reports->getList("*",array('goods_id'=>$_POST['goods_id'],'disabled'=>'false','status|notin'=>array('error','cancel','finish')));
        if( $aryGoods){
            $this->splash('failed',$error_url,app::get('b2c')->_('该商品已被举报，您无需再次举报。'),'','',false);
        }
        
        
        //每人每天对同一店铺最多只能举报4件商品
        $today= mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()));
        $aryGoods=$this->mdl_reports->getList("*",array('member_id'=>$_POST['from_member_id'],
                                                'disabled'=>'false','store_id'=>$_POST['store_id'],
                                                'createtime|bthan' =>$today,
            'status|notin'=>array('error','cancel','finish')));
        if( $aryGoods && count($aryGoods) > 3){
            $this->splash('failed',$error_url,app::get('b2c')->_('每人每天对同一店铺最多只能举报4件商品。'),'','',false);
        }

        
        $isBigImage=false;
        foreach($_FILES['image']['size'] as $key=>$v){
            if(intval($v)>5242880){
                $isBigImage=true;
                break;
            }
        }
        if($isBigImage){
            $this->splash('failed',$error_url,app::get('b2c')->_('图片不能大于5M。'),'','',false);
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
            $this->splash('failed',$error_url,app::get('b2c')->_('图片上传失败，请查看格式和大小是否符合要求。'),'','',false);
        }
        
        $minfo=$this->get_current_member();
        $c_id=$this->mdl_reports->gen_id();
        $data=array();
        $data['reports_id']=$c_id;
        $data['goods_id']=$_POST['goods_id'];
        $data['member_id']=$_POST['from_member_id'];
        $data['store_member_id']=$_POST['to_member_id'];
        $data['store_id']=$_POST['store_id'];
        $data['cat_id']  =$_POST['cat_id'];                      
        $data['mobile']=$_POST['mobile'];
        $data['memo']=$_POST['memo'];
        $data['store_uname']=$_POST['to_uname'];
        $data['status']='intervene';
        $data['createtime']=time();
        $data['last_modified']=time();

        $data_item=array();
        $data_item['reports_id']=$c_id;
        $data_item['author_id']=$_POST['from_member_id'];
        $data_item['author']=$minfo['uname'];
        $data_item['comment']=$data['memo'];
        $data_item['image_0']=$image_0;
        $data_item['image_1']=$image_1;
        $data_item['image_2']=$image_2;
        $data_item['last_modified']=time();
        
        $data['reports_comments'][]=$data_item;
        $result=$this->mdl_reports->save($data);
        if($result){
            $this->splash('success',$success_url,app::get('b2c')->_('举报成功，请等待处理'),'','',false);
        }else{        
            $this->splash('failed',$error_url,app::get('b2c')->_('举报失败'),'','',false);
            
        }  
       
    }


     public function show_comment($reports_id){
        $subsdf = array('reports_comments'=>'*'); 
        $complain=$this->mdl_reports->dump($reports_id, '*', $subsdf);

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
          $goodsData=  $objB2c->getList('name',array('goods_id'=>$complain['goods_id']));
          if($goodsData){
              $complain['goods_name'] =$goodsData[0]['name'];
          }

        }


        
        //$this->get_order_info($complain['from_member_id'],$complain['order_id'],$complain['store_id']);
        $this->get_member_info($complain['member_id']);
        $this->get_store_info($complain['store_id']);
        $this->pagedata['complain']=$complain;
        
        //echo '<pre>';print_r($complain);echo '</pre>';
        $this->_output();
    }


  public function cancel_comment($reports_id){ 
        $data=array();
        $data['reports_id']=$reports_id;
        $data['status']='cancel';
        $data['last_modified']=time();
        $result=$this->mdl_reports->save($data);
        echo $result?$reports_id:'';
    }


    public function save_comment(){
        
        //echo '<pre>';print_r($_FILES);echo '</pre>';exit;
        $url = $this->gen_url(array('app' => 'complain','ctl' => 'site_buyer_reports', 'act'=>'show_comment','arg0'=>$_POST['reports_id']));
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
        $data_item['reports_id']=$_POST['reports_id'];
        $data_item['author_id']=$_POST['from_member_id'];
        $data_item['author']=$minfo['uname'];
        $data_item['comment']=$_POST['comment'];
        $data_item['source']=$_POST['source'];
        $data_item['image_0']=$image_0;
        $data_item['image_1']=$image_1;
        $data_item['image_2']=$image_2;
        $data_item['last_modified']=time();
        $mdl_comments=$this->app_current->model('reports_comments');
        $result=$mdl_comments->save($data_item);
        if($result){
            $this->splash('success',$url,app::get('b2c')->_('留言成功，请等待处理'),'','',false);
        }else{        
            $this->splash('failed',$url,app::get('b2c')->_('留言失败'),'','',false);
            
        } 
    }
    
} 