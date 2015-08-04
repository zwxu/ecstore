(function(){
   Fx.Slide = new Class({
	Extends: Fx,
	options: {
		mode: 'vertical',
		wrapper: false,
		hideOverflow: true,
		resetHeight: false
	},

	initialize: function(element, options){
		element = this.element = this.subject = document.id(element);
		this.parent(options);
		options = this.options;

		var wrapper = element.retrieve('wrapper'),
			styles = element.getStyles('margin', 'position', 'overflow');

		if (options.hideOverflow) styles = Object.append(styles, {overflow: 'hidden'});
		if (options.wrapper) wrapper = document.id(options.wrapper).setStyles(styles);

		if (!wrapper) wrapper = new Element('div', {
			styles: styles
		}).wraps(element);

		element.store('wrapper', wrapper).setStyle('margin', 0);
		if (element.getStyle('overflow') == 'visible') element.setStyle('overflow', 'hidden');

		this.now = [];
		this.open = true;
		this.wrapper = wrapper;

		this.addEvent('complete', function(){
			this.open = (wrapper['offset' + this.layout.capitalize()] != 0);
			if (this.open && this.options.resetHeight) wrapper.setStyle('height', '');
		}, true);
	},

	vertical: function(){
		this.margin = 'margin-top';
		this.layout = 'height';
		this.offset = this.element.offsetHeight;
	},

	horizontal: function(){
		this.margin = 'margin-left';
		this.layout = 'width';
		this.offset = this.element.offsetWidth;
	},

	set: function(now){
		this.element.setStyle(this.margin, now[0]);
		this.wrapper.setStyle(this.layout, now[1]);
		return this;
	},

	compute: function(from, to, delta){
		return [0, 1].map(function(i){
			return Fx.compute(from[i], to[i], delta);
		});
	},

	start: function(how, mode){
		if (!this.check(how, mode)) return this;
		this[mode || this.options.mode]();

		var margin = this.element.getStyle(this.margin).toInt(),
			layout = this.wrapper.getStyle(this.layout).toInt(),
			caseIn = [[margin, layout], [0, this.offset]],
			caseOut = [[margin, layout], [-this.offset, 0]],
			start;

		switch (how){
			case 'in': start = caseIn; break;
			case 'out': start = caseOut; break;
			case 'toggle': start = (layout == 0) ? caseIn : caseOut;
		}
		return this.parent(start[0], start[1]);
	},

	slideIn: function(mode){
		return this.start('in', mode);
	},

	slideOut: function(mode){
		return this.start('out', mode);
	},

	hide: function(mode){
		this[mode || this.options.mode]();
		this.open = false;
		return this.set([-this.offset, 0]);
	},

	show: function(mode){
		this[mode || this.options.mode]();
		this.open = true;
		return this.set([0, this.offset]);
	},

	toggle: function(mode){
		return this.start('toggle', mode);
	}

});
   
CatSelect=new Object();
Object.append(CatSelect,{
    main:$('main'),
    activeIndex:0,
    from:0,
    to:0,
    showE:3,
    move:15,
    width:225,
    topName:'',
    AJAX_URL:"business-goods_import_cat.html",
    AJAX_URL_TYPE:"business-goods_import_type.html",
    AJAX_PAGE_URL:"business-goods_import_ajax.html",
    AJAX_IMPORT_URL:"business-goods_export.html",
    AJAX_EXPORT_DETAIL_URL:"business-goods_export_detail.html",
    AJAX_IMPORT_CSV_URL:"business-goods_import_csv.html",
    init:function(){
        this.Step1=new Fx.Slide('j_cate_container');
        this.Step2=new Fx.Slide('j_cate_container_2');
        this.catList=$('J_OlCascadingList');
        this.btn_next=$('J_LinkNext');
        this.btn_prve=$('J_LinkPrev');
        this.CatExtend();
        this.subCat(this.catList.getFirst());
        this.searchModel(this.catList.getFirst());
        this.btn_next.addEvent('click',function(e){e.stop();this.next();}.bind(this));
        this.btn_prve.addEvent('click',function(e){e.stop();this.prve();}.bind(this));
        $('btn_next_step').addEvent('click',function(event){
          event.stop();
          CatSelect.nextStep(this);
        });
        $('btn_submit').hide();
        this.Step2.slideOut();
    },
    nextStep:function(obj){
         if($('selectCat').value==''){
              Message.error('请选择类目');
              return;
          }
          if(obj.get('next') && obj.get('next')=='true'){
               this.loading();
              $type_result=CatSelect.main.retrieve('type_'+$('selectCat').value);
              if($type_result){
                  $('j_cate_container_2').set('html',$type_result);
                  obj.getElement('.btn-title').set('html','我需要重新选择类目，返回上一步');
                  obj.set('next','false');
                  $('J_SpanPointer').toggleClass('arrow up');
                  this.Step1.slideOut();
                  this.Step2.slideIn();
                  this.loading();
                  $('btn_submit').show();
                  
                  $('btn_submit').removeProperty('disabled');
                  return;
              }
              new Request({
                       url:CatSelect.AJAX_URL_TYPE,
                       method:'POST',
                       data:{cat_id:$('selectCat').value},
                       onFailure:function(xhr){CatSelect.Failure(xhr);},
                       onSuccess:function(re){
                       if(CatSelect.request_error(re))return;
                          $('j_cate_container_2').set('html',re);
                          CatSelect.main.store('type_'+$('selectCat').value,re);
                          obj.getElement('.btn-title').set('html','我需要重新选择类目，返回上一步');
                          obj.set('next','false');
                          $('J_SpanPointer').toggleClass('arrow up');
                          this.Step1.slideOut();
                          this.Step2.slideIn();
                          this.loading();
                          $('btn_submit').show().removeProperty('disabled');
                       }.bind(this)
                   }).send();
          }else{
              obj.getElement('.btn-title').set('html','我确定选择该类目，下一步');
              obj.set('next','true');
              $('J_SpanPointer').toggleClass('arrow up');
              this.Step1.slideIn();
              this.Step2.slideOut();
              $('btn_submit').hide();
          }
    },
    alertInfo:function(lastCat){
        if(lastCat){
            $('J_GuidWrap').getElement('.hint-content').set('html','亲，您已经选择了最终类目请进行下一步操作！');
        }else{                
            $('J_GuidWrap').getElement('.hint-content').set('html','亲，再往下选择试试 宝贝是挂在最终分类的！');
        }
    },
    getLength:function(){
        return this.catList.getElements('.cc-list-item').length;
    },
    searchModel:function(el){
      var idiv=el.getFirst();
      var cdiv=el.getLast();
       var input= idiv.getElement('input');
       input.addEvent('keyup',function(e){
            var self=this;
            if(self.value!=''){
               self.getPrevious().hide();
               cdiv.addClass('search-mode');
            }else{
                self.getPrevious().show();
                cdiv.removeClass('search-mode');
            }
            var catlist= el.getElements('.cc-tree-item');
            var count=catlist.length;
            catlist.map(function(cl){
                if(self.value!=''){
                    if(cl.get('text').test(self.value)){
                       cl.show();
                    }else{
                       cl.hide();
                       count--;
                    }
                }else{cl.show();}
            });
            self.setStyle('width',count>13 ? '176px':'192px' )
            
       });
    },
    next:function(){
        var length=this.getLength();
        var activeIndex=this.activeIndex;
        var maxindex=length-this.showE;
        if(length<=this.showE){
          this.activeIndex=0;
          this.btn_next.setStyle('visibility','hidden');
          this.btn_prve.setStyle('visibility','hidden');
          this.switchTo(activeIndex,0);
          return;
        }
        this.activeIndex=this.activeIndex>=maxindex ? maxindex:this.activeIndex+1;
        if(this.activeIndex>=maxindex){
            this.btn_next.setStyle('visibility','hidden');
        }
        this.btn_prve.setStyle('visibility','visible');
        this.switchTo(activeIndex,this.activeIndex);
    },
    prve:function(){
        var length=this.getLength();
        if(length<=this.showE){
            return;
        }
        var activeIndex=this.activeIndex;            
        this.activeIndex=this.activeIndex<=0?0:this.activeIndex-1;
        if(this.activeIndex<=0){
           this.btn_prve.setStyle('visibility','hidden');
        }
        this.btn_next.setStyle('visibility','visible');
        this.switchTo(activeIndex,this.activeIndex);
    },
    switchTo:function(from,to){
        if(from==to)return;
        this.from=-1*this.width*from;
        this.to=-1*this.width*to;
        this.catList.tween('left',this.from,this.to);
    },
    CatExtend:function(){
     var topcats=this.main.getElements('.cc-tree-gname');
     topcats.addEvent('click',function(e){
       e.stop();
       this.toggleClass('cc-focused');
       this.getParent('.cc-tree-group').toggleClass('cc-tree-expanded');
     });
    },
    loading:function(show){
        this.main.getElement('.cate-main').toggleClass('cate-loading');
    },
    subCat:function(li){
      var items=li.getElements('.cc-tree-item');
        items.each(function(el){
           el.addEvent('click',function(e){
               e.stop();
               var self=this;
               items.removeClass('cc-selected cc-focused');
               items.set('hasSelect',false);
               var nextLi=this.getParent('.cc-list-item').getAllNext();
               if(nextLi){nextLi.destroy();}
               this.addClass('cc-selected cc-focused');
               this.set('hasSelect',true);
               if(this.getParent('.cc-tree-group')){
                   CatSelect.topName=this.getParent('.cc-tree-group').getFirst().get('html');
               }
               if(el.hasClass('cc-hasChild-item')){//存在子节点。
                   CatSelect.showSelect(false,this);
                   CatSelect.alertInfo(false);
                   CatSelect.loading(true);
                   var store_cat=CatSelect.main.retrieve('subCat_'+this.get('cat_id'));
                   if(store_cat){
                     CatSelect.formatLi(store_cat);
                     return;
                   }
                   new Request({
                       url:CatSelect.AJAX_URL,
                       method:'POST',
                       data:{cat_id:this.get('cat_id')},
                       onFailure:function(xhr){CatSelect.Failure(xhr);},
                       onSuccess:function(re){
                          if(CatSelect.request_error(re))return;
                          if(re.length<=0){CatSelect.loading(false); return;}
                          CatSelect.main.store('subCat_'+self.get('cat_id'),re);                            
                          CatSelect.formatLi(re);
                       }
                   }).send();
               }else{
                   CatSelect.alertInfo(true);
                   CatSelect.next();
                   CatSelect.showSelect(true,this);
               }
           });
        });
    },
    showSelect:function(islast,obj){
       var cat_name=[CatSelect.topName];
       var selectList= this.main.getElements('li[hasSelect="true"]');
       var path=['<li>'+CatSelect.topName+'</li>'];
       selectList.each(function(el){
            path.push('<li>&nbsp;&gt;&nbsp;'+el.get('text')+'</li>');
            cat_name.push(el.get('text'));
            
       });
       if(islast==false){
           path.push('<li>&nbsp;&gt;&nbsp;...</li>');
       }
       $('selectCat').value=islast ? obj.get('cat_id'):'';
       $('J_OlCatePath').empty().set('html',path.join(''));
       $('selectCatName').value=cat_name.join('>');
    },
    formatLi:function(re){
         if(CatSelect.request_error(re))return;
         var li_cat= new Element('li').addClass('cc-list-item').set('html',re);
         li_cat.inject(this.catList); 
         this.subCat(li_cat);
         this.searchModel(li_cat);
         this.loading(false);
         this.next();
    },
    request_error:function( res){
        if(res==''){
            Message.error('系统返回错误，请重新登录或者联系管理员');
            return true;
         }else{
            if(res.test('<div class="head">')){
                location.href=import_login;//Message.error('需要重新登录.<a href="javascript:void(0);" onclick="location.reload();">点此重新登录</a>');
                return true;
            }
         }
         return false;
    },
    Failure:function(xhr){
        switch (xhr.status) {
        case 404:
          Message.error('页面末找到');
          break;
        case 401:
          Message.error('需要重新登录.<a href="javascript:void(0);" onclick="location.reload();">点此重新登录</a>');
          break;
        case 403:
          Message.show('需要重新登录.<a href="javascript:void(0);" onclick="location.reload();">点此重新登录</a>');
          break;
        default:
      }
    },
    download:function(obj,tpl_id){
        this.showDetail(obj,tpl_id,this.AJAX_EXPORT_DETAIL_URL);
    },
    import_csv:function(obj,tpl_id){        
        this.showDetail(obj,tpl_id,this.AJAX_IMPORT_CSV_URL);
    },
    showDetail:function(obj,tpl_id,r_url){
       var obj=$(obj);
        var crow=obj.getParent('tr');
        new Request({
           url:r_url,
           method:'POST',
           data:{tpl_id:tpl_id},
           onFailure:function(xhr){CatSelect.Failure(xhr);},
           onSuccess:function(re){
              if(CatSelect.request_error(re))return;
                var tbody=$(obj).getParent('tbody');
                var finder=tbody.getElement('.finder-detail');
                if(finder)finder.destroy();
              var newrow=new Element('tr');
                newrow.addClass('finder-detail');
                var td=new Element('td').set('colspan','4');
                td.set('html',re);
                td.inject(newrow);
                newrow.inject(crow, 'after');
           }.bind(this)
        }).send();
    },
    pagerAddEvent:function (){
        if($('import_tpl').getElement('.pager')){
              var pager=$('import_tpl').getElement('.pager').getElements('a');
               pager.each(function(el){
                  el.addEvent('click',function(event){
                      event.stop();
                      new Wpage({url:this.get('href')});
                      new Request({url:this.get('href'),method:'POST',
                        onFailure:function(xhr){CatSelect.Failure(xhr);},
                       onSuccess:function(re){
                         if(CatSelect.request_error(re))return;
                          $('import_tpl').set('html',re);
                          CatSelect.pagerAddEvent();
                       }.bind(this)
                    }).send();
                  });
               });
        }
    },
    importfile:function(obj){    
        //$(obj).set('disabled',true);
        var vl=$('file_csv').value;
        if(vl==''){
           Message.error('请选择要上传的CSV文件');
           //$(obj).removeProperty('disabled');
           return;
        }
        var ext=vl.split('.').getLast();
        if(ext!='csv'&&ext!='CSV'){
             Message.error('请选择CSV文件');
             //$(obj).removeProperty('disabled');
             return;
        }
        var _form=$('import_csv_form');
        $(_form).store('target',{
             onComplete:function(res){
                 if(res.test('<div class="head">')){
                        location.href=import_login;
                        return true;
                  }
                $('import_result').set('html',res);
             }
        });
      this.submitForm(_form);
    },
    submitForm:function(_form){
        if($(_form).retrieve('submiting'))return;
        $('uploadframe').addEvent('load',function() {
        if (_form && Slick.uidOf(_form)) $(_form).eliminate('submiting');
          var doc = this.contentWindow.document;
          var response = doc.body[(doc.body.innerHTML ?'innerHTML':(doc.body.innerText ? 'innerText': 'textContent'))];
          var targetobj = $(_form).retrieve('target',{});
          $(_form).eliminate('target');
          if (('onComplete' in targetobj) && 'function' == $type(targetobj.onComplete)) {
            try {
              targetobj.onComplete(response);
            } catch(e) {}
          }
          $('uploadframe').removeEvent('load', arguments.callee).set('src', $('uploadframe').retrieve('default:src'));
        }).store('default:src', $('uploadframe').src);
        _form.store('submiting', true).submit();
    }
});

CatSelect.pagerAddEvent();

$('btn_export_csv').addEvent('click',function(e){
    new Request({
       url:CatSelect.AJAX_IMPORT_URL,
       method:'POST',
       onFailure:function(xhr){CatSelect.Failure(xhr);},
       onSuccess:function(re){
          if(CatSelect.request_error(re))return;
          $('import_tpl').hide();
          $('export_tpl').set('html',re).show();
          CatSelect.init();
       }.bind(this)
    }).send();     
});
$('btn_import_list').addEvent('click',function(e){
    new Request({
       url:CatSelect.AJAX_PAGE_URL,
       method:'POST',
       onFailure:function(xhr){CatSelect.Failure(xhr);},
       onSuccess:function(re){
          if(CatSelect.request_error(re))return;
          $('import_tpl').set('html',re).show();
          $('export_tpl').hide();
          CatSelect.pagerAddEvent();
       }.bind(this)
    }).send();
});

})();