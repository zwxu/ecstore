/*
   Finder Class
*/
(function() {

    /*全局 finderGroup */
    this.finderGroup = {};

    this.finderDestory = function() {
        for (var key in finderGroup) {
            delete finderGroup[key];
        }
    };

    var _createRemoteEvents = function(revent,ctl, colinfo) {

        ctl = ctl || 'controller';
        var _return = {};
        var obj = {};

        obj[ctl] = obj[ctl] || {};

        obj[ctl][colinfo[0]] = colinfo[1];
        _return[revent] = obj;
        return _return;

    };


    var FinderColDrag = new Class({
        Extends: Drag,
        start: function(event) {

            //if(this.element.getParent('.finder-header').scrollLeft>0)return;
            this.parent(event);
        }
    });

    /*
      单元格编辑定位函数。
    */
    var _position = function(panel, event, rela, offsets) {
        offsets = offsets || {
            x: 0,
            y: 0
        };

        var size = (rela || window).getSize(),
        scroll = (rela || window).getScroll();

        var tip = {
            x: panel.offsetWidth,
            y: panel.offsetHeight
        };
        var props = {
            x: 'left',
            y: 'top'
        };
        for (var z in props) {
            var pos = event.page[z] + offsets[z];
            if ((pos + tip[z] - scroll[z]) > size[z]) {

                pos = event.page[z] - offsets[z] - tip[z];

            }
            panel.setStyle(props[z], pos);
        }

    };

    /*Finder 类定义*/
    Finder = new Class({
        Implements: [Events],
        options: {
            selectName: 'items[]'
        },
        detailStatus: {},
        initialize: function(finderId, options) {

            $extend(this.options, options);
            /*init finder area*/
            this.id = finderId;

            this.initStaticView();

            this.initView();

            this.listContainer = this.list.getContainer();

            this.attachStaticEvents();

            this.attachEvents();

            if (this.options.packet) {

                this.loadPacket();

            }

        },
        /*初始化 Finder 静态Elements*/
        initStaticView: function() {

            $each(['action', 'form', 'filter', 'search', 'tip', 'header', 'footer', 'pager', 'packet'], function(p) {

                this[p] = $('finder-' + p + '-' + this.id);

            },
            this);

        },
        /*初始化 Finder 动态Elements*/
        initView: function() {

            /* $each(['header','list','footer'],function(p){

                     this[p]=$('finder-'+p+'-'+this.id);
                     if('list'==p){
                           this[p].store('visibility',true);
                         }
                 },this);
*/
            this.list = $('finder-list-' + this.id).store('visibility', true);
            this.tip = $('finder-tip-' + this.id);

        },
        isVisibile: function() {
            if (!this.list['retrieve']) return false;
            return $chk(this.list.retrieve('visibility'));
        },
        attachStaticEvents: function() {
            var finder = this;

            if (finder.search) {
                var search_input = finder.search.getElement('input[search]').addEvent('keypress', function(e) {
                    if (e.code === 13) {
                        e.stop();
                        if (!this.value.trim().length) {
                            finder.filter.value = '';
                        }
                        var rowselected = finder.form.retrieve('rowselected', []);
                        finder.refresh(rowselected.length&&!rowselected.contains('_ALL_')&&!confirm(LANG_Finder['refresh_confirm']));
                    }
                });
                finder.search.getElement('.finder-search-btn').addEvent('click', function() {
                    search_input.fireEvent('keypress', {
                        stop: $empty,
                        code: 13
                    });
                });

                /*if(search_input.value.trim()!=''){

                      search_input.fireEvent('keypress',{stop:$empty,code:13});

                      search_input.focus();
                }   */
            }

            if (finder.action) {

                finder.action.getElements('*[submit]').addEvent('click', function(e) {
                    if(e&&e.stop){e.stop();}
                    var target = this.get('target');
                    var actionUrl = this.get('submit');
                    var itemSelected = finder.form.retrieve('rowselected');

                    /*rowindex*/
                    var itemindex= finder.form.retrieve('_rowindex',$H());

                    itemSelected=itemSelected[0]=='_ALL_'?itemSelected:[];

                    if(!itemSelected.length&&itemindex)
                    itemindex.getValues().sort(function(a,b){
                        return a.toInt()-b.toInt();
                    }).each(function(item){
                        itemSelected.push(itemindex.keyOf(item));
                    });

                    var tmpForm = new Element('form'),
                        fdoc = document.createDocumentFragment();
                    var isSelectedAll = false;
                    itemSelected.each(function(v) {
                        var _name = finder.options.selectName;
                        if (v == '_ALL_')
                        isSelectedAll = _name = 'isSelectedAll';
                        fdoc.appendChild(new Element('input', {
                            type: 'hidden',
                            'name': _name,
                            value: v
                        }));
                    });

                    tmpForm.appendChild(fdoc);
                    if (!tmpForm.getFirst()) return MessageBox.error(LANG_Finder['error']);

                    /*for(n in itemSelected){
                         if(n){
                           $A(itemSelected[n]).each(function(v){
                               fdoc.appendChild(new Element('input',{type:'hidden','name':n,value:v}));
                           })
                           tmpForm.appendChild(fdoc);
                         }
                      }*/

                    var targetType = target,
                    targetOptions = {};

                    if (target && target.contains('::')) {
                        targetType = target.split('::')[0];
                        targetOptions = JSON.decode(target.split('::')[1]);
                        if ($type(targetOptions) != 'object') {
                            targetOptions = {};
                        }
                    }


                    var con = this.getProperty('confirm');
                    if (!!con&&!window.confirm(con)){return;}

                    if (isSelectedAll) {
                        var fgqs = finder.form.action.match(/\?([\s\S]+$)/);
                            fgqs = fgqs[1]?fgqs[1]:'';
                        var querystring = [fgqs,finder.form.toQueryString(), finder.filter.value].join('&');
                        tmpForm.adopt(querystring.toFormElements());
                    }

                    switch (targetType) {

                    case 'refresh':
                        W.page(actionUrl, $extend({
                            data: tmpForm,
                            method: 'post',
                            onComplete: finder.refresh.bind(finder)
                        },
                        targetOptions));
                        break;
                    case 'command':
                        new cmdrunner(actionurl, {
                            onSuccess: finder.refresh.bind(finder)
                        });
                        break;
                    case 'dialog':
                        new Dialog(actionUrl, $extend({
                            title: this.get('dialogtitle') || this.get('text'),
                            ajaxoptions: {
                                data: tmpForm,
                                method: 'post'
                            },
                            onClose: function() {
                                finder.unselectAll();
                                finder.refresh.call(finder);
                            }
                        },
                        targetOptions));
                        break;
                    case '_blank':
                        var _blankForm = tmpForm.set({
                            action: actionUrl,
                            name: targetType,
                            target: '_blank',
                            method: 'post'
                        }).inject(document.body);
                        _blankForm.submit();
                        _blankForm.remove.delay(1000, _blankForm);
                        break;
                    default:
                        W.page(actionUrl, $extend({
                            data: tmpForm,
                            method: 'post'
                        },
                        targetOptions));
                        break;
                    }

                });

            }

            if (finder.header) {

                finder.header.addEvent('click', function(e) {
                    var target = $(e.target);
                    if (!target.hasClass('orderable')) {
                        target = target.getParent('.orderable');
                    }
                    if (!target) return;
                    var forFill = [('desc' == target.get('order')) ? 'asc': 'desc', target.get('key')].link({
                        '_finder[orderType]': String.type,
                        '_finder[orderBy]': String.type
                    });
                    finder.fillForm(forFill).refresh();
                    e.stopPropagation();
                    return;
                });

                var headerResizeHandle = function(main_w,main_h){
                        try{
                            finder.header.setStyles({'width':finder.listContainer.clientWidth-finder.listContainer.getPatch().x});
                        }catch(e){}
                    };
                headerResizeHandle();
                LAYOUT.content_main.addEvent('resizelayout',headerResizeHandle);

                finder.header.addEvent('dispose',function(){
                    LAYOUT.content_main.removeEvent('resizelayout',headerResizeHandle);
                });



            }



        },
        selectAll: function(un) {
            this.header.getElement('.sellist') && this.header.getElement('.sellist').set('checked', !un).fireEvent('change');
            if(!un){
                this.form.retrieve('rowselected').empty().push('_ALL_');
                this.tip.fireEvent('_update', 'selectedall').fireEvent('_show');
            }else{
                this.form.retrieve('rowselected').empty();
                this.form.retrieve('_rowindex',$H()).empty();
                this.tip.fireEvent('_hide');
            }

        },
        unselectAll: function() {
            this.selectAll(true);
        },
        selectFav:function(un){
            this.form.retrieve('rowselected').empty();
            this.form.retrieve('_rowindex',$H()).empty();

            this.list.getElements('.row .sel').each(function(item){
                if(item.hasClass('isfav')){
                    item.set('checked',!un).fireEvent('change');
                }else{
                    item.set('checked',!!un).fireEvent('change');
                }

            });
        },
        selectunFav:function(){
            this.selectFav(true);
        },
        attachEvents: function() {

            var finder = this;
            var finderListContainer = this.listContainer;
            /*finderListEventInfo*/
            var fleinfo = finder.list.retrieve('eventInfo', {});

            /*rowselected Array*/
            var frowselected = finder.form.retrieve('rowselected', []);

            /*finder drag col*/
            if (finder.header&&finder.list.getElement('tr')) {
                var finder_header_inner = finder.header.getElement('.finder-header');
                var finder_col_resizer = finder_header_inner.getElements('.finder-col-resizer');
                var finder_header_cols = finder_header_inner.getElements('col');
                var finder_header_tds  = finder_header_inner.getElement('tr').getChildren();
                var finder_list_cols   = finder.list.getElements('col');

                new FinderColDrag(finder_header_inner, {
                    modifiers: {
                        'x': false,
                        'y':false
                    },
                    limit: {
                        'x': [35, 1000]
                    },
                    handle: Array.from(finder_col_resizer),
                    onStart: function(el,evt) {

                        el.addClass('col-resizing');
                        var _td;
                        if(evt.target.getParent('.cell')){
                            _td = evt.target.getParent('.cell').getParent('td');
                        }else if(evt.target.getParent('td')){
                            _td = evt.target.getParent('td');
                        }else{
                            _td = evt.target;
                        }
                        var _index = finder_header_tds.indexOf(_td);
                        if(_index<0)return this.cancel();

                        var resizer = finder_header_tds[_index].getElement('.finder-col-resizer');

                        el.store('_dragTargetIndex',_index);

                        finder_header_cols[_index].addClass('resizing').setStyle('background','#e9e9e9');

                        var moveEL = el.retrieve('_dragTargetMoveEl');
                        if(!moveEL){
                            moveEL = new Element('div',{
                                'class':'resize-move-el',
                                styles:{
                                    height:finder.header.offsetHeight+finderListContainer.offsetHeight,
                                    width:resizer.offsetWidth,
                                    position:'absolute',
                                    top:resizer.getPosition().y,
                                    left:resizer.getPosition().x,
                                    background:'#e9e9e9',
                                    zIndex:65535,
                                    cursor:'col-resize',
                                    opacity:0.8,
                                    borderRight:'1px #cccccc solid'
                                }
                            }).inject(document.body);

                            el.store('_dragTargetMoveEl',moveEL);
                        }
                    },
                    onDrag: function(el) {
                        el.retrieve('_dragTargetMoveEl',{}).setStyle('left',this.mouse.now.x);

                    },
                    onComplete: function(el) {

                        el.removeClass('col-resizing');
                        var _index = el.retrieve('_dragTargetIndex');
                        var tcol = finder_header_cols[_index].removeClass('resizing').setStyle('background','');

                        if(!el.retrieve('_dragTargetMoveEl'))return;
                        el.retrieve('_dragTargetMoveEl').dispose();
                        el.eliminate('_dragTargetMoveEl');

                        var mouselimit = (this.mouse.now.x - this.mouse.start.x);
                        var startWidth = tcol.getStyle('width').toInt();
                        var nowWidth = (startWidth+mouselimit).limit(this.options.limit.x[0],this.options.limit.x[1]);
                        var fixEls = $$(tcol,finder_list_cols[_index]);
                        var targetListTd = finder.list.getElement('tr').getChildren()[_index];
                        if (window.webkit) {
                            fixEls = $$(fixEls, finder_header_tds[_index], targetListTd);
                        }

                        fixEls.setStyle('width', nowWidth);

                        var sl = finderListContainer.scrollLeft,s = finderListContainer.offsetWidth,sw = finderListContainer.scrollWidth;
                        if (sl>0&&sl+s>=sw) {
                            var lw = nowWidth - startWidth;
                            if(lw<0){
                                finderListContainer.scrollLeft = (finderListContainer.scrollLeft - Math.abs(lw)).limit(0, finderListContainer.scrollWidth);
                            }
                        }

                        finder.dropmenu && finder.dropmenu.fireEvent('position','x');

                        if (!targetListTd) return;
                        var key = targetListTd.get('key');
                        EventsRemote.post({
                            events:_createRemoteEvents('finder_colset',finder.options.object_name + '_' + finder.options.finder_aliasname,[key,nowWidth])
                        });

                    }
                });
            }

            if (finder.tip) {

                finder.tip.addEvents({
                    '_update': function(className, selectedCount) {
                        if (this.retrieve('arg:class', 'NULL') != className) {
                            $$(this.childNodes).hide();
                        }
                        var el = this.getElement('.' + className);

                        if (!!el) {
                            el.innerHTML = el.innerHTML.replace(/<em>([\s\S]*?)<\/em>/ig, function() {

                                return '<em>' + selectedCount + '</em>';
                            });
                            el.setStyle('display', 'block');
                        }
                        this.store('arg:class', className);


                        if(!this.retrieve('tipclone')){
                            var tipclone = new Element('div',{'class':'hide','html':'&nbsp;',styles:{'height':this.offsetHeight}}).injectTop(finderListContainer);
                            this.store('tipclone',tipclone);
                        }
                    },
                    '_show': function() {
                        if (this.style.visibility != 'hidden') return;
                        this.setStyle('visibility','visible');
                        this.retrieve('tipclone').removeClass('hide');
                    },
                    '_hide': function() {
                        if (this.style.visibility == 'hidden') return;
                        this.setStyle('visibility','hidden');
                        this.retrieve('tipclone').addClass('hide');
                    }
                });

                var selectedLength = frowselected.length;

                if (selectedLength > 1) {
                    if (selectedLength == finder.tip.get('count').toInt() || frowselected.contains('_ALL_')) {
                        finder.tip.fireEvent('_update', ['selectedall', selectedLength]).fireEvent('_show');
                    } else {
                        finder.tip.fireEvent('_update', ['selected', selectedLength]).fireEvent('_show');
                    }

                }

            }

            /*finder.list.addEvent('selectstart',function(e){
                   e.stop();
               });*/

            finder.list.addEvents({
                'selectrow': function(ckbox) {
                    ckbox.getParent('.row').addClass('selected');
                },
                'unselectrow': function(ckbox) {
                    ckbox.getParent('.row').removeClass('selected');
                }
            });

            var selectHandles = finder.list.getElements('.row .sel');

            finder.rowCount = selectHandles.length;

            if (finder.header && finder.header.getElement('.sellist')) {
                var sellist = finder.header.getElement('.sellist').addEvent('change', function() {
                    selectHandles.set('checked', this.checked).fireEvent('change');
                });
            }

            /*数据行 预处理*/
            selectHandles.addEvents({
                'click':function(){
                    this.fireEvent('change');
                },
                'focus':function(){
                    this.blur();
                },
                'change':function(){

                    if (!sellist) {
                        frowselected.empty().push(this.value);
                    } else {
                        frowselected[this.checked ? 'include': 'erase'](this.value);

                        /*rowindex Hash*/
                        var frowindex= finder.form.retrieve('_rowindex', $H());
                        this.checked ? frowindex.set(this.value,this.get('rowindex')):frowindex.erase(this.value);
                    }

                    if (!this.checked && frowselected.contains('_ALL_')) {
                        frowselected.erase('_ALL_');
                        return finder.unselectAll();
                    }

                    var selectedLength = frowselected.length;

                    var displayTipDelay = 0;

                    if (selectedLength > 1) {
                        if (selectedLength == finder.tip.get('count').toInt() || frowselected.contains('_ALL_')) {

                            finder.tip.fireEvent('_update', ['selectedall', selectedLength]).fireEvent('_show');

                        } else {
                            finder.tip.fireEvent('_update', ['selected', selectedLength]);
                            displayTipDelay = (function() {
                                $clear(displayTipDelay);
                                if (frowselected.length < 2) return;
                                if (finder.list.retrieve('eventState') == 'mousedown') {
                                    return displayTipDelay = arguments.callee.delay(200);
                                }
                                finder.tip.fireEvent('_show');

                            }).delay(200);
                        }
                    } else {
                        $clear(displayTipDelay);
                        finder.tip.fireEvent('_update', ['selected']).fireEvent('_hide');
                    }

                    finder.list.fireEvent(this.checked ? 'selectrow': 'unselectrow', this);
                }

            });

            selectHandles.filter(function(item){
                return frowselected && frowselected.push && (frowselected.contains(item.value) || frowselected.contains('_ALL_'));
            }).set('checked',true).fireEvent('change');

            var detailStatusRow = finder.list.getElement('tr[item-id='+finder.detailStatus.rowId+']');
            if(detailStatusRow){
                finder.showDetail(detailStatusRow.getElement('span[detail]').get('detail'), {},detailStatusRow);
            }


            finder.list.addEvent('click', function(e) {

                var target = $(e.target);
                if (!target) return;

                if (target.match('img')) {
                    target = $(target.parentNode);
                }

                /*新窗口查看，选中行
                if (target.match('a') && target.get('target') == '_blank') {
                    if (!target.getParent('.row')) return;
                    var selfSelected = target.getParent('.row').getElement('input[class=sel]');
                    selfSelected.set('checked', true);
                    finder.list.fireEvent('selectrow', selfSelected);
                    selfSelected.fireEvent('change');

                    return;
                }*/


                /*fav start*/
                if(target.hasClass('fav-star')){
                    target.toggleClass('fav-star-on');
                    var targetRow = target.getParent('tr[item-id]');
                        targetRow.getElement('.sel').toggleClass('isfav');
                    return EventsRemote.post({
                        events:_createRemoteEvents('finder_favstar',finder.options.object_name + '_' + finder.options.finder_aliasname,
                        ['id-'+targetRow.get('item-id'),(target.hasClass('fav-star-on')?1:0)])
                    });
                }




                /*view detail*/
                var _detail = target.get('detail');
                if (!!_detail) {
                    e.stopPropagation();
                    return finder.showDetail(_detail,{},target.getParent('.row'));
                }

                /*enter edit*/
                if (target.hasClass('cell') || target.hasClass('cell-inside')) {
                    target = target.getParent('td');
                }

                if (target.match('td')) {
                    if (! (/row/).test(target.parentNode.className)) return;
                    if (finder.detailStatus.row) {
                        var detailbtn = target.getParent('.row').getElement('*[detail]');
                        if (detailbtn && ! target.getParent('.row').hasClass('view-detail')) {
                            return finder.showDetail(detailbtn.get('detail'), {},
                            target.getParent('.row'));
                        }

                    }

                }

            });

            attachEsayCheck(finder.list, 'td:nth-child(first) .span-auto');

            var scrollTimer = 0;

            var listContainerScrollHandle = function() {
                $clear(scrollTimer);

                scrollTimer = (function(){
                    if(this.listContainer.scrollLeft!=this.header.scrollLeft){
                        var sl = this.header.scrollLeft = this.listContainer.scrollLeft;
                        var detailContainer = this.listContainer.getElement('.finder-detail-content');
                        if (!!detailContainer) {
                            detailContainer.setStyle('margin-left', sl);
                           }
                    }
                    if (this.tip&&this.tip.style.visibility!='none'){

                        this.tip.setStyles({left:this.listContainer.scrollLeft,top:this.listContainer.scrollTop});
                        //this.tip.retrieve('tip:move:fx',new Fx.Morph(this.tip,{link:'cancel',duration:100})).start({'left':sl,'top':this.listContainer.scrollTop});
                    }
                }).delay(200,this);
            }.bind(this);
            listContainerScrollHandle();
            this.listContainer.addEvent('scroll', listContainerScrollHandle);
            this.list.addEvent('dispose',(function(){
                    this.listContainer.removeEvent('scroll',listContainerScrollHandle);
            }).bind(this));

            this.cellOpts.call(this);
        },
        fillForm: function(hash) {
            if (!hash || 'object' != $type(hash)) return;
            hash = $H(hash);
            var finder = this;
            hash.each(function(v, k) {
                var focusHInput = (finder.form.getElement('input[name^=' + k.slice(0, - 1) + ']') || new Element('input', {
                    type: 'hidden',
                    name: k
                }).inject(finder.form));
                focusHInput.set('value', v);
            });

            return finder;

        },
        eraseSelected:function(){
            var finder = this;
            var items_selected = finder.form.retrieve('rowselected', []);
            if(items_selected[0]=='_ALL_')return;
            var cur_page_selected_el = finder.list.getElements('.row .sel');
            $splat(arguments).flatten().each(function(i){
                var f = cur_page_selected_el.filter(function(_i){

                                return _i.value ==i;
                });
                    if(f.length){
                        f.set('checked',false).fireEvent('change');
                    }else{
                        items_selected.erase(i);
                        finder.tip.fireEvent('_update', ['selected', items_selected.length]);
                    }
            });
        },
        eraseFormElement: function() {
            var readyNames = Array.flatten(arguments);
            var finder = this;
            $each(readyNames, function(name) {
                finder.form.getElement('input[name=' + name + ']').remove();
            });

            return finder;
        },
        scrollTab: function(tabsWrap,wrap,container){
            wrap || (wrap = LAYOUT.content_main);
            container || (container = tabsWrap);



            var tabItems = tabsWrap.getElements('li'),
                tscrollhandles = tabsWrap.getElements('.scroll-handle'),
                tabsItem = tabsWrap.getElement('.tabs-items'), _w = 2;

            tabItems.each(function(i){_w+=i.offsetWidth+i.getPatch('margin').x;});

            tabsWrap.getElement('ul').setStyle('width',_w);

            var fxscroll = new Fx.Scroll(tabsItem, {link: 'cancel'});

            var tabResizeHandle = function(){
                try{
                    var w = wrap.offsetWidth;
                    tabsWrap[w<_w?'addClass':'removeClass']('tabs-scroll').setStyle('width',w-2);
                    tabsItem.setStyle('width', w - tabsItem.getStyle('marginLeft').toInt()*2);
                    fxscroll.options.duration = 500;
                    fxscroll.scrollIntoView(tabsWrap.getElement('.current'));
                }catch(e){}
            };
            tabResizeHandle();
            LAYOUT.content_main.addEvent('resizelayout',tabResizeHandle);
            container.addEvent('dispose',function(){
                LAYOUT.content_main.removeEvent('resizelayout',tabResizeHandle);
            });
            tscrollhandles.addEvents({
                'mouseenter':function(){
                    fxscroll.options.duration = 850;
                    fxscroll[this.hasClass('r')?'toRight':'toLeft']();
                },'mouseleave':function(){
                    fxscroll.stop();
                }
            });
        },
        showDetail: function(url, options, row) {
            var _this = this;


            var dp = row.getNext(),
                rowId=row.get('item-id');

            if(this.detailCurTab && this.detailCurTab[0]==rowId)
            url=this.detailCurTab[1];

            if (dp&&dp.hasClass('finder-detail')) {
                return this.hideDetail(row, dp);
            }

            this.hideDetail(this.detailStatus.row, this.detailStatus.dp);

            var detailPanel = new Element('tr', {
                'class': 'finder-detail'
            });
            var dpInner = new Element('td', {
                colspan: row.getElements('td').length,
                'class': 'finder-detail-colspan'
            });
            var dpContainer = new Element('div', {
                'class': 'finder-detail-content clearfix',
                id: 'finder-detail-' + this.id
            }).set({
                'container': true
            });

            detailPanel.adopt(dpInner.adopt(dpContainer));
            var finderContainer = this.list.getContainer(),_req = this.detailStatus.Request;


            if (_req) {
                _req.cancel();
            }


            this.detailStatus.row = row.addClass('view-detail');
            this.detailStatus.rowId = rowId;

            this.detailStatus.Request = new Request.HTML({
                evalScripts: false,
                url: url + (url.indexOf('&') > 0 ? '&': '') + 'finder_name=' + this.id,
                onRequest: function() {
                    new MessageBox(LANG_Finder['detail']['request'], {
                        type: 'notice'
                    });
                },
                onComplete: function(ns, es, re, js) {

                    detailPanel.injectAfter(row);
                    new MessageBox(LANG_Finder['detail']['complete'], {
                        autohide: 1
                    });

                    W.render(dpContainer.set('html', re));


                    var dpContainerResizeHandle =function(){
                        try{
                            dpContainer.setStyle('width', finderContainer.clientWidth - dpInner.getPatch().x);
                        }catch(e){}
                    };
                    dpContainerResizeHandle();

                    LAYOUT.content_main.addEvent('resizelayout',dpContainerResizeHandle);
                    _this.list.addEvent('dispose',function(){
                            LAYOUT.content_main.removeEvent('resizelayout',dpContainerResizeHandle);
                    });


                    var tabWrap = dpContainer.getElement('.finder-tabs-wrap');
                    tabWrap  && dpContainer.getParent('.finder-detail-colspan').addEvent('click',function(e){
                        var el = $(e.target);
                        if(el.tagName=='A' && el.getParent('.finder-tabs-wrap')){
                            e.stop();
                            W.page(el.href,{update:dpContainer,onComplete:function(){
                                _this.scrollTab(dpContainer.getElement('.finder-tabs-wrap'),dpContainer,_this.list);
                            }});
                        }
                    });
                    tabWrap && _this.scrollTab(tabWrap,dpContainer,_this.list);
                    $globalEval(js);
                }.bind(this),
                onFailure: function() {

                    new MessageBox(LANG_Finder['detail']['failure'] + [this.xhr.status], {
                        type: 'error',
                        autohide: true
                    });

                }
            }).send().chain(function() {

                delete(this.detailStatus.Request);
                this.detailStatus.dp = detailPanel;

                /*finderContainer.retrieve('fxscroll', new Fx.Scroll(finderContainer, {
                    link: 'cancel'
                })).toElement(row);*/

            }.bind(this));
        },
        /*隐藏 详情展开区域 */
        hideDetail: function(row, dp) {

            if (row) row.removeClass('view-detail');

            if (dp) dp.remove();

            delete(this.detailCurTab);
            delete(this.detail);
            delete(this.detailStatus.row);
            delete(this.detailStatus.dp);
            delete(this.detailStatus.rowId);
        },
        getFormQueryString: function() {
            return this.form.toQueryString();
        },
        page: function(num) {
            this.form.store('page', num || 1);

            this.request({
                method: this.form.method || 'post'
            });

        },
        loadPacket: function() {
            var packetPanel = this.packet;
            var _this = this;
            if (!this.options.packet) return;
            new Request.HTML({
                url:this.form.action + '&action=packet',
                update: packetPanel,
                onRequest: function() {
                    packetPanel.addClass('loading');
                },
                onComplete: function() {
                   packetPanel.removeClass('loading');
                   _this.scrollTab(packetPanel);
                }
            }).get();

        },
        storeTab:function(){
            var dpContainer=$('finder-detail-'+this.id);
            if(!dpContainer)return;
            var finder_tab = dpContainer.getElement('.finder-tabs-wrap .current');
            if(finder_tab)
            this.detailCurTab=[finder_tab.get('item-id'),finder_tab.get('url')];
        },
        refresh: function(unselectAll) {
            this.storeTab();
            this.request({
                method: this.form.method || 'post',
                onComplete: function() {
                    this.loadPacket();
                    if(unselectAll){
                        this.unselectAll();
                    }
                }.bind(this)
            });

        },
        filter2packet:function(){

                var filter_query = this.filter.value;
                if(!!!filter_query)return;

                new Dialog(this.form.action + '&action=filter2packet',{
                    width:400,
                    height:200,
                    ajaxoptions:{
                        method:'post',
                        data:{filterquery:filter_query,finder_id:this.id}
                    }
                });


        },
        setCount: function() {
            var count = this.tip.get('count').toInt(),head_count = $E('.finder-title .count');
            if (head_count) head_count.setText(count);
            return this;
        },
        request: function() {

            var params = Array.flatten(arguments);
            var p = params.link({
                'options': Object.type,
                'action': String.type
            });
            //if(!p.action&&!$(this.form.id))return;
            p.action = p.action || this.form.action + '&page=' + (this.form.retrieve('page') || 1);
            p.options = p.options || {};
            var _onComplete = p.options.onComplete;
            if ($type(_onComplete) != 'function') {
                _onComplete = $empty;
            }

            $extend(p.options, {
                clearUpdateMap: false,
                updateMap: {
                    '.innerheader': this.header,
                    '.pager': this.pager
                },
                onComplete: function() {
                    this.initView();
                    this.setCount().attachEvents();
                    _onComplete.apply(this, Array.flatten(arguments));

                    var filter_tip = $('filter-tip-'+this.id);

                    if(filter_tip){
                        if(!!this.filter.value.trim().length){
                            filter_tip.setStyle('visibility','visible').highlight('#FFFFCC');
                        }else{
                            filter_tip.setStyle('visibility','hidden');
                        }
                    }

                }.bind(this)

            });

            if (this.search && this.search.getElement('input[search]').value.trim().length) {
                this.filter.value = this.search.toQueryString();
            }

            var _data = this.getFormQueryString().concat('&' + this.filter.value);


            var optData = p.options.data;

            switch ($type(optData)) {

            case 'string':
                p.options.data = [_data, optData].join('&');
                break;
            case 'object':
            case 'hash':
                p.options.data = [_data, Hash.toQueryString(optData)].join('&');
                break;
            case 'element':
                p.options.data = [_data, $(optData).toQueryString()].join('&');
                break;
            default:
                p.options.data = _data;
                break;
            }

            for (v in this.detailStatus) {
                if ($type(this.detailStatus[v]) == 'element') delete(this.detailStatus[v]);

            }

            W.page(p.action, p.options);
        },
        cellOpts:function(){
            var finder=this,handles = finder.list.getElements('.opt-handle');

            if(!handles)return;

            handles.each(function(el,i){

                finder.dropmenu = new DropMenu(el,{
                    eventType:'mouse',
                    stopState:true,
                    relative:$('main'),
                    offset:{x:0,y:0},
                    delay:0,
                    size:true,
                    onPosition:function(z){
                        if(z!='x')return;

                        var hdSizeX=el.getSize().x,
                            parent = el.getParent('td'),parentX=parent.getSize().x,parentPatchX = parent.getPatch().x,
                            offsetX=hdSizeX>parentX?parentX-parentPatchX-el.getParent('.cell').getStyle('padding-right').toInt():hdSizeX;

                        this.options.offset.x=offsetX-this.menu.getStyle('border-left-width').toInt();
                    },

                    onHide:function(){this.element.style.position='';},

                    onInitShow:function(){if(finder.detailStatus.rowId)this.status=true;},

                    onShow:function(menu){
                        this.element.style.position='relative';

                        if(this.bind)return;

                        menu.getElements('a').addEvent('click',function(e){
                            var url=this.get('submit')||this.get('url'),target=this.get('target');

                            if(!target||!url)return;

                            e.preventDefault();
                            var targetType = target.split('::')[0]||target,
                                targetOptions = JSON.decode(target.split('::')[1])||{};

                            switch(targetType){
                                case 'dialog':
                                    var _dialog = new Dialog(url,$extend(targetOptions,{onLoad:function(){
                                        this.dialog.getElement('form').store('target',{onComplete:function(rs){
                                            _dialog.close();
                                            //add:刷新错误 -- by Tyler Chao
                                            if(rs) finder.refresh();
                                            else return MessageBox.error('REFRESH ERROR!');
                                        }});
                                    }}));
                                    break;
                                case 'tab':
                                    finder.showDetail(url,targetOptions,this.getParent('tr'));
                                    break;
                                case 'request':
                                    new Request({
                                        'url':url,
                                        'method':'post',
                                        'data':targetOptions.data,
                                        'onComplete':function(re){
                                            finder.showDetail(targetOptions.url,{},this.getParent('tr'));
                                        }.bind(this)
                                    }).send();
                                    break;
                                case 'confirm':
                                     var cfm=this.get('confirm');
                                     if(cfm &&!confirm(cfm))return;
                                     W.page(url,{onComplete:function(re){
                                        finder.refresh();
                                     }});
                                    break;
                            }
                        });
                        this.bind=true;
                    }});
            });
        }
    });

    Filter = new Class({
        Implements: [Events, Options],
        options: {
            onPush: $empty,
            onRemove: $empty,
            onChange: $empty
        },
        initialize: function(filterId, finderId, options) {

            var _this = this;
            _this.finderId = finderId;
            _this.filter = $(filterId);
            _this.finderObj = window.finderGroup[finderId];

            this.setOptions(options);
        },
        update: function() {
            var f = this.filter;
            var qstr = f.toQueryString(
                function(el) {
                    var elp = $(el).getParent('dl'),m;
                    if(!elp||!elp.isDisplay()||!!!$(el).value)return;

                    if(m = el.name.match(/_([\s\S]+)_search/)){
                        if(!!!f.getElement('*[name='+m[1]+']').value)return;
                    }

                    if(el.name.match(/_DTYPE_TIME/)){
                        if(!!!f.getElement('*[name='+el.value+']').value)return;
                    }

                    if(m=el.name.match(/_DTIME_\[([^\]]+)\]\[([^\]]+)\]/)){
                        if(!!!f.getElement('*[name='+m[2]+']').value)return;
                    }
                    return true;
            },true);

            if(this.finderObj.search)
            this.finderObj.search.getElement('input[search]').value='';



            this.finderObj.filter.value = qstr;
            var rowselected = this.finderObj.form.retrieve('rowselected', []);
            if(rowselected.length&&!rowselected.contains('_ALL_')&&!confirm(LANG_Finder['refresh_confirm'])) {
                this.finderObj.form.eliminate('rowselected');
                this.finderObj.refresh(true);
            }else this.finderObj.refresh();


            this.fireEvent('change');

        },
        retrieve: function() {

            var _re = this.finderObj.filter.value || '';
            if (this.finderObj.search) {

                this.finderObj.search.getElement('input[search]').value = '';
            }
            var _this = this;

            _re.replace(/([^&]+)\=([^&]+)/g, function() {

                var arg = arguments;

                var _name = arg[1];

                var el = _this.filter.getElement('[name=' + _name + ']');

                if (_name && _name.slice( - 1) && _name.slice( - 1) == ']') {

                    el = _this.filter.getElement('[name^=' + _name.substr(0, _name.length - 1) + ']');
                }

                if (el) {

                    el.value = decodeURIComponent(arg[2]);
                }
            });

        }
    });

}) ();







