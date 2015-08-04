<?
//查询所有店铺

require_once "base.php";
//系统级参数必填
$post_data['direct']=true;
$post_data['date']=date('Y-m-d H:i:s',time());//当前时间
$post_data['method']="businessapi.searchstore.search_store";

//应用级参数
//分页大小,可选
$post_data['counts']=2;
//页数,可选
$post_data['page']=1;
//可选返回店铺信息字段
$post_data['columns']='store_id|shop_name|approved';
//认证状态,可根据认证状态查询
$post_data['approvestatus']='1';

//验签
$token="";
$sign = gen_sign($post_data,$token);
$post_data['sign'] = $sign;

$url = 'http://58.214.7.150:8992/src/index.php/api';//接口地址	

//调用接口
$rs=post($post_data,$url);
//打印结果
output($rs);