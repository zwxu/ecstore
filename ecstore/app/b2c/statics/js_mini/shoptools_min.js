(function(){var a={name:"localStorage",init:function(){if(!window.localStorage)return!1;this._storage=window.localStorage;return this},setStorage:function(e,a){this._storage.setItem(e,a);return!0},getStorage:function(e,a){var b=this._storage.getItem(e);a(b?b:null)},removeStorage:function(e){this._storage.removeItem(e);return!0},clearStorage:function(){if(this._storage.clear)this._storage.clear();else for(i in this._storage)this._storage[i].value&&this.remove(i);return!0}},c={name:"globalStorage",
init:function(){if(!Browser.firefox||!window.globalStorage)return!1;this._storage=globalStorage[location.hostname];return this},setStorage:function(e,a){this._storage.setItem(e,a);return!0},getStorage:function(e,a){var b=this._storage.getItem(e);a(b?b.value:null)},removeStorage:function(e){this._storage.removeItem(e);return!0},clearStorage:function(){if(this._storage.clear)this._storage.clear();else for(i in this._storage)this._storage[i].value&&this.remove(i);return!0}},f={name:"userdata",init:function(){this.Master=
"ie6+";if(!Browser.ie)return!1;this._storage=(new Element("span")).setStyles({display:"none",behavior:"url('#default#userData')"}).inject(document.body);return this},setStorage:function(e,a){this._storage.setAttribute(e,a);this._storage.save("shopEX_VS");return!0},getStorage:function(e,a){this._storage.load("shopEX_VS");a(this._storage.getAttribute(e))},removeStorage:function(a){this._storage.removeAttribute(a);this._storage.save("shopEX_VS");return!0},clearStorage:function(){var a=new Date;a.setMinutes(a.getMinutes()-
1);this._storage.expires=a.toUTCString();this._storage.save("shopEX_VS");this._storage.load("shopEX_VS");return!0}},d={name:"openDatabase",init:function(){if(!window.openDatabase)return!1;this._storage=window.openDatabase("viewState","1.0","ShopEX48 ViewState Storage",2E4);this._createTable();return this},setStorage:function(a,b){this._storage.transaction(function(c){c.executeSql("SELECT v FROM SessionStorage WHERE k = ?",[a],function(c,d){d.rows.length?c.executeSql("UPDATE SessionStorage SET v = ?  WHERE k = ?",
[b,a]):c.executeSql("INSERT INTO SessionStorage (k, v) VALUES (?, ?)",[a,b])})});return!0},getStorage:function(a,b){this._storage.transaction(function(c){v=c.executeSql("SELECT v FROM SessionStorage WHERE k = ?",[a],function(a,e){if(e.rows.length)return b(e.rows.item(0).v);b(null)})})},removeStorage:function(a){this._storage.transaction(function(b){b.executeSql("DELETE FROM SessionStorage WHERE k = ?",[a])});return!0},clearStorage:function(){this._storage.transaction(function(a){a.executeSql("DROP TABLE SessionStorage",
[])});return!0},_createTable:function(){this._storage.transaction(function(a){a.executeSql("SELECT COUNT(*) FROM SessionStorage",[],function(){},function(a){a.executeSql("CREATE TABLE SessionStorage (k TEXT, v TEXT)",[],function(){})})})}},b={setStorage:function(){},getStorage:function(){},removeStorage:function(){},clearStorage:function(){}};BrowserStore=new Class({initialize:function(){this.storage=d.init()||c.init()||a.init()||f.init()||b;return this},set:function(a,b){b&&"string"===typeOf(b)&&
this.storage.setStorage(a,b);return this},get:function(a,b){this.storage.getStorage(a,b)},remove:function(a){if(!a||!this.storage)return!1;a&&this.storage.removeStorage(a);return this},clear:function(){if(!this.storage)return!1;this.storage.clearStorage();return this}})})();
var AutoFloatGrid=new Class({Implements:[Events,Options],options:{fixResize:!0,calcSize:!1,autoRowSize:{h3:"height"},hasEdge:!1,cols:4},initialize:function(a,c,f){a=this.container=$(a)||$(document.body);this.setOptions(f);if((this.gridlist="string"==typeOf(c)?a.getElements(c):c).length)this.fireEvent("init",this),this.autoColsWidth(),this.autoRow(this.options.autoRowSize),this.options.fixResize&&window.addEvent("resize",function(){this.options.fixResize=function(){clearTimeout(this.options.fixResize);
this.autoColsWidth();this.autoRow(this.options.autoRowSize)}.delay(200,this)}.bind(this)),this.fireEvent("grid",this)},autoColsWidth:function(){var a=this.gridlist[1]||this.gridlist[0],c=a.measure(function(){return this.getSize()}),f=this.container.getParent()||document.body,d=this.container.getPatch("padding","border"),b=a.retrieve("els:parent"),e=f.measure(function(){return this.getSize()});this.gridlist.length<this.options.cols&&this.gridlist.length*c.x<e.x-f.getPatch("margin").x?this.manal=!0:
(this.options.calcSize?(c=this.containerSize={x:e.x-f.getPatch("margin").x-this.container.getPatch().x,y:e.y-f.getPatch("margin").y-this.container.getPatch().y},this.container.setStyle("width",c.x)):c=this.containerSize=this.container.measure(function(){return this.getSize()}),b||(b=a.getParents((this.container.id?"#"+this.container.id:!1)||(this.container.className?"."+this.container.className.split(" ")[0]:!1)),a.store("els:parent",b)),1<b.length&&(b.shift(),b.each(function(a){a=$(a).getPatch();
d.x=d.x+a.x;d.y=d.y+a.y})),b=a.getPatch("padding","border"),a=a.getPatch("margin"),this.gridlist.setStyle("width",Math.floor((c.x-d.x-a.x*(this.options.hasEdge?this.options.cols:this.options.cols-1))/this.options.cols)-b.x-(Browser.ie?6:0)),a=this.gridlist[this.options.cols-1],!this.options.hasEdge&&(a&&0<a.getStyle("margin-right").toInt())&&a.setStyle("margin-right",0))},autoRow:function(a){if(a){var c=this.options.cols;for(i=0;i<=this.gridlist.length/c;i++){var f=this.gridlist.slice(i*c,i*c+c);if(f.length){var d=
f[0],b=f.getLast();for(key in a)d.addClass("row-first"),b.addClass("row-last"),this.options.hasEdge||(0<d.getStyle("margin-left").toInt()&&d.setStyle("margin-left",0),0<b.getStyle("margin-right").toInt()&&b.setStyle("margin-right",0)),new AutoSize(f.invoke("getElement",key),a[key])}}}}}),fixProductImageSize=function(a,c){a&&a.length&&a.each(function(a){a.src&&new Asset.image(a.src,{onload:function(){var d=a.getParent(c||"a");if(!this||!this.get("width"))return d.adopt(a);var b=d.outerSize().x-d.getPatch().x,
d=d.outerSize().y-d.getPatch().y;if(!(0>=b||0>=d))return b=this.zoomImg(b,d,!0),a.set(b),b={"margin-top":""},a&&(a.get("height")&&a.get("height").toInt()<d)&&(b=Object.merge(b,{"margin-top":Math.round((d-a.get("height").toInt())/2)})),a.setStyles(b),!0},onerror:function(){}})})},AutoSize=new Class({initialize:function(a,c){this.elements=$$(a);this.doAuto(c)},doAuto:function(a){a||(a="height");var c=0,f=(!Browser.ie6?"min-":"")+a,d="offset"+a.capitalize();this.elements.each(function(a){a=a[d];a>c&&
(c=a)},this);this.elements.each(function(b){b.setStyle(f,c-(b[d]-b.getStyle(a).toInt()))});return c}}),InlineCheck=function(a,c){for(var a=$$(a),f=0,d=!0,b=0,e=0;e<a.length;e++){var j=a[e].getPosition().y;if(0!=f&&f!=j){d=!1;break}b+=1;f=j}if("function"==typeOf(c))c(d,b);else return d},HeightCheck=function(a,c){for(var a=$$(a),f=0,d=!0,b=0;b<a.length;b++){var e=a[b].getHeight;if(0!=f&&f!=e){d=!1;break}f=e}if("function"==typeOf(c))c(d);else return d};
(function(){browserStore=null;withBrowserStore=function(a){if(browserStore)return a(browserStore);window.addEvent("domready",function(){(browserStore=new BrowserStore)?a(browserStore):window.addEvent("load",function(){a(browserStore=new BrowserStore)})})}})();
// modified by cam begin
//window.addEvent("domready",function(){var a=Shop.url.fav_url,c=Cookie.read("S[MEMBER]"),f=new Cookie("S[GFAV]["+c+"]",{duration:365}),d={"star-on":"off","star-off":"on",off:"del",on:"add",off_:"erase",on_:"include"},b=function(b,e,h){"on"==b.get("data-type")&&b.hasClass("star-on")||(b.className=b.className.replace("star-"+d["star-"+e],"star-"+e),h&&(f.write(Array.from((f.read("S[GFAV]["+c+"]")||"").split(","))[d[e+"_"]](h).clean().join(",")),b=b.get("_type")?b.get("_type"):"goods",(new Request({url:a,
//onSuccess:function(a){(a=JSON.decode(a))&&a.success&&Message.success(a.success)}})).post({t:new Date,act_type:d[e],type:b,gid:h})))},e=Array.from((f.read("S[GFAV]["+c+"]")||"").split(","));_fav_=function(){$$("li[star]").each(function(a){var c=a.get("star");e.contains(c)&&b(a,"on")});Ex_Event_Group._fav_={fn:function(a,e){e.stop();var a=$(a.target)||$(a),c=$(a).getParent("li"),f=c.hasClass("star-on")?"star-on":"star-off";b(c,c.get("data-type")||d[f],c.get("star"))}}};_fav_()});
window.addEvent('domready', function() {
  var member_fav_url = Shop.url.fav_url;
        /*加入收藏夹*/
        var MEMBER = Cookie.read('S[MEMBER]');
        var FAVCOOKIE = new Cookie('S[GFAV][' + MEMBER + ']', {
                duration: 365
        });
        
        var FAVCOOKIEtwo = new Cookie('S[SFAV][' + MEMBER + ']', {
          duration: 365
        });
       
        var _toogle = {
                'star-on': 'off',
                'star-off': 'on',
                'off': 'del',
                'on': 'add',
                'off_': 'erase',
                'on_': 'include'
        };
        var setStar = function(item, state, gid) {
                if(item.get('data-type') == 'on' && item.hasClass('star-on')) return;
                item.className = item.className.replace('star-' + _toogle['star-' + state], 'star-' + state);
                // item.title = state == 'on' ? '已加入收藏': '加入收藏';
                if (!gid) return;
               
                if(item.get('isspecial') && item.get('isspecial') == 1){
                    member_fav_url = Shop.url.fav_store;
                    FAVCOOKIEtwo.write(Array.from((FAVCOOKIE.read('S[SFAV][' + MEMBER + ']') || '').split(','))[_toogle[state + '_']](gid).clean().join(','));
                }else{
                    member_fav_url = Shop.url.fav_url;
                    FAVCOOKIE.write(Array.from((FAVCOOKIE.read('S[GFAV][' + MEMBER + ']') || '').split(','))[_toogle[state + '_']](gid).clean().join(','));
                }
             
                var _type = item.get('_type') ? item.get('_type') : 'goods';
                new Request({
                        url: member_fav_url,
                        onSuccess: function(rs){
                rs = JSON.decode(rs);
                if (rs && rs.success) {
                    
                    var span = item.getElement('span').getElement('span')||'';
                    if(span){
                      span.set('html',parseInt(span.get('html'))+1);
                    }
                   
                    Message.success(rs.success);
                }
                        }
                }).post({
                        t: new Date(),
                        act_type:_toogle[state],
                        type:_type,
                        gid:gid
                });
        };
        var splatFC = Array.from((FAVCOOKIE.read('S[GFAV][' + MEMBER + ']') || '').split(','));
        var splatFCtwo = Array.from((FAVCOOKIEtwo.read('S[SFAV][' + MEMBER + ']') || '').split(','));

        _fav_ = function() {
                $$('li[star]').each(function(item) {
                        var GID = item.get('star');
                        
                        if(item.get('isspecial') && item.get('isspecial') == 1){
                          if (splatFCtwo.contains(GID)) {
                            setStar(item, 'on');
                          }
                          return true;
                        }
                       
                        if (splatFC.contains(GID)) {
                                setStar(item, 'on');
                        }
                });
                Ex_Event_Group['_fav_'] = {
                        fn: function(el, e) {
                                e.stop();
                                el = $(el.target) || $(el);
                                var item = $(el).getParent('li');
                                var cls = item.hasClass('star-on') ? 'star-on': 'star-off';
                                setStar(item, item.get('data-type')||_toogle[cls], item.get('star'));
                        }
                };
        };
        _fav_();

});
// modified by cam end
window.addEvent("domready",function(){var a=$("goods-compare")||(new Element("div")).set("html",["<div class='FormWrap goods-compare' id='goods-compare' style='display:none;'>","<div class='title clearfix'><h3 class='flt'>"+LANG_goodscupcake.goodsCompare+"</h3><span class='close-gc del-bj frt' onclick='gcompare.hide();'>"+LANG_goodscupcake.close+"</span></div>","<form action='"+Shop.url.diff+"' method='post' target='_compare_goods'>","<ul class='compare-box'>\n<li class='division clearfix tpl'>\n<div class='goods-name'>\n<a href='{url}' gid='{gid}' title='{gname}'>{gname}</a>\n</div>",
"<a class='btn-delete' onclick='gcompare.erase(\"{gid}\",this);'>"+LANG_goodscupcake.del+"</a>","</li>\n</ul>\n<div class='compare-bar'>\n<button name='comareing' type='button' onclick='gcompare.submit()' class='btn btn-compare submit-btn'><span><span>\u5bf9\u6bd4</span></span></button>\n<button class='btn btn-compare' type='button' onclick='gcompare.empty()'><span><span>\u6e05\u7a7a</span></span></button>\n</div>\n</form>\n</div>"].join("\n")).getFirst().inject(document.body),c=a.getElement(".compare-box"),
f=a.getElement(".compare-box .tpl").get("html");if(Browser.ie6){var d=function(){"none"!=a.style.display&&a.setStyle("top",window.getScrollTop()+40)};window.addEvents({scroll:d})}else a.setStyle("position","fixed");var b=new Cookie("S[GCOMPARE]");gcompare={init:function(){var a=Array.from((b.read("S[GCOMPARE]")||"").split("|")).erase("").clean();a.length&&a.each(function(a){this.add(JSON.decode(a),!0)}.bind(this))},hide:function(){a.hide()},show:function(){a.show();Browser.ie6&&d()},add:function(a,
d){this.show();if(!d){var g=Array.from((b.read("S[GCOMPARE]")||"").split("|")).erase("").clean(),h="errortype";if(g.length&&g.some(function(b){var c=JSON.decode(b).gid==a.gid;c&&(h="isset");return JSON.decode(b).gtype+"_"!=a.gtype+"_"||c}))return Message.error(LANG_goodscupcake[h]);if(4<g.length)return Message.error(LANG_goodscupcake.lengtherror);b.write(g.include(JSON.encode(a)).join("|"))}if(c.getElement('a[gid="'+a.gid+'"]'))return this;g=(new Element("li",{"class":"division clearfix"})).set("html",
f.substitute(a));g.getElement("a").set("href",a.url);return c.adopt(g)},erase:function(a,c){var d=Array.from((b.read("S[GCOMPARE]")||"").split("|")).erase("").clean();d.each(function(b){(JSON.decode(b)&&JSON.decode(b).gid+"_")==a+"_"&&d.erase(b)});b.write(d.join("|"));$(c).getParent("li").destroy()},empty:function(){b.dispose();c.getElements("li").each(function(a){if(!a.hasClass("tpl"))return a.destroy()});a.hide()},submit:function(){if(3>c.getElements("li").length)return Message.error(LANG_goodscupcake.minlengtherror);
c.getParent("form").submit()}};gcompare.init();Ex_Event_Group._gcomp_={fn:function(a){gcompare.add(JSON.decode($(a).get("data-gcomp")))}}});