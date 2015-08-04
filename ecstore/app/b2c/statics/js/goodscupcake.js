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

window.addEvent('domready', function() {
        /*商品对比*/
        var gc = $('goods-compare') || new Element('div').set('html', ["<div class='FormWrap goods-compare' id='goods-compare' style='display:none;'>", "<div class='title clearfix'><h3 class='flt'>" + LANG_goodscupcake['goodsCompare'] + "</h3><span class='close-gc del-bj frt' onclick='gcompare.hide();'>" + LANG_goodscupcake['close'] + "</span></div>", "<form action='" + Shop.url.diff + "' method='post' target='_compare_goods'>", "<ul class='compare-box'>", "<li class='division clearfix tpl'>", "<div class='goods-name'>", "<a href='{url}' gid='{gid}' title='{gname}'>{gname}</a>", "</div>", "<a class='btn-delete' onclick='gcompare.erase(\"{gid}\",this);'>" + LANG_goodscupcake['del'] + "</a>", "</li>", "</ul>", "<div class='compare-bar'>", "<button name='comareing' type='button' onclick='gcompare.submit()' class='btn btn-compare submit-btn'><span><span>对比</span></span></button>", "<button class='btn btn-compare' type='button' onclick='gcompare.empty()'><span><span>清空</span></span></button>", "</div>", "</form>", "</div>"].join('\n')).getFirst().inject(document.body);

        var gcBox = gc.getElement('.compare-box');
        var tpl = gc.getElement('.compare-box .tpl').get('html');
        var itemClass = 'division clearfix';
        if (!Browser.ie6) gc.setStyle('position', 'fixed');
        else {
                var fixLayout = function() {
                        if (gc.style.display == 'none') return;
                        gc.setStyle('top', window.getScrollTop()+40);
                };
                window.addEvents({
                        //'resize': fixLayout,
                        'scroll': fixLayout
                });
        }
        var GCOMPARE_COOKIE = new Cookie('S[GCOMPARE]');

        gcompare = {
                init: function() {
                        var tmpC = Array.from((GCOMPARE_COOKIE.read('S[GCOMPARE]') || '').split('|')).erase("").clean();

                        if (tmpC.length) {
                                tmpC.each(function(i) {
                                        this.add(JSON.decode(i), true);
                                }.bind(this));
                        }
                },
                hide: function() {
                        gc.hide();
                },
                show: function() {
                        gc.show();
                        if (Browser.ie6) fixLayout();
                },
                add: function(dataItem, isInit) {
                        this.show();
                        if (!isInit) {
                                var tmpC = Array.from((GCOMPARE_COOKIE.read('S[GCOMPARE]') || '').split('|')).erase("").clean();
                                var errorType = 'errortype';
                                if (tmpC.length && tmpC.some(function(i) {
                                        var bool = JSON.decode(i)['gid'] == dataItem.gid;
                                        if (bool) errorType = 'isset';
                                        return ((JSON.decode(i)['gtype'] + '_') != (dataItem.gtype + '_')) || bool;
                })) return Message.error(LANG_goodscupcake[errorType]);

                                if (tmpC.length > 4) {
                    return Message.error(LANG_goodscupcake['lengtherror']);
                                }
                                GCOMPARE_COOKIE.write(tmpC.include(JSON.encode(dataItem)).join('|'));
                        }

                        if (gcBox.getElement('a[gid="' + dataItem['gid'] + '"]')) return this;
                        var newItem = new Element('li', {
                                'class': itemClass
                        }).set('html', tpl.substitute(dataItem));
                        var glink = newItem.getElement('a');
                        glink.set('href', dataItem.url);
                        return gcBox.adopt(newItem);
                },
                erase: function(gid, el) {
                        var tmpC = Array.from((GCOMPARE_COOKIE.read('S[GCOMPARE]') || '').split('|')).erase("").clean();
                        tmpC.each(function(i) {
                                if ((JSON.decode(i) && JSON.decode(i)['gid'] + '_') == (gid + '_')) {
                                        tmpC.erase(i);
                                }
                        });
                        GCOMPARE_COOKIE.write(tmpC.join('|'));
                        $(el).getParent('li').destroy();
                },
                empty: function() {
                        GCOMPARE_COOKIE.dispose();
                        gcBox.getElements('li').each(function(itm) {
                                if (!itm.hasClass('tpl')) return itm.destroy();
                        });
                        gc.hide();
                },
                submit: function() {
                        if (gcBox.getElements('li').length < 3) {
                return Message.error(LANG_goodscupcake['minlengtherror']);
                        }
                        gcBox.getParent('form').submit();
                }
        };
        gcompare.init();
        Ex_Event_Group['_gcomp_'] = {
                fn: function(el, e) {
                        gcompare.add(JSON.decode($(el).get('data-gcomp')));
                }
        };
});

