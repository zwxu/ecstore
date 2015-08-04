var validatorMap = {
    'required': [LANG_formplus['validate']['required'], function(element, v) {
        return v != null && v != '';
    }],
    'number': [LANG_formplus['validate']['number'], function(element, v) {
        return ! isNaN(v) && ! /^\s+$/.test(v);
    }],
    'msn': [LANG_formplus['validate']['msn'], function(element, v) {
        return v == null || v == '' || /\S+@\S+/.test(v);
    }],
    'skype': [LANG_formplus['validate']['skype'], function(element, v) {
        return ! /\W/.test(v) || /^[a-zA-Z0-9]+$/.test(v);
    }],
    'digits': [LANG_formplus['validate']['digits'], function(element, v) {
        return ! /[^\d]/.test(v);
    }],
    'unsignedint': [LANG_formplus['validate']['unsignedint'], function(element, v) {
        return (!/[^\d]/.test(v) && v > 0);
    }],
    'unsigned': [LANG_formplus['validate']['unsigned'], function(element, v) {
        return (!isNaN(v) && ! /^\s+$/.test(v) && v >= 0);
    }],
    'positive': [LANG_formplus['validate']['positive'], function(element, v) {
        return (!isNaN(v) && ! /^\s+$/.test(v) && v > 0);
    }],
    'alpha': [LANG_formplus['validate']['alpha'], function(element, v) {
        return v == null || v == '' || /^[a-zA-Z]+$/.test(v);
    }],
    'alphaint': [LANG_formplus['validate']['alphaint'], function(element, v) {
        return ! /\W/.test(v) || /^[a-zA-Z0-9]+$/.test(v);
    }],
    'alphanum': [LANG_formplus['validate']['alphanum'], function(element, v) {
        return ! /\W/.test(v) || /^[\u4e00-\u9fa5a-zA-Z0-9]+$/.test(v);
    }],
    'unzhstr': [LANG_formplus['validate']['unzhstr'], function(element, v) {
        return ! /\W/.test(v) || ! /^[\u4e00-\u9fa5]+$/.test(v);
    }],
    'date': [LANG_formplus['validate']['date'], function(element, v) {
        return v == null || v == '' || /^(19|20)[0-9]{2}-([1-9]|0[1-9]|1[012])-([1-9]|0[1-9]|[12][0-9]|3[01])$/.test(v);
    }],
    'email': [LANG_formplus['validate']['email'], function(element, v) {
        return v == null || v == '' || /(\S)+[@]{1}(\S)+[.]{1}(\w)+/.test(v);
    }],
    'mobile': [LANG_formplus['validate']['mobile'], function(element, v) {
        return v == null || v == '' || /^0?1[3458]\d{9}$/.test(v);
    }],
    'tel': [LANG_formplus['validate']['tel'], function(element, v) {
        return v == null || v == '' || /^(0\d{2,3}-?)?[23456789]\d{5,7}(-\d{1,5})?$/.test(v);
    }],
    'phone': [LANG_formplus['validate']['phone'], function(element, v) {
        return v == null || v == '' || /^0?1[3458]\d{9}$|^(0\d{2,3}-?)?[23456789]\d{5,7}(-\d{1,5})?$/.test(v);
    }],
    'zip': [LANG_formplus['validate']['zip'], function(element, v) {
        return v == null || v == '' || /^\d{6}$/.test(v);
    }],
    'url': [LANG_formplus['validate']['url'], function(element, v) {
        return v == null || v == '' || /^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*)(:(\d+))?\/?/i.test(v);
    }],
    'area': [LANG_formplus['validate']['area'], function(element, v) {
        return element.getElements('select').every(function(sel) {
            var selValue = sel.get('value');
            sel.focus();
            return selValue != '' && selValue != '_NULL_';
        });
    }],
    'requiredcheckbox': [LANG_formplus['validate']['requiredonly'], function(element, v, type) {
        type = type || element.get('type');
        var parent = element.getParent();
        var name = element.get('name');
        if (name) element = parent.getElements('input[type=' + type + '][name="' + name + '"]');
        else element = parent.getElements('input[type=' + type + ']');
        return element.some(function(el) {
            return el.checked == true;
        });
    }],
    'requiredradio': [LANG_formplus['validate']['requiredonly'], function(element, v, type) {
        type = type || element.get('type');
        var parent = element.getParent();
        var name = element.get('name');
        if (name) element = parent.getElements('input[type=' + type + '][name="' + name + '"]');
        else element = parent.getElements('input[type=' + type + ']');
        return element.some(function(el) {
            return el.checked == true;
        });
    }]
};

