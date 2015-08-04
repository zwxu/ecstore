var AutoPlay=new Class({
    options:{
        autoplay:true,
        interval:3000,
        pauseOnHover:true    //鼠标悬停停止播放
    },
    _autoInit:function(fn){
        if(!this.options.autoplay)return;
        this.autofn=fn||function(){};
        this.autoEvent().startAutoplay();
    },
    autoEvent:function(){             //悬停时停止播放
        if(this.options.pauseOnHover&&this.container){
            this.container.addEvents({
            'mouseenter':this.stopAutoplay.bind(this),
            'mouseleave':function(){
                this.startAutoplay();
            }.bind(this)});
        }
        return this;
    },
    startAutoplay:function(){
        this.paused=false;
        this.autoTimer=function(){
            if(this.paused)return;
            this.autofn();
        }.periodical(this.options.interval,this);
    },
    stopAutoplay:function(){
        if(this.autoTimer){
            clearInterval(this.autoTimer);
            this.autoTimer=undefined;
        }
        this.paused=true;
    }
});

var LazyLoad=new Class({                    //延时加载基类
    Implements:[Options,Events],
    options:{
        img:'img-lazyload',       //存图象地址的属性
        textarea:'textarea-lazyload',   //textarea的class
        lazyDataType:'textarea',            //延时类型
        execScript:true,                   //是否执行脚本
        islazyload:true,                   //是否执行延时操作
        lazyEventType:'beforeSwitch',        //要接触延时的事件
        after:function(){} //LazyLoad完成后触发
    },
    loadCustomLazyData: function(containers, type) {
        var area, imgs,area_cls=this.options.textarea,img_data=this.options.img;
        if(!this.options.islazyload)return;
        Array.from(containers).each(function(container){
            switch (type) {
                case 'img':
                    imgs=container.nodeName === 'IMG'?[container]:container.getElements('img');
                    imgs.each(function(img){
                        this.loadImgSrc(img, img_data);
                    },this);
                    break;
                default:
                    area=container.getElement('textarea');
                    if(area && area.hasClass(area_cls))
                    this.loadAreaData(area);
                    break;
            }
        },this);
    },
    loadImgSrc: function(img, flag ,cb) {
        flag = flag || this.options.img;
        var dataSrc = img.getProperty(flag);
        img.removeProperty(flag);
        if (dataSrc && img.src != dataSrc) {
            var pic= new Image();
            pic.onload = function(){
                pic.onload = null;
                img.src = dataSrc;
                cb && cb();
            };
            pic.src = dataSrc;
            img.onload = function(){this.options.after(img);}.bind(this);
        }
    },
    loadAreaData: function(area ,cb) {
            //area.setStyle('display','none').className='';
            var content = new Element('div').inject(area,'before');
            this.stripScripts(area.value,content);
            area.destroy();
            cb && cb();
    },
    isAllDone:function(){
        var type=this.options.lazyDataType,flag=this.options[type],
            elems, i, len, isImgSrc = type === 'img';
        if (type && this.container) {
            elems = this.container.getElements(type);
            for (i = 0, len = elems.length; i < len; i++) {
                if (isImgSrc ?elems[i].get(flag): elems[i].hasClass(flag)) return false;
            }
        }
        return true;
    },
    stripScripts: function(v,content){
        var scripts = '';
        var text = v.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(){
            scripts += arguments[1] + '\n';
            return '';
        });
        content.set('html',this.options.execScript?text:v);
        this.options.execScript && Browser.exec(scripts);
    },
    _lazyloadInit:function(panel){
        var loadLazyData=function(){
            var containers=typeOf(panel)=='function'?panel(arguments):panel;
            this.loadCustomLazyData(containers,this.options.lazyDataType);
            if (this.isAllDone()) {
                this.removeEvent(this.options.lazyEventType,arguments.callee);
            }
        };
        this.addEvent(this.options.lazyEventType,loadLazyData.bind(this));
    }
});

