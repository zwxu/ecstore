(function(){
  var dialogTemplete,dialogTempleteWithFrame;
  var getDialogTemplete = function(frame){
    if(!frame){
      if(undefined!=dialogTemplete){
        return dialogTemplete;
      }
      //dialogTemplete=getTplById('dialog');
      var html=[];
	  html.push('<div class="dialog-box" style="display:block">');
      html.push('  <div class="dialog-head clearfix">');
      html.push('    <div class="dialog-title flt" style="background: -moz-linear-gradient(center top , white, #E0E3EC) repeat scroll 0 0 #F0F0F0;"></div>');
      html.push('    <a class="btn-close frt" href=\'javascript:void(0)\'>&#10006;</a>');
      html.push('  </div>');
      html.push('  <div class="dialog-content-head" style="font-size:0;height:0;"></div>');
      html.push('  <div container="true" class="dialog-content-body">');
      html.push('    <{t}>正在加载...<{/t}>');
      html.push('  </div>');
      html.push('  <div class="dialog-content-foot" style="font-size:0;height:0;"></div>');
      html.push('  <div class="btn-resize"></div>');
      html.push('</div>');
	  dialogTemplete=html.join('');
      return dialogTemplete;
    }
    if(undefined!=dialogTempleteWithFrame){
      return dialogTempleteWithFrame;
    }
	var html=[];
	html.push('<div class="dialog-box" style="display:block">');
    html.push('    <div class="dialog-head clearfix">');
    html.push('      <div class="dialog-title flt"></div>');
    html.push('      <a class="btn-close frt" href=\'javascript:void(0)\'>&#10006;</a>');
    html.push('    </div>');
    html.push('    <div class="dialog-content-head" style="font-size:0;height:0;overflow:hidden"></div>');
	html.push('<iframe frameborder="0" src="about:blank" container="true" class="dialog-content-body dialog-frame-body" name="dialog-frame">');
        	
	html.push('      </iframe>');
	html.push('        <div class="dialog-content-foot" style="font-size:0;height:0;overflow:hidden"></div>');
	html.push('        <div class="btn-resize">&nbsp;</div>');
	html.push('      </div>');
    dialogTempleteWithFrame = html.join('');
    return dialogTempleteWithFrame;
  };
  var getTplById = function(tplId,tplframe){
    tplframe = tplframe||'tplframe';
    var frameDoc = $(tplframe).contentWindow.document,_html = frameDoc.getElementById(tplId).value;
    if(!!_html){return _html;}

    return false;
  };
  //Dialog scpoe : window
  Dialog=new Class({
    Implements:[Options,Events],
    options:{
      onShow:$empty,//显示时的事件
      onHide:$empty,//关闭时的事件注册
      onClose:$empty,//关闭时的事件注册
      onLoad:$empty,
      callback:false,
      iframe:false,
      ajaksable:true,
      width:700,/*窗口宽度*/
      height:400,/*窗口高度*/
      dialogBoxWidth:2,
      title:'',/*窗口标题*/
      dragable:true,/*是否允许拖拽*/
      resizeable:true,/*是否允许改变尺寸*/
      singlon:true,/*是否仅允许单独实例*/
      modal:true,/*是否在弹出时候其他区域不可操作*/
      ajaxoptions: {/*ajax请求参数对象*/
        update:false,
        evalScripts: true,
        method: 'get',
        autoCancel:true,
        render:true
      }
    },
    initialize:function(url,options){
      var currentRegionDialogs=this.currentRegionDialogs=$$('.dialog');
      if(currentRegionDialogs.some(function(item,idx){
       if(item.retrieve('serial')==url.toString().trim()&&item.style.display!='none'){
          item.inject(document.body);
          return true;
       }
      }))return;
      this.url=url;
      this.setOptions(options);
      options=this.options;
      var _dialogTemplete=getDialogTemplete(this.options.iframe);
      this.dialog = new Element('div',{id:'dialog_'+this.UID,'class':'dialog dialog_div','styles':{'visibility':'hidden','zoom':1,'opacity':0,'zIndex':65534,'border':'none'}});
      this.UID = Slick.uidOf(this.dialog);
      this.dialog.set('id','dialog_'+this.UID).setHTML(_dialogTemplete).inject(document.body).store('serial',url.toString().trim());
      if(this.options.callback){
        this.dialog.store('callback',this.options.callback);
      }
      this.dialog_head=$E('.dialog-head',this.dialog).addEvent('click',function(e){
        if($$('.dialog').length>1)
        this.inject(document.body);
      }.bind(this.dialog));
      this.dialog_body=$E('.dialog-content-body',this.dialog);
      this.setTitle(options.title);
      $E('.btn-close',this.dialog_head).addEvents({'click':function(e){
        if(e)
        e=new Event(e).stop();
        this.close();
      }.bind(this),'mousedown':function(e){new Event(e).stop();}});
      if(options.dragable){
        this.dragDialog();
      }
      if (options.resizeable) {
        this.dialog_body.makeResizable({
          handle: $E('.btn-resize',this.dialog),
          limit: {
            x: [200,window.getSize().x*0.9],
            y:[100,Math.max(window.getSize().y,window.getScrollSize().y)]
          },
          onDrag: function(){
            this.setDialogWidth();
          }.bind(this)
        });
      }else{
        $E('.btn-resize',this.dialog).hide();
      }
      if(!options.ajaksable){
        options.ajaxoptions.render = false;
      }
      $extend(options.ajaxoptions,{
        update:this.dialog_body,
        sponsor:false,
        resizeupdate:false,
        evalScripts:false,
        onRequest:function(){
          //this.setDialog_bodySize();
        }.bind(this),
        onFailure:function(){
          this.close();
          Message.error('对话框加载失败');
        }.bind(this),
        onComplete:function(){
          this.fireEvent('onComplete',$splat(arguments));
          this.showDialog.attempt(arguments,this);
        }.bind(this)
      });
      this.popup(url,options);
    },
    popup:function(url,options){
      this.fireEvent('onShow',this);
      this.initContent(url,options);
    },
    initContent:function(url,options,isreload){
      url = url || this.url;
      options=options||this.options;
      var _this=this,dataform;
      if(!isreload){
        var ic=arguments.callee;
        this.reload=function(){
          ic(url,options,true);
        };
      }
      if(options.iframe){
        new Message('正在加载...',{type:'notice'});
        if(options.ajaxoptions.data){
          var data = options.ajaxoptions.data;
          switch (typeOf(data)){
            case 'element': data = document.id(data).toQueryString(); break;
            case 'object': case 'hash': data = Object.toQueryString(data);
          }
          var dataform = new Element('form');
          dataform.adopt(data.toFormElements());
          dataform.set({
            'id':'abbcc',
            'action':url,
            'method':options.ajaxoptions.method,
            'target':this.dialog_body.name
          });
          dataform.injectAfter(this.dialog_body);
          return this.dialog_body.set('src','about:blank').addEvent('load',function(){
            _this.showDialog.call(_this,this);
            new Message('正在加载...',{autohide:true});
            this.removeEvent('load',arguments.callee);
            dataform.submit();
          });
        }
        return this.dialog_body.set('src',url).addEvent('load',function(){
          _this.showDialog.call(_this,this);
           new Message('加载完成...',{autohide:true});
          this.removeEvent('load',arguments.callee);
        });
      }
      if($type(url)=='element'){
        try{
          this.dialog_body.empty().adopt(url);
        }catch(e){
          this.dialog_body.setHTML('内容加载失败!');
        }
        if(!isreload){
          this.showDialog.call(this);
        }
         return;
      }
      W.page(url,options.ajaxoptions);
    },
    showDialog:function(re,xml,js){
      var closebtn=$ES('[isCloseDialogBtn]',this.dialog);
      if(closebtn.length){
           closebtn.addEvent('click',this.close.bind(this));
      }
      var _form=$E('form[isCloseDialog]',this.dialog); //form finder refresh
      _form && _form.store('target',{onComplete:function(rs){
        if(!rs)return;
        var json={};
        try{json = JSON.decode(rs);}catch(e){}
        var finderId=json.finder_id, error=json.error;
        if(error)return;
        this.close();
        if(finderId && finderGroup[finderId])
        finderGroup[finderId].refresh();
      }.bind(this)});
      this.dialog.store('instance',this);
      this.setDialog_bodySize();
      var crd=this.currentRegionDialogs;
      var crd_length=crd?crd.length:0;
      if(crd_length&&crd_length>0){
        this.dialog.position($H(crd[crd_length-1].getPosition()).map(function(v){
          return v+=20;
        })).setOpacity(1);
      }else{
        this.dialog.amongTo(window);
      }
      if(this.options.modal){MODALPANEL.show();}
      $exec(js);
      this.fireEvent('onLoad',this);
    },
    close:function(){
      try{
        this.fireEvent('onClose',this.dialog);
      }catch(e){}
      // this.dialog.style.display='none';
      $(this.dialog).destroy();
      $('dialogdragghost_'+this.UID)?$('dialogdragghost_'+this.UID).destroy():'';
      if(this.currentRegionDialogs.length>0)return false;
      if(this.options.modal){MODALPANEL.hide();}
      return 'nodialog';
    },
    hide:function(){
      this.fireEvent('onHide');
      this.close.call(this);
    },
    setDialog_bodySize:function(){
      this.options.height = $type(this.options.height)=='string'?this.options.height.toInt():this.options.height;
      this.options.width = $type(this.options.width)=='string'?this.options.width.toInt():this.options.width;
      var _npS =  this.dialog.getElement('.dialog-content-head').getSize().y+
        this.dialog.getElement('.dialog-content-foot').getSize().y;
      this.dialog_body.setStyles({
        'height':(this.options.height<1)?(this.options.height*window.getSize().y-_npS):this.options.height-_npS,
        'width':(this.options.width<1)?(this.options.width*window.getSize().x):this.options.width
      });
      this.setDialogWidth();
    },
    setDialogWidth:function(){
      this.dialog.setStyle('width',this.dialog_body.getSize().x+this.dialog.getElement('.dialog-box').getPatch().x);
    },
    setTitle:function(titleHtml){
      var head = this.dialog_head;
      if (titleHtml === false) {
        head.destroy();
      }
      else $E('.dialog-title',head).set('html',titleHtml);
    },
    dragDialog:function(){
      var dialog=this.dialog;
      var dragGhost=new Element('div',{'id':'dialogdragghost_'+this.UID});
      dragGhost.setStyles({
        'position':'absolute',
        'cursor':'move',
        'background':'#66CCFF',
        'display':'none',
        'opacity':0.3,
        'zIndex':65535
      }).inject(document.body);
      this.addEvent('load',function(e){
        dragGhost.setStyles(dialog.getCis());
      });
      new Drag(dragGhost,{
        'handle':this.dialog_head,
        'limit':{'x':[0,window.getSize().x],'y':[0,window.getSize().y]},
        'onStart':function(){
          dragGhost.setStyles(dialog.getCis());
          dragGhost.show();
        },
        'onComplete':function(){
          var pos=dragGhost.getPosition();
          dialog.setStyles({
            'top': pos.y,
            'left':pos.x
          });
          dragGhost.hide();
        }
      });
    }
  });
})();
