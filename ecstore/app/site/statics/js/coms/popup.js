/*
 * 弹框组件
 * @param target 弹出框内容元素
 * @param type 弹框类型:popup,nohead,notitle,noclose,nofoot
 * @param template 页面中模板位置ID
 * @param width 弹出框宽度 0 或'auto'为不限制
 * @param height 弹出框高度 0 或'auto'为不限制
 * @param title 弹出框标题
 * @param autoHide 是否一定时间自动关闭 false或具体数值(秒)
 * @param onLoad 载入时触发事件
 * @param onShow 显示时触发事件
 * @param onClose 关闭时触发事件
 * @param modal 是否在弹出时候其他区域不可操作
 * @param pins 是否把弹窗固定不动
 * @param single 单一窗口，还是多层窗口
 * @param effect 是否使用特效 false true or {style from to}
 * @param position 定位到哪里
 *      target 相对定位的目标
 *      to 相对定位基点x,y(九点定位:0/left/right/100%/center/50%,0/top/bottom/100%/center/50%)
 *      base 弹框的定位基点x,y(同上)
 *      offset 偏移目标位置
 *          x 横向偏移
 *          y 纵向偏移
 *      intoView 如果超出可视范围，是否滚动使之可见
 * @param useIframeShim 是否使用iframe遮盖
 * @param async 异步调用方式: false, frame, ajax
 * @param frameTpl iframe方式调用的模板
 * @param ajaxTpl ajax方式调用的模板
 * @param asyncOptions 异步请求的参数
 *      cache 是否缓存请求内容 2012.6.24
 *      target 请求的缓存目标
 *      method 请求的方式
 *      data ajax/iframe方式请求的数据
 *      onRequest 请求时执行
 *      onSuccess 请求成功后执行
 *      onFailure 请求失败后执行
 *      etc. 还有更多Request里其它参数
 * @param component 弹出框的构成组件
 * @method getTemplate 获取模板
 * @method setTemplate 处理设置模板
 * @method maxSize 使窗口最大化
 * @method toElement 返回this.container窗体
 * @return this Popup instance
 */
