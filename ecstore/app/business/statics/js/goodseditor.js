Array.prototype.S=String.fromCharCode(2);
Array.prototype.in_array=function(e){
  var r=new RegExp(this.S+e+this.S);
  return (r.test(this.S+this.join(this.S)+this.S));
}; 
var ShopExGoodsEditor = new Class({
  Implements:[Options],
  options: {
    periodical: false,
    delay: 500,
    postvar:'finderItems',
    varname:'items',
    width:500,
    height:400
  },
  initialize: function(el, options){
    this.el = $(el);
    this.setOptions(options);
    this.cat_id = $('gEditor-GCat-input').get('value');
    this.type_id = $('gEditor-GType-input').get('value');
    this.goods_id = $('gEditor-GId-input').get('value');
    this.initEditorBody.call(this);
  },
  initEditorBody:function(){
    var _this=this;
    var gcatSelect=$('gEditor-GCat-input');
    var gtypeSelect=$('gEditor-GType-input');
    gtypeSelect.addEvent('click',function(){
      this.store('tempvalue',this.get('value'));
    });
    gtypeSelect.addEvent('change',function(e){
      var tmpTypeValue = this.retrieve('tempvalue');
      var info = '\t是否根据所选分类的默认类型重新设定商品类型？\n\n如果重设，可能丢失当前所输入的类型属性、关联品牌、参数表等类型相关数据。';
      if(this.get('value')){
        //Message.show('正在加载规格...',false);
        MODALPANEL.show();
        _this.updateEditorBody.call(_this);
        _this.type_id=this.get('value');
      }else{
        //this.getElement('option[value='+tmpTypeValue+']').set('selected',true);
      }
    });
  },
  updateEditorBody:function(options){
    try{
      if($('productNode')&&$('productNode').retrieve('specOBJ')){
        $('productNode').appendChild($('productNode').retrieve('specOBJ').toHideInput($('productNode').getElement('tr')));
      }
    }catch(e){}
    new Request.HTML({
      url:'business-goods_update.html',
      method:'post',
      update:'gEditor-ginfo',
      //secure:false,
      data:$('gEditor-Body'),
      onComplete:function(p1,p2,re,js){
        
      }
    }).send();
    return false;
  },
  mprice:function(e){
      for(var dom=$(e).getParent(); dom.tagName!='TR';dom=dom.getParent()){;}
      var info = {};
      dom.getElements('input').each(function(el){
          if(el.name == 'price[]')
              info['price']=el.value;
          else if(el.name == 'goods[product][0][price]')
              info['price']=el.value;
          else if(el.getAttribute('level'))
              info['level['+el.getAttribute('level')+']']=el.value;
      });
      window.fbox = new Dialog('business-set_mprice.html',{title:LANG_goodseditor['editvipprice'], ajaxoptions:{data:info,method:'post'},modal:true});
      window.fbox.onSelect = goodsEditor.setMprice.bind({base:goodsEditor,'el':dom});
  },
  setMprice:function(arr){
      var parr={};
      arr.each(function(p){
          parr[p.name] = p.value;
      });
      this.el.getElements('input').each(function(d){
          var level = d.getAttribute('level');
          if(level && parr[level]!=undefined){
              d.value = parr[level];
          }
      });
  },
  adj:{
      addGrp:function(s){
          this.dialog = new Dialog('business-addGrp.html?goods_id='+this.goods_id+'&_form='+(s?s:'goods-adj'), {title:LANG_goodseditor['widget']});
      }
  },
  pic:{
      del:function(obj){
        if(obj.get('ident') && $('typeDetailView')){
          var obj_input = $('typeDetailView').getElements('.spec_goods_images')||[];
          for(var i=0;i<obj_input.length;i++){
            if(obj_input[i].value.trim().split(',').in_array(obj.get('ident'))){
              Message.error('该图片已经关联规格，请取消关联关系，再删除');
              return false;
            }
          }
        }
          if(confirm(LANG_goodseditor['comfirmDelPic'])){
              var input = $ES('input[name^=img['+obj.get('ident')+']','goods_spec_images')||'';
              if(input) input.destroy();
              obj = $(obj);
              var pic_box=obj.getParent('.gpic-box'),picNext=pic_box.getNext('.gpic-box');
              try{
              if(obj.get('ident')){
                     if($$('#all-pics input[name=image_default]')[0])
                     $$('#all-pics input[name=image_default]')[0].value=obj.get('ident');
                     $('all-pics').eliminate('cururl');
                     pic_box.destroy();
                 /*    if($$('#all-pics .gpic-box .current')[0])return;
                     if($$('#all-pics .gpic-box').length&&$$('#all-pics .gpic-box').length>0){
                       $('all-pics').empty().set('html','<div class="notice" style="margin:0 auto">请重新选择默认商品图片.</div>');
                     }else{
                       $('all-pics').empty().set('html','<div class="notice" style="margin:0 auto">您还未上传商品图片.</div>');
                     }   */
              }}catch(e){
                 pic_box.destroy();
              }
              if(picNext)picNext.getElement('.gpic').onclick();
          }
      },
      setDefault:function(id,obj){
          var pic_main = $(obj).getParent('.pic-main');
          var area = pic_main.getElement('.pic-area');
          var target=$E('.gpic[image_id='+id+']',area);
          //var target=$$('#pic-area .gpic[image_id='+id+']')[0];
              if(target.hasClass('current')){return;}
              var cur,imgdefinput;
              if(cur = $E('.current',area)){
                   cur.removeClass('current');
               }
              if(imgdefinput = $E('input[name=image_default]',area)){
                 imgdefinput.set('value',id);
              }
          target.addClass('current');
      },
      getDefault:function(){
          var pic_main = obj.getParent('.pic-main');
          var area = pic_main.getElement('.pic-area');
          var o = $E('input[name=image_default]',area);
          //var o = $$('#pic-area input[name=image_default]')[0];

          if(o){
            return o.value;
          }else{
            return false;
          }
      },
      viewSource:function(act){
         return new Dialog(act,{title:LANG_goodseditor['viewSource'],singlon:false,'width':650,'height':300});
      }
  },
  rateGoods:{
      add:function(){
          window.fbox = new Dialog('index.php?ctl=goods/product&act=select',{modal:true,ajaxoptions:{data:{onfinish:'goodsEditor.rateGoods.insert(data)'},method:'post'}});
      },
      del:function(){
      },
      insert:function(data){
          $$('div.rate-goods').each(function(e){
              data['has['+e.getAttribute('goods_id')+']'] = 1;
          });
          new Request({url:'index.php?ctl=goods/product&act=ratelist',data:data,onComplete:function(s){$('x-rate-goods').innerHTML+=s;}}).send();
      }
  }
});

