<?php



/**
 * b2c aftersales interactor with center
 */
class businessapi_api_1_0_searchstore
{
    /**
     * app object
     */
    public $app;

    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = app::get('business');
		$this->app_b2c=app::get('b2c');

     }
	/**
     * 查询店铺
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回店铺信息
     */
	 public function search_store(&$sdf,&$obj){
	     
		 if(!empty($sdf['store_cert'])){
		     $filter['store_cert']=$sdf['store_cert'];
		 }else{
	         if(isset($sdf['approvestatus'])){
		         $filter['approved']=$sdf['approvestatus'];
		     }
			 if(isset($sdf['balance_type'])){
			     $filter['balance_type']=$sdf['balance_type'];
			 }
		 }
		 if(!empty($sdf['start_time'])&&!empty($sdf['end_time'])){
		     if(strtotime($sdf['start_time'])>strtotime($sdf['end_time'])){
				    $obj->send_user_error(app::get('business')->_('起始时间不能大于结束时间！'));
		 	 }
		 }
		 if(!empty($sdf['start_time'])){
		       $filter['last_modify|bthan']=strtotime($sdf['start_time']);
		 }
		 if(!empty($sdf['end_time'])){
		       $filter['last_modify|sthan']=strtotime($sdf['end_time']);
		 }
		 if(!empty($sdf['columns'])){
		     $arr=explode('|',$sdf['columns']);
			 $clos=implode(',',$arr);
		 }else{
		     $clos="store_id,shop_name,store_idcardname,store_name,area,addr,tel,zip,store_grade,last_time,earnest,company_name,company_no,company_taxno,company_codename,company_idname,company_idcard,company_cname,company_cidcard,company_charge,company_ctel,company_area,company_addr,company_carea,company_caddr,company_earnest,company_time,company_timestart,company_timeend,company_remark,company_url,shopstatus,status,approved,last_modify,remark,approvedremark,approve_time,approved_time,apply_time,store_cert,bank_name,bank_cardid,balance_type,merchant_name,merchant_id";
		 }
		 if(!empty($sdf['page'])&&$sdf['page']>0){
		     $page=$sdf['page'];
		 }else{
		     $page=1;
		 }
		 if(!empty($sdf['counts'])&&$sdf['counts']>0){
		     $counts=$sdf['counts'];
		 }else{
		     $counts=20;
		 }

		  $mdl_storemanger=$this->app->model('storemanger');
     
          $rows=$mdl_storemanger->count($filter);
			$nPage=ceil($rows/$counts);
			if($page>$nPage)
				 $page=$nPage;

		 $arr_storemanger=$mdl_storemanger->getList($clos,$filter,($page-1)*$counts,$counts);
		 if(empty($arr_storemanger)){
		     $obj->send_user_error(app::get('business')->_('没有查到店铺信息！'));	
		 }
		 if(!empty($sdf['store_cert'])&&count($arr_storemanger)==1){
		      $arr_page['limit']=1;
			  $arr_page['cPage']=1;
			  $arr_page['counts']=1;
		 }else{
		      $arr_page['limit']=intval($counts);
			  $arr_page['cPage']=intval($page);
			  $arr_page['counts']=intval($rows);

		 }
		 foreach($arr_storemanger as &$storemanger){
		    if(!empty($storemanger['company_time']))
				$storemanger['company_time']=strtotime($storemanger['company_time']);
			if(!empty($storemanger['company_timestart']))
				$storemanger['company_timestart']=strtotime($storemanger['company_timestart']);
			if(!empty($storemanger['company_timeend']))
				$storemanger['company_timeend']=strtotime($storemanger['company_timeend']);
			if(!empty($storemanger['last_time']))
				$storemanger['last_time']=intval($storemanger['last_time']);
		    if(!empty($storemanger['last_modify']))
				$storemanger['last_modify']=intval($storemanger['last_modify']);
			if(!empty($storemanger['approve_time']))
				$storemanger['approve_time']=intval($storemanger['approve_time']);
			if(!empty($storemanger['approved_time']))
				$storemanger['approved_time']=intval($storemanger['approved_time']);
			if(!empty($storemanger['apply_time']))
				$storemanger['apply_time']=intval($storemanger['apply_time']);
		 }
		 $return['page']=$arr_page;
		 $return['info']=$arr_storemanger;
		 echo "执行成功";
		 return $return;
	 }
	
}

