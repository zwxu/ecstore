<?php
class complain_mdl_reports extends dbeav_model{
  var $has_tag = true;
  var $defaultOrder = array('createtime','DESC');
  var $has_many = array(
        'reports_comments'=>'reports_comments'
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
            $row = $this->db->selectrow('SELECT reports_id from sdb_complain_reports where order_id ='.$order_id);
        }while($row);
        return $order_id;
    }


    public function get_List($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){

     $data =$this->getList('*',$filter,$offset,$limit);
     $objCat=&app::get('complain')->model('reports_cat');
     $objB2c=&app::get('b2c')->model('goods');

      foreach($data as $key => &$row) {
            //举报类型
            if ($row['cat_id']) {
              $catData=  $objCat->getList('cat_name',array('cat_id'=>$row['cat_id']));
              if($catData){
                  $row['cat_name'] =$catData[0]['cat_name'];
              }

            }
            //商品
            if ( $row['goods_id']) {
              $goodsData=  $objB2c->getList('name',array('goods_id'=>$row['goods_id']));
              if($goodsData){
                  $row['goods_name'] =$goodsData[0]['name'];
              }

            }

      }

      return  $data;

    }


    public function getGoods_idbyname($goods_name){

        $sql ="SELECT sdb_complain_reports.goods_id FROM sdb_complain_reports LEFT JOIN sdb_b2c_goods ON  sdb_complain_reports.goods_id = sdb_b2c_goods.goods_id
           WHERE   sdb_b2c_goods.`name` LIKE  '%{$goods_name}%'";

      return  $this->db->select($sql);


    }
    
}
