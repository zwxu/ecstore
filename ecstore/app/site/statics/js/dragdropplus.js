/*
 DragDropPlus  -用于拖拽模板区块的类.

<div id='drag_operate_box' class='drag_operate_box' style='visibility:hidden;'>
  <div class='drag_handle_box'>
    <table cellpadding='0' cellspacing='0' width='100%'>
      <tr>
        <td><span class='dhb_title'>标题</span></td>
        <td width='40'><span class='dhb_edit'>编辑</span></td>
        <td width='40'><span class='dhb_del'>删除</span></td>
      </tr>
    </table>
  </div>
</div>
<div id='drag_ghost_box' class='drag_ghost_box' style='visibility:hidden'></div>
*/

var DragDropPlus = new Class({
    Implements: [Options, Events],
    options: {
        /*ddScope: window
        onInitDrags:function(){},
        onInitDrops:function(){},
        onEdit:function(){},
        onDelete:function(){},
        onAdd:function(){}*/
    },
    initialize: function(drags, drops, options) {
        this.dragSelecterString = drags;
        this.dropSelecterString = drops;
        this.drags = $$(drags);
        this.drops = $$(drops);
        this.setOptions(options);
        /*if (this.options.ddScope) {
            this.winScroll = new Scroller(this.options.ddScope, {
                velocity: 1
            });
        }*/
        this.drag_operate_box = $('drag_operate_box');
        if (!this.drag_operate_box) return;
        this.drag_operate_box.store('lock', false);
        this.drag_handle_box = this.drag_operate_box.getElement('.drag_handle_box');
        this.scrollFx = new Fx.Scroll(document, {
            fps:50,
            duration: 200,
            link: 'cancel'
        });
        this.dobFx = new Fx.Morph(this.drag_operate_box, {
            fps: 50,
            duration: 200,
            link: 'cancel'
        });
        this.dhbFx = new Fx.Morph(this.drag_handle_box, {
            fps: 50,
            duration: 200,
            link: 'cancel'
        });

        this.dragSign = $('drag_ghost_box').inject(document.body);
        this.fireEvent('onInit',this);

        this.initDOBBase(this.drops);
        this.initDrags(this.drags);
        this.initDrops(this.drops);

    },
    checkEmptyDropPanel: function(dp) {
        if (!dp || !dp.hasClass(this.dropSelecterString.substring(1, this.dropSelecterString.length))) return;
        if (!dp.getElement(this.dragSelecterString)) {
            if (!dp.getElement('.empty_drop_box')) {
                var emptyBox = new Element('div.empty_drop_box').set('html','&nbsp;<button type="button" class="btn btn-add-widgets"><span><span><i class="icon"></i>添加挂件</span></span></button>').inject(dp);
                emptyBox.addEvent('click', function(e) {
                    this.fireEvent('add', [emptyBox], this);
                }.bind(this));
                //new Element('div.empty_drop_box').set('text',LANG_dragdropplus['empty_box']).inject(dp);
                if (this.dragmoveInstance) {
                    dp.store('droppanel', true);
                    this.dragmoveInstance.droppables.include(dp);
                }
            }
        } else {
            if (dp.getElement('.empty_drop_box')) {
                dp.getElement('.empty_drop_box').destroy();
            }
        }
    },
    dragLeave: function() {
        //this.checkEmptyDropPanel(arguments[1]);
    },
    dargInject: function(dob, element) {
        var dragging = this.dragging;
        if (!dragging) return;
        var where = 'inside';
        if (!element.retrieve('droppanel')) {
            where = dragging.getAllPrevious().contains(element) ? 'before': 'after';
        }
        dragging.inject(element, where);
        this.checkEmptyDropPanel(dob.retrieve('droped'));
        this.checkEmptyDropPanel(element);
        dob.store('droped', element);
        this.dragSign.setStyles(dragging.getCoordinates());
        //dob.setStyles({width:dragging.getSize().x,height:dragging.getSize().y});
    },
    getDropables: function() {
        var drag = this.dragging;
        var dropables = Array.from(this.drags).erase(drag).combine(this.drops.filter(function(el) {
            if (el.getElement(this.dragSelecterString)) {
                el.store('droppanel', false);
                return false;
            } else {
                el.store('droppanel', true);
                return true;
            }
        }.bind(this)));
        return dropables;
    },
    initDOBBase: function(drops) {
        var dob = this.drag_operate_box;
        var dhb = this.drag_handle_box;
        var _this = this;
        if (!drops) return;

        var updown = dhb.getElements('.btn-up-slot,.btn-down-slot');
        updown.addEvent('click',function(e){
            var drag = dob.retrieve('drag');
            var els = drag.getParent().getChildren();
            var swap = drag[this.hasClass('btn-up-slot')?'getPrevious':'getNext']();
            if(!swap) return;
            e.stop();
            var sorter = new Fx.Sort(els, {
                duration: 250,
                mode: 'vertical',
                link: 'chain',
                onComplete:function(){
                    sorter.rearrangeDOM();
                    document.body.fireEvent('mouseover',{target:drag});
                    _this.fireEvent('upDown',[dob.retrieve('drag')],_this);
                }
            }).swap(drag,swap);
        });
        dhb.getElement('.btn-edit-widgets').addEvent('click', function(e) {
            e.stop();
            this.fireEvent('edit', [dob.retrieve('drag')], this);
        }.bind(this));
        dob.addEvent('dblclick', function(e) {
            e.stop();
            this.fireEvent('edit', [dob.retrieve('drag')], this);
        }.bind(this));
        dhb.addEvent('dblclick', function(e) {
            e.stop();
        }.bind(this));
        dhb.getElement('.btn-del-widgets').addEvent('click', function(e) {
            e.stop();
            this.fireEvent('delete', [dob.retrieve('drag')], this);
        }.bind(this));
        dhb.getElements('li').addEvent('click', function(e) {
            e.stop();
            this.fireEvent('add', [dob.retrieve('drag'), $(e.target)], this);
        }.bind(this));
    },
    initDrags: function(drags) {
        var _this = this;
        document.body.addEvents({
            'mouseover': function(e) {
                e = $(e.target);
                var drag = e.getParent(_this.dragSelecterString);
                var dob = _this.drag_operate_box;
                var dhb = _this.drag_handle_box;
                var minWidth = 235;
                if(!drag && !e.hasClass(_this.dragSelecterString.substr(1))) return; //dob.setStyle('visibility','hidden');
                if(dob.retrieve('lock')) return;
                drag = drag || e;
                _this.fireEvent('initDrags', [drag, drags], _this);
                dhb.set('title', drag.get('title') || "&nbsp;");
                dob.setStyle('visibility', 'visible');
                dob.store('drag', drag);
                var toStyles = drag.getCoordinates();
                toStyles = Object.append(toStyles, {
                    top: toStyles.top - dhb.getSize().y,
                    height: toStyles.height - dob.getPatch().y + dhb.getSize().y,
                    width: toStyles.width - dob.getPatch().x
                });
                delete toStyles.bottom;
                delete toStyles.right;
                var dobW =  minWidth + dob.getPatch().x;
                _this.dhbFx.set({left:toStyles.left + dobW + dob.getStyle('border-left').toInt() > document.body.getSize().x && !Browser.ie6 ? toStyles.width - dobW : 0});
                _this.dobFx.set(toStyles);
                // if(dob.getPosition(document.body).y < document.body.getScroll().y) {
                    // $('drag_handle_arrow') ? $('drag_handle_arrow').show() : new Element('div#drag_handle_arrow',{html:'<div>up</div>',style:'position:absolute;bottom:0;right:0;width:20px;height:20px;background:#FFF;line-height:20px;text-align:center;color:#333;cursor:pointer;',events:{'click':function(){_this.scrollFx.toElement(dob);}}}).inject(_this.drag_operate_box);
                // }

                dhb.getElements('.btn-up-slot,.btn-down-slot').removeClass('disabled');
                if(!drag.getPrevious()) dhb.getElement('.btn-up-slot').addClass('disabled');
                if(!drag.getNext()) dhb.getElement('.btn-down-slot').addClass('disabled');
            }
        });
        drags.each(function(drag){
            _this.checkEmptyDrag(drag);
            drag.getElements('form').removeEvents().addEvent('submit', function(e) {
                e.stop();
            });
            drag.getElements('a').removeEvents().addEvent('click', function(e) {
                e.stop();
            });
        });
    },
    checkEmptyDrag: function(drag){
        window.addEvent('load',function(){
            if(!drag.offsetHeight){
                this.fireEvent('emptyDrag',[drag],this);
                //new Element('div.empty_drag_box',{html:'(NO DATA)'}).inject(drag);
            }
        }.bind(this));
    },
    initDrops: function(drops) {
        drops.each(function(drop, index) {
            this.checkEmptyDropPanel(drop);
            this.fireEvent('initDrops', [drop, drops], this);
        }, this);
    }
});