var validate = function(container) {
	//
    container = container || container === 0 ? document.id(container) : null;
    if (!container) return true;
    var formElements = container.match('form')?container.getElements('[vtype]'):[container];
    var err_log = new Elements();
       // console.info(formElements);return false;
        formElements.each(function(element) {

                var vtype = element.get('vtype');
                if (!vtype||!element.isDisplay()) return true;
                var vtypeArr =  vtype.split('&&');
                if (element.get('required')) {
                        vtypeArr = ['required'].combine(vtypeArr.clean());
                }

               var flag = vtypeArr.every(function(key) {

                        var validator = validatorMap[key];
                        if (!validator) return true;
                        var caution = {el:element.getNext('.caution'),msg:(element.get('caution') || validator[0])}; 
                               validator = validator[1]||function(){return true};

                        if (validator(element, element.get('value'), element.get('type'))) {
                                if(caution.el)
                                caution.el.destroy();
                                return true;
                        }else{
                            (caution.el || new Element('span', {
                                        'class': 'error caution notice-inline'
                                    }).inject(element, 'after')).set('html', caution.msg);
                            //element[['input','select','textarea'].contains(element.get('tag'))?'onblur':'onmouseout'] = function() {validate(this);};
                            if(key!=='area'){
                                  element[['input','select','textarea'].contains(element.get('tag'))?'onblur':'onmouseout'] = function() {validate(this);};
                             }else{
                                  element.getElements('select').each(function(el){
                                     el.addEvent('change',function(se){
                                         validate($(this).getParent('.region'));
                                     });                                  
                                  });
                             }
                               return false;
                        }

                    });
              // console.info(flag,element);
                        if(!flag)
                        err_log.include(element);
        });
           
         if(err_log.length){
                    if(container.match('form'))new Fx.Scroll(window,{link:'cancel'}).toElement(err_log.shift());
                    return false;
        }
        return true;
};

