<?php
class business_api_verify_store
{

    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct()
    {
        $this->app =  app::get("business");
        $this->b2c =  app::get("b2c");
    }


    public function verifyStore($store_cert){
        $store_cert=trim($store_cert);

        if($store_cert){
            $arycert=$this->app->model('storemanger')->getList('store_id',array('store_cert'=>$store_cert));
            if($arycert){
              $store_id=$arycert[0]['store_id'];
              $arystore= $this->app->model('storemanger')->getList('company_no,company_taxno,company_codename,store_idcard',array('store_id'=>$store_id));
              if($arystore){
                 if(base_certificate::gen_sign($arystore[0])==$store_cert){
                        return true;
                 }
              }

            }
        }
        return false;
    }

     public function checkStore($data,&$ermsg){
        $store_cert=trim($data['store_cert']);

        if (!isset($data['list_quantity']) || !$data['list_quantity'])
        {
            return false;
        } else {
            $arr_store = json_decode($data['list_quantity'], true);
        }

        if( $store_cert){
            if($this->verifyStore($store_cert)){

                $arycert=$this->app->model('storemanger')->getList('store_id',array('store_cert'=>$store_cert));

                if($arycert){
                      $store_id=$arycert[0]['store_id'];
                      $product = app::get("b2c")->model('products');
                      $fail_products = array();
                      $has_error = false;

                      foreach ($arr_store as $arr_product_info)
                      {
                        if($arr_product_info['bn'])
                        {
                            $arystore=$product->getstoreidbyproductbn($arr_product_info['bn']);

                            if($arystore){
                                $prdStoreid=$arystore[0]['store_id'];
                                if($store_id==$prdStoreid){

                                }else {
                                    $has_error = true;
                                    $fail_products[] = $arr_product_info['bn'];
                                    continue;
                                }
                            }else {
                                $has_error = true;
                                $fail_products[] = $arr_product_info['bn'];
                                continue;
                            }

                        }else {
                            $has_error = true;
                            continue;
                        }
                      }


                      if (!$has_error)
                            return true;
                      else
                      {

                          $ermsg=app::get('b2c')->_('此货品不是该店铺商品！').json_encode($fail_products);
                      }

                }
            }else{
                   $ermsg=app::get('b2c')->_('店铺校验失败！');
            }
        }else{
            $ermsg=app::get('b2c')->_('参数错误！');
        }

        return false;
    }

}