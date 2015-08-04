var editor_style_1 = new Abstract({
    style_init: function() {
        this.mce_handle = this.el;
        this.addEvent('click', this.status.bind(this));
        this.addEvent('click', function(e) {
            if (!this.$init) {
                this.mce_handle.setOpacity(1);
                if (this.inc.doc.body.innerHTML.substr(0, 6) == '&nbsp;') {
                    this.inc.doc.body.innerHTML = this.inc.doc.body.innerHTML.slice(6);
                }
                var obj = this;
                //this.mce_handle.setOpacity(1);
                $ES('img', this.mce_handle).addEvent('click', function() {
                    this.inc.win.focus();
                }.bind(this));

                $E('.ft_select', this.mce_handle).addEvent('change', function(e) {
                    var v = this.getValue();
                    if (!v) return;
                    if (Browser.ie && obj.range) obj.range.select();
                    obj.set('fontName', v);
                });
                $E('.fs_select', this.mce_handle).addEvent('change', function(e) {
                    var v = this.getValue();
                    if (!v) return;
                    if (Browser.ie && obj.range) obj.range.select();
                    obj.set('fontSize', v);
                });
                $$($E('.fontColorPicker', this.el), $E('.fontBGColorPicker', this.el), $E('.ft_select', this.mce_handle), $E('.fs_select', this.mce_handle)).addEvent('click', function() {
                    this.range = null;
                    if (Browser.ie && this.getSelection().type.toLowerCase() != 'none') {
                        this.range = this.getRange();
                    }
                    if (!Browser.ie) {
                        this.range = this.getRange();
                    }
                }.bind(this));

                Ex_Loader('picker', function() {
                    new GoogColorPicker($E('.fontColorPicker', this.el), {
                        onSelect: function(hex, rgb, e) {
                            if (Browser.ie && this.range) this.range.select();
                            this.set('forecolor', hex);
                        }.bind(this),
                        onShow: function(ins) {
                            if (Browser.ie && this.range) this.range.select();
                        }.bind(this)
                    });
                    new GoogColorPicker($E('.fontBGColorPicker', this.el), {
                        onSelect: function(hex, rgb, e) {
                            if (Browser.ie) {
                                if (this.range) this.range.select();
                                return this.set('backColor', hex);
                            }
                            this.set('hilitecolor', hex);
                        }.bind(this),
                        onShow: function(ins) {
                            if (Browser.ie && this.range) this.range.select();
                        }.bind(this)
                    });
                }.bind(this));

                /*new MooRainbow($E('.fontColorPicker',this.el),{
                id:this.el.id+'_fontColor'+$time(),
                onChange:function(){
                  if(Browser.ie&&this.range)
                  this.range.select();
                }.bind(this),
                onComplete: function(color) {
                    this.set('forecolor',color.hex);
                }.bind(this)
            });
            new MooRainbow($E('.fontBGColorPicker',this.el),{
                id:this.el.id+'_bgFontColor'+$time(),
                onChange:function(){
                   if(Browser.ie&&this.range)
                  this.range.select();
                }.bind(this),
                onComplete: function(color) {
                    if(Browser.ie)
                    return  this.set('backColor',color.hex);
                    this.set('hilitecolor',color.hex);
                }.bind(this)
            });*/
            }
            this.$init = true;
        }.bind(this));
        this.styler = $ES('.x-section', this.el);
        this.stylerEl = $ES('.x-style', this.el);
        this.align = $ES('.x-align', this.el);
        this.setting = $ES('.x-enable', this.el);

    },
    status: function(e) {
        new Event(e);
        if (!this.target) {
            this.target = e.target.getElementsByTagName('body')[0] || e.target;
        }
        var style = this.target.style;
        if (style['background-color'] == 'transparent') {
            style['background-color'] = '#fff';
        }
        this.styler.setStyle('background-color', (style['background-color'] == 'transparent') ? '#fff': style['background-color']);
        this.stylerEl.setStyle('color', style['color']);

        var status = this.queryValues('Bold', 'Italic', 'Underline', 'strikeThrough', 'subscript', 'superscript', 'align', 'CreateLink', 'FontName', 'FontSize', 'ForeColor', 'FormatBlock', 'insertOrderedList', 'insertUnorderedList');

        if (status.align == 'center') {
            this.align[0].parentNode.className = '';
            this.align[1].parentNode.className = 'in';
            this.align[2].parentNode.className = '';
        } else if (status.align == 'right') {
            this.align[0].parentNode.className = '';
            this.align[1].parentNode.className = '';
            this.align[2].parentNode.className = 'in';
        } else if (status.align == 'left') {
            this.align[0].parentNode.className = 'in';
            this.align[1].parentNode.className = '';
            this.align[2].parentNode.className = '';
        } else {
            this.align[0].parentNode.className = '';
            this.align[1].parentNode.className = '';
            this.align[2].parentNode.className = '';
        }
        this.setting.each(function(el) {
            el.parentNode.className = status[el.getAttribute('value')] ? 'in': '';
        });

    },
    set: function(cmd, arg) {
        if (!this.inc || ! this.inc.win) return;
        if (Browser.ie) {
            this.inc.win.focus();
        }
        this.exec(cmd, arg);
        try {
            this.status.call(this);
        } catch(e) {}
        //this.status(this).delay(8,this);
    }
});