var DataLazyLoad = new Class({
    Extends:LazyLoad,
    options:{
        threshold:null,               //获取要延时的窗口距离
        syncEl:null,                  //images ,areas
        config:{
            mod:'manual',             //延时模式
            diff:'default',           //默认取为两屏窗口的延时元素
            placeholder:'none'         //默认延时图片地址
        }
    },
    initialize:function(options,containers){
        this.containers=Array.from($(containers)||document);
        if(!this.containers)return;
        this.setOptions(options);
        this.lazyinit();
    },
    lazyinit:function(){
        this.threshold=this.getThreshold();
        this.filterItems().getItemsLength();
        this.initLoadEvent();
    },
    filterItems:function(){
        var containers=this.containers,imgs,areas,i,len,img,area,
        lazyImgs=[],lazyAreas=[];
        containers.each(function(n){
            lazyImgs=lazyImgs.combine(n.getElements('img').filter(this.filterImg,this));
            lazyAreas=lazyAreas.combine(n.getElements('textarea').filter(this.filterArea,this));
        },this);
        this.images=lazyImgs;
        this.areas=lazyAreas;
        return this;
    },
    filterImg:function(img){
        var img_data=this.options.img, dataSrc=img.getAttribute(img_data),
        threshold=this.threshold,placeholder=this.options.config.placeholder,
        isManualMod=this.options.config.mod==='manual';
        if(isManualMod){
            if(dataSrc){
                if(placeholder!=='none')img.src=placeholder;
                return true;
            }
        }else{
            if($(img).getOffsets().y> threshold && ! dataSrc){
                img.set(img_data,img.src);
                placeholder !== 'none'? img.src=placeholder:img.removeAttribute('src');
                return true;
            }
        }
    },
    filterArea:function(area){
        return area.hasClass(this.options.textarea);
    },
    initLoadEvent:function(){
        var timer,self=this,win =window;
        win.addEvent('domready',this.loadItems.bind(this));

        if(!this.getItemsLength())return;
        win.addEvents({'scroll':loader,'resize':resizeloader});

        function resizeloader(){
            self.threshold=self.getThreshold();
            loader();
        }
        function loader(){
            if(timer) return;
            timer = function(){
                self.loadItems.call(self);
                if(!self.getItemsLength())
                win.removeEvents({'scroll':loader,'resize':resizeloader});
                timer = null;
            }.delay(100);
        }
    },
    loadItems: function() {
        var syncEl= this.options.syncEl;
        if(syncEl)return this.loadsync(this[syncEl],syncEl);

        this.initItems(this.images.concat(this.areas));
        this.fireEvent('callback');
    },
    initItems:function(items){
        var scrollTop = window.getScroll().y, threshold = this.threshold + scrollTop,
            obj = {images:[],areas:[]},
            fnObj = {'areas':'loadAreaData','images':'loadImgSrc'};

        items.each(function(el){
            var isArea=el.tagName=='TEXTAREA'?'areas':'images',ele =el;
            if(isArea) el= $(el.getStyle('display')=='none'?el.parentNode:el);

            if (el.getOffsets().y<= threshold) {
                this[fnObj[isArea]](ele);
            }else{
                obj[isArea].push(ele);
            }
        },this);

        this.images=obj.images; this.areas=obj.areas;
    },
    loadsync:function(els,tag){
        if(!els.length)return;
        var scrollTop = window.getScroll().y, threshold = this.threshold + scrollTop,
            isArea = tag=='areas';

        els = els.filter(function(el){
            if(isArea) el = el.getStyle('display')=='none'?el.parentNode:el;
            return el.getOffsets().y<= threshold;
        });

        if(!els.length)return;
        var el= els.shift(),
            callback= this.loadsync.bind(this, [this[tag].erase(el),tag]);

        if(isArea) return this.loadAreaData(el,callback);
        this.loadImgSrc(el,false,callback);
    },
    getThreshold:function(){
        if(this.options.threshold)return this.options.threshold;
        var diff=this.options.config.diff,vh=window.getSize().y;
        if(diff==='default')return 1 * vh;
        return vh + diff;
    },
    getItemsLength:function(){
        return this.images.length+this.areas.length;
    }
});

