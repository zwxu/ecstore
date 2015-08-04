/*shopAdmin Widgets
    Extends DragDropPlus.js
*/
var Widgets = new Class({
    Extends: DragDropPlus,
    initialize: function(drags, drops, options) {
        this.parent(drags, drops, options);

        this.drags.each(function(drag) {
            if (drag.getProperty('ishtml')) {
                drag.getElement('.content-html').set('html',drag.getElement('.content-textarea').get('value'));
            }

        });

    },
    inject: function(widget, theme) {
        this.addWidget(this.curEl, widget, theme || this.theme);
    },
    ghostDrop: function(widget, theme) {
        widget = typeOf(widget) === 'string' ? JSON.decode(widget) : widget;
        this.drag_operate_box.setStyle('visibility', 'hidden').store('lock', true);
        $('tempDropBox') && $('tempDropBox').destroy();
        //parent._showWidgets_tip('在您需要放入版块的蓝色区域点击鼠标左键即可添加版块。点击鼠标右键则取消添加版块操作。');
        this.tempDropBox = new Element('div', {
            'id': 'tempDropBox'
        }).inject(document.body);
        try {
            var item = this.drag_operate_box.retrieve('drag');
            this.tempDropBox.empty();
            this.addWidget(item, widget, theme);
            this.drag_operate_box.store('lock', false);
        } catch(e) {
            alert(JSON.encode(e));
        }
        document.body.addEvent('contextmenu', function(e) {
            e.stop();
            $('tempDropBox') && $('tempDropBox').destroy();
            // parent._hideWidgets_tip();
            this.drag_operate_box.store('lock', false);
            document.body.removeEvent('contextmenu', arguments.callee);
        }.bind(this));
    },
    copyWidgets: function(el) {
        /*var elsource=el.source;
             new Ajax('index.php?ctl=system/template&act=copyWg&p[0]='+elsource.getProperty('id')+'&p[1]='+elsource.getProperty('widgets_id'),{onComplete:function(re){
                var widgets_id=JSON.decode(re).widgetid;
                elsource.setProperty('widgets_id',widgets_id);
             }}).request();
             elsource.setStyle('border','1px dashed #333');*/
    },
    addWidget: function(drop, widget, theme) {
        var dialogSetting = {
            modal: true,
            title: LANG_shopwidgets['addWidget'] + (widget.label || ""),
            ajaxoptions: {
                render: false
            },
            width: 0.7,
            height: 0.7
        };
        this.curdialog = new top.Dialog(top.SHOPADMINDIR + 'index.php?&app=site&ctl=admin_theme_widget&act=do_add_widgets&widgets=' + widget.name + '&widgets_app=' + widget.app + '&widgets_theme=' + widget.theme + '&theme=' + theme, dialogSetting);
        this.curDrop = drop;
    },
    editWidget: function(widget) {
        var dialogSetting = {
            modal: true,
            title: LANG_shopwidgets['editWidget'] + (widget.label || widget.title),
            ajaksable: false,
            width: 0.7,
            height: 0.7
        };
        this.curWidget = $(widget);
        if (widget.get('ishtml')) return this.curdialog = new top.Dialog(top.SHOPADMINDIR + 'index.php?ctl=content/pages&act=editHtml', Object.merge(dialogSetting, {
            ajaksable: true,
            ajaxoptions: {
                method: 'post',
                data: 'htmls=' + encodeURIComponent(widget.getElement('.content-html').get('html').clean().trim())
            },
            title: LANG_shopwidgets['editHTML']
        }));

        return this.curdialog = new top.Dialog(top.SHOPADMINDIR + 'index.php?app=site&ctl=admin_theme_widget&act=do_edit_widgets&widgets_id=' + widget.get('widgets_id') + '&theme=' + widget.get('widgets_theme'), dialogSetting);

    },
    delWidget: function(widget) {
        var dob = this.drag_operate_box;
        dob.setStyle('visibility', 'hidden').store('lock', true);
        var drop = widget.getParent();
        new Fx.Tween(widget).start('opacity', 0).chain(function() {
            widget.destroy();
            dob.store('lock', false);
            this.checkEmptyDropPanel(drop);
            top.document.id('btn_save') && (top.document.id('btn_save').disabled = false);
        }.bind(this));
    },
    preview : function(url,target){
        var params = [];
        var wpanels = this.drops;
        var file = {};
        wpanels.each(function(item, index) {
            var widgets = item.getElements('.shopWidgets_box');
            widgets.each(function(widgetbox) {
                params.push(("widgets[{widgetsId}]={baseFile}:{baseSlot}:{baseId}").substitute({
                    widgetsId: widgetbox.get('widgets_id'),
                    baseFile: this.bf,
                    baseSlot: this.bs,
                    baseId: this.bi
                }));

                if (widgetbox.get('ishtml')) {
                    var ch = widgetbox.getElement('.content-html');
                    params.push(('html[{widgetsId}]={htmls}').substitute({
                        widgetsId: widgetbox.get('widgets_id'),
                        htmls: encodeURIComponent(ch.get('html'))
                    }));
                }
            },
            {
                mce: this.mce,
                bf: item.get('base_file'),
                bs: item.get('base_slot'),
                bi: item.get('base_id')
            });
            file[item.get('base_file')] = 1;
        }.bind(this));

        for (f in file) {
            params.push(("files[]={file}").substitute({
                file: f
            }));
        }

        new Request({
            url:url,
            method:'post',
            data:params.join('&'),
            onRequest:function(){
                $(target).set({'disabled':true,'html':'<span><span>正在生成预览...</span></span>'});
            },
            onComplete:function(rs){
                rs = JSON.decode(rs);
                $(target).set({'disabled':false,'html':'<span><span>预览模板</span></span>'});
                if(rs && rs.success){
                  //模拟a事件点击以在新窗口打开预览页面->by TylerChao
                  var a = $('_temp_preview_link') || new Element('a#_temp_preview_link.hide',{target:'preview',href:rs.url||top.PREVIEW_URL}).inject(document.body);
                  if(document.createEvent) {
                      var evt = document.createEvent('MouseEvent');
                      evt.initEvent('click', false, false);
                      a.dispatchEvent(evt);
                  }
                  else a.click();
                  // _open(rs.url||top.PREVIEW_URL,{width:screen.availWidth,height:screen.availHeight});
                }
            }
        }).send();
    },
    saveAll: function(fn,bind) {
        var params = [];
        var wpanels = this.drops;
        var file = {};
        wpanels.each(function(item, index) {
            var widgets = item.getElements('.shopWidgets_box');
            widgets.each(function(widgetbox) {
                params.push(("widgets[{widgetsId}]={baseFile}:{baseSlot}:{baseId}").substitute({
                    widgetsId: widgetbox.get('widgets_id'),
                    baseFile: this.bf,
                    baseSlot: this.bs,
                    baseId: this.bi
                }));

                if (widgetbox.get('ishtml')) {
                    var ch = widgetbox.getElement('.content-html');
                    params.push(('html[{widgetsId}]={htmls}').substitute({
                        widgetsId: widgetbox.get('widgets_id'),
                        htmls: encodeURIComponent(ch.get('html'))
                    }));
                }
            },
            {
                mce: this.mce,
                bf: item.get('base_file'),
                bs: item.get('base_slot'),
                bi: item.get('base_id')
            });
            file[item.get('base_file')] = 1;
        }.bind(this));

        for (f in file) {
            params.push(("files[]={file}").substitute({
                file: f
            }));
        }

        new Request({
            url:top.SHOPADMINDIR + 'index.php?app=site&ctl=admin_theme_widget&act=save_all',
            method:'post',
            data:params.join('&'),
            onRequest: function() {
                new top.MessageBox(LANG_shopwidgets['saving']);
            },
            onSuccess: function(re) {
                fn && fn.call(bind||this,this);
                try {
                    re = JSON.decode(re);
                    for (dom in re) {
                        if ($(dom) && re[dom]) $(dom).set('widgets_id', re[dom]);
                    }
                    top.MessageBox.success(LANG_shopwidgets['saveSuccess']);
                } catch(e) {
                    top.MessageBox.error(LANG_shopwidgets['saveError'] + e.message);
                }
            }.bind(this)
        }).send();
    }
});

