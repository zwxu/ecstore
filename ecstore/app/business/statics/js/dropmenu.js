var DropMenu=new Class({
    Implements: [Events, Options,LazyLoad],
    options: {
        onLoad:$empty,
        onShow:$empty,
        onHide:$empty,
        showMode:function(menu){menu.setStyle('display','block');},
        hideMode:function(menu){menu.setStyle('display','none');},
        dropClass:'droping',
        eventType:'click',
        relative:document.body,
        stopEl:false,
        stopState:false,
        lazyEventType:'show',
        delay:200,
        offset:{x:0,y:20}
    },
    initialize:function(el,options){
        this.element=$(el);
        if(!this.element)return;
        this.setOptions(options);
        var menu=this.options.menu||this.element.get('dropmenu');
        this.menu=$(menu)||$E('.'+menu,this.element.getParent());
        if(!this.menu)return;
        this.load().attach()._lazyloadInit(this.menu);
    },
    attach:function(){
        var options=this.options,stopState=options.stopState,
            dropClass=options.dropClass,eventType=options.eventType;
        if(eventType=='mouse'){
            $$(this.element,this.menu).addEvents({
                'mouseenter':function(e){
                    this.show();
                    if(this.timer)$clear(this.timer);
                }.bind(this),
                'mouseleave':function(){
                    if(!this.status)return;
                    this.timer=this.hide.delay(this.options.delay,this);
                }.bind(this)
            });
        }else{
            this.element.addEvent(eventType,function(e){
                if(this.showTimer)$clear(this.showTimer);
                if(stopState)e.stop();
                this.showTimer=this.show().outMenu.delay(this.options.delay,this);
            }.bind(this));
        }
        this.menu.addEvent('click',function(e){
            if(true===options.stopEl) options.stopEl='stop';
            if(options.stopEl)return e[options.stopEl]();
            return this.hide();
        }.bind(this));
        return this;
    },
    load:function(){
        return this.fireEvent('load',[this.element,this]);
    },
    show:function(){
        this.fireEvent('initShow');
        if(this.status)return this;
        this.element.addClass(this.options.dropClass);
        this.options.showMode.call(this,this.menu);
        if(this.options.relative)
        this.position({page: this.element.getPosition(this.options.relative)});
        this.status=true;
        return this.fireEvent('show',this.menu);
    },
    hide:function(){
        this.options.hideMode.call(this,this.menu);
        this.element.removeClass(this.options.dropClass);
        this.status=false;
        this.fireEvent('hide',this.menu);
    },
    position:function(event){
        var options=this.options,relative=$(options.relative),
            size = (relative||window).getSize(), scroll = (relative||window).getScroll();
        var menu = {x: this.menu.offsetWidth, y: this.menu.offsetHeight};

        if(options.temppos){
            var l = event['page'].x+options.offset.x,t =event['page'].y+this.element.getSize().y+options.offset.y;
            return this.menu.setStyles({'top':t,'left':l});
        }
        var props = {x: 'left', y: 'top'},obj={};

        for (var z in props){
            this.fireEvent('position',z);
            obj[props[z]] = event.page[z] + this.options.offset[z] + scroll[z];

            if (obj[props[z]] + menu[z] - scroll[z] > size[z]){
                var n=this.options.size?this.element.getSize()[z]:0;
                obj[props[z]] = event.page[z] - menu[z] + scroll[z] + n + 2;
            }
        }
        this.menu.setStyles(obj);
        return this;
    },
    outMenu:function(){
        var _this=this;
        document.body.addEvent('click',function(e){
            if(_this.options.stopEl!=e.target&&_this.menu){
                _this.hide.call(_this);
                $clear(_this.showTimer);
                this.removeEvent('click',arguments.callee);
            }
        });
    }
});