var Tabs = new Class({
    Implements:[AutoPlay,LazyLoad],
    options:{
    //  onLoad:function(){},
    //  onInit:function(){},                        //初始化时调用
    //  onBeforeSwitch:function(){},               //tab切换前调用
    //  onSwitch:function(){},                     //切换时调用
        eventType:'mouse',                  //事件类型click和mouse
        hasTriggers:true,                  //是否有触点选择
        triggersBox:'.switchable-triggerBox',         //触点的父元素
        triggers:'.switchable-trigger',               //触点class
        panels:'.switchable-panel',                  //显示面板class
        content:'.switchable-content',              //显示区域class
        activeIndex:0,                              //默认显示的元素索引
        activeClass:'active',                       //当前触点元素class
        steps:1,                             //一次显示一个panel
        delay:100,                           //mouse延时触发
        haslrbtn:false,                     //是否支持前后按钮
        prev:'.prev',
        next:'.next',
        autoplay:false,
        disableCls:null
    },
    initialize:function(container,options){
        this.container=$(container);
        if(!this.container)return;
        this.setOptions(options);
        this.activeIndex=this.options.activeIndex;
        this.init();
    },
    init:function(){
        this.fireEvent('load');
        this.getMarkup();
        this.triggersEvent().extendPlugins();
        if(this.options.hasTriggers&&this.triggers[this.activeIndex])
        this.triggers[this.activeIndex].addClass(this.options.activeClass);
        if(this.options.islazyload) this.fireEvent('beforeSwitch',{toIndex:this.activeIndex});
        this.fireEvent('init');
    },
    extendPlugins:function(){
        var options=this.options;
        if(options.autoplay) this._autoInit(this.autofn.bind(this));
        if(options.islazyload) this._lazyloadInit(this.getLazyPanel.bind(this));
        Tabs.plugins.each(function(plugin){
            if(plugin.init)plugin.init.call(this);
        },this);
    },
    autofn:function(){
        var index=this.activeIndex < this.triggers.length - 1 ? this.activeIndex + 1 : 0;
        this.switchTo(index, 'FORWARD');
    },
    getMarkup:function(){
        var container=this.container,options=this.options,triggersBox;

        if(options.hasTriggers) triggersBox=$(options.triggersBox)||container.getElement(options.triggersBox);
        this.triggers=triggersBox?triggersBox.getChildren():container.getElements(options.triggers);

        panels=this.panels=container.getElements(options.panels);

        this.content=$(options.content)||container.getElement(options.content)?container.getElement(options.content):panels[0]?panels[0].getParent():[];

        this.content=Array.from(this.content);

        if(!panels.length&&this.content.length) this.panels=this.content[0].getChildren();

        if(!this.panels.length)return this;
        this.activeIndex = this.activeIndex.limit(0, this.panels.length - 1);
        this.length=this.panels.length/options.steps;
    },
    triggersEvent:function(){
        var options=this.options,triggers=this.triggers;
        if(options.hasTriggers)
        triggers.each(function(trigger,index){
            trigger.addEvent('click',function(e){
                if(!this.triggerIsValid(index))return;
                this.cancelTimer().switchTo(index);
            }.bind(this));

            if(options.eventType==='mouse')
            trigger.addEvents({
                'mouseenter':function(e){
                    if(!this.triggerIsValid(index))return;
                    this.switchTimer=this.switchTo.delay(options.delay,this,index);
                }.bind(this),
                'mouseleave': this.cancelTimer.bind(this)
            });
        },this);
        if(options.haslrbtn) this.lrbtn();
        return this;
    },
    //前后按钮事件
    lrbtn:function(){
        ['prev','next'].each(function(d){
            this[d+'btn']=this.container.getElements(this.options[d]).addEvent('click',function(e){
                if(!$(e.target).hasClass(this.options.disableCls))this[d]();
            }.bind(this));
        },this);
        this.disabledBtn();
    },
    disabledBtn:function(){
        var disableCls=this.options.disableCls;
        if(disableCls){
            this.addEvent('switch',function(ev){
                var i=ev.currentIndex,
                disableBtn=(i===0)?this['prevbtn']:(i===Math.ceil(this.length)-1)?this['nextbtn']:undefined;
                this['nextbtn'].removeClass(disableCls);
                this['prevbtn'].removeClass(disableCls);
                if(disableBtn)disableBtn.addClass(disableCls);
            }.bind(this));
        }
    },
    triggerIsValid:function(index){
        return this.activeIndex!==index;
    },
    cancelTimer:function(){
        if(this.switchTimer){
            clearTimeout(this.switchTimer);
            this.switchTimer=undefined;
        }
        return this;
    },
    switchTo:function(index,direction){
        var options=this.options,triggers=this.triggers,panels=this.panels,
            activeIndex=this.activeIndex,steps=options.steps,
            fromIndex = activeIndex * steps, toIndex = index * steps;

            if(!this.triggerIsValid(index))return this;

            this.fireEvent('beforeSwitch',{toIndex:index});

            if(options.hasTriggers)
            this.switchTrigger(activeIndex > -1 ? triggers[activeIndex] : null, triggers[index]);

            if (direction === undefined)
            direction = index > activeIndex ? 'FORWARD' : 'BACKWARD';

            this.switchView(
                panels.slice(fromIndex, fromIndex + steps),
                panels.slice(toIndex, toIndex + steps),
                index, direction);

            this.activeIndex = index;

            return this.fireEvent('switch',{currentIndex:index});
    },
    switchTrigger:function(fromTrigger,toTrigger,index){
            var activeClass=this.options.activeClass;
            if (fromTrigger)fromTrigger.removeClass(activeClass);
            toTrigger.addClass(activeClass);
    },
    switchView:function(fromPanels,toPanels,index,direction){
        fromPanels[0].setStyle('display','none');
        toPanels[0].setStyle('display','');
    },
    prev: function() {
        var activeIndex = this.activeIndex;
        this.switchTo(activeIndex > 0 ? activeIndex - 1 : this.length - 1, 'BACKWARD');
    },
    next: function() {
        var activeIndex = this.activeIndex;
        this.switchTo(activeIndex < this.length - 1 ? activeIndex + 1 : 0, 'FORWARD');
    },
    getLazyPanel:function(args){
        var steps = this.options.steps,from = args[0].toIndex * steps , to = from + steps;
        return this.panels.slice(from, to);
    }
});

