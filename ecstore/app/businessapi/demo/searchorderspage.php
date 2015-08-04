<?
//查询所有订单

require_once "base.php";
//系统级参数必填
$post_data['direct']=true;
$post_data['date']=date('Y-m-d H:i:s',time());//当前时间
$post_data['method']="businessapi.searchorder.search_order";

//应用级参数
//店铺ID,查询店铺下订单，可选，不填查询所有
$post_data['store_id']=58;
//每页大小，可选，默认20
$post_data['counts']=3;
//页数，可选，默认1
$post_data['page']=1;
//开始时间，可选，可根据时间段查询
$post_data['start_time']=date('Y-m-d H:i:s',strtotime('2012-1-2 6:02:08'));
//结束时间，可选
$post_data['end_time']=date('Y-m-d H:i:s',strtotime('2014-12-3 16:02:08'));
//可选返回订单信息字段,可选，空返回所有字段
$post_data['columns']='tid|created|modified|pay_status|status|promotion_details|order';
//订单状态，可选，可根据订单状态查询，可传TRADE_ACTIVE(活动中)，TRADE_CLOSED(死单)，TRADE_FINISHED(完成)
$post_data['status']='TRADE_ACTIVE';

$rs=_post($post_data);
//处理bom头，json格式要求严格，只要有不符合规定就无法解析
$result=json_decode(trim($rs, chr(239).chr(187).chr(191)),true);

//查询成功，返回数据
if($result['rsp']=='succ'){
	$page=$result['data']['page'];
	$counts=$page['counts'];//总信息数
	$limit=$page['limit'];  //分页大小


	$totalPage=ceil($counts/$limit);//总页数

	//循环迭代出每页结果
	for($i=1; $i<=$totalPage;$i++){
		$post_data['page']=$i;
		$r=json_decode(trim(_post($post_data), chr(239).chr(187).chr(191)),true);
		//查询成功，输出数据信息
		if($r['rsp']=='succ'){
			echo "<br/>";
			echo "============返回".$r['data']['page']['cPage']."页结果";
			echo "<pre>";
			print_r($r);
			echo "</pre>";
			echo "<br/>============返回结结果end";
		//output($r);
		}
	}
	//查询失败
}elseif($result['rsp']=='fail'){
    echo $result['res'];
}

//发送请求
function _post($post_data){
	//验签
	$token="";
	$sign = gen_sign($post_data,$token);
	$post_data['sign'] = $sign;

	$url = 'http://58.214.7.150:8992/src/index.php/api';//接口地址	
	//调用接口
	$rs=post($post_data,$url);
	return $rs;
}