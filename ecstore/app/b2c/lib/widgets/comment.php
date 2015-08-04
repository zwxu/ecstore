<?php
/**
 * @author chris.zhang
 * 
 */
class b2c_widgets_comment extends b2c_widgets_public {
    //评论返回数据格式
    protected $_outData = array(
        'commentAuthor' => 'author',        //评论人
        'comment'       => 'comment',       //评论内容
        'commentTime'   => 'time',          //讨论时间
        'goodsId'       => 'type_id',       //商品ID
        'goodsName'     => 'name',          //商品名称
        'goodsPic'      => '_s_pic_',       //商品图片(小图)
        'goodsLink' =>'_goodsLink_',
        'goodsDetail' =>'_goodsDetail_',
    );
    
    /**
     * 获取最新的评论（默认10条，不超过20条）
     * @param int $number   //评论数量
     */
    public function getTopComment($number){
        $num    = intval($number) <= 0 ? 10 : (intval($number) >= 20 ? 20 : intval($number));
        $_data  = kernel::single('b2c_message_disask')->getTopComment($num);
        $data   = array();
        foreach ((array)$_data as $row){
            if ($row['image_default_id']){
                $imageUrl = $this->get_image_url($row['image_default_id']);
                $row['_s_pic_'] = $imageUrl['url_small'];
            }else {
                $row['_s_pic_'] = '';
            }

            $row['_goodsLink_'] = $this->get_link(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','args'=>array($row['type_id'])));
            $row['_goodsDetail_']  = b2c_widgets::load('Goods')->getGoodsList(array('goodsId' =>array($row['type_id'])));
            $row['_goodsDetail_'] = $row['_goodsDetail_']['goodsRows'][$row['type_id']];
            $data[] = $this->_getOutData($row);
        }
        return $data;
    }
}