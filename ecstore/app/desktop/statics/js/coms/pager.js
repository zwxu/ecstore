var Pager= new Class({
    Implements:[Options,Events],
    options: {
        onShow:$empty,
        onHide:$empty,
        format:function(json){
            return  {tag_id:json['tagId']||"",tag_name:json['tagName']||"",type:json['type']||""};
        },
        pageNum:10,
        current:1,
        curClass:'current',
        pageClass:'pagernum',
        nextBtn:'next',
        preBtn:'prev',
        pageMainClass:'pager'
    },
    initialize: function(tpl,data,options){
        if(!tpl)return;
        this.data=data||[];
        this.setOptions(options);
        this.tpl=tpl;
        this.initFormat();
        this.total=this.getTotal();
        this.goPage(this.options.current);
        this.preCur=0;
    },
    initFormat:function(){
        this.data.each(function(e,i){
            this.options.format.call(this,this.data[i]);
        },this);
    },
    getTotal:function(n){
        return Math.ceil(n||this.data.length/this.options.pageNum);
    },
    updateContent:function(from,to){
        var content=[],data=this.data,curdata=[];
        if(!to||!from){return $(this.options.updateMain).empty();}
        var l=to+1;
        if(to<=data.length){
            for(var i=from;i<l;i++){
                curdata.push(data[i-1]);
                content.push(this.tpl.substitute(data[i-1]));
            }
        }
        this.curdata=curdata;
        $(this.options.updateMain).empty();
        $(this.options.updateMain).set('html',content.join(''));
        this.updatePageList.call(this);
        return this.fireEvent('show',this.data);
    },
    pageLink:function(from,to){
        var links=[];
        for(var i=from,l=to+1;i<l;i++){
            this.options.current==i?links.push('<span class="'+this.options.curClass+'">'+i+'</span>'):links.push('<a class="'+this.options.pageClass+'" href="javascript:void(0)">'+i+'</a>');
        }
        return links.join(' ');
    },
    bindLink:function(){
        var links=[],t=this.total,c=this.options.current;
        if(t<11){links.push(this.pageLink(1,t));
        }else{
            if(t-c<8){
                links.push(this.pageLink(1,3));
                links.push(this.pageLink(t-8,t));
            }else if(c<10){
                links.push(this.pageLink(1,Math.max(c+3,10)));
                links.push(this.pageLink(t-1,t));
            }else{
                links.push(this.pageLink(1,3));
                links.push(this.pageLink(c-2,c+3));
                links.push(this.pageLink(t-1,t));
            }
        }
        return links.join('...');
    },
    updatePageList:function(){
        var pagelist=$E('.'+this.options.pageMainClass)||new Element('div',{'class':''+this.options.pageMainClass+''});
        if(this.total<=1)return pagelist.destroy();
        var main=$(this.options.updateMain).tagName=='TBODY'?$(this.options.updateMain).getParent():$(this.options.updateMain);
        if(main.hasClass('gridlist') && !pagelist.getParent('.gridlist-footer')) pagelist.addClass('gridlist-footer');
        pagelist.inject(main,'after');
        pagelist.empty();
        pagelist.innerHTML=this.prePage.call(this)+this.bindLink.call(this)+this.nextPage.call(this);
        return this.attach.call(this);
    },
    attach:function(){
        var _this=this;
        $ES('a.'+this.options.pageClass,'.'+this.options.pageMainClass).addEvent('click',function(e){
                _this.goPage(this.get('text').toInt());
        });
        if($E('a.'+this.options.nextBtn))$E('.'+this.options.nextBtn).addEvent('click',function(e){
                this.goPage(this.options.current+1);
        }.bind(this));
        if($E('a.'+this.options.preBtn))$E('.'+this.options.preBtn).addEvent('click',function(e){
                this.goPage(this.options.current-1);
        }.bind(this));
    },
    goPage:function(i){
        var to=i*this.options.pageNum;
        var form=to-this.options.pageNum+1;
        to=to<this.data.length?to:this.data.length;
        this.fireEvent('hide',this.options.current);
        this.preCur=this.options.current;
        this.options.current=i;
        this.updateContent.apply(this,[form,to]);
    },
    nextPage:function(){
        return this.total>this.options.current?'<a href="javascript:void(0)" class="'+this.options.nextBtn+'" title='+LANG_Pager['nextpage']+'>'+LANG_Pager['nextpage']+'&gt;</a>':'&nbsp;';
    },
    prePage:function(){
        return this.options.current>1?'<a href="javascript:void(0)" class="'+this.options.preBtn+'" title='+LANG_Pager['prevpage']+'>&lt;'+LANG_Pager['prevpage']+'</a>':'&nbsp;';
    }
});

