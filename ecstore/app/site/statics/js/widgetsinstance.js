/*shopAdmin Widgetsinsance
    Extends DragDropPlus.js
*/
var Widgetsinsance = new Class({
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
        this.addWidget(this.curEl, widget, theme ? theme: this.theme);
    },
    ghostDrop: function(widget, theme) {
        this.drag_operate_box.setStyle('visibility', 'hidden').store('lock', true);
        $('tempDropBox') && $('tempDropBox').destroy();
        //parent._showWidgets_tip('在您需要放入版块的蓝色区域点击鼠标左键即可添加版块。点击鼠标右键则取消添加版块操作。');
        this.tempDropBox = new Element('div', {
            'id': 'tempDropBox'
        }).inject(document.body);
        try {
            this.drops.each(function(item, index) {
                if (!item) return;
                var _this = this;
                var cis = item.getCoordinates();
                if (cis.height > 5 && cis.width > 5) {
                    cis.height -= 5;
                    cis.width -= 5;
                }
                var dropghost = new Element('div', {
                    'class': 'widgets_drop_ghost',
                    'styles': Object.merge(cis, {
                        'opacity': 0.3
                    }),
                    // 'text': '[' + (index + 1) + ']',
                    'title': LANG_shopwidgets['dropghostTitle1'] + widget.name + LANG_shopwidgets['dropghostTitle2']
                }).addEvents({
                    'mouseover': function() {
                        this.addClass('widgets_drop_ghost_on');
                    },
                    'mouseleave': function() {
                        this.removeClass('widgets_drop_ghost_on');
                    },
                    'click': function() {
                        _this.tempDropBox.empty();
                        _this.addWidget(item, widget, theme);
                        _this.drag_operate_box.store('lock', false);
                        //    parent._hideWidgets_tip();
                    }
                }).inject(_this.tempDropBox);
            },
            this);
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
        this.curdialog = new top.Dialog(top.SHOPADMINDIR + 'index.php?&app=site&ctl=admin_widget_proinstance&act=do_add_widgets&widgets=' + widget.name + '&widgets_app=' + widget.app + '&widgets_theme=' + widget.theme + '&theme=' + theme, dialogSetting);
        this.curDrop = drop;
    },
    editWidget: function(widget) {
        var dialogSetting = {
            modal: true,
            title: LANG_shopwidgets['editWidget'] + (widget.label || ''),
            ajaksable: false,
            width: 0.7,
            height: 0.7
        };
        this.curWidget = $(widget);
        return this.curdialog = new top.Dialog(top.SHOPADMINDIR + 'index.php?app=site&ctl=admin_widget_proinstance&act=do_edit_widgets&widgets_id=' + widget.get('widgets_id') + '&theme=' + widget.get('widgets_theme'), dialogSetting);

    }
});

window.addEvent('domready', function() {
    document.body.style.cssText = "background:#FFFFFF!important;padding:10px 90px 0 90px!important;";
    this.shopWidgets = new Widgetsinsance('.shopWidgets_box', '.shopWidgets_panel', {
        onEdit: function(widget, widget_panel) {
            shopWidgets.editWidget(widget);
        },
        onDelete: function(widget,chain2) {
            if (!(chain2 || confirm(LANG_shopwidgets['comfirmDel']))) return;
            var dob = this.drag_operate_box;
            var _this = this;
            dob.setStyle('visibility', 'hidden').store('lock', true);
            var drop = widget.getParent();
            new Fx.Tween(widget).start('opacity', 0).chain(function() {
                widget.destroy();
                dob.store('lock', false);
                _this.checkEmptyDropPanel(drop);
                chain2 && chain2.call(top);
            });
        },
        onAdd:function(widget, widget_panel) {
            widget_panel.widgetsDialog=new top.Dialog('index.php?app=site&ctl=admin_theme_widget&act=add_widgets_page&theme=' + widget.get('widgets_theme'),{width:770,height:500,title:'添加挂件',modal:true,resizeable:false,onShow:function(e){
                this.dialog_body.id='dialogContent';
            }});
        }
    });

});

