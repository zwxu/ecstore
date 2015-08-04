(function(){
    var ModeDialog=this.ModeDialog=new Class({
        Implements:[Options,Events],
        options: {
            /*handle:null,
            onLoad:$empty,
            onShow:$empty,
            onHide:$empty,
            onCallback:$empty,*/
            params:{
                /*url:null,
                name:null,
                type:null*/
            },
            title:'title',
            width:800,
            height:600,
            resizable:true
        },
        initialize:function(url,options){
            if(!url) return;
            var explorer = window.navigator.userAgent ;
            this.setOptions(options);
            options=this.options;
            options.resizable = options.resizable?'yes':'no';
            if(explorer.indexOf("Chrome") >= 0){
        		this.url = url+"&params_url="+options.params.url+"&params_type="+options.params.type+"&params_name="+options.params.name+"&params_app="+options.params.app+"&params_postdata="+options.params.postdata+"&handle="+options.handle;
        	}else{
        		this.url = url;
        	}
            if(options.handle)this.handle=$(options.handle);
            var config="dialogWidth={width}px;dialogHeight={height}px;resizable={resizable};status=no;";
            config=config.substitute(options);
            this.fireEvent('init');
            if(explorer.indexOf("Chrome") >= 0){
            	window.open(this.url,this,"height="+options.height+",width="+options.width+",top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable="+options.resizable+",location=no, status=no");
        	}else{
        		this.returnValue=window.showModalDialog(this.url,this,config);
        	}
            //this.returnValue=window.showModalDialog(this.url,this,config);
            //window.open(this.url,this,"height="+options.height+",width="+options.width+",top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable="+options.resizable+",location=no, status=no");
            if(this.isSubmit)this.onClose.call(this,this.returnValue);
        },
        onLoad:function(win){
            this.win=win;
            this.doc=this.win.document;
            this.fireEvent('load',[this,this.options.pname,this.options.params]);
            this.onShow.call(this);
        },
        //submit:function(){},
        onShow:function(){
            this.fireEvent('show');
            if(this.doc.getElement('.dialogBtn'))
            this.doc.getElement('.dialogBtn').addEvent('click',function(e){
                try{
                    this.submit.call(this,this.win);
                }catch(e){}
                this.win.close();
                this.isSubmit=true;
            }.bind(this));
        },
        onClose:function(returnValue){
            this.fireEvent('hide',[returnValue]);
        }
    });

    finderDialog=new Class({
        Extends:ModeDialog,
        options:{
            onLoad:function(){
                if(!$(this.handle))return;
                var data=$(this.handle).getParent().getElement('input[type=hidden]').value;
                if(!data)return;
                var form=this.doc.getElement('form[id^=finder-form-]');
                form.store('rowselected',data.split(','));
                this.win.fireEvent('resize');
                var fid=form.id.slice(-6),finder;
        //      if(finder=this.win.finderGroup[fid])finder.refresh();
            },
            onHide:function(value){
                if(!value||!value.length)return;
                var tmpForm=new Element('div'),fdoc=document.createDocumentFragment();
                var params=this.options.params;
                for(var i=0,l=value.length;i<l;i++){
                    fdoc.appendChild(new Element('input',{type:'hidden','name':params.name,value:value[i]}));
                }
                tmpForm.appendChild(fdoc);
                var data=(params.postdata)?tmpForm.toQueryString()+'&'+params.postdata:tmpForm.toQueryString();
                data = this.filterData?data+'&filter[advance]='+this.filterData:data;
                new Request({url:params.url,onSuccess:function(rs){
                     tmpForm.destroy();
                     if(params.type)this.options.select(params,rs,value);
                     this.fireEvent('callback',rs);
                }.bind(this)}).send(data);
            },
            select:function(options,rs,data){
                if(options.type=='radio'){
                    if(JSON.decode(rs)){
                        $(this.handle).tagName==='INPUT'?$(this.handle).value=JSON.decode(rs).name:$(this.handle).setText(JSON.decode(rs).name);
                    }
                }else if(options.type=='checkbox'){
                    $(this.handle).innerHTML=rs;
                }
                $(this.handle).getParent().getElement('input[type=hidden]').value=data;
            }
        },
        submit:function(win){
            var form=this.doc.getElement('form[id^=finder-form-]');
            var value=form.retrieve('rowselected','');
            var data = decodeURI(form.getElement('input[id^=finder-filter-]').value);
            var params = this.options.params;
            if(value=='_ALL_'){
            	var value1 = new Array();
            	this.doc.getElement('id=main').getElements('tr').each(function(item){
                	var item_id = item.getAttribute("item-id");
                	value1.push(item_id);
                });
                value = value1.join(",");
            }
            if(params.app&&data&&value=='_ALL_') {
                data = data.replace(/&amp;/g,',');
                data = data.replace(/&/g,',');
                data = encodeURIComponent(data);
                this.filterData = data;
            }

            if(!value || !value.length) return;
            win.returnValue=value.toString().split(',');
        }
    });

    imgDialog=new Class({
        Extends:ModeDialog,
        options:{
            onCallback:function(image_id,image_src){
                 var image_input_panel=$(this.handle).getParent('.image-input');
                 var _hidden = image_input_panel.getElement('input');
                 var _img = image_input_panel.getElement('img');
                 _img.removeProperties('width','height');
                 _hidden.value = image_id;
                 _img.src= image_src;
            }
        },
        imgcallback:function(image_id,image_src){
            this.isSubmit=true;
            this.fireEvent('callback',[image_id,image_src]);
            this.win.close();
        }
    });
})();
