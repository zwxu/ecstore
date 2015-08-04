Element.extend({
    amongTo:function(elp,opts){
        var el=this;
        var elSize=el.getSize(),
            elpSize=elp.getSize();
        var options={width:2,height:2};

        if(opts){options=Object.merge(options,opts);}

        el.setStyle('position','absolute');

        var pos={
            'top':Math.abs(((elpSize.size.y / options.height).toInt())-((elSize.size.y / options.height).toInt())+elp.getPosition().y+elpSize.scroll.y),
            'left':Math.abs(((elpSize.size.x / options.width).toInt())-((elSize.size.x / options.width).toInt())+elp.getPosition().x+elpSize.scroll.x)
        };
        el.setStyles(pos);

        if(el.getStyle('opacity')<1)el.setOpacity(1);
        if(el.getStyle('visibility')!='visible')el.setStyle('visibility','visible');
        if(el.getStyle('display')=='none')el.setStyle('display','');
        return this;
    },
    zoomImg:function(maxwidth,maxheight,v){
       if(this.getTag()!='img'||!this.width)return;
       var thisSize={'width':this.width,'height':this.height}, zommC;
           if (thisSize.width>maxwidth){
              zommC=(maxwidth/thisSize.width).toFloat();
              var zoomSizeH=(thisSize.height*zommC).toInt();
              Object.append(thisSize,{'width':maxwidth,'height':zoomSizeH});
           }
           if (thisSize.height>maxheight){
              zommC=(maxheight/thisSize.height).toFloat();
              var zoomSizeW=(thisSize.width*zommC).toInt();
              Object.append(thisSize,{'width':zoomSizeW,'height':maxheight});
           }
       if(!v)return this.set(thisSize);
       if($type(v)=='function'){
            this.set(thisSize);
            return v.apply(this,[maxwidth,maxheight,thisSize]);
        }
       return thisSize;
    },
    subText:function(count){
        var txt=this.get('text');
        if(!count||txt.length<=count)return txt;
        this.setText(txt.substring(0,count)+"...");
        if(!this.retrieve('tip:title'))
        this.set('title',txt);
        return txt;
    },
    getValues:function(){
        var values = {};
        this.getFormElements().each(function(el){
            var name = el.name;
            var value = el.getValue();
            if (value === false || !name || el.disabled) return;
            values[el.name] = value;
        });
        return values;
    },
    getCis:function(){
        return this.getCoordinates(arguments[0]);
    },
    getContainer:function(){
        return this.getParent("*[container='true']")||$('main')||document.body;
    },
    show:function(display){
          this.fireEvent('show',this);
          return this?this.setStyle('display', display ? display : ''):this;
    },
    hide:function(){
        this.fireEvent('hide',this);
        return this?this.setStyle('display','none'):this;
    },
    isDisplay:function(){
        if('none'==this.style.display)return false;
        if('hidden'==this.style.visibility)return false;
        if((this.offsetWidth+this.offsetHeight)===0){return false;}
        return true;
    },

    toggleDisplay:function(){
        return this&&this.getStyle('display')=='none'?this.setStyle('display',''):this.setStyle('display','none');
    },

    getFormElementsPlus:function(ft){
        var elements=[];
        var nofilterEls=$$(this.getElements('input'), this.getElements('select'), this.getElements('textarea'));
        if(ft){
            nofilterEls=nofilterEls.filter(ft);
        }
        nofilterEls.each(function(el){
            var name = el.name;
            var value = el.getValue();
            if(!name||!value)return;
            if(el.getProperty('type')=='checkbox'||el.getProperty('type')=='radio'){
                if(!!el.getProperty('checked')) return elements.include($(el).toHiddenInput());
                return;
            }
            elements.include(el);
        });
        return $$(elements);
    },
    toHiddenInput:function(){
        return  new Element('input',{'type':'hidden','name':this.name,'value':this.value});
    },
    fixEmpty:function(){
         if(this.get('html').trim()===''||this.get('html')=='&nbsp;'){
            return this.setStyle('font-size',0);
         }
         if(this.style.height.toInt() === 0){this.setStyle('height','');}
         return this.setStyle('font-size','');
    },
    getSelectedRange: function() {
        if (!Browser.Engine.trident) return {start: this.selectionStart, end: this.selectionEnd};
        var pos = {start: 0, end: 0};
        var range = this.getDocument().selection.createRange();
        if (!range || range.parentElement() != this) return pos;
        var dup = range.duplicate();
        if (this.type == 'text') {
            pos.start = 0 - dup.moveStart('character', -100000);
            pos.end = pos.start + range.text.length;
        } else {
            var value = this.value;
            var offset = value.length - value.match(/[\n\r]*$/)[0].length;
            dup.moveToElementText(this);
            dup.setEndPoint('StartToEnd', range);
            pos.end = offset - dup.text.length;
            dup.setEndPoint('StartToStart', range);
            pos.start = offset - dup.text.length;
        }
        return pos;
      },
      selectRange: function(start, end) {
        if (Browser.Engine.trident) {
            var diff = this.value.substr(start, end - start).replace(/\r/g, '').length;
            start = this.value.substr(0, start).replace(/\r/g, '').length;
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', start + diff);
            range.moveStart('character', start);
            range.select();
        } else {
            this.focus();
            this.setSelectionRange(start, end);
        }
        return this;
    }
});