(function() {
    var disabled = 'disabled',
        ajaxName = '_ajax',
        attr = 'rel';

    var Sync = this.Sync = new Class({
        Extends: Request.HTML,
        options: {
            syncCache: false,
            disabled: disabled,
            loadtip: 'loading',
            tipCls: '-tip',
            ajaxTip: 'ajax-tip',
            tipHidden: false,
            position: 'before',
            evalScripts: true
        },
        initialize: function(target, options) {
            this.sponsor = target;
            if (target) options = this._getOptions(target, options);
            this.parent(options);
        },
        _getOptions: function(target, options) {
            options = options || {};
            var _options;
            try {
                _options = JSON.decode(target.get('data-ajax-config')) || {};
            } catch(e) {
                _options = {};
            }

            var dataForm, opt, isSubmit = target.type === 'submit' ? true: false;

            if (isSubmit) dataForm = this.dataForm = target.getParent('form') || {};
            if (isSubmit) opt = {
                data: dataForm,
                url: dataForm.action,
                method: dataForm.method || 'post'
            };
            else opt = {
                url: target.get('href'),
                method: 'get'
            };

            _options = Object.merge(opt, options, target.retrieve('_ajax_config', {}), _options);
            return _options;
        },
        _nearText: function(elem) {
            var el = elem,node;
            while (elem) {
                node = elem.lastChild;
                if (typeOf(node) === 'whitespace') node = node.previousSibling;
                if (node && node.nodeType === 3) return $(elem);
                elem = node;
            }
            return el;
        },
        _defaultState: function() {
            this.sponsor && this.sponsor.removeClass(this.options.disabled).retrieve('default:state', function() {})();
            return this;
        },
        onFailure: function() {
            this._defaultState().parent();
        },
        _validate: function(elem) {
            return validate(elem);
        },
        _getCache: function(sponsor) {
            return sponsor.retrieve('ajax:cache', false);
        },
        _clearCache: function(sponsor) {
            sponsor.eliminate('ajax:cache');
        },
        _setCache: function(sponsor, value) {
            sponsor && sponsor.store('ajax:cache', value);
        },
        _progressCache: function(sponsor) {
            var cache = this._getCache(sponsor);
            if (cache) return cache.success(cache.response.data) || true;
        },
        success: function(text, xml) {
            this.response.data = text;
            if ((/text\/jcmd/).test(this.getHeader('Content-type'))) return this._jsonSuccess(text);

            if (['update', 'append', 'filter'].some(function(n) {
                return this.options[n];
            },this)) return this.parent(text, xml);

            this.onSuccess(this.processScripts(text), xml);
        },
        _jsonSuccess: function(text) {
            var json;
            try {
                json = this.response.json = JSON.decode(text);
            } catch(e) {
                json = null;
            }
            this.onSuccess(json);
        },
        onSuccess: function(text) {
            this._defaultState();
            if (this.response.json) this._progress(text);
            this._setCache(this.sponsor, this);
            this.parent(arguments);
        },
        _progress: function(cmd) {
            if (!cmd) return;
            var redirect = cmd['redirect'];
            var m;
            ['error', 'success'].each(function(v, k) {
                m = cmd[v];
                if (this.options.inject && m) {
                    if (v != this.options.tipHidden) return this._injectTip(v, m);
                    this._clearTip(v, m);
                }
            }, this);
            if ((m = cmd['error'])) {
                var state = this.options.progress ? this.options.progress(cmd) : true;
                if (m && state !== false) return Message(m, 'error', function() {
                    if (redirect) {
                        if (redirect == 'back') history.back();
                        else if (redirect == 'reload') location.reload();
                        else location.href = redirect;
                    } else {
                        this._defaultState();
                    }
                }.bind(this));
            } else if ((m = cmd['success'])) {
                return Message(m,'success',function(){
                    if (m && redirect) {
                        if (redirect == 'back') history.back();
                        else if (redirect == 'reload') location.reload();
                        else location.href = redirect;
                    }
                });
            }
        },
        _clearTip: function() {
            if (!this.inject || ! this.tipElem) return;
            this.tipElem.destroy();
        },
        _injectTip: function(cls, html) {
            var options = this.options,
                inject = this.inject = document.id(options.inject),
                position = options.position,
                ajaxTip = options.ajaxTip,
                tipCls = options.tipCls,
                cls = cls + tipCls,
                tipBox;

            if (!inject) return;
            tipBox = inject.getParent();
            if (tipBox && (this.tipElem = tipBox.getElement('.' + ajaxTip))) return this.tipElem.set('html', html);
            new Element('div', {
                'class': cls + ' ' + ajaxTip
            }).set('html', html).inject(inject, position);
        },
        _request: function(sponsor) {
            if (!sponsor) return this;
            sponsor.addClass(this.options.disabled);
            var obj = {
                    'INPUT': 'value',
                    'BUTTON': 'html'
                },
                key,
                btnText,
                btn;
            if (key = obj[sponsor.tagName]) {
                btnText = sponsor.get(key);
                btn = this._nearText(sponsor);
                btn.set(key, this.options.loadtip);
            }
            sponsor.retrieve('default:state') || sponsor.store('default:state', function() {
                sponsor && sponsor.set(key, btnText);
            });
            return this;
        },
        _isCheck: function(elem, options) {
            options = options || {};
            var dataElem = this.dataForm || options.data || this.options.data;

            if (typeOf(dataElem) === 'element' && ! this._validate(dataElem)) return false;
            return true;
        },
        send: function(options) {
            var target = this.sponsor;
            if (target) {
                if (target.hasClass(this.options.disabled) || ! this._isCheck(target, options)) return;
                if (this.options.syncCache && this._progressCache(target)) return;
            }
            this._request(target).parent(options);
        }
    });

    var async = function(elem, event, _form) {
        if (elem.hasClass(disabled)) return false;

        if (_form) {
            if (!validate(_form)) {
                elem.removeClass(disabled);
                return false;
            }
            if (!elem.get('isDisabled')) return elem.addClass(disabled);
        }
        if (sync = elem.retrieve('ajax:cache', false)) return sync.send();
        sync = new Sync(elem).send();
    };

    var Ex_Event_Group = this.Ex_Event_Group = {
        _request: {
            fn: async
        }
    };

    var nearest = function(elem, type) {
        var i = 3,
            el;
        for (; i; i--) {
            if (!elem || elem.nodeType === 9) return el;
            if (elem.type === 'submit' || ($(elem) && $(elem).get(type))) return elem;
            elem = elem.parentNode;
        }
        return el;
    };

    $(document.documentElement || document.body).addEvent('clickd', function(e) {
        var target = $(e.target),
            elem;
        if ((elem = nearest(target, attr))) {
            if (elem.type === 'submit' && elem.get(attr) !== '_request') return async(elem, e, elem.getParent('form'));

            var type = elem.get(attr),
                eventType = Ex_Event_Group[type];
            if (eventType) {
                var fn = eventType['fn'],
                    loader = eventType['loader'];

                e.preventDefault();
                if ($(elem).get && $(elem).get(type)) return elem;

                if (loader) {
                    Ex_Loader(type, function() {
                        fn && fn(elem, e);
                    });
                }
                else {
                    fn && fn(elem, e);
                }
            }
        }

    });

})();

