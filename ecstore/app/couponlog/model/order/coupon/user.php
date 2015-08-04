<?php 
/**
 * @package default
 * @author kxgsy163@163.com
 */
class couponlog_mdl_order_coupon_user extends dbeav_model
{
    
    var $defaultOrder = array('id',' DESC');
    public function modifier_member_id( $cols ) {
        if( $cols ) {
            $arr = app::get('pam')->model('account')->dump( $cols );
            return $arr['login_name'];
        } else {
            return '非会员顾客';
        }
    }
	
	/**
     * 重写搜索的下拉选项方法
     * @param null
     * @return null
     */
    public function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v){
            if(isset($v['searchtype']) && $v['searchtype']){
                if ($k == 'member_id')
                {
                    $columns['member_key'] = $v['label'];
                }
                else
                    $columns[$k] = $v['label'];
            }
        }

        return $columns;
    }
	
	public function _filter($filter,$tableAlias=null,$baseWhere=null){
        if($filter['member_key']){
            $aData = app::get('pam')->model('account')->getList('account_id',array('login_name|has' => $filter['member_key']));
            if($aData){
                foreach($aData as $key=>$val){
                    $member[$key] = $val['account_id'];
                }
                $filter['member_id'] = $member;
            }
            else{
                return 0;
            }
            unset($filter['member_key']);
        }
        $filter = parent::_filter($filter);
        return $filter;
    } 
}