/*finder tips*/

(function(){
    var finderColImgTip = new Tips({
        onShow:function(tip,el){
            el.addClass('active');
            var img_src;
            tip.setStyle('display','block').store('tip:imgsource',img_src = $pick(el.get('href'),el.get('src')));

            var tip_t = tip.getElement('.tip-text').set('html','&nbsp;').addClass('loading');

            Asset.image(img_src,{onload:function(){
                    if(this.src!=tip.retrieve('tip:imgsource'))return;
                    tip_t.empty().adopt(this.zoomImg(tip_t.offsetWidth,tip_t.offsetHeight)).removeClass('loading');
                    this.setStyle('margin-top',(tip_t.offsetHeight-this.height)/2);
            }});
        },
        text: function(element){
            return '&nbsp;';
        },
        className:'finder-col-img-tip'
    });

    var finderColLoadTip = new Tips({
        onShow:function(tip,el){

            el.addClass('active');
            tip.setStyle('display','block');
            var loadedhtml =el.retrieve('loaded:html');
            var tip_t = tip.getElement('.tip-text');
                if(loadedhtml)return tip_t.set('html',loadedhtml);
                tip_t.set('html','&nbsp;').addClass('loading');

            new Request({url:el.get('data-load'),onSuccess:function(re){
                    tip_t.removeClass('loading').empty().set('html',re);
                    el.store('loaded:html',re);
            }}).get();
        },
        text: function(element){
            return '&nbsp;';
        },
        className:'finder-col-desc-tip'
    });

    var finderColTextTip = new Tips({
        onShow:function(tip,el){
            el.addClass('active');
            tip.setStyle('display','block');
        },
        text: function(element){
            return element.get('title') || element.get('rel');
        },
        className:'finder-col-text-tip'
    });



    var finderColDescTip = new Tips({
        onShow:function(tip,el){
            el.addClass('active');
            tip.setStyle('display','block');
        },
        text: function(element){
            return element.getElement('textarea').value;
        },
        className:'finder-col-desc-tip'
    });



    this.bindFinderColTip = function(e){
        e  = new Event(e);
        var el = e.target;
        if(!el)return;
        el.onmouseover = null;
        if(el.hasClass('img-tip')){
            finderColImgTip.attach(el);
        }else if(el.hasClass('desc-tip')){
            finderColDescTip.attach(el);
        }else if(el.hasClass('load-tip')){
            finderColLoadTip.attach(el);
        }else{
            finderColTextTip.attach(el);
        }
        //(el.hasClass('img-tip')?finderColImgTip:finderColTextTip)['attach'](el);

        el.addEvent('mouseleave',function(){
            this.removeClass('active');
        });
        el.fireEvent('mouseenter',e);
    };
})();