var Popup = new Class({
    Implements: [Events, Options],
    options: {
        type: 'popup',
        template: null,
        width: 0,
        height: 0,
        title: LANG_shopwidgets.tip,
        autoHide: false,
        /* onLoad: function(){},
         * onShow: function(){},
         * onClose: function(){},*/
        modal: false,
        pins: false,
        single: false,
        minHeight:220,
        minWidth:250,
        effect: {
            style: 'opacity',
            duration: 400,
            from: 0,
            to: 1,
            maskto: 0.3
        },
        position: {
            target: document.body,
            base: {x: 'center', y: 'center'},
            to: {x: 'center', y: 'center'},
            offset: {x: 0, y: 0},
            intoView: false
        },
        useIframeShim: false,
        async: false,
        frameTpl: '<iframe allowtransparency="allowtransparency" align="middle" frameborder="0" height="100%" width="100%" scrolling="auto" src="about:blank">请使用支持iframe框架的浏览器。</iframe>',
        ajaxTpl: '<div class="loading">loading...</div>',
        asyncOptions: {
            method: 'get',
            cache: true
            /*target: null,
            data: '',
            onRequest: function() {},
            onSuccess: function() {},
            onFailure: function() {}*/
        },
        component: {
            container: 'popup-container',
            body: 'popup-body',
            header: 'popup-header',
            close: 'popup-btn-close',
            content: 'popup-content',
            mask: 'popup-modalMask'
        }
    },
    initialize: function(target, options) {
        if (!target) return;
        this.target = target;
        this.setOptions(options);

        options = this.options;
        var asyncOptions = options.asyncOptions || {};
        var container = this.container = this.setTemplate(options.template);
        var el = new Element('div');
        this.body = container.getElement('.' + options.component.body) || el;
        this.header = container.getElement('.' + options.component.header) || el;
        this.title = this.header.getElement('h2') || el;
        this.close = container.getElement('.' + options.component.close) || el;
        this.content = container.getElement('.' + options.component.content) || el;
        if(options.width && !isNaN(options.width)) {
            if(options.width <= 1 && options.width > 0) {
                options.width = options.width * window.getSize().x;
            }
            else {
                options.width = options.width.toInt();
            }
        }
        if(options.height && !isNaN(options.height)) {
            if(options.height <= 1 && options.height > 0) {
                options.height = options.height * window.getSize().y;
            }
            else {
                options.height = options.height.toInt();
            }
        }
        this.size = {
            x: options.width || '',
            y: options.height || ''
        };
        options.title || (this.header.getElement('h2') && this.header.getElement('h2').destroy());
        container.retrieve('instance') || this.body.setStyles({
            width: this.size.x,
            height: this.size.y
        });
        this.fireEvent('load', this);
        if (typeOf(target) === 'string') {
            if (options.async === 'ajax') {
                this.requestCache(Object.merge({
                    url: target + '',
                    update: this.content
                }, asyncOptions));
            }
            else {
                var url = asyncOptions.data ? target + (target.indexOf('?') > 1 ? '&' : '?') + asyncOptions.data : target + '';
                this.content.getElement('iframe').set('src', url).addEvent('load', (asyncOptions.onSuccess || function(){}).bind(this));
            }
        }
        if (options.modal) {
            var effect = !!options.effect ? {
                style: options.effect.style,
                duration: options.effect.duration,
                from: options.effect.from,
                to: options.effect.maskto
            } : false;
            this.mask = new Mask({
                target: options.modal,
                'class': options.component.mask,
                effect: effect
            });
        }
        this.hidden = true;
        this.attach(); //执行初始化加载
    },
    attach: function() {
        this.show();
        //如果没有存储实例，就绑定关闭事件
        if (this.bindHide) {
            // this.container.retrieve('instance').hidden = false;
            return;
        }
        var closeBtn = this.container.getElements('.' + this.options.component.close);
        closeBtn.length && closeBtn.addEvent('click', function(e){
            this.hide();
            this.bindHide = true;
        }.bind(this));
        this.container.store('instance', this);
    },
    show: function() {
        if(!this.hidden) return this;
        this.container.setStyle('display', 'block');
        this.position();

        if(Browser.ie6 && this.options.useIframeShim) {
            new Element('iframe', {
                src: 'about:blank',
                style: 'position:absolute;z-index:-1;border:0 none;filter:alpha(opacity=0);top:-' + (this.container.getPatch().y || 0) + ';left:-' + (this.container.getPatch().x || 0) + ';width:' + (this.container.getSize().x || 0) + 'px;height:' + (this.container.getSize().y || 0) + 'px;'
            }).inject(this.container);
        }

        var eff = this.options.effect;
        if(eff) {
            if(eff === true || eff.style === 'opacity') this.container.setStyle('opacity', eff.from || 0).setStyle('visibility','visible');
            new Fx.Tween(this.container, {duration: eff.duration || 400}).start(eff.style || 'opacity', eff.from || 0, eff.to || 1);
        }
        this.hidden = false;
        this.fireEvent('show', this);

        this.mask && this.mask.show();
        this.options.autoHide && this.hide.delay(this.options.autoHide.toInt() * 1000, this);

        return this;
    },
    hide: function() {
        if (this.hidden) return this;
        this.fireEvent('close', this);
        this.options.pins && this.container.pin(false, false, false);
        if (this.options.single) {
            this.container.setStyle('display', 'none');
            this.hidden = true;
            this.mask && this.mask.hide();
            return this;
        }
        var eff = this.options.effect;
        if(eff) {
            new Fx.Tween(this.container, {
                duration: eff.duration || 400,
                onComplete: function(){
                    this.container.destroy();
                }.bind(this)
            }).start(eff.style || 'opacity', eff.to || 1, eff.from || 0);
        }
        else {
            this.container.destroy();
        }
        this.hidden = true;
        if (this.mask && ($$('.' + this.options.component.container).every(function(el) {
            return this.hidden;
        }.bind(this)))) this.mask.hide();
        return this;
    },
    getTemplate: function(template, type) {
        var options = this.options;
        template = template || options.template;
        if (template && typeOf(template) === 'string') {
            if(!document.id(template)) return template;
            template = $(template);
        }
        if (typeOf(template) === 'element' && (/^(?:script|textarea)$/i).test(template.tagName)) return $(template).get('value') || $(template).get('html');
        type = type || options.type;
        var containerTpl = [
            '<div class="{body}">',
            '<div class="{header}">',
            '<h2>{title}</h2>',
            '<span><button type="button" class="{close}" title="关闭"><i>×</i></button></span>',
            '</div>',
            '<div class="{content}">{main}</div>',
            '</div>'
        ];
        if (type === 'nohead') containerTpl[1] = containerTpl[2] = containerTpl[3] = containerTpl[4] = '';
        else if (type === 'notitle') containerTpl[2] = '';
        else if (type === 'noclose' || !!options.autoHide) containerTpl[3] = '';

        return containerTpl.join('\n');
    },
    setTemplate: function(template) {
        var options = this.options;
        var single = document.getElement('[data-single=true].' + options.component.container);
        var main = '';

        if(options.single && single) return single;

        template = '<div class="{container}" data-single="'+ !!options.single +'">' + this.getTemplate(template) + '</div>';
        if (typeOf(this.target) === 'element') {
            main = this.target.get('html');
        }
        else if (typeOf(this.target) === 'string') {
            main = options.async === 'ajax' ? options.ajaxTpl : options.frameTpl;
        }

        var component = Object.merge(options.component, {
            title: options.title,
            main: main
        });
        return new Element('div', {html: template.substitute(component)}).getFirst().inject(document.body);
    },
    position: function(){
        var options = this.options, element;
        if(!this.size.x && Browser.ie && Browser.version < 8 && this.container.getSize().x >= document.body.getSize().x){
            $(this.body).setStyle('width', options.minWidth.toInt() - this.container.getPatch().x);
        }
        if(!this.size.y && Browser.ie && Browser.version < 8 && this.container.getSize().y >= document.body.getSize().y){
            var y = Math.min(options.minHeight.toInt(), $(document.body).getSize().y);
            $(this.body).setStyle('height', y - this.container.getPatch().y);
        }
        if (this.size.y) element = this.container;
        else if(this.container.getSize().y >= document.body.getSize().y) element = document.body;
        if(typeOf(element) === 'element') this.setHeight(element);
        this.container.position(options.position);
        options.pins && this.container.pin();
    },
    setHeight: function(el) {
        el = el || this.container;
        this.content.setStyle('height', (!this.size.y && Browser.ie && Browser.version < 8 ? this.body.getStyle('height').toInt() : el.getSize().y - this.container.getPatch().y) - $(this.body).getPatch().y - $(this.header).outerSize().y - this.content.getPatch().y);
    },
    setTitle: function(html) {
        this.title.set('html', html);
    },
    requestCache: function(options) {
        var cache;
        if(!options) return null;
        if(options.target && options.cache) {
            cache = options.target.retrieve('request:cache');
            if(cache) return cache.success(cache.response.text);
        }
        cache = new Request.HTML(options).send();
        options.target && options.target.store('request:cache', cache);
        return cache;
    },
    toElement: function() {
        return this.container;
    }
});

