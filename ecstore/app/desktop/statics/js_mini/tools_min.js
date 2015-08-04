var MessageBox=new Class({Implements:[Events,Options],options:{element:"messagebox",type:"default",autohide:!1,type2class:{"default":"default",error:"exception",notice:"warning"}},initialize:function(b,a){this.setOptions(a);$clear(MessageBox.delay);this.options.element=$(this.options.element);for(e in this.options.element.$events)this.options.element.removeEvents(e);this.options.element.className=this.options.element.className.replace(/(default|warning|exception)/,"");this.options.element.set("html",
b);var d=this.options.autohide;d&&(d="number"==$type(d)?d:3E3,MessageBox.delay=this.hide.delay(d,this));return this.show()},show:function(){this.options.element.addClass(this.options.type2class[this.options.type]);return this.fireEvent("onShow",arguments)},hide:function(){this.options.element.removeClass(this.options.type2class[this.options.type]);return this.fireEvent("onHide",arguments)}});MessageBox.delay=0;
$extend(MessageBox,{error:function(b){new MessageBox(b,{type:"error",autohide:!0})},success:function(b){new MessageBox(b,{autohide:!0})},show:function(b){new MessageBox(b,{type:"notice",autohide:!0})}});
(function(){var b,a,d=function(f){return!f?void 0!=b?b:b=getTplById("dialog"):void 0!=a?a:a=getTplById("dialogwithframe")};Dialog=new Class({Implements:[Options,Events],options:{onShow:$empty,onHide:$empty,onClose:$empty,onLoad:$empty,callback:!1,iframe:!1,ajaksable:!0,width:700,height:400,dialogBoxWidth:2,title:"",dragable:!0,resizeable:!0,singlon:!0,modal:!0,ajaxoptions:{update:!1,evalScripts:!0,method:"get",autoCancel:!0,render:!0}},initialize:function(a,c){if(!(this.currentRegionDialogs=$$(".dialog")).some(function(c){if(c.retrieve("serial")==
a.toString().trim()&&"none"!=c.style.display)return c.inject(document.body),!0})){this.url=a;this.setOptions(c);var c=this.options,b=d(this.options.iframe);this.dialog=new Element("div",{id:"dialog_"+this.UID,"class":"dialog",styles:{visibility:"hidden",zoom:1,opacity:0,zIndex:65534}});this.UID=Slick.uidOf(this.dialog);this.dialog.set("id","dialog_"+this.UID).setHTML(b).inject(document.body).store("serial",a.toString().trim());this.options.callback&&this.dialog.store("callback",this.options.callback);
this.dialog_head=$E(".dialog-head",this.dialog).addEvent("click",function(){1<$$(".dialog").length&&this.inject(document.body)}.bind(this.dialog));this.dialog_body=$E(".dialog-content-body",this.dialog);this.setTitle(c.title);$E(".btn-close",this.dialog_head).addEvents({click:function(a){a&&(new Event(a)).stop();this.close()}.bind(this),mousedown:function(a){(new Event(a)).stop()}});c.dragable&&this.dragDialog();c.resizeable?this.dialog_body.makeResizable({handle:$E(".btn-resize",this.dialog),limit:{x:[200,
0.9*window.getSize().x],y:[100,Math.max(window.getSize().y,window.getScrollSize().y)]},onDrag:function(){this.setDialogWidth()}.bind(this)}):$E(".btn-resize",this.dialog).hide();c.ajaksable||(c.ajaxoptions.render=!1);$extend(c.ajaxoptions,{update:this.dialog_body,sponsor:!1,resizeupdate:!1,evalScripts:!1,onRequest:function(){}.bind(this),onFailure:function(){this.close();new MessageBox("\u5bf9\u8bdd\u6846\u52a0\u8f7d\u5931\u8d25",{type:"error",autohide:true})}.bind(this),onComplete:function(){this.fireEvent("onComplete",
$splat(arguments));this.showDialog.attempt(arguments,this)}.bind(this)});this.popup(a,c)}},popup:function(a,c){this.fireEvent("onShow",this);this.initContent(a,c)},initContent:function(a,c,b){var a=a||this.url,c=c||this.options,d=this,i;if(!b){var k=arguments.callee;this.reload=function(){k(a,c,!0)}}if(c.iframe){new MessageBox(LANG_Dialog.loading,{type:"notice"});if(c.ajaxoptions.data){var j=c.ajaxoptions.data;switch(typeOf(j)){case "element":j=document.id(j).toQueryString();break;case "object":case "hash":j=
Object.toQueryString(j)}i=new Element("form");i.adopt(j.toFormElements());i.set({id:"abbcc",action:a,method:c.ajaxoptions.method,target:this.dialog_body.name});i.injectAfter(this.dialog_body);return this.dialog_body.set("src","about:blank").addEvent("load",function(){d.showDialog.call(d,this);new MessageBox(LANG_Dialog.loading,{autohide:!0});this.removeEvent("load",arguments.callee);i.submit()})}return this.dialog_body.set("src",a).addEvent("load",function(){d.showDialog.call(d,this);new MessageBox(LANG_Dialog.success,
{autohide:!0});this.removeEvent("load",arguments.callee)})}if("element"==$type(a)){try{this.dialog_body.empty().adopt(a)}catch(l){this.dialog_body.setHTML(LANG_Dialog.error)}b||this.showDialog.call(this)}else W.page(a,c.ajaxoptions)},showDialog:function(a,c,d){a=$ES("[isCloseDialogBtn]",this.dialog);a.length&&a.addEvent("click",this.close.bind(this));(a=$E("form[isCloseDialog]",this.dialog))&&a.store("target",{onComplete:function(a){if(a){var c={};try{c=JSON.decode(a)}catch(d){}a=c.finder_id;c.error||
(this.close(),a&&finderGroup[a]&&finderGroup[a].refresh())}}.bind(this)});this.dialog.store("instance",this);this.setDialog_bodySize();(c=(a=this.currentRegionDialogs)?a.length:0)&&0<c?this.dialog.position($H(a[c-1].getPosition()).map(function(a){return a+20})).setOpacity(1):this.dialog.amongTo(window);this.options.modal&&MODALPANEL.show();$exec(d);this.fireEvent("onLoad",this)},close:function(){try{this.fireEvent("onClose",this.dialog)}catch(a){}$(this.dialog).destroy();$("dialogdragghost_"+this.UID)&&
$("dialogdragghost_"+this.UID).destroy();if(0<this.currentRegionDialogs.length)return!1;this.options.modal&&MODALPANEL.hide();return"nodialog"},hide:function(){this.fireEvent("onHide");this.close.call(this)},setDialog_bodySize:function(){this.options.height="string"==$type(this.options.height)?this.options.height.toInt():this.options.height;this.options.width="string"==$type(this.options.width)?this.options.width.toInt():this.options.width;var a=this.dialog.getElement(".dialog-content-head").getSize().y+
this.dialog.getElement(".dialog-content-foot").getSize().y;this.dialog_body.setStyles({height:1>this.options.height?this.options.height*window.getSize().y-a:this.options.height-a,width:1>this.options.width?this.options.width*window.getSize().x:this.options.width});this.setDialogWidth()},setDialogWidth:function(){this.dialog.setStyle("width",this.dialog_body.getSize().x+this.dialog.getElement(".dialog-box").getPatch().x)},setTitle:function(a){var c=this.dialog_head;!1===a?c.destroy():$E(".dialog-title",
c).set("html",a)},dragDialog:function(){var a=this.dialog,c=new Element("div",{id:"dialogdragghost_"+this.UID});c.setStyles({position:"absolute",cursor:"move",background:"#66CCFF",display:"none",opacity:0.3,zIndex:65535}).inject(document.body);this.addEvent("load",function(){c.setStyles(a.getCis())});new Drag(c,{handle:this.dialog_head,limit:{x:[0,window.getSize().x],y:[0,window.getSize().y]},onStart:function(){c.setStyles(a.getCis());c.show()},onComplete:function(){var d=c.getPosition();a.setStyles({top:d.y,
left:d.x});c.hide()}})}})})();
var validatorMap=new Hash({required:[LANG_Validate.required,function(b,a){return null!=a&&""!=a&&""!=a.trim()}],number:[LANG_Validate.number,function(b,a){return null==a||""==a||!isNaN(a)&&!/^\s+$/.test(a)}],digits:[LANG_Validate.digits,function(b,a){return null==a||""==a||!/[^\d]/.test(a)}],unsignedint:[LANG_Validate.unsignedint,function(b,a){return null==a||""==a||!/[^\d]/.test(a)&&0<a}],unsigned:[LANG_Validate.unsigned,function(b,a){return null==a||""==a||!isNaN(a)&&!/^\s+$/.test(a)&&0<=a}],positive:[LANG_Validate.positive,
function(b,a){return null==a||""==a||!isNaN(a)&&!/^\s+$/.test(a)&&0<a}],alpha:[LANG_Validate.alpha,function(b,a){return null==a||""==a||/^[a-zA-Z]+$/.test(a)}],alphaint:[LANG_Validate.alphaint,function(b,a){return null==a||""==a||!/\W/.test(a)||/^[a-zA-Z0-9]+$/.test(a)}],alphanum:[LANG_Validate.alphanum,function(b,a){return null==a||""==a||!/\W/.test(a)||/^[\u4e00-\u9fa5a-zA-Z0-9]+$/.test(a)}],date:[LANG_Validate.date,function(b,a){return null==a||""==a||/^(19|20)[0-9]{2}-([1-9]|0[1-9]|1[012])-([1-9]|0[1-9]|[12][0-9]|3[01])$/.test(a)}],
email:[LANG_Validate.email,function(b,a){return null==a||""==a||/(\S)+[@]{1}(\S)+[.]{1}(\w)+/.test(a)}],mobile:[LANG_Validate.mobile,function(b,a){return null==a||""==a||/^0?1[3458]\d{9}$/.test(a)}],tel:[LANG_Validate.tel,function(b,a){return null==a||""==a||/^(0\d{2,3}-?)?[23456789]\d{5,7}(-\d{1,5})?$/.test(a)}],phone:[LANG_Validate.phone,function(b,a){return null==a||""==a||/^0?1[3458]\d{9}$|^(0\d{2,3}-?)?[23456789]\d{5,7}(-\d{1,5})?$/.test(a)}],zip:[LANG_Validate.zip,function(b,a){return null==
a||""==a||/^\d{6}$/.test(a)}],url:[LANG_Validate.url,function(b,a){return null==a||""==a||/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*)(:(\d+))?\/?/i.test(a)}],area:[LANG_Validate.area,function(b){return b.getElements("select").every(function(a){var d=a.getValue();a.focus();return""!=d&&"_NULL_"!=d})}],greater:[LANG_Validate.greater,function(b){var a=b.getPrevious("input[type=text]");return""===b.getValue()||b.getValue().toInt()>a.getValue().toInt()}],requiredcheckbox:[LANG_Validate.requiredonly,function(b){var a=
b.getParent();return(b.get("name")?a.getElements('input[type=checkbox][name="'+b.get("name")+'"]'):a.getElements("input[type=checkbox]")).some(function(a){return!0==a.checked})}],requiredradio:[LANG_Validate.requiredonly,function(b){var a=b.getParent();return(b.get("name")?a.getElements('input[type=radio][name="'+b.get("name")+'"]'):a.getElements("input[type=radio]")).some(function(a){return!0==a.checked})}]}),validate=function(b){if(!b)return!0;var a=!1,d=(b.match("form")?b.getElements("[vtype]"):
[b]).every(function(c){var d=c.get("vtype");if(!$chk(d)||!c.isDisplay()&&"hidden"!=c.getAttribute("type"))return!0;var b=d.split("&&");c.get("required")&&(b=["required"].combine(b.clean()));return d.split("&&").every(function(d){if(!validatorMap[d])return!0;var b=c.getNext(".caution"),h=c.get("caution")||validatorMap[d][0];if(validatorMap[d][1](c,c.getValue()))return b&&b.hasClass("error")&&b.remove(),!0;!b||!b.hasClass("caution")?((new Element("span",{"class":"error caution notice-inline",html:h})).injectAfter(c),
c.onblur=function(){validate(c)&&b&&b.hasClass("error")&&b.remove()}):b&&(b.hasClass("caution")&&b.get("html")!=h)&&b.set("html",h);"hidden"!=c.type&&(c.isDisplay()&&!a)&&(a=c);return!1})});if(b.match("form")&&a)try{a.focus()}catch(f){}return d};
(function(){this.$globalEval=Browser.exec;var b=function(){$(document.body).addEvent("click",function(a){for(var b=$(a.target),f=0;3>f;f++)b&&!$chk(b.get("href"))&&(b=b.getParent());if(!b||!b.get("href"))return null;var f=b.get("target")||"",c=b.href||"";b.get("text");var g=!((!$chk(f)||f.match(/({|:)/))&&!c.match(/^javascript.+/i)&&!b.onclick),h=RegExp(""+SHOPADMINDIR+"");if(f.match(/blank/)&&c.match(h))return a.stop(),_open(c);if(g)return null;a.stop();if(f.match(/::/)){var i=f.split("::");switch(i[0]){case "dialog":return new Dialog(c,
JSON.decode(i[1]||{}));case "open":return _open(c,JSON.decode(i[1]||{}));case "command":return Ex_Loader("cmdrunner",function(){(new cmdrunner(c,JSON.decode(i[1]||{}))).run()})}}if(a.shift)return open(c.replace("?","#"));W.page(c,$extend({method:"get"},JSON.decode(f)),b)})};this.Wpage=new Class({Extends:Request,exoptions:{evalScripts:!0,link:"cancel",message:!1,render:!0,sponsor:!1,clearUpdateMap:!0,updateMap:{},url:"",data:"",headers:{"X-Requested-With":"XMLHttpRequest",Accept:"text/javascript, text/html, application/xml, text/xml, */*"},
async:!0,format:!1,method:"post",emulation:!0,urlEncoded:!0,encoding:"utf-8",evalResponse:!1,noCache:!1,update:!1},initialize:function(a,d){a=$merge(this.exoptions,a);this.parent(a);b();a.singlepage||("undefined"!=typeof history.pushState?(this.page("index.php?"+(location.hash.slice(1)?location.hash.slice(1):d)),window.onpopstate=function(a){this.popstate=!0;a.state&&this.page("index.php?"+a.state.go)}.bind(this)):Ex_Loader("historyMan",function(){var a=new HistoryManager({iframeSrc:SHOPBASE+"/app/desktop/view/blank.html"});
this.hstMan=a.register("page",[d],function(a,b){return this.page("index.php?"+(a.input?a.input:b[0]))}.bind(this),function(a){return a[0]},".*");a.start()}.bind(this)))},success:function(a,b){if(/text\/jcmd/.test(this.getHeader("Content-type")))return this.doCommand.apply(this,$splat(arguments));var a=a.stripScripts(function(a){this.response.javascript=a}.bind(this)),f=a.match(/<body[^>]*>([\s\S]*?)<\/body>/i);f&&(a=f[1]);var f=this.options.update,c=this.options.updateMap;this.options.clearUpdateMap&&
Object.each(c,function(a,b){!/side-/.test(b)&&a&&a.empty().fixEmpty()});a=a.replace(/<\!-{5}(.*?)-{5}([\s\S]*?)-{5}(.*?)-{5}>/g,function(a,b,d){a=b;a=c[a]||$(a);(d=d||null)&&a&&a.empty().set("html",d).fixEmpty();return""});f.empty().set("html",a);if(f==LAYOUT.content_main){var g=this.options.url.match("index\\.php\\?(.*)");try{g&&g[1]&&("undefined"!=typeof history.pushState?(history[this.popstate?"replaceState":"pushState"]({go:g[1]},"","#"+g[1]),this.popstate=!1):this.hstMan&&this.hstMan.setValue(0,
g[1]))}catch(e){}}this.render(f);this.options.evalScripts&&Browser.exec(this.response.javascript);this.onSuccess(a,b,this.response.javascript);this.onComplete()},onFailure:function(){switch(this.status){case 404:new MessageBox(LANG_Wpage.failure.status_404,{type:"error",autohide:!0});break;case 401:new MessageBox(LANG_Wpage.failure.status_401,{type:"error"});break;case 403:new MessageBox(LANG_Wpage.failure.status_401,{type:"notice",autohide:!0});break;default:new MessageBox(LANG_Wpage.failure.status+this.status+
"]",{type:"error",autohide:!0})}this.parent()},doCommand:function(a,b){var f=this,c;if("string"==$type(a))try{c=JSON.decode(a)}catch(g){}else c=a;if(c&&"string"!=$type(c)){c=$H(c);var h=c.getKeys(),i=c.getValues();return new MessageBox(i[0],{type:"success"==h[0]?"default":h[0],onHide:function(){if("error"==h[0])return f.onSuccess(a,b)},onShow:function(){if("error"!=h[0]){if(!$chk(i[1]))return f.onSuccess(a,b);var c={},g;"forward"==h[1]&&$extend(c,{method:f.options.method,data:f.options.data});f.onSuccess(a,
b);(g=i[1].match(/javascript:([\s\S]+)/))?$globalEval(g[1]):f.page(i[1],c)}},autohide:c.autohide||1500})}new MessageBox(a,{type:"notice",onHide:function(){f.onSuccess(a,b)},autohide:4E3})},eventClear:function(){for(e in this.$events)for(var a=this.$events[e].clean(),b=a.length;b--;)this.removeEvent(e,a[b]);return this},page:function(){var a,b=Array.flatten(arguments).link({url:String.type,options:Object.type,sponsor:Element.type});this.eventClear();if(a=b.options&&b.options.update?$(b.options.update):
"")for(e in a.retrieve("events",{}))a.removeEvents(e);delete this.options.updateMap;b.options=b.options||{};this.setOptions(this.exoptions);b.options&&this.setOptions(b.options);b.sponsor&&(this.options.sponsor=b.sponsor);b.url&&(this.options.url=b.url);!this.options.update&&(this.options.sponsor&&"element"==$type(this.options.sponsor))&&(this.options.update=this.options.sponsor.getContainer());a=this.options.update=$(this.options.update||LAYOUT.content_main);a={".side-r-content":LAYOUT.side_r_content,
".mainHead":a.getPrevious(),".mainFoot":a.getNext(),".side-content":LAYOUT.side.getElement(".side-content")};this.options.updateMap=$merge(a,this.options.updateMap);this.setHeader("WORKGROUND",self.currentWorkground||"NULL");this.send(this.options);this.msgBox=new MessageBox(this.options.message||LANG_Wpage.loading,{type:"notice"})},render:function(a){if(!this.options.render)return this;var a=a||this.options.update,b=this;$(a).getElements("form").each(function(a){a.addEvent("submit",function(a){var g=
this;if(g.retrieve("submiting"))return a.stop();if(!validate(g))return a.stop(),new MessageBox(LANG_Wpage.form.error,{type:"error",autohide:!0});$ES("textarea[ishtml=true]",g).getValue();var h=g.get("target");if("_blank"==h)return!0;if("multipart/form-data"==g.get("enctype")||"multipart/form-data"==g.get("encoding"))return g.target="upload",$("uploadframe").addEvent("load",function(){g&&Slick.uidOf(g)&&$(g).eliminate("submiting");var a=this.contentWindow.document,a=a.body[a.body.innerText?"innerText":
"textContent"],c=null;try{(c=JSON.decode(a))&&c.splash&&b.eventClear().doCommand.call(b,c)}catch(h){}var f=$(g).retrieve("target",{});if("onComplete"in f&&"function"==$type(f.onComplete))try{f.onComplete(a)}catch(i){}c||new MessageBox(LANG_Wpage.form.complete,{autohide:2E3});$("uploadframe").removeEvent("load",arguments.callee).set("src",$("uploadframe").retrieve("default:src"))}).store("default:src",$("uploadframe").src),g.store("submiting",!0).submit(),new MessageBox(LANG_Wpage.form.loading,{type:"notice"}),
!0;a.stop();var a=$merge(g.retrieve("target",{}),JSON.decode(h)),f=a.onComplete||$empty;a.onComplete=function(){f.apply(b,$splat(arguments));g&&Slick.uidOf(g)&&$(g).eliminate("submiting")};b.page(g.store("submiting",!0).action,$merge({method:g.method,data:g,message:LANG_Wpage.form.loading},a),g)})})},onComplete:function(){new MessageBox(LANG_Wpage.complete,{autohide:1E3});var a=this.options.update,b=$(a).getElements("input[date]");b&&b.length&&Ex_Loader("picker",function(){b.each(function(a){a.makeCalable()})});
if(BREADCRUMBS){var f=BREADCRUMBS.split(":");[LAYOUT.head,LAYOUT.side].clean().each(function(a){a.getElements(".current").removeClass("current");f.each(function(b){(b=a.getElement("a[mid="+b+"]"))&&b.addClass("current")})})}var c=$(a).getElements("input[autocompleter], textarea[autocompleter]");c&&c.length&&Ex_Loader("autocompleter",function(){c.each(function(a){var b=a.get("autocompleter"),c="?app=desktop&ctl=autocomplete&params="+b,b=b.match(/:([^,]*)/)[1];a.addEvent("keydown",function(a){13==a.code&&
a.stop()});b=$merge({getVar:b,fxOptions:!1,delay:300,callJSON:function(){return window.autocompleter_json},injectChoice:function(a){var a=a[this.options.getVar],b=new Element("li",{html:this.markQueryValue(a)});b.inputValue=a;this.addChoiceEvents(b).inject(this.choices)}},JSON.decode(a.get("ac_options")));new Autocompleter.script(a,c,b)})})}})})();
var DropMenu=new Class({Implements:[Events,Options,LazyLoad],options:{onLoad:$empty,onShow:$empty,onHide:$empty,showMode:function(b){b.setStyle("display","block")},hideMode:function(b){b.setStyle("display","none")},dropClass:"droping",eventType:"click",relative:document.body,stopEl:!1,stopState:!1,lazyEventType:"show",delay:200,offset:{x:0,y:20}},initialize:function(b,a){if(this.element=$(b)){this.setOptions(a);var d=this.options.menu||this.element.get("dropmenu");(this.menu=$(d)||$E("."+d,this.element.getParent()))&&
this.load().attach()._lazyloadInit(this.menu)}},attach:function(){var b=this.options,a=b.stopState,d=b.eventType;"mouse"==d?$$(this.element,this.menu).addEvents({mouseenter:function(){this.show();this.timer&&$clear(this.timer)}.bind(this),mouseleave:function(){this.status&&(this.timer=this.hide.delay(this.options.delay,this))}.bind(this)}):this.element.addEvent(d,function(b){this.showTimer&&$clear(this.showTimer);a&&b.stop();this.showTimer=this.show().outMenu.delay(this.options.delay,this)}.bind(this));
this.menu.addEvent("click",function(a){!0===b.stopEl&&(b.stopEl="stop");return b.stopEl?a[b.stopEl]():this.hide()}.bind(this));return this},load:function(){return this.fireEvent("load",[this.element,this])},show:function(){this.fireEvent("initShow");if(this.status)return this;this.element.addClass(this.options.dropClass);this.options.showMode.call(this,this.menu);this.options.relative&&this.position({page:this.element.getPosition(this.options.relative)});this.status=!0;return this.fireEvent("show",
this.menu)},hide:function(){this.options.hideMode.call(this,this.menu);this.element.removeClass(this.options.dropClass);this.status=!1;this.fireEvent("hide",this.menu)},position:function(b){var a=this.options,d=$(a.relative),f=(d||window).getSize(),d=(d||window).getScroll(),c={x:this.menu.offsetWidth,y:this.menu.offsetHeight};if(a.temppos)return f=b.page.x+a.offset.x,this.menu.setStyles({top:b.page.y+this.element.getSize().y+a.offset.y,left:f});var a={x:"left",y:"top"},g={},h;for(h in a)if(this.fireEvent("position",
h),g[a[h]]=b.page[h]+this.options.offset[h]+d[h],g[a[h]]+c[h]-d[h]>f[h]){var i=this.options.size?this.element.getSize()[h]:0;g[a[h]]=b.page[h]-c[h]+d[h]+i+2}this.menu.setStyles(g);return this},outMenu:function(){var b=this;document.body.addEvent("click",function(a){b.options.stopEl!=a.target&&b.menu&&(b.hide.call(b),$clear(b.showTimer),this.removeEvent("click",arguments.callee))})}});