Tabs.plugins=[];

Tabs.Effects = {
    none: function(fromEls, toEls) {
        fromEls[0].setStyle('display','none');
        toEls[0].setStyle('display','block');
    },
    fade: function(fromEls, toEls ) {
        if (fromEls.length !== 1) {
            throw new Error('fade effect only supports steps == 1.');
        }
        var fromEl = fromEls[0], toEl = toEls[0];

        if(this.anim)this.anim.cancel();
        this.anim=new Fx.Tween(fromEl,{duration:this.options.duration,
            onStart:function(){toEl.setOpacity(1);},
            onCancel:function(){
                this.element.setOpacity(0);
                this.fireEvent('complete');
            },
            onComplete:function(){
                toEl.setStyle('zIndex',9);
                fromEl.setStyle('zIndex',1);
                this.anim = undefined;
        }.bind(this)}).start('opacity',1,0);
    },
    scroll: function(fromEls, toEls, index, direction) {
        var self = this,
            options = this.options,
            activeIndex = this.activeIndex,
            isX = options.effect === 'scrollx',
            len = this.length,
            content = this.content[0],
            viewDiff = this.viewSize[isX ? 0 : 1],
            steps = options.steps,
            panels = this.panels,
            prop = isX ? 'left': 'top',
            diff = - viewDiff * index,
            from,
            isCritical,
            isBackward = direction !== 'FORWARD';

        prop = content.getStyle('position') == 'absolute' ? prop : 'margin-' + prop;
        isCritical = (isBackward && activeIndex === 0 && index === len - 1) || (!isBackward && activeIndex === len - 1 && index === 0);
        if (isCritical) { diff = position.call(this,true); }

        fromp=content.getStyle(prop).toInt();
        fromp=isNaN(fromp)?0:fromp;

        if(this.anim)this.anim.cancel();

        this.anim=new Fx.Tween(content,{
            duration:this.options.duration,
            transition: this.options.transition,
            onComplete:function(){
                if (isCritical) position.call(self);
                this.anim = undefined;
        }.bind(this)}).start(prop,fromp,diff);

        function position(reset) {
            var start = isBackward ? len - 1 : 0, from = start * steps, to = (start + 1) * steps, i;

            for (i = from; i < to; i++) {
                var l=(isBackward ? -1 : 1) * viewDiff * len;
                panels[i].setStyle('position',reset?'relative':'').setStyle(prop,reset?l:'');
            }
            if(reset) return isBackward ? viewDiff : -viewDiff * len;
            return content.setStyle(prop,isBackward ? -viewDiff * (len - 1) :'');
        }
    }
};