String.extend({
    format:function(){
        if(arguments.length === 0) return this;
        var reg = /{(\d+)?}/g;
        var args = arguments;
        var string=this;
        var result = this.replace(reg,function($0, $1) {
            return  args[$1.toInt()]||"";
        });
        return result;
    },
    toFormElements:function(){
        if(!this.contains('=')&&!this.contains('&'))return new Element('input',{type:'hidden'});
        var elements=[];

        var queryStringHash=this.split('&');

        Array.from(queryStringHash).each(function(item){

            if(item.contains('=')){
                item=item.split('=');

                elements.push(new Element('input',{type:'hidden',name:item[0],value:decodeURIComponent(item[1])}));
            }else{
              elements.push(new Element('input',{type:'hidden',name:item}));
            }
        });
        return new Elements(elements);
    },

    getLength:function(charAt){
        var str = this;
        len = 0;
        for(i=0;i<str.length;i++){
            iCode = str.charCodeAt(i);
            if((iCode>=0 && iCode<=255)||(iCode>=0xff61 && iCode<=0xff9f)){
                len += 1;
            }else{
                len += charAt||3;
            }
        }
        return len;
    }
});

/*checkbox划选
  @params scope checkbox所在容器
  @params match 从容器去取得所有checkbox的selector
*/
Element.implement({
    easyCheck: function(match,fn){
        attachEsayCheck(this,match,fn&&fn.call(this));
    }
});
var attachEsayCheck=function(scope,match,callback){
    callback=callback||function(){};
    scope=$(scope);
    if(!scope)return;
    var checks=scope.getElements(match);

    if(!checks.length)return;

    var targetRoot;
    scope.addEvents({
      'mousedown':function(e){
          scope.store('eventState',e.type);
          targetRoot=false;
      },
      'mouseup':function(e){
          scope.eliminate('eventState');
      },
      'mouseleave':function(){
          scope.eliminate('eventState');
      }
    });
    checks.addEvent('mouseover',function(){
        if(scope.retrieve('eventState')!='mousedown')return;
        var _target= this.match('input')?this:this.getElement('input');

        if(!_target||_target.get('disabled'))return;
        if(!targetRoot){
            targetRoot=_target.set('checked',!_target.get('checked')).fireEvent('change');
            callback(targetRoot);
            return;
        }
        _target.set('checked',targetRoot.get('checked')).fireEvent('change');
        callback(_target);
    });
};

