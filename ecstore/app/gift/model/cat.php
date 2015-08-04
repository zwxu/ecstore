<?php





class gift_mdl_cat extends dbeav_model {
    public $recycle_msg;

	public $defaultOrder = array('p_order',' ASC',',cat_id',' ASC');
    
    function pre_recycle( $arr ) {
        foreach( $arr as $row ) {
            $arr_cat_id[] = $row['cat_id'];
            $arr_cat_name[$row['cat_id']] = $row['cat_name'];
        }
        
        $arr_gift = $this->app->model('ref')->getList( 'cat_id',array('cat_id'=>$arr_cat_id) );
        if( $arr_gift ) {
            foreach( $arr_gift as $gift ) {
                $name = $arr_cat_name[$gift['cat_id']];
                break;
            }
            $this->recycle_msg = '分类'.$name.'下有赠品存在！不允许删除！';
            return false;
        }
        return true;
    }
}