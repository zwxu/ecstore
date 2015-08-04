(function(){Element.implement({delegate:function(a,c,b,e,d){this.addEvent(c,function(c){var f=$(c.target),k=this.getElements(a);null!=d&&Array.from(d);k.contains(f)&&b.apply(e,null==d?[f,c]:d.concat([f,c]))})}});var f=this.ExMvc={},b={_create:function(){return"function"!==typeof Object.create||Browser.ie?function(a){function c(){}c.prototype=a;return new c}(this):Object.create(this)},extend:function(a){return Object.append(this._create(),a||{})},initance:function(){var a=this._create();a.init&&a.init.apply(a,
arguments);return a}},e={map:{add:"post",update:"put",remove:"delete",get:"get"},getUrl:function(a,c,b){var e=["-",".html"],d,h;if(a)return h=a[1],a[1]=a[1].replace(/\{(.*)\}/,b),d=a.join(e[0]),d=-1<"add get".indexOf(b)?a[0]+e[0]+a[1]:d.replace(/\{[^\}]+\}/g,function(a){var b=a.substring(1,a.length-1);return c[b]?c[b]:a}),a[1]=h,d+e[1]},sync:function(a,c,b){var d=a.toJSON();b.url=b.url||e.getUrl(b.route,d,c);!b.data&&(a&&-1<"add update".indexOf(c))&&(b.data=Object.toQueryString(d));b.method=b.method||
e.map[c];b.secure=!1;(new Request.JSON(b)).send()}},b=b.extend(new Events).extend(new Options),d=f.Records=b.extend({idAttribute:"id",init:function(a,c){this.attributes={};this.cid=Slick.uidOf(this.idAttribute);if((c=this.setOptions(c).options)&&c.idAttribute)this.idAttribute=c.idAttribute;a&&this.set(a);c.model&&(this.model=c.model);this.fireEvent("init")},set:function(a){if(!a)return this;a.attributes&&(a=a.attributes);this.fireEvent("check",a);if(this.checked)return!1;this.idAttribute in a&&(this.id=
a[this.idAttribute]);for(var c in a){var b=a[c];this.attributes[c]=b;this.fireEvent("change:"+c,b)}this.fireEvent("set",a);return this},unset:function(a){if(!(a in this.attributes))return this;this.fireEvent("check",this.attributes[a]);if(this.checked)return!1;delete this.attributes[a];a==this.idAttribute&&delete this.id;this.fireEvent("change:"+a).fireEvent("unset");return this},clear:function(){this.fireEvent("check");if(this.checked)return!1;this.attributes={};return this},isNew:function(){return!this.id},
has:function(a){return!!this.get(a)},get:function(a){return this.attributes[a]},toJSON:function(){return Object.clone(this.attributes)},sync:function(a,c){a||(a={});var b=a.onComplete;a.onComplete=function(a){if("remove"===c)this.fireEvent("destroy",[a,this.model]);else if(!this.set(a))return!1;b&&b(a,this)}.bind(this);a=Object.append(this.options,a);a.onError&&(a.onError=a.onError.bind(this));(f.Sync||e.sync)(this,c,a)},fetch:function(a){return this.sync(a,"get")},save:function(a,c){if(a&&!this.set(a,
c))return!1;var b=this.isNew()?"add":"update";return this.sync(c,b)},destroy:function(a){return this.sync(a,"remove")}});f.Model=b.extend({options:{record:d},init:function(a,c){c=this.setOptions(c).options;this.idAttribute=c.idAttribute;this.record=c.record;this._reset();a&&this.refresh(a);this.fireEvent("init",c)},_reset:function(){this.length=0;this.records=[];this._byId={};this._byCid={};return this},_removeReference:function(a){this==a.model&&delete a.model;return this},toJSON:function(){return Object.map(this.records,
function(a){return a.toJSON()})},refresh:function(a){Object.each(this.records,this._removeReference,this);this._reset().add(a);this.fireEvent("refresh");return this},add:function(a,c){"array"===typeOf(a)?Array.each(a,function(a){this._add(a,c)},this):this._add(a,c);return this},_add:function(a,c){a.idAttribute||(a=this.record.initance(a,{model:this,idAttribute:this.idAttribute}));var b=this.getByCid(a);if(b)throw Error(["Can't add the same model to a set twice",b.id]);this._byId[a.id]=a;this._byCid[a.cid]=
a;a.model||(a.model=this);this.records.splice(this.length,0,a);this.length++;this.fireEvent("addRecord",[a,c]);return a},remove:function(a,c){"array"===typeOf(a)?Array.each(a,function(a){this._remove(a,c)},this):this._remove(a);return this},_remove:function(a,c){delete this._byId[a.id];delete this._byCid[a.cid];this.records.splice(this.records.indexOf(a),1);this.length--;this.fireEvent("removeRecord",[a,c]);this._removeReference(a);return a},get:function(a){return null==a?null:this._byId[null!=a.id?
a.id:a]},getByCid:function(a){return a&&this._byCid[a.cid||a]},clear:function(){for(var a=this.records.length-1;0<=a;a--)this._remove(this.records[a]);return this},fetch:function(a){a||(a={});var c=a.onComplete,b=a.add?"add":"get";a.onComplete=function(b){this[a.add?"add":"refresh"](b,a);c&&c(b);this.fireEvent("getRecord")}.bind(this);a=Object.append(this.options,a);a.onError&&(a.onError=a.onError.bind(this));(f.Sync||e.sync)(this,b,a)},create:function(a,c){c||(c={});if(a.idAttribute)a.model=this;
else{var b=a,a=this.record.initance(null,{model:this,idAttribute:this.idAttribute});if(!a.set(b))return!1}var e=c.onComplete;c.onComplete=function(c){this.add(c);e&&e(c);this.fireEvent("createRecord",a)}.bind(this);a.save(null,c);return a}});f.View=b.extend({options:{config:{tagName:"div",attrs:{"class":"ba"}}},init:function(a,c){a=this.setOptions(a).options;this.el=a.el?$(a.el):this.make(a.config);this.view_id=a.record_id;this.contains=$(a.contains);this.tpl=a.tpl;a.events&&this.delegateEvents(a.events,
c);a.attrs&&this.render(a.attrs);this.fireEvent("init")},make:function(a){a.attrs||(a.attrs={});a.content||(a.content="");return(new Element(a.tagName,a.attrs)).set("html",a.content)},delegateEvents:function(a,c){var b=/^(\w+)\s*(.*)$/,c=c||this;Object.each(a,function(a,e){var d=e.match(b),f=$(this.el),k=d[1],d=d[2],g="function"==typeOf(a)?a:c[a];""===d?g&&f.addEvent(k,function(a){g.call(c,this,a)}):g&&f.delegate(d,k,g,c,[this])},this);return this},add:function(a){this.fireEvent("addView",a);this.contains&&
this.render(a).el.inject(this.contains);return this},remove:function(){$(this.el).destroy();this.fireEvent("removeView");return this},getTpl:function(a){a=a||{};this.fireEvent("tpl");return this.template=Mustache.to_html(this.tpl,a).replace(/^\s*/mg,"")},render:function(a){a&&a.idAttribute&&(a=a.toJSON());this.el.set("html",this.getTpl(a));this.fireEvent("render",a);return this}});f.Controller=b.extend({options:{model:f.Model,view:f.View},create:function(a,c){record=this.getRecord(a.view_id);record.unset("gid");
this.add(record.toJSON(),c)},add:function(a,c){c=this.getOpt(c);this.model.create(a,Object.append(c.modelOpt,{route:this.route,onComplete:function(a){c.viewOpt.record_id=a[this.model.idAttribute];this.addView(a,c.viewOpt);this.fireEvent("add",a)}.bind(this)}));return this},remove:function(a,c){var c=this.getOpt(c),b=this.model.get(a.view_id);b&&b.destroy(Object.append(c.modelOpt,{route:this.route,onComplete:function(d){a.remove();this.model.remove(b,c.modelOpt);delete this.viewItem[a.view_id];this.fireEvent("remove",
d)}.bind(this)}));return this},init:function(a){a=Object.append(this.options,a);this.view=a.view;this.route=a.modelOpt.route;this.viewItem={};this.model=this.options.model.initance(null,a.modelOpt)},updateAttribute:function(a,c){var b=this.getRecord(a.view_id),d={};d[c.name]=c.value;b.set(d);this.update(a)},getRecord:function(a){return this.model.get(a)},update:function(a,b){var b=this.getOpt(b),d=this.getRecord(a.view_id);b.modelOpt.onComplete=function(b){a.render(b);this.fireEvent("update",b)};
b.modelOpt.route=b.route||this.route;d.save(d,b.modelOpt)},getOpt:function(a){a=a||{};return{modelOpt:Object.append(this.options.modelOpt,a.modelOpt||{}),viewOpt:Object.append(this.options.viewOpt,a.viewOpt||{})}},fetch:function(a){a=this.getOpt(a);a.modelOpt=Object.append({onComplete:function(b){this.getView(this.model,a.viewOpt);this.fireEvent("fetch",b)}.bind(this)},a.modelOpt);this.model.fetch(a.modelOpt);return this},clear:function(){this.viewItem&&Object.each(this.viewItem,function(a){this.remove(a)},
this);return this},addView:function(a,b){var d=this.view.initance(b,this).add(a);return this.viewItem[b.record_id]=d},getView:function(a,b){a&&Object.each(a._byId,function(a,d){b.record_id=d;this.addView(a.toJSON(),b)},this)}})})();
var Mustache=function(){var f=function(){};f.prototype={otag:"{{",ctag:"}}",pragmas:{},buffer:[],pragmas_implemented:{"IMPLICIT-ITERATOR":!0},context:{},render:function(b,e,d,a){a||(this.context=e,this.buffer=[]);if(this.includes("",b)){b=this.render_pragmas(b);b=this.render_section(b,e,d);if(a)return this.render_tags(b,e,d,a);this.render_tags(b,e,d,a)}else{if(a)return b;this.send(b)}},send:function(b){""!=b&&this.buffer.push(b)},render_pragmas:function(b){if(!this.includes("%",b))return b;var e=
this;return b.replace(RegExp(this.otag+"%([\\w-]+) ?([\\w]+=[\\w]+)?"+this.ctag),function(b,a,c){if(!e.pragmas_implemented[a])throw{message:"This implementation of mustache doesn't understand the '"+a+"' pragma"};e.pragmas[a]={};c&&(b=c.split("="),e.pragmas[a][b[0]]=b[1]);return""})},render_partial:function(b,e,d){b=this.trim(b);if(!d||void 0===d[b])throw{message:"unknown_partial '"+b+"'"};return"object"!=typeof e[b]?this.render(d[b],e,d,!0):this.render(d[b],e[b],d,!0)},render_section:function(b,
e,d){if(!this.includes("#",b)&&!this.includes("^",b))return b;var a=this;return b.replace(RegExp(this.otag+"(\\^|\\#)\\s*(.+)\\s*"+this.ctag+"\n*([\\s\\S]+?)"+this.otag+"\\/\\s*\\2\\s*"+this.ctag+"\\s*","mg"),function(b,f,j,i){b=a.find(j,e);if("^"==f)return!b||a.is_array(b)&&0===b.length?a.render(i,e,d,!0):"";if("#"==f)return a.is_array(b)?a.map(b,function(b){return a.render(i,a.create_context(b),d,!0)}).join(""):a.is_object(b)?a.render(i,a.create_context(b),d,!0):"function"===typeof b?b.call(e,i,
function(b){return a.render(b,e,d,!0)}):b?a.render(i,e,d,!0):""})},render_tags:function(b,e,d,a){for(var c=this,f=function(){return RegExp(c.otag+"(=|!|>|\\{|%)?([^\\/#\\^]+?)\\1?"+c.ctag+"+","g")},j=f(),i=function(a,b,g){switch(b){case "!":return"";case "=":return c.set_delimiters(g),j=f(),"";case ">":return c.render_partial(g,e,d);case "{":return c.find(g,e);default:return c.escape(c.find(g,e))}},b=b.split("\n"),h=0;h<b.length;h++)b[h]=b[h].replace(j,i,this),a||this.send(b[h]);if(a)return b.join("\n")},
set_delimiters:function(b){b=b.split(" ");this.otag=this.escape_regex(b[0]);this.ctag=this.escape_regex(b[1])},escape_regex:function(b){arguments.callee.sRE||(arguments.callee.sRE=RegExp("(\\/|\\.|\\*|\\+|\\?|\\||\\(|\\)|\\[|\\]|\\{|\\}|\\\\)","g"));return b.replace(arguments.callee.sRE,"\\$1")},find:function(b,e){var b=this.trim(b),d;if(!1===e[b]||0===e[b]||e[b])d=e[b];else if(!1===this.context[b]||0===this.context[b]||this.context[b])d=this.context[b];return"function"===typeof d?d.apply(e):void 0!==
d?d:""},includes:function(b,e){return-1!=e.indexOf(this.otag+b)},escape:function(b){return(""+(null===b?"":b)).replace(/&(?!\w+;)|["'<>\\]/g,function(b){switch(b){case "&":return"&amp;";case "\\":return"\\\\";case '"':return"&quot;";case "'":return"&#39;";case "<":return"&lt;";case ">":return"&gt;";default:return b}})},create_context:function(b){if(this.is_object(b))return b;var e=".";this.pragmas["IMPLICIT-ITERATOR"]&&(e=this.pragmas["IMPLICIT-ITERATOR"].iterator);var d={};d[e]=b;return d},is_object:function(b){return b&&
"object"==typeof b},is_array:function(b){return"[object Array]"===Object.prototype.toString.call(b)},trim:function(b){return b.replace(/^\s*|\s*$/g,"")},map:function(b,e){if("function"==typeof b.map)return b.map(e);for(var d=[],a=b.length,c=0;c<a;c++)d.push(e(b[c]));return d}};return{name:"mustache.js",version:"0.3.1-dev",to_html:function(b,e,d,a){var c=new f;a&&(c.send=a);c.render(b,e,d);if(!a)return c.buffer.join("\n")}}}();