window.addEvent('domready', function() {
    this.shopWidgets = new Widgets('.shopWidgets_box', '.shopWidgets_panel', {
        onInit:function(){
            this.theme = top.THEME_NAME||'';
        },
        onEdit: function(widget, widget_panel) {
            shopWidgets.editWidget(widget);
        },
        onDelete: function(widget) {
            if(top.confirmDialog) {
                top.confirmDialog(LANG_shopwidgets['comfirmDel'],function(){
                    this.delWidget(widget);
                }.bind(this));
            }
            else {
                if (confirm(LANG_shopwidgets['comfirmDel'])) this.delWidget();
            }
        },
        onAdd:function(widget,el) {
            var dob = this.drag_operate_box;
            var where = el ? el.get('class')||'' : '';
            dob.store('lock', true);
            shopWidgets.widgetsDialog=new top.Dialog(top.SHOPADMINDIR + 'index.php?app=site&ctl=admin_theme_widget&act=add_widgets_page&theme=' + this.theme,{width:770,height:500,title:'添加挂件',modal:true,resizeable:false,onShow:function(e){
                this.dialog_body.id='dialogContent';
                shopWidgets.injectWhere = where;
                if(widget.hasClass('empty_drop_box')) shopWidgets.injectBox = widget.getParent();
                else shopWidgets.injectBox = null;
            },onClose:function(){
                dob.store('lock', false);
            }});
        },
        onUpDown:function(widget,el){
            top.document.id('btn_save') && (top.document.id('btn_save').disabled = false);
        },
        onEmptyDrag:function(widget){
            new Element('div').inject(widget);
        }
    });
});