Effects = Tabs.Effects;
Effects['scrollx'] = Effects['scrolly'] = Effects.scroll;

var Switchable=new Class({
    Extends:Tabs,
    options:{
        autoplay:true,
        position:'absolute',
        isFloat:true,
        effect:'none',
        circular:false,                         //是否开启循环滚动
        duration:500,
        transition: Fx.Transitions.linear,
        direction:'FORWARD',                  //'BACKWARD'
        viewSize:[]                           //显示区域的大小
    },
    extendPlugins:function(){
        this.parent();
        this.effInit();
    },
    effInit:function(){
        var options = this.options,
            effect = options.effect,
            panels = this.panels,
            content = this.content[0],
            steps = options.steps,
            viewStep = options.viewStep,
            activeIndex = this.activeIndex,
            len = panels.length,
            size = panels[0].measure(function(){
                return {
                    x: this.getSize().x ? this.getSize().x + this.getPatch('margin').x : this.getStyle('width').toInt() + this.getPatch().x,
                    y: this.getSize().y ? this.getSize().y + this.getPatch('margin').y : this.getStyle('height').toInt() + this.getPatch().y
                };
            });

        this.viewSize = [options.viewSize[0] || size.x * steps, options.viewSize[1] || size.y * steps];

        if(effect!=='none'){
            switch (effect) {
                case 'scrollx':
                case 'scrolly':
                    if(options.position == 'absolute') {
                        content.setStyle('position','absolute');
                        content.getParent().setStyle('position','relative');
                    }
                    else if (options.position == 'relative') {
                        content.setStyle('position', 'relative');
                    }
                    if (effect === 'scrollx') {
                        options.isFloat && panels.setStyle('float','left');
                        content.setStyle('width',this.viewSize[0] * (len/steps));
                    }
                    break;
                case 'fade':
                    var min = activeIndex * steps, max = min + steps - 1, isActivePanel;

                    panels.each(function(panel,i){
                        isActivePanel = i >= min && i <= max;
                        panel.setStyles({
                            opacity: isActivePanel ? 1 : 0,
                            position: options.position || '',
                            zIndex: isActivePanel ? 9 : 1
                        });
                    });
                    break;
                default :
                    break;
            }
        }
    },
    switchView: function(fromEls, toEls, index, direction) {
        var options= this.options, effect = options.effect,circular=options.circular,
            fn = typeOf(effect)=='function' ? effect : Effects[effect];
        if(circular)direction=options.direction;
        if(fn)fn.call(this, fromEls, toEls,index, direction);
    }
});

