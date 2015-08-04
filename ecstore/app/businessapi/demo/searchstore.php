<?
//查询单个店铺信息

require_once "base.php";

//系统级参数必填
$post_data['direct']=true;
$post_data['date']=date('Y-m-d H:i:s',time());//当前时间
$post_data['method']="businessapi.searchstore.search_store";

//应用及参数
//店铺识别码
$post_data['store_cert']='60DC53DC24A89F2C8C3DFB3D75446137';
//可选返回店铺信息字段
$post_data['columns']='store_id|shop_name|approved';

//验签
$token="";
$sign = gen_sign($post_data,$token);
$post_data['sign'] = $sign;

$url = 'http://58.214.7.150:8992/src/index.php/api';//接口地址	

//调用接口
$rs=post($post_data,$url);
//打印结果
output($rs);