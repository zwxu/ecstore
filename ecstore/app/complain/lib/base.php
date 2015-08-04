<?php

class complain_base
{
    function __construct(&$app){
       $this->app=$app;
    }
    public function getCurrent_store(){
        $member_info=$obj->get_current_member();
        $memberstore=kernel::single('business_memberstore');
        $memberstore->process($member_info['member_id']);
        return $memberstore->storeinfo;
    }
    public function get_list($post,$obj,$page=1,$list_listnum){ 
        $list_listnum = isset($list_listnum)?intval($list_listnum):10;
        $filter=$this->get_filter($post,$obj);
        $mdl_complain=$this->app->model('complain');
        $count=$mdl_complain->count($filter);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start; 
        $params['data'] = $mdl_complain->getList('*',$filter,$start,$list_listnum);
        $params['page'] = $maxPage;
        return $params;
    }
    public function get_filter($post,$obj){
        $member_info=$obj->get_current_member();
        
        $filter=array();
        if('seller'==$member_info['seller']){
            $memberstore=kernel::single('business_memberstore');
            $memberstore->process($member_info['member_id']);
            //echo '<pre>';print_r($memberstore->storeinfo);echo '</pre>';
           $filter['store_id']=$memberstore->storeinfo['store_id'];
        }else{            
           $filter['from_member_id']=$member_info['member_id'];
        }
        if($post['complain_id']){
           $filter['complain_id']=$post['complain_id'];
        }
        if($post['order_id']){
           $filter['order_id']=$post['order_id'];
        }
        if($post['status'] && 'all'!=$post['status']){
           $filter['status']=$post['status'];
        }
        if($post['reason'] && 'all'!=$post['reason']){
           $filter['reason']=$post['reason'];
        }
        if($post['applyTime']['start']&& $post['applyTime']['end']){
            $start=strtotime($post['applyTime']['start']);
            $end=strtotime($post['applyTime']['end']);
            if($end<$start){
                $filter['createtime|bthan']=$end;//>=
                $filter['createtime|sthan']=$start;//<=
            }else{                
                $filter['createtime|bthan']=$start;//>=
                $filter['createtime|sthan']=$end;//<=
            }
        }else{
            if($post['applyTime']['start']){
               $start=strtotime($post['applyTime']['start']);
               $filter['createtime|bthan']=$start;
            }
            if($post['applyTime']['end']){
               $end=strtotime($post['applyTime']['end']);
               $filter['createtime|sthan']=$end;
            }
        }
        $filter['disabled']='false';
        return $filter;
    }
}