// pin
Element.implement({
    pin: function(enable, forceScroll, restore){
        //if(this.getStyle('display') == 'none') this.setStyle('display', '');
        if (enable !== false){
            if (!this.retrieve('pin:_pinned')){
                var scroll = window.getScroll();
                this.store('pin:_original', this.getStyles('position', 'top', 'left'));
                var pinnedPosition = this.getPosition(!Browser.ie6 ? document.body : this.getOffsetParent());
                var currentPosition = {
                    left: pinnedPosition.x - scroll.x,
                    top: pinnedPosition.y - scroll.y
                };
                if (!Browser.ie6){
                    this.setStyle('position', 'fixed').setStyles(currentPosition);
                } else {
                    if(!!forceScroll) this.setPosition({
                        x: this.getOffsets().x + scroll.x,
                        y: this.getOffsets().y + scroll.y
                    });
                    if (this.getStyle('position') == 'static') this.setStyle('position', 'absolute');

                    var position = {
                        x: this.getLeft() - scroll.x,
                        y: this.getTop() - scroll.y
                    };
                    var scrollFixer = function(){
                        if (!this.retrieve('pin:_pinned') || this.getStyle('left').toInt() >= document.body.clientWidth || this.getStyle('top').toInt() >= document.body.clientHeight) return;
                        var scroll = window.getScroll();
                        this.setStyles({
                            left: position.x + scroll.x,
                            top: position.y + scroll.y
                        });
                    }.bind(this);

                    this.store('pin:_scrollFixer', scrollFixer);
                    window.addEvent('scroll', scrollFixer);
                }
                this.store('pin:_pinned', true);
            }
        } else {
            if (!this.retrieve('pin:_pinned')) return this;
            if(!!restore) this.setStyles(this.retrieve('pin:_original', {}));
            this.eliminate('pin:_original');
            this.store('pin:_pinned', false);
            if (Browser.ie6) {
                window.removeEvent('scroll', this.retrieve('pin:_scrollFixer'));
                this.eliminate('pin:_scrollFixer');
            }
        }
        return this;
    },
    togglePin: function(){
        return this.pin(!this.retrieve('pin:_pinned'));
    }
});

