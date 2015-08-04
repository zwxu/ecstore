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
    catalogSelect:function(typeid,id){
        typeid=typeid||1;
        var gtypeSelect=$('gEditor-GType-input');
        if(typeid!=gtypeSelect.get('value')){
            if(confirm(LANG_goodseditor['comfirm'])||id<0){
                gtypeSelect.getElement('option[value='+typeid+']').set('selected',true);
                this.updateEditorBody.call(this);
            }
        }
        this.cat_id = id;
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
            if(this.get('value')&&confirm(LANG_goodseditor['comfirm'])){
                    _this.updateEditorBody.call(_this);
                    _this.type_id=this.get('value');
            }else{
                this.getElement('option[value='+tmpTypeValue+']').set('selected',true);
           }
        });
    },
    updateEditorBody:function(options){
        try{
        if($('productNode')&&$('productNode').retrieve('specOBJ')){
            $('productNode').appendChild($('productNode').retrieve('specOBJ').toHideInput($('productNode').getElement('tr')));
        }
        }catch(e){}
       var parma={
            method:'post',
            data:$('gEditor').toQueryString(),
            url:'index.php?app=b2c&ctl=admin_goods_editor&act=update',
            evalScripts:false,
            onComplete:function(res){
                /** 替换相应的节点 **/
                var updateMap = {
                    '#menu-desktop' : $('menu-desktop'),
                    '#gEditor-Body' : $('gEditor-Body')
                };
                //console.log(res);
                res = res.replace(/<\!-{5}(.*?)-{5}([\s\S]*?)-{5}(.*?)-{5}>/g,
                function() {
                    var $k = arguments[1];
                    $k = updateMap[$k] || $($k);
                    var $v = arguments[2] || null;

                    if ($v && $k) {
                        $k.empty().set('html', $v).fixEmpty();
                    }
                    return '';
                });

                var scripts = '';
                this.response.text.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(all, code){
                    scripts += code + '\n';
                    return '';
                });
                Browser.exec(scripts);
                goodsEditFrame();
            }
        };
       new Request(parma).send();
    //   W.page('index.php?app=b2c&ctl=admin_goods_editor&act=update',parma);
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
        window.fbox = new Dialog('index.php?app=b2c&ctl=admin_goods_editor&act=set_mprice',{title:LANG_goodseditor['editvipprice'], ajaxoptions:{data:info,method:'post'},modal:true});
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
    spec:{
        addCol:function(s,typeid){
            this.dialog = new Dialog('index.php?app=b2c&ctl=admin_products&act=set_spec&_form='+(s?s:'goods-spec')+'&p[0]='+typeid,{ajaxoptions:{data:$('goods-spec').toQueryString()+($('nospec_body')?'&'+$('nospec_body').toQueryString():''),method:'post'},title:LANG_goodseditor['type']});
        },
        addRow:function(){
            this.dialog = new Dialog('index.php?app=b2c&ctl=admin_goods_editor/spec&act=addRow',{ajaxoptions:{data:$('goods-spec'),method:'post'}});
        }
    },
    adj:{
        addGrp:function(s){
            this.dialog = new Dialog('index.php?app=b2c&ctl=admin_goods_editor&act=addGrp&goods_id='+this.goods_id+'&_form='+(s?s:'goods-adj'), {title:LANG_goodseditor['widget']});
        }
    },
    pic:{
        del:function(obj){
            if(confirm(LANG_goodseditor['comfirmDelPic'])){
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


var CatalogSelect=new Class({
    Implements: [Events, Options],
    options: {
        /*onLoad:function(){},
        onShow:function(){},
        onHide:function(){},*/
        updateMain:"catalog-x",
        url:'index.php?app=b2c&ctl=admin_goods_cat&act=get_subcat_list',
        childClass:'.subs',
        params:'p[0]'
    },
    initialize:function(handle,options){
        if(!handle)return;
        this.handle=$(handle);
        this.setOptions(options);
        var option=this.options;
        this.url=option.url;
        this.updateMain=$(option.updateMain);
        if(!this.updateMain||!this.url)return;
        this.cacheHS={};
        this.load.call(this);
    },
    load:function(){
        this.request('0');
    },
    attach:function(){
        var _this=this;
        this.updateMain.getElements(this.options.childClass).addEvent('click',function(e){
            var id=this.get('id')||this.get('pid');
            if(this.hasClass('cat-no-child')){
                _this.callback('',this.get('text'));
                return  document.body.fireEvent('click',e);
            }
            return _this.isCache(id);
        });
        this.updateMain.getElements('.cat-child').addEvent('click',function(e){
            var _handle=this.getParent('*[type_id]');
            if(!_handle)return;

            _this.callback(_handle.id,this.get('text'),_handle.get('type_id'));
            document.body.fireEvent('click',e);
        });
    },
    isCache:function(id){
        this.cacheHS.id ? this.updateMain.innerHTML=this.getCache(id):this.request(id);
        this.fireEvent('show').attach.call(this);
    },
    request:function(){
        var _this=this;
        var params=Array.flatten(arguments);
        var p=params.link({'options':Object.type,'id':String.type});
        p.options=Object.append(p.options||{},{url:this.url,data:this.options.params+'='+p.id,method:'get',
        onComplete:function(rs){
            _this.updateMain.innerHTML=rs;
            _this.setCache(p.id,rs).attach();
            _this.fireEvent('show');
        }});
        new Request(p.options).send();
        return this;
    },
    callback:function(id,text,typeid){
        var handle=this.handle.getElement('.label').set('text',text);
        if(this.handle.getElement('input[type=hidden]'))
        this.handle.getElement('input[type=hidden]').value=id;
        this.fireEvent('callback',[id,typeid,text]);
    },
    setCache:function(k,v){
        if(this.cacheHS[k] == undefined) this.cacheHS[k] = v;
        return this;
    },
    getCache:function(k){
        return this.cacheHS[k];
    }
});