var ItemAgg = new Class({
        Implements: [Events,Options],
        options:{
           // onActive:function(){},
           // onBackground:function(){},
           show:0,
           eventName:'click',
           activeName:'cur',
           itemsClass:null,
           firstShow:true
        },
        initialize: function(tabs, items, options){
            if(!tabs.length||!items.length)return;
            this.setOptions(options);
            this.tabs=$$(tabs);
            this.items=$$(items);
            this.curIndex=this.options.show||0;
            this.attach();
            if(this.options.firstShow) this.show(this.curIndex);
        },
        attach:function(){
            this.tabs.each(function(item,index){
                this.items[index].hide();
                item.addEvent(this.options.eventName,function(e){
                    if(this.curIndex==index||!this.items[index])return;
                    this.show(index);
                    this.hide(this.curIndex);
                    this.curIndex=index;
                }.bind(this));
            },this);
        },
        show:function(index){
            this.items[index].show();
            if(this.options.itemsClass)
            this.items[index].addClass(this.options.itemsClass);
            this.tabs[index].addClass(this.options.activeName);
            this.fireEvent('active',[this.tabs[index],this.items[index],index],this);
        },
        hide:function(index){
            $(this.items[index]).hide();
            if(this.options.itemsClass)
            $(this.items[index]).removeClass(this.options.itemsClass);
            $(this.tabs[index]).removeClass(this.options.activeName);
            this.fireEvent('background',[this.tabs[index],this.items[index],index],this);
        }
});

var _open = function(url,options){
    options = options || {};
    if(options.width&&options.width<=1){options.width = window.getSize().x*options.width; }
    if(options.height&&options.height<=1){options.height = window.getSize().y*options.height;}
    options = Object.append({
        width: !!options.maxmize ? screen.availWidth-10 : window.getSize().x * 0.8,
        height: !!options.maxmize ? screen.availHeight-25 : window.getSize().y * 0.9,
        left:0,
        top:0,
        scrollbars: 'yes',
        resizable: 'yes'
    }, options);
    var params = 'toolbar=no,location=no,status=no,menubar=no,scrollbars={scrollbars},resizable={resizable},top={top},left={left},width={width},height={height}';
    params = params.substitute(options);

    window.open(url || 'about:blank', '_blank', params);
};

getTplById = function(tplId,tplframe){
    tplframe = tplframe||'tplframe';
    var frameDoc = $(tplframe).contentWindow.document,_html = frameDoc.getElementById(tplId).value;
    if(!!_html){return _html;}

    return false;
};

var LazyLoad=new Class({
    Implements:[Options,Events],
    options:{
        img:'img-lazyload',                    //存图象地址的属性
        textarea:'textarea-lazyload',          //textarea的class
        lazyDataType:'textarea',            //延时类型
        execScript:true,                   //是否执行脚本
        islazyload:true,                   //是否执行延时操作
        lazyEventType:'beforeSwitch'       //要接触延时的事件
    },
    initialize:function(options){
        this.setOptions(options);
    },
    loadCustomLazyData: function(containers, type) {
        var area, imgs,area_cls=this.options.textarea,img_data=this.options.img;
        if(!this.options.islazyload)return;
        $splat(containers).each(function(container){
            switch (type) {
                case 'img':
                    imgs=container.nodeName === 'IMG'?[container]:$ES('img',container);
                    imgs.each(function(img){
                        this.loadImgSrc(img, img_data);
                    },this);
                    break;
                default:
                    area=$E('textarea',container);
                    if(area && area.hasClass(area_cls))
                    this.loadAreaData(container, area);
                    break;
            }
        },this);
    },
    loadImgSrc: function(img, flag) {
        flag = flag || this.options.img;
        var dataSrc = img.getProperty(flag);
        img.removeProperty(flag);
        if (dataSrc && img.src != dataSrc) {
            new Asset.image(dataSrc,{onload:function(image){
                img.set('src',dataSrc);
            },onerror:function(){
                if(window.ie && this.options.IE_show_alt){
                    new Element('span',{'class':'error-img','text':img.alt||img.title}).inject(img,'after');
                    img.remove();
                }
            }.bind(this)});
        }
    },
    loadAreaData: function(container,area) {
          area.setStyle('display','none').className='';
          //var content = new Element('div').inject(area,'before');
          this.stripScripts(area.value,container);
    },
    isAllDone:function(){
        var type=this.options.lazyDataType,flag=this.options[type],
            elems, i, len, isImgSrc = type === 'img';
        if (type) {
            elems = $ES(type,this.container);
            for (i = 0, len = elems.length; i < len; i++) {
                if (isImgSrc ?elems[i].get(flag): elems[i].hasClass(flag)) return false;
            }
        }
        return true;
    },
    stripScripts: function(v,container){
        var scripts = '';
        var text = v.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(){
            scripts += arguments[1] + '\n';
            return '';
        });
        container.innerHTML=text;
        if(this.options.execScript) $exec(scripts);
    },
    _lazyloadInit:function(panel){
        var loadLazyData=function(){
            var containers=$type(panel)=='function'?panel(arguments):panel;
            this.loadCustomLazyData(containers,this.options.lazyDataType);
            if (this.isAllDone()) {
                this.removeEvent(this.options.lazyEventType,arguments.callee);
            }
        };
        this.addEvent(this.options.lazyEventType,loadLazyData.bind(this));
    }
});