var PageData=new Class({
    Extends:Pager,
    options:{
        PRIMARY_ID:'product_id'
    },
    initialize:function(tpl,data,options){
        this.parent(tpl,data,options);

        this.PRIMARY_ID=this.options.PRIMARY_ID;

        this.lastId=this.data.getLast()?this.data.getLast()[this.PRIMARY_ID]:0;

    },
    editData:function(id,data){
        this.data.each(function(d){
            if(d[this.PRIMARY_ID]==id){d[data[0]]=data[1];}
        },this);
    },
    selectData:function(id,state){
        var data,index;
        this.data.each(function(d,i){
            if(d[this.PRIMARY_ID]==id){data=d;index=i;}
        },this);
        if(state)return index;
        return data;
    },
    clearData:function(data){
        var loop=arguments.callee,h=new Hash();
        $H(data).each(function(v,k){
            $type(v)=='object'||$type(v)=='hash'?h.set(k,loop(v)):h.set(k,'');
        });
        return h;
    },
    getAddTpl:function(addTpl){
        return this.clearData(addTpl);
    },
    addData:function(data){
        var d=data||this.getAddTpl(this.data[0]);
        this.options.format.call(this,d);
        this.lastId=d[this.PRIMARY_ID]='new_'+((isNaN(this.lastId)?this.lastId.substring(4):this.lastId).toInt()+1);
        this.data.push(d);
        this.render('add');
    },
    delData:function(id){
        for(var i=0,l=this.data.length;i<l;i++){
            if(this.data[i][this.PRIMARY_ID]==id){
                this.data.splice(i,1); break;
            }
        }
        this.render('del');
    },
    sort:function(key,obj){
        if(!key||!obj)return;
        var by=obj.className=obj.hasClass('desc')?'asc':'desc';
        this.data.sort(function(a, b){
            return by =="asc"?a[key].localeCompare(b[key]):b[key].localeCompare(a[key]);
        });
        this.render(1);
    },
    render:function(state){
        this.total=this.getTotal();var p;
        switch (state){
            case 'add': p=this.total; break;
            case 'del': p=this.total>this.options.current?this.options.current:this.total;break;
            default: p=state; break;
        }

        this.goPage(p);
    },
    filter:function(dataRow){
        var hs=new Hash();
        if(dataRow&&dataRow.getElements('input').length)
        dataRow.getElements('input').each(function(el){
            hs.set(el.get('key'),el.get('tname'));
        });
        return hs;
    },
    toHideInput:function(dataRow){
        var hs=this.filter(dataRow);
        var hiddenMain=new Element('div');
        var fdoc=document.createDocumentFragment();

        this.data.each(function(d){
            var p=d[this.PRIMARY_ID];
            hs.each(function(v,k){
                if($chk(d[k])&&v){
                    var n=v.replace(/_PRIMARY_/g,p);
                    fdoc.appendChild(new Element('input',{type:'hidden','name':n,'value':d[k]}));
                }
            });
        },this);
        hiddenMain.empty().appendChild(fdoc);
        var params=hiddenMain.toQueryString();
        hiddenMain=null;
        return params;
    }
});

