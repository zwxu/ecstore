<?php
class complain_mdl_complain extends dbeav_model{
  var $has_tag = true;
  var $defaultOrder = array('createtime','DESC');
  var $has_many = array(
        'complain_comments'=>'complain_comments'
    );
    /**
     * 得到唯一的投诉编号
     * @params null
     * @return string 投诉编号
     */
    public function gen_id()
    {
        $i = rand(0,999999);
        do{
            if(999999==$i){
                $i=0;
            }
            $i++;
            $order_id = '4'.date('YmdH').str_pad($i,6,'0',STR_PAD_LEFT);
            $row = $this->db->selectrow('SELECT complain_id from sdb_complain_complain where complain_id ='.$order_id);
        }while($row);
        return $order_id;
    }
    
}