window.setTab = function(c,a){
    var cur=c[0],el=c[1],cur_cls=a[0],el_cls=a[1];

    var url = $('_'+cur).getAttribute('url');
    if(url && !$(cur).getAttribute('url')){
        W.page(url,{update:cur});
        $(cur).setAttribute('url',url);
    }
    $(cur).style.display='';

    /*切换tab时显示标示tab的pop*/
    var notePops = $(cur).getElements('[data-note-pop]');
    if($('_'+cur).get('data-note-pop')){
        notePops.include($('_'+cur));
    }
    if(notePops.length){
        notePops.get('data-note-pop').each(NOTEPOP.active.bind(NOTEPOP));
    }
    $('_'+cur).addClass(cur_cls);
    el.each(function(e){
        if(e!=cur){
            $(e).style.display='none';
            $('_'+e).removeClass(cur_cls);
            /*切换tab时隐藏标示tab的pop*/
            var notePops = $(e).getElements('[data-note-pop]');
            if($('_'+e).get('data-note-pop')){
                notePops.include($('_'+e));
            }
            if(notePops.length){
                notePops.get('data-note-pop').each(NOTEPOP.hide.bind(NOTEPOP));
            }
        }
    });
};


function selectArea(sel,path,depth){
    sel=$(sel);
    if(!sel)return;
    var sel_value=sel.value;
    var sel_panel=sel.getParent();
    var selNext=sel.getNext();
    var areaPanel= sel.getParent('*[package]');
    var hid=areaPanel.getElement('input[type=hidden]');
    var curOption=$(sel.options[sel.selectedIndex]);

    var setHidden=function(sel){
        var rst=[];
        var sel_break = true;

        if (curOption && !curOption.get('has_c')){
            /** 删除多余的三级地区 **/
            var _currChliSpan = sel.getNext('.x-region-child');
            if (_currChliSpan){
                _currChliSpan.destroy();
            }
            /** end **/
        }

        var sels=$ES('select',areaPanel);
        sels.each(function(s){
          if(s.getValue()!= '_NULL_' && sel_break){
              rst.push($(s.options[s.selectedIndex]).get('text'));
          }else{
            sel_break = false;
          }
        });
        if(sel.value != '_NULL_'){
            $E('input',areaPanel).value = areaPanel.get('package')+':'+rst.join('/')+':'+sel.value;
        }else{
            $E('input',areaPanel).value =function(sel){
              var s=sels.indexOf(sel)-1;
              if(s>=0){
                 return areaPanel.get('package')+':'+rst.join('/')+':'+sels[s].value;
              }
              return '';
            }(sel);
        }

    };
    if(sel_value=='_NULL_'&&selNext&&(selNext.getTag()=='span' && selNext.hasClass('x-areaSelect'))){
        sel.nextSibling.empty();
        setHidden(sel);
    }else{
        /*nextDepth*/
        if(curOption.get('has_c')){
          new Request({
                url:'index.php?app=ectools&ctl=tools&act=selRegion&path='+path+'&depth='+depth,
                onSuccess:function(response){
                    var e;
                    if(selNext && (selNext.getTag()=='span'&& selNext.hasClass('x-region-child'))){
                        e = selNext;
                    }else{
                        e = new Element('span',{'class':'x-region-child'}).inject(sel_panel);
                    }
                    setHidden(sel);
                    if(response){
                        e.set('html',response);
                        if(hid){
                           hid.retrieve('sel'+depth,function(){})();
                           hid.retrieve('onsuc',function(){})();
                        }
                    }else{
                        sel.getAllNext().remove();
                        setHidden(sel);
                        hid.retrieve('lastsel',function(){})(sel);
                    }
                }
            }).get();
            if($('shipping')){
                $('shipping').setText('');
            }
        }else{
            sel.getAllNext().remove();
            setHidden(sel);
            if(!curOption.get('has_c')&&curOption.value!='_NULL_')
            hid.retrieve('lastsel',function(){})(sel);
        }
    }
}