//Mask
var Mask = new Class({
    Implements: [Options, Events],
    options: {
        target:null,
        'class': 'mask',
        width: 0,
        height: 0,
        effect: {
            style: 'opacity',
            duration: 400,
            from: 0,
            to: 0.3
        }
    },
    initialize: function(options) {
        this.target = (options && $(options.target)) || $(document.body);
        //this.target.store('mask', this);
        this.setOptions(options);

        this.element = $$('div[rel=mask].' + this.options['class'])[0] || new Element('div[rel=mask].' + this.options['class']).inject(this.target == window ? document.body : this.target);
        this.hidden = true;
    },
    setSize: function() {
        this.element.setStyles({
            width: this.options.width.toInt() || Math.max(this.target.getScrollSize().x, this.target.getSize().x, this.target.clientWidth || 0),
            height: this.options.height.toInt() || Math.max(this.target.getScrollSize().y, this.target.getSize().y, this.target.clientHeight || 0)
        });
    },
    show: function() {
        if (!this.hidden) return;
        if(this.target == window) {
            document.html.setStyles('height:100%;overflow:hidden;');
        }
        window.addEvent('resize', this.setSize.bind(this));
        this.setSize();

        this.element.setStyle('display','block');
        var effect = this.options.effect;
        if(effect) {
            // this.opacity = this.element.get('opacity');
            if(effect === true || effect.style == 'opacity') this.element.setStyle('opacity', effect.from || 0).setStyle('visibility','visible');
            new Fx.Tween(this.element,{duration: effect.duration || 400}).start(effect.style || 'opacity', effect.from || 0, effect.to);
        }
        else if(this.element.get('opacity') === 0){
            this.element.set('opacity', '').setStyle('visibility', '');
        }
        this.hidden = false;
        return this;
    },
    hide: function() {
        if (this.hidden) return;
        window.removeEvent('resize', this.setSize.bind(this));

        var effect = this.options.effect;
        if(effect) {
            new Fx.Tween(this.element, {
                duration:effect.duration || 400,
                onComplete: function(){
                    this.element.setStyle('display','none');
                }.bind(this)
            }).start(effect.style || 'opacity', effect.to, effect.from || 0);
        }
        else {
            this.element.setStyle('display','none');
        }
        if(this.target == window) {
            document.html.setStyles('');
        }
        this.hidden = true;
        return this;
    },
    toggle: function() {
        return this[this.hidden ? 'show' : 'hide']();
    }
});

