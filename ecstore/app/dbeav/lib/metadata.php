<?php

 
/**
 * dbeav_meta
 * meta?
 *
 * @uses modelFactory
 * @package
 * @version $Id$
 * @license Commercial
 */

 class dbeav_metadata
 {
    private $_meta_columns = array();
	
	function __construct(){
		$sql = "select * from sdb_dbeav_meta_register";
		$arr_rows = kernel::database()->select($sql);
		foreach ($arr_rows as $row){
			$this->_meta_columns[$row['tbl_name']][$row['col_name']] = $row;
		}
	}
	
	public function get_all(){
		return $this->_meta_columns;
	}
 
 }