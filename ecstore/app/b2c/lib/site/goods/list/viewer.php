<?php


$goods_cat_viewer = '商品分类列表展示信息';
class b2c_site_goods_list_viewer{

    function __construct($app){
        $this->app = $app;
        $this->db = kernel::database();
    }

    function get_view($cat_id,$view,$type_id=null,$vircat){
        
    
            if(!is_array($cat_id)){
                $cat_id=array($cat_id);
         
            }
			$type_id = intval($type_id);
            $oGtype = $this->app->model('goods_type');
            if($type_id){
                $sqlString = 'SELECT t.schema_id,t.setting,t.type_id FROM sdb_b2c_goods_type t WHERE type_id ='.$this->db->quote($type_id);
            }elseif($cat_id[0]){
                $cat_id='('.implode($cat_id,' OR ').')';
				$sqlString = 'SELECT c.cat_id,c.cat_name,c.tabs,c.addon,t.setting,t.schema_id,t.setting,t.type_id FROM sdb_b2c_goods_cat c
					LEFT JOIN sdb_b2c_goods_type t ON c.type_id = t.type_id
					WHERE '.$this->db->quote($cat_id);
            }
            
            if($sqlString) {
                $row = $this->db->selectrow($sqlString);
				
				/** 获取商品品牌 **/
				$sqlString = "SELECT props.*,props_value.props_value_id,props_value.name AS props_value_name,props_value.alias AS props_value_alias,props_value.order_by
								FROM sdb_b2c_goods_type_props props
								LEFT JOIN sdb_b2c_goods_type_props_value props_value ON props.props_id=props_value.props_id
								WHERE props.type_id=".$this->db->quote($type_id)
								." ORDER BY props_value.order_by ASC, props.ordernum ASC";
				$row_props_values = $this->db->select($sqlString);
				$arr_props_values = array();
				
				foreach ((array)$row_props_values as $key=>$arr){
					if (!$arr_props_values[$arr['goods_p']])
						$arr_props_values[$arr['goods_p']] = array(
							'props_id'=>$arr['props_id'],
							'type_id'=>$arr['type_id'],
							'type'=>$arr['type'],
							'search'=>$arr['search'],
							'show'=>$arr['show'],
							'name'=>$arr['name'],
							'alias'=>$arr['alias'],
							'goods_p'=>$arr['goods_p'],
							'ordernum'=>$arr['ordernum'],
							'lastmodify'=>$arr['lastmodify'],
							's_type'=>$arr['s_type'],//属性前台单选还是多选
						);
					$arr_props_values[$arr['goods_p']]['options'][$arr['props_value_id']] = $arr['props_value_name'];
					$arr_props_values[$arr['goods_p']]['optionAlias'][$arr['props_value_id']] = $arr['props_value_alias'];
					$arr_props_values[$arr['goods_p']]['optionIds'][$arr['props_value_id']] = $arr['props_value_id'];
				}				
                $row = array_merge( $row, array('props'=>$arr_props_values) );
            }
            //如果未设定类型则取全部品牌。
            if($row['type_id']){
                if($vircat){ 
                    //如果是虚拟分裂似则取商品分类所对应的品牌
					$cat_id = '('.implode(',',$cat_id).')';
					$sqlString = "SELECT b.brand_id,b.brand_name,brand_url,brand_logo
									FROM sdb_b2c_goods_cat cat
									LEFT JOIN sdb_b2c_type_brand t_brand ON cat.type_id=t_brand.type_id
									LEFT JOIN sdb_b2c_brand b ON b.brand_id=t_brand.brand_id
									WHERE cat.cat_id IN" . $cat_id;
					$row['brand'] = $this->db->select($sqlString);
                }else{
                    //取类型所对应的品牌。
					$sqlString = 'SELECT b.brand_id,b.brand_name,brand_url,brand_logo FROM sdb_b2c_type_brand t
									LEFT JOIN sdb_b2c_brand b ON b.brand_id=t.brand_id
									WHERE disabled="false" AND t.type_id='.$row['type_id'].' ORDER BY brand_order';

					$row['brand'] = $this->db->select($sqlString);
                }
            }else{//全部品牌
                $oBrand = $this->app->model('brand');
                $row['brand'] = $oBrand->getList('brand_id,brand_name', '', 0, -1);
            }
            $dftList = array(
                    app::get('b2c')->_('店铺')=>'shop',
                    //app::get('b2c')->_('列表')=>'list',
                    app::get('b2c')->_('大图')=>'grid',
                    //app::get('b2c')->_('文字')=>'text',
                    app::get('b2c')->_('小图')=>'sgrid',
                );
            //商品类型对应的设定a:4:{s:8:"use_spec";N;s:9:"use_brand";i:1;s:9:"use_props";N;s:10:"use_params";N;}
            if($row['setting']&&!is_array($row['setting'])){
                $row['setting'] = unserialize($row['setting']);
            }
            if(isset($row['setting']['list_tpl']) && is_array($row['setting']['list_tpl']))
                foreach($row['setting']['list_tpl'] as $k=>$tpl){
                    if(!in_array($tpl,$dftList)){
                        if(!file_exists(SCHEMA_DIR.$row['schema_id'].'/view/'.$tpl.'.html')){
                            unset($row['setting']['list_tpl'][$k]);
                        }
                    }
                }
              
            if(!isset($row['setting']['list_tpl']) || !is_array($row['setting']['list_tpl']) || count($row['setting']['list_tpl'])==0){
                $row['setting']['list_tpl'] = $dftList;
            }
            if($view=='index') $view = current($row['setting']['list_tpl']);
            //如果视图存在则调用。不存在则取
            if(in_array($view,$dftList)){
                    $row['tpl'] = '/site/gallery/type/'.$view.'.html';
            }else{
                $row['tpl'] = realpath(SCHEMA_DIR.$row['schema_id'].'/view/'.$view.'.html');
            }
            $row['dftView'] = $view;
            $row['setting']['list_tpl'][key($row['setting']['list_tpl'])] = 'index';

            return $row;
    }
}