var Ex_Dialog = new Class({
    Extends: Popup,
    initialize: function(target,options){
        options = Object.merge({
            width:330,
            useIframeShim: true,
            template: $('popup-template'),
            position: {
                intoView: true
            }
        }, options || {});
        this.parent(target,options);
    }
});
Ex_Dialog.alert = function(msg, title) {
    var html = '<div class="message-main"><div class="figure"><dfn class="alert">alert!</dfn><span class="mark">' + msg + '</span></div> <div class="bottom"> <button type="button" class="popup-btn-close"><i><i>确定</i></i></button> </div></div>';
    new Ex_Dialog(new Element('div', {html: html}), {
        width: 400,
        title: title || '请注意',
        modal: window,
        pins: true,
        single: false,
        effect: false,
        position: {
            intoView: true
        }
    });
};
Ex_Dialog.confirm = function(msg, callback) {
    var html = '<div class="message-main"><div class="figure"><dfn class="confirm">confirm!</dfn><span class="mark">' + msg + '</span></div> <div class="bottom"><button type="button" class="btn-confirm" data-return="1"><i><i>确认</i></i></button>　 <button type="button" class="btn-cancel" data-return="0"><i><i>取消</i></i></button></div></div>';
    new Ex_Dialog(new Element('div', {html: html}), {
        width: 400,
        title: '请确认',
        modal: window,
        pins: true,
        single: false,
        effect: false,
        position: {
            intoView: true
        },
        onLoad: function() {
            var _this = this, _return;
            this.content.getElements('[data-return]').addEvent('click', function(e){
                _return = !!this.get('data-return').toInt();
                _this.hide();
                callback && callback.call(this, _return);
            });
        }
    });
};

//Tips
var Ex_Tip = new Class({
    Extends: Popup,
    /*options: {
        intoView: true,
        onShow: function(){},
        onClose: function(){}
    },*/
    initialize: function(msg,options){
        if(!msg) return;
        options = options || {};
        var target = new Element('div[html=' + msg + ']');
        var relative = options.relative || document.body,
            rel = (/^(?:body|html)$/i).test(relative.tagName.toLowerCase()),
            x = rel ? 'center' : 0,
            y = rel ? 0 : 'top',
            pins = !!rel,
            offsetY = rel ? 0 : 'bottom';

        this.options = Object.merge(this.options, {
            type: options.type || 'nofoot',
            template: options.template || $('xtip-template'),
            modal: false,
            pins: pins,
            single: false,
            effect: true,
            position: {
                target: relative,
                to: {x: x, y: y},
                base: {x: 0, y: offsetY},
                offset: {
                    x: options.offset && options.offset.x ? options.offset.x : 0,
                    y: options.offset && options.offset.y ? options.offset.y : 0
                },
                intoView: options.intoView !== undefined ? options.intoView : true
            },
            component: {
                container: 'xtip-container',
                body: 'xtip-body',
                header: 'xtip-header',
                close: 'xtip-close',
                content: 'xtip-content'
            }
        });
        this.parent(target, options);
    }
});

/*
 * tooltips,需要在元素上添加自定义属性"data-tip"
 */