function setPos(){
  $$('.pic-uploader').each(function(el){
    $$('.std-upload-txt')[0].setStyle('top',el.getPosition().y+$('main').getScroll().y);
  });
}
var goodsEditor = null;
var goodsEditFrame = (function(op){
  //setPos();
  goodsEditor = new ShopExGoodsEditor('main',{});
  Ex_Loader('uploader',function(){
    $$('.pic-uploader').each(function(el,j){
      if (document.getElements('.swiff-uploader-box')) {
        document.getElements('.swiff-uploader-box').destroy();
      }
      var pic_main = el.getParent('.pic-main');
      var area = pic_main.getElement('.pic-area');
      var pics = pic_main.getElement('.all-pics');
      new Swiff.Uploader({
        allowDuplicates: true,
        verbose: true,
        url:op.url,
        path: op.path,
        typeFilter: {
          'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png'
        },
        fileSizeMax:op.IMAGE_MAX_SIZE,
        target:el,
        onSelect:function(rs){
          if(rs)
          rs.each(function(v){
              if(v.size>this.fileSizeMax){
                Message.error('文件超出大小');
              };
            },this);
        },
        onSelectFail:function(rs){
          rs.each(function(v){
            if(v.validationError=='sizeLimitMax'){
              Message.error(v.name+'文件超出大小');
            };
          });
        },
        onSelectSuccess:function(rs){
          var PID='up_';
          var _this=this;
          rs.each(function(v,i){
            new Element('div',{'class':'gpic-box','id':PID+j+v.id}).inject(pics);
          });
          this.start();
        },
        onFileOpen:function(e){
          $('up_'+j+e.id).setHTML('<em style="font-size:13px;font-family:Georgia;">0%</em>');
        },
        onFileProgress:function(e){
          $('up_'+j+e.id).getElement('em').set('text',(e.progress.percentLoaded>100?100:e.progress.percentLoaded)+'%');
        },
        onFileComplete: function(res){
          if(res.response.error){
            $('up_'+j+res.id).destroy();
            return Message.error('文件'+res.name+'上传失败');
          }else if(res.response.text == 1){
            $('up_'+j+res.id).destroy();
            return Message.error('上传图片尺寸不是'+op.imgsize+'以上的正方形图片或上传图片大小超过1M！');
          }else if(res.response.text == 2){
            $('up_'+j+res.id).destroy();
            return Message.error('上传图片空间已满，请与管理员联系！');
          }
          $('up_'+j+res.id).setHTML(res.response.text);
          if(!$E('.current',area)&&$E('.gpic',area)){
            $E('.gpic',area).onclick();
          }
        }
      });
    });
  });
  var _form=$('main'),_formActionURL=_form.get('action');
  var subGoodsForm = function (event,sign){
    var specOBJ='';
    var _target=$(new Event(event).target);
    if($('productNode')&&$('productNode').retrieve('specOBJ')){
      if(!$('productNode').retrieve('specOBJ').data.length){
        return Message.error('请先添加货品!!!');
      }
      specOBJ=$('productNode').retrieve('specOBJ').toHideInput($('productNode').getElement('tr'));
    }
    var target={extraData:$('finder-tag').toQueryString()+'&'+specOBJ,onRequest:function(){_target.disabled = true;}};
    $extend(target,{
      onComplete:function(rs){
        if(rs&&!!JSON.decode(rs).success){
          if(window.opener.finderGroup&&window.opener.finderGroup['<{$env.get.finder_id}>']){
            window.opener.finderGroup['<{$env.get.finder_id}>'].refresh();
          }
          window.close();
        }
        _target.disabled = false;
      }
    });
    _form.store('target',target);
    _form.set('action',_formActionURL+'&but='+sign).fireEvent('submit',new Event(event));
  }
  var clearOldValue=function(){
    $('id_gname').set('value','');
    $('gEditor-GId-input').set('value','');
    if($$('.product_id').length)
    $$('.product_id').each(function(el){
      el.value='';
    });
  }
});