<?php

 

class bdlink_link {
    private $_ident_op = '#r-p';
    
    
    public function __construct( $app ) {
        $this->app = $app;
    }
    
    public function set_arr($id=0, $type='') {
       if(empty($id) || empty($type)) return false;
       $this->get_refer($data);
       
       if(empty($data['refer_id'])) return false;
       $data['target_id']   = $id;
       $data['target_type'] = $type;
       $arr = $this->app->model('list')->getList('*', array('generatecode|foot'=>$this->_ident_op .$data['refer_id']) );

       if( $arr[0] ) {
           $arr = $arr[0];
           if( $arr['validtime'] )
               $timeout = $arr['validtime'];
           
           //链接有效期
           $tmp = $this->app->model('link')->getList('*', array('refer_id'=>$data['refer_id']), 0, 1, 'c_refer_time ASC');
           if( $tmp ) {
               $tmp = $tmp[0];
               if( empty($timeout) || (1 >= (time()-$tmp['refer_time']) / (60*60*24*$timeout)) ) {
                   $data['refer_url']  = $tmp['refer_url'];
                   $data['refer_time'] = $tmp['refer_time'];
               }
            }
            return kernel::single('bdlink_mdl_link')->save($data);
       } else {
           return false;
       }
    }
    
    public function get_arr($id=0, $type='') {
        if(empty($id) || empty($type)) return false;
        $filter = array(
                                'target_id'=> $id,
                                'target_type' => $type,
                            );
        $arr = kernel::single('bdlink_mdl_link')->getList('*', $filter);;
        return $arr[0];
    }
    
    
    
    private function get_refer(&$data){

        if(isset($_COOKIE['S']['FIRST_REFER'])||isset($_COOKIE['S']['NOW_REFER'])){
            $firstR = json_decode(stripslashes($_COOKIE['S']['FIRST_REFER']),true);
            $nowR = json_decode(stripslashes($_COOKIE['S']['NOW_REFER']),true);
            $data['refer_id'] = urldecode($firstR['ID']);
            $data['refer_time'] = $firstR['DATE']/1000;
            $data['c_refer_id'] = urldecode($nowR['ID']);
            $data['c_refer_url'] = $nowR['REFER'];
            $data['c_refer_time'] = $nowR['DATE']/1000;
            $data['refer_url'] = $firstR['REFER'] ? $firstR['REFER'] : $data['c_refer_url'];
        }
    }
    
    
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
    	#print_R($filter);
        $arr = kernel::single('bdlink_mdl_link')->getList( $cols, $filter, $offset, $limit, $orderType );
        return $arr;
    }
    
    
    
}