var Ex_Tips = function(elements, msg) {
    elements = $(elements) || $$('[data-tip]');
    if(!elements || !elements.length) return null;
    //build elements
    var container = $('xtips-container') || new Element('div', {
        html: '<div id="xtips-container" class="xtips-container"><i class="xtips-arr">◆</i><i class="xtips-arr2">◆</i><div id="xtips-content"></div></div>'
    }).getFirst().inject(document.body);
    var content = $('xtips-content');

    return elements.addEvents({
        mouseenter: function() {
            var text = msg || this.get('data-tip') || this.retrieve('tip:text');
            if(!text) return;
            content.set('text', text); // set message
            //position it and set width?
            var position = this.getPosition(),
                size = container.getSize();
            container.setStyle('display', 'block').setStyles({
                left: Math.max(position.x - 4, 0),
                top: Math.max(position.y - container.getSize().y - 6, 0),
                width: this.get('data-tip-width') ? this.get('data-tip-width') : container.getSize().x > window.getSize().x ? window.getSize().x : '',
                opacity: 0
            }).tween('opacity', 0, Browser.ie6 ? 1 : 0.95);
        },
        mouseleave: function() {
            container.tween('opacity', Browser.ie6 ? 1 : 0.95, 0);
        }
    });
};
Element.implement({
    tips: function(msg){
        return Ex_Tips(this, msg);
    }
});
window.addEvent('domready', function(){
    Ex_Tips();
});

//Message box
function Message(msg, type, delay, callback, template){
    if(!msg) return null;
    if(typeOf(type) === 'number') {
        delay = type;
        type = 'show';
    }
    else if(typeOf(delay) === 'function') {
        callback = delay;
        delay = 3;
    }
    else {
        type = type || 'show';
        delay = delay && delay.toInt() ? delay.toInt() : 3;
    }
    var component = {
        container: type +'-message',
        body: type +'-message-body',
        content: type +'-message-content'
    };
    new Popup(new Element('div[html=' + msg + ']'), {
        type: 'nohead',
        template: template || $('message-template'),
        modal: false,
        pins: true,
        single: false,
        effect: true,
        autoHide: delay,
        component: component,
        onClose: typeOf(callback) === 'function' ? callback.bind(this) : function() {}
    });
    return (type == 'error' ? false : true);
}
Message.show = function(msg, delay, callback) {
    Message(msg || LANG_jstools['messageShow'], 'show', delay, callback);
};
Message.error = function(msg, delay, callback) {
    return Message(msg || LANG_jstools['messageError'], 'error', delay, callback);
};
Message.success = function(msg, delay, callback) {
    return Message(msg || LANG_jstools['messageSuccess'], 'success', delay, callback);
};

//Dropmenu
var Dropmenu = new Class({
    Extends: Popup,
    initialize: function(content, options){
        if(!content) return;
        var offset = {x: 0, y: 0};
        options = Object.merge({
            type: 'nohead',
            template: $('dropmenu-template'),
            position: {
                target: $(options.relative) || document.body,
                base: {x: 0, y: 0},
                to: {x: 0, y: 'bottom'},
                intoView: 'in'
            },
            component: {
                container: 'dropmenu-container',
                body: 'dropmenu-body',
                content: 'dropmenu-content'
            },
            useIframeShim: true
        }, options || {});

        this.parent($(content), options);
    }
});
function dropMenu(el, content, options) {
    if(!$(el) || !content) return;
    options = options || {};
    switch (options.eventType) {
    case 'click':
        el.addEvent(options.eventType, function(){
            if(!this.retrieve('_dropmenu_')){
                var menu = new Dropmenu(content, options);
                menu.container.setStyle('font-size', '14px');
                this.store('_dropmenu_', menu);
            }
            else {
                this.retrieve('_dropmenu_').hide();
                this.eliminate('_dropmenu_');
            }
        });
        break;
    case 'mouseover':
    default:
        el.addEvents({
            'mouseover': function(){
                var menu = new Dropmenu(content, options);
                menu.container.setStyle('font-size', '14px');
                this.store('_dropmenu_', menu);
            },
            'mouseout': function(){
                this.retrieve('_dropmenu_').hide();
                this.eliminate('_dropmenu_');
            }
        });
        break;
    }
}
