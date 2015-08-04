(function(){
Element.implement({
    delegate:function(selector,eventName,method,bind,args){
        this.addEvent(eventName,function(e){
            var target = $(e.target),els = this.getElements(selector);
            args!=null && Array.from(args);
            if(els.contains(target))
            method.apply(bind,args ==null? [target,e] : args.concat([target,e]));
        });
    }
});

var ExMvc = this.ExMvc ={},
Ex_Class = {
    _create:function(){
        return (typeof Object.create !== "function" || Browser.ie) ?
        (function(o) {
            function F() {}
            F.prototype = o;
            return new F();
        })(this) : Object.create(this);
    },
    extend :function(extend){
        return Object.append(this._create(),extend||{});
    },
    initance:function(){
        var initance = this._create();
        initance.init && initance.init.apply(initance, arguments);
        return initance;
    }
},
Helper = {
    map:{
        'add':'post',
        'update':'put',
        'remove':'delete',
        'get':'get'
    },
    getUrl : function(route,obj,type){
        var t = ['-','.html'], url, tmp ;

        if(!route)return;
        tmp = route[1];
        route[1] = route[1].replace(/\{(.*)\}/,type);
        url = route.join(t[0]);

        if('add get'.indexOf(type)>-1) url = route[0]+t[0]+ route[1];
        else url = url.replace(/\{[^\}]+\}/g,function(arg1){
                var str = arg1.substring(1,arg1.length-1);
                return !!obj[str] ? obj[str] : arg1;
             });

        route[1] = tmp ;
        return url + t[1];
    },
    sync:function(obj,type,options){
        var json = obj.toJSON(),sync;
        options.url =options.url || Helper.getUrl(options.route,json,type);

        if (!options.data && obj && ('add update'.indexOf(type)>-1)) {
            options.data = Object.toQueryString(json);
        }
        options.method = options.method || Helper.map[type];
        options.secure = false;

        if(sync && sync.running)sync.cancel();
        sync = new Request.JSON(options).send();
    }
};

Ex_Class = Ex_Class.extend(new Events).extend(new Options);

var Records = ExMvc.Records = Ex_Class.extend({
    idAttribute : 'id',
    init:function(attrs,options){
        this.attributes = {};
        //this.cid = $uid(this.idAttribute);
        this.cid = Slick.uidOf(this.idAttribute);
        options = this.setOptions(options).options;
        if(options && options.idAttribute)
        this.idAttribute = options.idAttribute;

        attrs && this.set(attrs);
        if (options.model) this.model= options.model;
        this.fireEvent('init');
    },
    set:function(attrs){
        if (!attrs) return this;
        if (attrs.attributes) attrs = attrs.attributes;

        this.fireEvent('check',attrs);
        if (this.checked) return false;

        if (this.idAttribute in attrs)
        this.id = attrs[this.idAttribute];

        for(var name in attrs){
            var val = attrs [name];
            this.attributes[name] = val;
            this.fireEvent('change:'+name,val);
        }
        this.fireEvent('set',attrs);
        return this;
    },
    unset:function(attr) {
        if (!(attr in this.attributes)) return this;

        this.fireEvent('check',this.attributes[attr]);
        if (this.checked) return false;

        delete this.attributes[attr];
        if (attr == this.idAttribute) delete this.id;
        this.fireEvent('change:'+attr).fireEvent('unset');
        return this;
    },
    clear:function(){
        this.fireEvent('check');
        if (this.checked) return false;
        this.attributes = {};
        return this;
    },
    isNew:function() {return !this.id;},

    has:function(attr) {return !!this.get(attr);},

    get:function(attr){return this.attributes[attr];},

    toJSON:function(){return Object.clone(this.attributes);},

    sync:function(options,type){
        options || (options = {});
        var complete= options.onComplete;
        options.onComplete= function(resp) {
            if(type==='remove'){
                this.fireEvent('destroy',[resp,this.model]);
            }else {if (!this.set(resp))return false;}
            complete && complete(resp,this);
        }.bind(this);
        options = Object.append(this.options,options);
        if(options.onError) options.onError = options.onError.bind(this);
        (ExMvc.Sync || Helper.sync)(this,type,options);
    },
    fetch:function(options){
        return this.sync(options,'get');
    },
    save:function(attrs,options){
        if (attrs && !this.set(attrs, options)) return false;
        var method = this.isNew() ? 'add' : 'update';
        return this.sync(options,method);
    },
    destroy:function(options) {
        return this.sync(options,'remove');
    }
});

var Model = ExMvc.Model = Ex_Class.extend({
    options:{ record:Records },
    init:function(records,options){
        options = this.setOptions(options).options;
        this.idAttribute = options.idAttribute;
        this.record= options.record;
        this._reset();
        if(records)this.refresh(records);
        this.fireEvent('init',options);
    },
    _reset:function(options) {
        this.length = 0;
        this.records= [];
        this._byId  = {};
        this._byCid = {};
        return this;
    },
    _removeReference:function(record) {
        if (this == record.model) delete record.model;
        return this;
    },
    toJSON:function(){
        return Object.map(this.records,function(record){return record.toJSON();});
    },
    refresh: function(records,options){
        Object.each(this.records,this._removeReference,this);
        this._reset().add(records);
        this.fireEvent("refresh");
        return this;
    },
    add:function(records,options){
        if(typeOf(records)==='array'){
            Array.each(records,function(record){
                this._add(record,options);
            },this);
        }else{this._add(records,options);}
        return this;
    },
    _add:function(record,options){
        if(!record.idAttribute)
        record = this.record.initance(record,{model:this,idAttribute:this.idAttribute});

        var already = this.getByCid(record);
        if (already) throw new Error(["Can't add the same model to a set twice", already.id]);

        this._byId[record.id] = record;
        this._byCid[record.cid] = record;
        if (!record.model) record.model= this;

        this.records.splice(this.length, 0, record);
        this.length++;
        this.fireEvent("addRecord",[record,options]);
        return record;
    },
    remove:function(records,options){
        if(typeOf(records)==='array'){
            Array.each(records,function(record){
                this._remove(record,options);
            },this);
        }else {this._remove(records);}
        return this;
    },
    _remove:function(record,options){
        delete this._byId[record.id];
        delete this._byCid[record.cid];
        this.records.splice(this.records.indexOf(record), 1);
        this.length--;
        this.fireEvent("removeRecord",[record,options]);
        this._removeReference(record);
        return record;
    },
    get:function(id) {
        if (id == null) return null;
        return this._byId[id.id != null ? id.id : id];
    },
    getByCid:function(cid) {
        return cid && this._byCid[cid.cid || cid];
    },
    clear:function(){
        var len =this.records.length-1;
        for(;len>=0;len--) this._remove(this.records[len]);
        return this;
    },
    fetch:function(options) {
        options || (options = {});
        var complete = options.onComplete,type = options.add ? 'add':'get';
        options.onComplete = function(resp) {
            this[options.add ? 'add' : 'refresh'](resp,options);
            complete && complete(resp);
            this.fireEvent('getRecord');
        }.bind(this);
        options = Object.append(this.options,options);
        if(options.onError) options.onError = options.onError.bind(this);
        (ExMvc.Sync || Helper.sync)(this,type,options);
    },
    create:function(record,options){
        options || (options = {});
        if (!record.idAttribute) {
            var attrs = record;
            record= this.record.initance(null,{model:this,idAttribute:this.idAttribute});
            if (!record.set(attrs)) return false;
        } else {record.model= this;}
        var complete = options.onComplete;
        options.onComplete= function(resp) {
            this.add(resp);
            complete && complete(resp);
            this.fireEvent('createRecord',record);
        }.bind(this);
        record.save(null, options);
        return record;
    }
});

var View = ExMvc.View = Ex_Class.extend({
    options:{
        config:{tagName:'div', attrs:{'class':'ba'}}
    },
    init:function(options,bind){
        options = this.setOptions(options).options
        this.el = options.el ? $(options.el) : this.make(options.config);
        this.view_id = options.record_id;
        this.contains = $(options.contains);
        this.tpl = options.tpl;
        options.events && this.delegateEvents(options.events,bind);
        options.attrs && this.render(options.attrs);
        this.fireEvent('init');
    },
    make:function(tag) {
        tag.attrs || (tag.attrs = {});
        tag.content || (tag.content='');
        return new Element(tag.tagName,tag.attrs).set('html',tag.content);
    },
    delegateEvents: function(events,bind){
        var eventSplitter = /^(\w+)\s*(.*)$/, bind = bind || this;

        Object.each(events,function(method,key){
            var match = key.match(eventSplitter),elem = $(this.el),
                eventName = match[1], selector = match[2],
                fn =typeOf(method)=='function'? method :bind[method];
            if (selector === '') {
                fn && elem.addEvent(eventName,function(e){fn.call(bind,this,e);});
            } else {
                fn && elem.delegate(selector, eventName, fn, bind,[this]);
            }
        },this);
        return this;
    },
    add:function(attrs){
        this.fireEvent('addView',attrs);
        this.contains && this.render(attrs).el.inject(this.contains);
        return this;
    },
    remove: function(options){
        $(this.el).destroy();
        this.fireEvent('removeView');
        return this;
    },
    getTpl:function(attr){
        var json = attr||{};
        this.fireEvent('tpl');
        this.template = Mustache.to_html(this.tpl,json).replace(/^\s*/mg, '');
        return this.template;
    },
    render:function(attr){
        if(attr && attr.idAttribute)attr = attr.toJSON();
        this.el.set('html',this.getTpl(attr));
        this.fireEvent('render',attr);
        return this;
    }
});

var Controller = ExMvc.Controller = Ex_Class.extend({
    options:{
        model:ExMvc.Model,
        view:ExMvc.View
    },
    create:function(view,options){
        record = this.getRecord(view.view_id);
        record.unset('gid');
        this.add(record.toJSON(),options);
    },
    add:function(json,options){
        options = this.getOpt(options);
        this.model.create(json,Object.append(options.modelOpt,
            {route:this.route, onComplete:function(resp){
                options.viewOpt.record_id = resp[this.model.idAttribute];
                this.addView(resp,options.viewOpt);
                this.fireEvent('add',resp);
            }.bind(this)}));
        return this;
        this.fireEvent('add');
    },
    remove:function(view,options){
        options = this.getOpt(options);
        var record =this.model.get(view.view_id);
        record && record.destroy(Object.append(options.modelOpt,
            {route:this.route,onComplete:function(resp){
                view.remove();
                this.model.remove(record,options.modelOpt);
                delete this.viewItem[view.view_id];
                this.fireEvent('remove',resp);
            }.bind(this)}));
        return this;
    },
    init:function(options){
        options = Object.append(this.options,options);
        this.view = options.view;
        this.route = options.modelOpt.route;
        this.viewItem = {};
        this.model = this.options.model.initance(null,options.modelOpt);
    },
    updateAttribute:function(view,el){
        var record = this.getRecord(view.view_id),attr = {};
        attr[el.name] = el.value;
        record.set(attr);
        this.update(view);
    },
    getRecord:function(id){return this.model.get(id);},
    update:function(view,options){
        options = this.getOpt(options);
        var record =this.getRecord(view.view_id);
        options.modelOpt.onComplete= function(resp){
            view.render(resp);
            this.fireEvent('update',resp);
        };
        options.modelOpt.route= options.route || this.route;
        record.save(record,options.modelOpt);
    },
    getOpt:function(options){
       options = options || {};
       return {modelOpt:Object.append(this.options.modelOpt,options.modelOpt || {}),
               viewOpt:Object.append(this.options.viewOpt,options.viewOpt || {})};
    },
    fetch:function(options){
        options = this.getOpt(options);
        options.modelOpt = Object.append({
            onComplete:function(resp){
                this.getView(this.model,options.viewOpt);
                this.fireEvent('fetch',resp);
            }.bind(this)},options.modelOpt);
        this.model.fetch(options.modelOpt);
        return this;
    },
    clear:function(model){
        this.viewItem && Object.each(this.viewItem,function(v){
            this.remove(v);
        },this);
        return this;
    },
    addView:function(json,options){
        var view =this.view.initance(options,this).add(json);
        this.viewItem[options.record_id] = view;
        return view;
    },
    getView:function(model,options){
        model && Object.each(model._byId,function(m,k){
            options.record_id = k;
            this.addView(m.toJSON(),options);
        },this);
    }
});

})();
/*
ExMvc.Sync=function(obj,type,options){
    var resp;
    switch (type) {
        case "get":  resp = 'a';
        break;
        case "add":  resp = 'a';break;
        case "update":  resp ='a';break;
        case "delete":  resp = 'k';break;
    }
    if (resp) { options.onSuccess(resp); }
};
var ie= ExMvc.Controller.initance({
    modelOpt:{
        route:a,
        onSuccess:function(){
            //var m = ie.model.length;
        },
        idAttribute:'gid'
    },
    viewOpt:{
        contains:$('goodsbody').getElement('tbody'),
        events:{
          'click .consume_score':'create',
        },
        tpl:$('tpl2').get('html'),
        config:{tagName:'tr', attrs:{'class':'havechild'}}
    }
}).fetch();

*/