Hotkey ={
    keyStr:['shiftKey','ctrlKey','altKey'],
    init:function(event,keyobj){
        if(keyobj.length)
        keyobj.each(function(c,t){
            if(!!c['keycode'].every(function(key){
                return (this.keyStr.contains(key+'Key')) ? !!event['event'][key+'Key'] : event.key==key;
            },this)){
                event.stop();
                this.keyfn(c['type'],c['arg'],c['options']);
            }
        },this);
    },
    keyfn:function(type,url,config){
        var row,el;
        switch (type){
            case 'cmd':
                return Ex_Loader('cmdrunner',function(){
                    new cmdrunner(url,config).run();
                });
                break;
            case 'dialog':
                new Dialog(url,config);
                break;
            case 'showDetail':
            case 'refresh':
                for(var cf in finderGroup)
                if(finderGroup[cf]) finderGroup[cf][type](url,{},config);
                break;
            case 'close':
                if($E('.dialog')) $E('.dialog').retrieve('instance').close();
                break;
            case 'event':
                el =($(config)||document).getElement(url);
                if(el)el.fireEvent('click',{stop:$empty});
                break;
            case 'detail':
                var detail;
                if((detail = $E('.view-detail')))
                if((row = detail[url]('.row'))){
                    var href= row.getElement('.btn-detail-open').get('detail');
                    arguments.callee('showDetail',href,row);
                }
                break;
            case 'tabs':
                var tab;
                if((row = $E('.finder-detail .current')))
                if((tab= row[url]('.tab'))){
                    el =tab.getElement('a');
                    W.page(el.href,JSON.decode(el.target));
                }
                break;
            default:
                W.page(url,config);
                break;
        }
    }
};

function WidgetImg(el){
    var target=$(el).getParent().getElement('.imageSrc'),
        url='index.php?app=desktop&act=alertpages&goto='+encodeURIComponent("index.php?app=image&ctl=admin_manage&act=image_broswer&type=big");
    Ex_Loader("modedialog",function(){
        return new imgDialog(url,{onCallback:function(image_id,image_src){
                target.value=image_src;
        }});
    });
}


(function() {
    var log, history, con = window.console;
    window.log = log = function() {
        history.push(arguments);
        con ? con.log[ con.firebug ? 'apply' : 'call'](con, Array.prototype.slice.call(arguments)) : alert(Array.prototype.slice.call(arguments).join('\n'));
    };
    log.history = history = [];
})();

Element.Events.enter = {
    base: 'keyup',
    condition: function(event){
        return (event.key == 'enter');
    }
};



