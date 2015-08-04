<?php

 
class business_member_storefav
{
    /**
     * 构造方法
     * @param object app
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     * 添加收藏
     * @param string member id
     * @param string object type
     * @param sttring goods id 不能为空
     * @return boolean true or false
     */
    public function add_fav($member_id,$object_type='stores',$nSid=null)
    {
        if(!$nSid || !$member_id) return true;
        $obj_member = &$this->app->model('member_stores' );
        
        return $obj_member->add_fav($member_id,$object_type,$nSid);
    }
    
    /**
     * 去除收藏
     * @param string member id
     * @param string object type
     * @param string goods id 可以为空
     * @param int 当前页
     * @return boolean true or false
     */
    public function del_fav($member_id,$object_type='stores',$nSid=null,&$page=null)
    {
        if (!$member_id) return true;
        
        $flag = false;
        $obj_member = &$this->app->model('member_stores');
        
        if (is_null($nSid)){
            return $obj_member->delAllFav($member_id);
        }else{
            return $obj_member->delFav($member_id,$nSid,$page,10);
        }
    }
    
    /**
     * 获取当前页的收藏内容
     * @param string member id
     * @param string member level
     * @param int page
     * @return array data
     */
    public function get_favorite($member_id,$member_lv,$page=1,$limit)
    {
        $aData = array();
        if (!$member_id || !$member_lv) return $aData;
        
        $obj_member = &$this->app->model('member_stores');
        $aData = $obj_member->get_favorite($member_id,$member_lv,$page,$limit);
        
        return $aData;
    }
}