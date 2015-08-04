var mceInstance = new Class({
    Implements: [Events, Options],
    options: {
        acitve: false,
        maskOpacity: 0.5,
        autoHeight: false,
        cleanup: true,
        includeBase: true
    },
    initialize: function(seri, options) {
        this.seri = seri;

        var name = 'mce_body_' + seri;
        this.el = $(name);
        this.setOptions(options);
        this.input = $E('textarea', this.el).set('ishtml', 'true');
        this.frmContainer = $(name + "_frm_container");
        var that = this;
        var includeBase = this.options.includeBase;
        var state = 0;
        var frm = this.frm = new Element('iframe', {
            'src': DESKTOPRESFULLURL + '/about.html',
            'id': name + "_frm",
            'name': name + "_frm",
            'frameborder': 0,
            'border': 0
        }).addEvent('load', function() {
            if (state) return;
            state = 1;
            var editor = that;

            var frmWin = frm.contentWindow;
            var baseStr = '<base href=' + SHOPBASE + '/>';
            var documentTemplate = ['<html>',
                '<head>',
                (includeBase ? baseStr: ""),
                '<script>window.onerror=function(){return false;}</script>',
                '<link rel="stylesheet" type="text/css" href="' + DESKTOPRESURL + '/wysiwyg_editor.css"/>',
                '</head>',
                '<body spellcheck="false" id="' + seri + '" style="break-word:break-all;word-wrap:break-word;">',
                (editor.cleanup(editor.input.getProperty('value')) || '&nbsp;'),
                '</body></html>'].join('\n');

            frmWin.document.open();
            frmWin.document.write(documentTemplate);
            frmWin.document.close();
            frmWin.document.designMode = 'on';

            if (frmWin.document.addEventListener) frmWin.document.addEventListener('mousedown', editor.active.bind(editor), false);
            else frmWin.document.attachEvent('on' + 'mousedown', editor.active.bind(editor));
            editor.win = frmWin;
            editor.doc = frmWin.document;
            editor.input.setAttribute('filled', 'true');
            this.removeEvent('load', arguments.callee);
        });

        frm.inject(this.frmContainer);

        this.input.getValue = function() {
            if (!this.input.getAttribute('filled')) {
                return 'textarea-unfilled';
            }
            if ('textarea' == this.editType) {
                return this.input.value;
            }
            var v = this.getValue();
            this.input.value = v;
            return v;
        }.bind(this);

        if (this.options.autoHeight) this.autoHeight.call(this);
        if ($(name)) this.el = $(name).setStyle('visibility', 'visible');
        this.input.store('mce:instance', this);
    },
    autoHeight: function() {
        try {
            this.frm.setStyle('height', this.doc.body.offsetHeight + 50);
        } catch(e) {}
    },
    setValue: function() {
        this.doc.body.innerHTML = this.input.value;
    },
    getValue: function() {
        return this.cleanup(this.doc.body.innerHTML);
    },
    regexpReplace: function(in_str, reg_exp, replace_str, opts) {
        if (in_str == null) return in_str;

        if (typeof(opts) == "undefined") opts = 'g';

        var re = new RegExp(reg_exp, opts);
        return in_str.replace(re, replace_str);
    },
    cleanup: function(html) {

        var br = '<br/>';
        var xhtml = [
            [/(<(?:img|input)[^\/>]*)>/g, '$1 />'] // Greyed out -  make img tags xhtml compatable     #if (this.options.xhtml)
        ];
        var semantic = [
            [/<li>\s*<div>(.+?)<\/div><\/li>/g, '<li>$1</li>'], // remove divs from <li>        #if (Browser.Engine.trident)
            [/<span style="font-weight: bold;">(.*)<\/span>/gi, '<strong>$1</strong>'], //
            [/<span style="font-style: italic;">(.*)<\/span>/gi, '<em>$1</em>'], //
            [/<b\b[^>]*>(.*?)<\/b[^>]*>/gi, '<strong>$1</strong>'], //
            [/<i\b[^>]*>(.*?)<\/i[^>]*>/gi, '<em>$1</em>'], //
            [/<u\b[^>]*>(.*?)<\/u[^>]*>/gi, '<span style="text-decoration: underline;">$1</span>'], //
            [/<p>[\s\n]*(<(?:ul|ol)>.*?<\/(?:ul|ol)>)(.*?)<\/p>/ig, '$1<p>$2</p>'], // <p> tags around a list will get moved to after the list.  not working properly in safari? #if (['gecko', 'presto', 'webkit'].contains(Browser.Engine.name))
            [/<\/(ol|ul)>\s*(?!<(?:p|ol|ul|img).*?>)((?:<[^>]*>)?\w.*)$/g, '</$1><p>$2</p>'], // ''
            [/<br[^>]*><\/p>/g, '</p>'], // Remove <br>'s that end a paragraph here.
            [/<p>\s*(<img[^>]+>)\s*<\/p>/ig, '$1\n'], // If a <p> only contains <img>, remove the <p> tags
            [/<p([^>]*)>(.*?)<\/p>(?!\n)/g, '<p$1>$2</p>\n'], // Break after paragraphs
            [/<\/(ul|ol|p)>(?!\n)/g, '</$1>\n'], // Break after </p></ol></ul> tags
            [/><li>/g, '>\n\t<li>'], // Break and indent <li>
            [/([^\n])<\/(ol|ul)>/g, '$1\n</$2>'], // Break before </ol></ul> tags
            [/([^\n])<img/ig, '$1\n<img'], // Move images to their own line
            [/^\s*$/g, ''] // Delete empty lines in the source code (not working in opera)
        ];
        var nonSemantic = [
            [/\s*<br ?\/?>\s*<\/p>/gi, '</p>'] // if (!this.options.semantics) - Remove padded paragraphs
        ];
        var appleCleanup = [
            [/<br class\="webkit-block-placeholder">/gi, "<br />"], // Webkit cleanup - add an if(webkit) check
            [/<span class="Apple-style-span">(.*)<\/span>/gi, '$1'], // Webkit cleanup - should be corrected not to get messed over on nested spans - SG!!!
            [/\s+class="Apple-style-span"/gi, ''], // Webkit cleanup
            [/<span style="">/gi, ''], // Webkit cleanup
            [/^([\w\s]+.*?)<div>/i, '<p>$1</p><div>'], // remove stupid apple divs     #if (Browser.Engine.webkit)
            [/<div>(.+?)<\/div>/ig, '<p>$1</p>'] // remove stupid apple divs     #if (Browser.Engine.webkit)
        ];
        var cleanup = [
            [/<br\s*\/?>/gi, br], // Fix BRs, make it easier for next BR steps.
            //  [/><br\/?>/g, '>'],                                                     // Remove (arguably) useless BRs
            [/^<br\/?>/g, ''], // Remove leading BRs - perhaps combine with removing useless brs.
            [/<br\/?>$/g, ''], // Remove trailing BRs
            [/<br\/?>\s*<\/(h1|h2|h3|h4|h5|h6|li|p)/gi, '</$1'], // Remove BRs from end of blocks
            [/<p>\s*<br\/?>\s*<\/p>/gi, '<p>\u00a0</p>'], // Remove padded paragraphs - replace with non breaking space
            [/<p>(&nbsp;|\s)*<\/p>/gi, '<p>\u00a0</p>'], // ''
            //[/<p>\W*<\/p>/g, ''],                                                   //  Remove ps with other stuff, may mess up some formatting.
            [/<\/p>\s*<\/p>/g, '</p>'] // Remove empty <p> tags
            //  [/<[^> ]*/g, function(match){return match.toLowerCase();}],             // Replace uppercase element names with lowercase
            //   [/<[^>]*>/g, function(match){                                           // Replace uppercase attribute names with lowercase
            //     match = match.replace(/ [^=]+=/g, function(match2){return match2.toLowerCase();});
            //     return match;
            //   }],
            //  [/<[^>]*>/g, function(match){                                           // Put quotes around unquoted attributes
            //     match = match.replace(/( [^=]+=)([^"][^ >]*)/g, "$1\"$2\"");
            //    return match;
            //  }]
        ];
        var depracated = [
            // The same except for BRs have had optional space removed
            [/<p>\s*<br ?\/?>\s*<\/p>/gi, '<p>\u00a0</p>'], // modified as <br> is handled previously
            [/<br>/gi, "<br />"], // Replace improper BRs if (this.options.xhtml) Handled at very beginning
            [/<br ?\/?>$/gi, ''], // Remove leading and trailing BRs
            [/^<br ?\/?>/gi, ''], // Remove trailing BRs
            [/><br ?\/?>/gi, '>'], // Remove useless BRs
            [/<br ?\/?>\s*<\/(h1|h2|h3|h4|h5|h6|li|p)/gi, '</$1'], // Remove BRs right before the end of blocks
            //Handled with DOM:
            [/<p>(?:\s*)<p>/g, '<p>'] // Remove empty <p> tags
        ];

        cleanup.extend(xhtml);
        cleanup.extend(semantic);
        if (Browser.chrome || Browser.safari) cleanup.extend(appleCleanup);

        cleanup.each(function(reg) {
            html = html.replace(reg[0], reg[1]);
        });
        html = html.trim();

        return html;
    },
    active: function() {
        if (!this.actived) {
            this.actived = true;
            var doc = this.doc;

            var _fn = function(e) {
                this.fireEvent('docClick', new Event(e));
            }.bind(this);

            if (doc.addEventListener) doc.addEventListener('click', _fn, false);
            else doc.attachEvent('on' + 'click', _fn);

        }
        this.fireEvent('active', this);
    },
    sleep: function() {}
});