//pop
var NotePop = new Class({
    Implements:[Events,Options],
    options:{
        map:null, //用户定义泡泡列表
        popList:{},//已经实例化的泡泡
        padding:{
            x:20,
            y:5
        },//泡泡与目标地的间距
        pageWidth :window.getSize().x, //总区域宽
        pageHeight :window.getSize().y //总区域高
    },
    initialize : function(options){
        this.setOptions(options);
    },
    attach : function(v,k){
        var ele = document.getElement(v.ele);
        if(ele){
            var parentEle = document.body;
            var eleBox = ele.getSize();
            ele.set('data-note-pop',k);
            if(ele.getParent('#main')){
                parentEle = $('main');
            }
            var elePosition = ele.getPosition(parentEle);
            var tmpPop = new Element('div',{
                    'class':'note-pop',
                    'html':'<div class="pop-content">'+ v.html + '</div><div class="close-box"><span class="close">'+LANG_Jstools.knew+'</span></div>',
                    'style':'display:none'
                    });
            tmpPop.inject(parentEle);
            this.options.popList[k] = {};
            if(v.url){
                this.options.popList[k].url = v.url;
            }
            if(v.ar == false){
                this.options.popList[k].ar = false;
            }else{
                var tmpAr = new Element('span',{'class':'pop-ar','html':'<span></span>'});
                this.options.popList[k].ar = tmpAr;
            }
            var popSize = tmpPop.measure(function(){
                    return this.getSize();
            });
            this.options.popList[k].pop = tmpPop;            //实例化泡泡
            this.options.popList[k].popWidth = popSize.x;    //泡泡宽度
            this.options.popList[k].popHeight = popSize.y;   //泡泡高度
            this.options.popList[k].target = ele;            //泡泡目标元素
            this.options.popList[k].targetWidth = eleBox.x;  //泡泡目标元素宽度
            this.options.popList[k].targetHeight = eleBox.y; //泡泡目标元素高度
            this.options.popList[k].targetX = elePosition.x; //泡泡目标元素position x
            this.options.popList[k].targetY = elePosition.y; //泡泡目标元素position y
            this.popPosition(this.options.popList[k]);
            this.options.popList[k].pop.getElement('.close').addEvent('click',function(){
                    this.hide(k);
            }.bind(this));
        }
    },
    loop: function(list){
        Object.each(list,function(v,k){
            this.attach(v,k);
        }.bind(this));
    },
    checkUrl:function(url){
        if(url == window.location.href){
            return true;
        }else{
            return false;
        }
    },
    popPosition:function(popObj){
        var endX = 0;
        var endY = 0;
        if(popObj.popHeight >= popObj.targetY){
            endY = popObj.targetY + popObj.targetHeight + this.options.padding.y; //pop在下方
            if(popObj.ar){
                popObj.ar.addClass('top');
            }
        }else{
            endY = popObj.targetY - popObj.popHeight - this.options.padding.y; //pop在上方
            if(popObj.ar){
                popObj.ar.addClass('bottom');
            }
        }
        if(popObj.popWidth + popObj.targetX > this.options.pageWidth){
            endX =this.options.pageWidth - popObj.popWidth - this.options.padding.x; //避免右边超出page
        }else{
            endX =popObj.targetX - this.options.padding.x;
        }
        if(endX < 0){
            endX = 0; //避免左边超出page
        }
        popObj.popX = endX;
        popObj.popY = endY;
        if(popObj.ar){
            popObj.ar.setStyle('left',popObj.targetX - endX + (popObj.targetWidth * 0.382));
            popObj.ar.inject(popObj.pop);
        }
    },
    show:function(popObj){
        if(!popObj.url || this.checkUrl(popObj.url)){
            popObj.pop.setStyles({
                'display':'',
                'left':popObj.popX,
                'top':popObj.popY
            });
        }
    },
    hide:function(name){
        this.options.popList[name].pop.setStyles({
            'display':'none'
        });
    },
    active:function(name){
        if(name){
            this.show(this.options.popList[name]);
        }else{
            Object.each(this.options.popList,function(v,k){
                this.show(v);
            }.bind(this));
        }
    },
    clear:function(name){
          var objLength = Object.getLength(this.options.popList);
          if(objLength > 0){
              if(name){
				  try{
					  this.options.popList[name].pop.destroy();
					  delete this.options.popList[name];
				  }catch(e){}
              }else{
				  try{
					  Object.values(this.options.popList).each(function(item){
						  item.pop.destroy();
					  });
					  this.options.popList = {};
				  }catch(e){}
              }
          }
    },
    create: function(map){
        this.clear();
        this.options.map = map;
        this.loop(map);
    }
});
