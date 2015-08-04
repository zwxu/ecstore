<?php
class business_mdl_violation extends dbeav_model{
	/**
	 * 构造方法
	 * @param object model相应app的对象
	 * @return null
	 */
    public function __construct($app){
        parent::__construct($app);
        $this->use_meta();
    }

	public function count_finder($filter = null) {
        $row = $this -> db -> select('SELECT count( DISTINCT store_id) as _count FROM ' . $this -> table_name(1) . ' WHERE ' . $this -> _filter($filter));
        return intval($row[0]['_count']);
    }

    public function getList($cols = '*', $filter = array(), $offset = 0, $limit = -1, $orderType = null) {
       
        $tmp = parent::getList($cols, $filter, $offset, $limit, $orderType);

        $objviolationcat = &$this->app->model('violationcat');

        foreach($tmp as $key => &$row) {
            if ( $row['cat_id']) {
                $gradename = $objviolationcat -> getList('cat_name', array('cat_id' => $row['cat_id']));
                $row['violationcat'] = $gradename['0']['cat_name']; 
            }

        }
        return $tmp;
    }

   



    

}