var Accordion= new Class({
    Extends:Tabs,
    options:{
        eventType:'click',
        multiple:false
    },
    triggerIsValid:function(index){
        return this.activeIndex !== index || this.options.multiple;
    },
    switchView: function(fromPanels, toPanels, index) {
        var  options= this.options,panel = toPanels[0];
            if (options.multiple) {
                this.triggers[index].toggleClass(options.activeClass);
                panel.setStyle('display',panel.getStyle('display')=='none'?'block':'none');
            } else {
                fromPanels[0].setStyle('display','none');
                panel.setStyle('display','block');
            }
    }
});

var Slide = new Class({
    Extends: Switchable,
    options:{
        viewStep:4
    },
    effect:function(fromEls, toEls, index, direction){
        var self = this, options = this.options,
        activeIndex = this.activeIndex,
        isX = options.effect === 'scrollx',
        len = this.length, content = this.content[0],
        viewDiff = this.viewSize[isX ? 0: 1],
        steps = options.steps, panels = this.panels,
        prop = isX ? 'left': 'top', diff = - viewDiff * index, from,
        isCritical, isBackward = direction !== 'FORWARD';

        isCritical = (isBackward && activeIndex === 0 && index === len - 1) || (!isBackward && activeIndex === len - 1 && index === 0);

        if (isCritical) { diff = position.call(this, true); }

        fromp = content.getStyle(prop).toInt();

        fromp = isNaN(fromp) ? 0: fromp;

        if (this.anim) return this;

        var  temp= [], viewStep = options.viewStep,viewIndex = viewStep - steps;
        this.anim = new Fx.Tween(content, {
            duration: this.options.duration,
            transition: this.options.transition,
            onStart:function(){
                this.to[0].value=isBackward?viewDiff:-viewDiff;
                if(!isBackward){
                    var l = index,j;
                    for(var i = viewStep-1;i>=0;i--){
                        l++;
                        if(l>len){
                            if(!j)j =viewStep - i;
                            temp.push(i);
                            panels[i].setStyle(prop,j*viewDiff);
                        }
                    }
                    if(!index){
                        panels[viewIndex].setStyle(prop,viewDiff);
                        panels[0].setStyle(prop,viewDiff);
                    }
                }
            },
            onComplete: function() {
                content.setStyle(prop,0);
                panels.each(function(el,i){
                    var v = !index? 0 :-viewDiff*index,
                        j = index+ viewStep-len;

                    if(isBackward && i<j) v = -viewDiff*(index-len);
                    if(!isBackward && temp.contains(i)) v = viewDiff*(viewIndex-temp[0]);
                    el.setStyle('position','relative').setStyle(prop,v);
                });
                this.anim = undefined;
            }.bind(this)
        }).start(prop, fromp, diff);

        function position(reset) {
            var start = isBackward ? len - 1: 0,
                from = start * steps, to = (start + 1) * steps, i;

            for (i = from; i < to; i++) {
                var l = (isBackward ? - 1: 1) * viewDiff * len;
                panels[i].setStyle('position', reset ? 'relative': '').setStyle(prop, reset ? l: '');
            }
            if (reset) return isBackward ? viewDiff: - viewDiff * len;
            return content.setStyle(prop, isBackward ? - viewDiff * (len - 1) : '');
        }

    },
    switchView: function(fromEls, toEls, index, direction) {
        var options = this.options, circular = options.circular;
        if (circular) direction = options.direction;
        this.effect(fromEls, toEls, index, direction);
    }
});

Switchable.autoRender=function(autoClass,container){
        var cls=autoClass||'.Auto_Widget',  clt=$(container||document.body).getElements(cls);
        if(clt.length)
        clt.each(function(el){
             var type = el.get('data-widget-type'), config;
             if (type && ('Tabs Switchable DropMenu Accordion DataLazyLoad'.indexOf(type) > -1)) {
                try {
                    config = el.get('data-widget-config')||{};
                    if(type=='DataLazyLoad')return  new window[type](JSON.decode(config),el);
                    new window[type](el, JSON.decode(config));
                } catch(e) {}
             }
        });
};

window.addEvent('domready',Switchable.autoRender.bind(Switchable));