var mceHandler = new Class({
    Implements: [Events, Options],
    initialize: function(name, instance, options) {
        try {
            this.el = $(name);
            $ES('img', this.el).each(function(e) {
                new DropMenu(e);
            });
            this.setOptions(options);
            if (instance) {
                if (instance.length) {
                    instance.each(this.addInstance.bind(this));
                } else {
                    this.addInstance.call(this, instance);
                }
            }
        } catch(e) {
            alert(e.message);
        }
        if ('style_init' in this) {
            this.style_init();
        }
    },
    addInstance: function(inc) {
        inc.addEvent('active', this.active.bind(this));
        inc.addEvent('docClick', this.docClick.bind(this));
    },
    active: function(e) {
        this.inc = e;
        if (this.inc) {
            this.inc.sleep.call(this.inc);
        }

    },
    docClick: function(e) {
        var curEl = this.currentEl = $(e.target);
        this.fireEvent('click', e);
    },
    getSelection: function() {
        // this.inc.win.focus();
        return (document.selection) ? this.inc.doc.selection: this.inc.win.getSelection();
    },
    getRange: function() {
        if (!this.inc) return false;
        //if (this.range) return this.range;
        var s = this.getSelection();
        if (!s) return null;
        try {
            return s.rangeCount > 0 ? s.getRangeAt(0) : (s.createRange ? s.createRange() : null);
        } catch(e) {
            // IE bug when used in frameset
            return this.inc.doc.body.createTextRange();
        }
    },
    setRange: function(range) {
        if (range.select) {
            Function.attempt(function() {
                range.select();
            });
        } else {
            var s = this.getSelection();
            if (s.addRange) {
                s.removeAllRanges();
                s.addRange(range);
            }
        }
    },
    clearSelection: function() {
        if (window.getSelection) { // all browsers, except IE before version 9
            var selection = this.inc.win.getSelection();
            selection.deleteFromDocument();
            /* The deleteFromDocument does not work in Opera.
               Work around this bug.*/
            if (!selection.isCollapsed) {
                var selRange = selection.getRangeAt(0);
                selRange.deleteContents();
            }
            // The deleteFromDocument works in IE,
            // but a part of the new content becomes selected
            // prevent the selection
            if (selection.anchorNode) {
                selection.collapse(selection.anchorNode, selection.anchorOffset);
            }
        }
        else if (document.selection) { // Internet Explorer
            this.inc.doc.selection.clear();
        }
    },
    emptySelection: function() {
        var s = this.getSelection();
        s.empty ? s.empty() : s.removeAllRanges();
    },
    selectNode: function(node, collapse) {
        var r = this.getRange();
        var s = this.getSelection();

        if (r.moveToElementText) {
            Function.attempt(function() {
                r.moveToElementText(node);
                r.select();
            });
        } else if (s.addRange) {
            collapse ? r.selectNodeContents(node) : r.selectNode(node);
            s.removeAllRanges();
            s.addRange(r);
        } else {
            s.setBaseAndExtent(node, 0, node, 1);
        }

        return node;
    },
    isCollapsed: function() {
        var r = this.getRange();
        if (r.item) return false;
        return r.boundingWidth == 0 || this.getSelection().isCollapsed;
    },
    collapse: function(toStart) {
        var r = this.getRange();
        var s = this.getSelection();

        if (r.select) {
            r.collapse(toStart);
            r.select();
        } else {
            toStart ? s.collapseToStart() : s.collapseToEnd();
        }
    },
    getContent: function() {
        var r = this.getRange();
        var body = new Element('body');

        if (this.isCollapsed()) return '';

        if (r.cloneContents) {
            body.appendChild(r.cloneContents());
        } else if (r.item != undefined || r.htmlText != undefined) {
            body.set('html', r.item ? r.item(0).outerHTML: r.htmlText);
        } else {
            body.set('html', r.toString());
        }

        var content = body.get('html');
        return content;
    },
    getRangeText: function() {
        var r = this.getRange();
        var s = this.getSelection();
        return this.isCollapsed() ? '': r.text || (s.toString ? s.toString() : '');
    },
    getNode: function() {
        var r = this.getRange();

        if (!Browser.ie) {
            var el = null;

            if (r) {
                el = r.commonAncestorContainer;

                // Handle selection a image or other control like element such as anchors
                if (!r.collapsed && r.startContainer == r.endContainer && r.startOffset - r.endOffset < 2 && r.startContainer.hasChildNodes()) el = r.startContainer.childNodes[r.startOffset];

                while (typeOf(el) != 'element') el = el.parentNode;
            }

            return document.id(el);
        }

        return document.id(r.item ? r.item(0) : r.parentElement());
    },
    insertContent: function(content) {
        if (Browser.ie) {
            var r = this.getRange();
            if (r.pasteHTML) {
                r.pasteHTML(content);
                r.collapse(false);
                r.select();
            } else if (r.insertNode) {
                r.deleteContents();
                if (r.createContextualFragment) {
                    r.insertNode(r.createContextualFragment(content));
                } else {
                    var doc = this.inc.doc;
                    var fragment = doc.createDocumentFragment();
                    var temp = doc.createElement('div');
                    fragment.appendChild(temp);
                    temp.outerHTML = content;
                    r.insertNode(fragment);
                }
            }
        } else {
            this.inc.doc.execCommand('insertHTML', false, content);
        }
    },
    exec: function(cmd, arg) {
        if (!this.busy) {
            this.busy = true;
            if (!cmd || ! this.inc) return;
            if (this.dlg) {
                if (Browser.ie && this.range) {
                    this.range.select();
                }
                this.dlg.hide();
                this.dlg = null;
            }
            switch (cmd) {
            case "formatblock":
                this.inc.doc.execCommand("FormatBlock", false, '<' + arg + '>');
                break;
            case "wrap":
                this.exec('insertHTML', arg[0] + this.getRangeText() + arg[1]);
                break;
            case "insertHTML":
                if (this.getNode().tagName == "IMG") {
                    this.clearSelection();
                }
                if (this.replaceEl && this.replaceEl.tagName == "BODY") {
                    try {
                        var _tempDiv = this.inc.doc.createElement('div');
                        _tempDiv.innerHTML = arg;
                        _tempDiv = _tempDiv.firstChild;

                        this.replaceEl.parentNode.replaceChild(_tempDiv, this.replaceEl);
                    } catch(e) {
                        MessageBox.error(e);
                    } finally {
                        this.replaceEl = null;
                    }
                } else {
                    if (Browser.ie) {
                        this.inc.win.focus();
                        var r = this.getRange();
                        if (r.pasteHTML) r.pasteHTML(arg);
                    } else {
                        this.inc.doc.execCommand('insertHTML', false, arg);
                    }
                }
                break;
            default:
                try {
                    this.inc.doc.execCommand(cmd, false, arg);
                } catch(e) {
                    MessageBox.error(e);
                }
            }

            this.busy = false;
        }
    },
    mklink: function() {
        if (!this.inc) return;

        this.replaceEl = null;
        var curEl = this.currentEl;
        var data, node = this.getNode();
        if (curEl && curEl.tagName && 'body' == curEl.tagName.toLowerCase() && ! this.getRangeText()) {
            return;
        }
        // if (curEl && curEl.tagName && curEl.tagName.toLowerCase() == 'img') {
        // return MessageBox.error(LANG_Editor['error']);
        // }
        if (curEl && curEl.tagName && curEl.tagName.toLowerCase() == 'a') {
            if (this.isCollapsed()) {
                this.selectNode(node);
            }
            data = {
                text: curEl.innerHTML,
                href: curEl.href,
                alt: curEl.alt,
                title: curEl.title,
                target: curEl.target
            };
            this.replaceEl = curEl;
        } else {
            if (this.isCollapsed()) {
                this.selectNode(node);
            }
            data = {
                'text': this.getContent()
            };
        }

        this.dialog('link', {
            height: null,
            width: 450,
            ajaxoptions: {
                method: 'post',
                'data': data
            }
        });
    },
    editHTML: function() {
        var _this = this;
        if (!this.inc) return;
        var mhh = $('mce_handle_htmledit_' + this.inc.seri);
        var mh = $('mce_handle_' + this.inc.seri);
        this.inc.input.getValue();
        mhh.show();
        mh.hide();
        var frmcis = this.inc.frm.getSize();
        this.inc.input.show();
        this.inc.frmContainer.hide();
        mhh.getElement('.returnwyswyg').addEvent('click', function() {
            mh.show();
            mhh.hide();
            _this.inc.doc.body.innerHTML = _this.inc.input.value.clean().trim();
            _this.inc.input.hide();
            _this.inc.frmContainer.show();
            _this.inc.editType = 'wysiwyg';
            this.removeEvent('click', arguments.callee);
        });

        this.inc.editType = 'textarea';
        /*var data={'htmls':this.inc.getValue(),'seri':this.inc.seri};
        this.dialog('editHTML',{title:'HTML编辑模式',height:null,width:450,ajaxoptions:{method:'post','data':data}});*/
    },
    dialog: function(action, options) {
        if (!this.inc) return;
        this.inc.win.focus();
        this.range = null;

        this.range = this.getRange();

        var url = ('image' == action) ? 'index.php?app=desktop&act=alertpages&goto=' + encodeURIComponent("index.php?app=image&ctl=admin_manage&act=image_broswer&type=big") : 'index.php?ctl=editor&act=' + action;

        options = Object.filter(options, function(o) {
            return !! o;
        });

        var _this = this;
        if ('image' == action) return Ex_Loader('modedialog', function() {
            new imgDialog(url, {
                onCallback: function(image_id, image_src) {
                    _this.insertImg(image_id, image_src);
                }
            });
        });

        this.dlg = new Dialog(url, $merge(options, {
            modal: true
        }));

        window.curEditor = this;
    },
    insertImg: function(image_id, image_src, center) {
        //if (SHOPBASE && image_src.contains(SHOPBASE)) {
        //image_src=image_src.replace(SHOPBASE,'');
        //}
        var img = new Element('img', {
            src: image_src
        });
        // imgID = 'img' + Slick.uidOf(img);
        // img.set('id', imgID).set('turl', image_src);
        var d = new Element('div').adopt(img);
        var html = center ? '<div align="center">' + d.get('html') + '</div>': d.get('html');
        this.exec.bind(this)('insertHTML', html);

        // var win_img = this.inc.doc.getElementById(imgID);
        // console.log(win_img);
        // if (!win_img) return;
        // win_img.src = img.get('turl');
        // win_img.removeAttribute('turl');
        // win_img.removeAttribute('id');
    },
    queryValue: function(s, state) {
        if (s == 'align') {
            s = 'justifyRight';
        }
        try {
            if (state) {
                return this.inc.doc.queryCommandState(s);
            } else {
                return this.inc.doc.queryCommandValue(s);
            }
        } catch(e) {}
    },
    queryValues: function() {
        var ret = {};
        var stat = ['Bold', 'Italic', 'Underline', 'strikeThrough', 'subscript', 'superscript', 'insertOrderedList', 'insertUnorderedList'];
        for (var i = 0; i < arguments.length; i++) {
            ret[arguments[i]] = this.queryValue(arguments[i], stat.contains(arguments[i]));
        }
        return ret;
    }
});

