(function() {

    var specExtend = {
        sortKeys: function(specHS) {
            var sortItem = Object.keys(specHS).sort(),
                sortHS = {};
            Object.each(sortItem, function(item) {
                sortHS[item] = specHS[item];
            });
            return sortHS;
        },
        filterSpec: function(regExp, state) {
            var filterArr = [],
                specSelected = Object.values(this.selectedHS);
            this.pid = null;

            Object.each(this.PRODUCT_HASH, function(v, key) {
                var spec = Object.values(v.spec_private_value_id).sort(),
                    reg = ":" + spec.join(':') + ":";
                if (regExp.test(reg)) filterArr.include(spec);
                if (specSelected.length == this.specNum && specSelected.every(function(it) {
                    return spec.contains(it);
                }, this)) {
                    this.pid = key;
                }
            }, this);

            filterArr.sort();
            if (state) return this.merge(filterArr);
            return filterArr;
        },
        merge: function(arr) {
            var spec_arr = [];
            var sarr = [];
            arr && arr[0] && arr[0].length && arr[0].each(function(e, i) {
                arr.each(function(el) {
                    sarr.include(el[i]);
                });
                spec_arr.push(sarr);
            });
            return spec_arr;
        },
        collect: function(prearr, arr, hs, key, state) {
            var inarr = [],
                hskeys = Object.keys(hs);
            prearr.each(function(el, index) {
                var barr = [];
                if (key != index && hskeys.contains(index.toString()) && hskeys.length != prearr.length && ! state) {
                    barr.combine(prearr[index].include(arr[index]));
                } else {
                    arr && arr[index] && barr.combine(arr[index].filter(function(item) {
                        return el.contains(item);
                    }));
                }
                inarr.include(barr);
            });
            inarr[key] = prearr[key];
            return inarr;
        },
        to_find: function(selectedHS, specvid) {
            var hsvalue = Object.values(selectedHS).sort(),
                hskey = Object.keys(selectedHS),
                subReg = ":" + hsvalue.join(":(\\d+:)*") + ":",
                tpReg = new RegExp("" + subReg.split("(:\\d+:)*") + ""),
                keys = Object.keyOf(selectedHS, specvid),
                sbCollect,
                filterArr = [],
                chs = $H(selectedHS);

            if (hskey.length > 2) {
                var sbReg = "";
                chs.erase(keys).each(function(item, key) {
                    var tmphs = $H(chs).erase(key).set(keys, specvid),
                        v = Object.values(this.sortKeys(tmphs)).sort();
                    sbReg += ":" + v.join(":(\\d+:)*") + ":|";
                }, this);
                sbReg = new RegExp("" + sbReg.substr(0, sbReg.length - 1) + "");
                if (chs) var preStore = arguments.callee.call(this, chs, chs.getValues()[0]);
                var sbSpec = this.filterSpec(sbReg, true);
                sbCollect = this.collect(preStore, sbSpec, selectedHS, keys, true);
            } else {
                sbCollect = this.filterSpec(new RegExp("" + hsvalue.join("|") + ""), true);
            }

            if (hskey.length == this.specNum) return sbCollect;
            var tpCollect = this.filterSpec(tpReg, true);
            return this.collect(sbCollect, tpCollect, selectedHS, keys);
        },
        init: function(spec, specvid, specv_Arr) {
            var selectedHS = this.selectedHS = this.sortKeys(spec.selectedHS);
            var specValues;
            this.PRODUCT_HASH = spec.productData;
            this.specNum = spec.specItems.length;
            if (Object.keys(selectedHS).length > 1) {
                specValues = this.to_find(selectedHS, specvid).flatten();
            } else {
                var regExp = new RegExp(":" + specvid + ":");
                specValues = this.filterSpec(regExp).flatten();

                specv_Arr.each(function(item) {
                    if (item.contains(specvid)) specValues.combine(item);
                });
            }
            return specValues;
        }
    };

    var hand = function(el) {
        var specHandles = el.getElements('.spec-item .handle'),
            specContents = el.getElements('.spec-item .content'),
            tempSlipIndex = 0,
            tempCurrentIndex = - 1;

        specHandles.length && specHandles.each(function(handle, index) {
            var content = specContents[index];
            content.store('handle', handle);
            handle.addEvent('click', function(e) {
                if (tempCurrentIndex >= 0 && tempCurrentIndex != index) {
                    specHandles[tempCurrentIndex].removeClass('curr');
                    specContents[tempCurrentIndex].removeClass('content-curr');
                }
                tempCurrentIndex = index;
                this.toggleClass('curr');
                var offsetEl = this.getOffsetParent();
                var handlePosition = this.getPosition(offsetEl);
                var handleSize = this.getSize().y - this.getPatch('border').y;
                content.setStyles({
                    // 'left': handlePosition.x,
                    'top': handlePosition.y + handleSize
                });
                content.toggleClass('content-curr');

                // 改造：计算宽度撑满规格列表最大值 --by Tyler Chao 2012.6.26
                if (!content.isDisplay()) return;
                var parent = this.getParent('.dialog-specauto');
                var psize, csize, hsize, patch = content.getPatch().x, width;
                if (parent) {
                    parent = Browser.ie6 ? parent.getParent() : parent.getElement('.spec-content');
                    psize = parent.getSize().x - parent.getPatch().x;
                    csize = content.getSize().x;
                    hsize = handlePosition.x + this.getSize().x;
                    if (psize != csize) {
                        if (hsize > psize || hsize >= csize || Browser.ie6 && hsize == csize - patch) width = hsize - patch;
                        width && content.setStyle('width', width);
                    }
                }
            });
        });
    };
    var specMenu = function(content, el) {
        var handle = content.retrieve('handle'),
            text;
        if (el) text = el.getElement('img') ? el.getElement('img').alt: el.getElement('span').get('text');
        else text = LANG_spec['select'];
        handle.getElement('span').set('text', text).addClass('select');

        // 处理handle的文字超出规格对话框即截断 --by Tyler Chao 2012.6.27
        var parent = handle.getParent('.dialog-specauto');
        if (parent) {
            parent = Browser.ie6 ? parent.getParent() : parent.getElement('.spec-content');
            var psize = parent.getSize().x;
            var hsize = handle.getSize().x;
            var inner = handle.getElement('.inner');
            inner && inner.setStyles(hsize > psize ? 'overflow:hidden;width:' + (psize - handle.getPatch().x - inner.getPatch().x) + 'px;' : '');
        }
        handle.removeClass('curr');
        content.removeClass('content-curr');
    };
    var specDialog = function(content) {
        // 非ie6 需计算规格对话框高度 --by Tyler Chao 2012.6.27
        if (!Browser.ie6) {
            var container = content.getParent('.popup-container');
            if (container) {
                var dialog = container.retrieve('instance');
                dialog.body.setStyle('height', '');
                dialog.content.setStyle('height', dialog.content.getElement('.ec-spec-box').measure(function() {
                    return this.getSize().y;
                }));
            }
        }
    };

    var Goods_spec = this.Goods_spec = new Class({
        Implements: [Events, Options],
        options: {
            onLoad: hand,
            /*onSelected:function(){},
            onComplete:function(){},
            onUpdatedefault:function(){},*/
            productData: {},
            spec_hash: {},
            selectItems: [],
            lockCls: 'lock',
            selectedCls: 'selected',
            specBtn: 'a[specvid]',
            specItems: '.specItem',
            isDefault: false,
            isSelected: true
        },
        initialize: function(contains, options) {
            if (!$(contains)) return;
            contains = this.contains = $(contains);
            this.setOptions(options);
            var option = this.options,
                proData = contains.getElement('input[data-type-product]').value,
                specData = contains.getElement('input[data-type-spec]').value;

            this.productData = proData ? JSON.decode(proData) : option.productData;
            this.spec_hash = specData ? JSON.decode(specData) : option.spec_hash;
            this.specData = option.specData;
            this.specItems = contains.getElements(option.specItems);
            this.specBtn = contains.getElements(option.specBtn);
            this.fireEvent('load', contains).attach();
            Goods_spec._selectUpdate['updateBtn'](contains);
        },
        attach: function() {
            var self = this;
            specDialog(this.contains);
            this.specBtn.addEvent('click', function(e) {
                e && e.stop();
                self.selectspec.call(self, this);
                specDialog(this);
            });
            // ie6 单独处理详情页规格值宽度问题 --by Tyler Chao 2012.6.27
            if (Browser.ie6 && this.contains.getElement('div[data-sync-type]')) {
                var width = this.contains.getElement('.spec-item').getSize().x;
                this.specItems.setStyle('width', width - this.specItems[0].getPatch().x);
            }
            //　改造：增加设定：当每个规格只有一个规格值时默认选中
            //　--by Tyler Chao 2012.6.23 alter for set selected default if it's only one spec value in every specs.
            this.specItems.each(function(si) {
                // 平铺规格需计算规格值文字超出的情况 --by Tyler Chao 2012.6.28
                var sv = si.getElement('.spec-values');
                var parent = si.getParent('.dialog-specwrap');
                if (sv && parent) {
                    var psize = parent.measure(function() {
                        return this.getSize().x - this.getPatch().x;
                    });
                    var svsize = sv.measure(function() {
                        return this.getSize().x;
                    });
                    sv.getElement('span') && sv.getElement('span').setStyles(psize < svsize ? 'overflow:hidden;width:' + (psize - sv.getPatch().x - sv.getElement('ul').getPatch().x - sv.getElement('li').getPatch().x - sv.getElement('a').getPatch().x - sv.getElement('span').getPatch().x) + 'px;' : '');
                }

                var specs = si.getElements(this.options.specBtn);
                var list = specs.length ? specs.filter(function(sp) {
                    return !sp.hasClass(self.options.lockCls);
                }) : [];
                if (list.length >= 1 && !! this.options.isDefault) list[0].fireEvent('click', {stop: function() {}});
            }, this);
        },
        selectspec: function(specEl) {
            var options = this.options,
                lockCls = options.lockCls;

            if (specEl.hasClass(lockCls)) return null;
            var specid = specEl.get('specid'),
                specvid = specEl.get('specvid'),
                selectedCls = options.selectedCls,
                contains = this.contains,
                content = specEl.getParent(this.options.specItems) || specEl.getParent('ul'),
                em = content.retrieve('handle', content).getElement('em');

            em && em.removeClass('warn');
            if (specEl.hasClass(selectedCls)) {
                specEl.removeClass(selectedCls);
                var selected = this.selected = contains.getElements('.' + selectedCls),
                    selectednum = selected.length;

                if (content.hasClass('content')) specMenu(content);

                if (selectednum <= 1) {
                    this.specSelectedCall(specvid, specid, specEl);
                    this.specBtn.removeClass(lockCls);
                }
                if (selectednum) {
                    specvid = selected[0].get('specvid');
                    specid = selected[0].get('specid');
                    this.specSelectedCall(specvid, specid, specEl);
                }
                return this;
            }

            var tempsel = content.retrieve('ts', specEl);
            if (tempsel != specEl) {
                tempsel.removeClass(selectedCls);
            }
            content.store('ts', specEl.addClass(selectedCls));

            if (content.hasClass('content')) specMenu(content, specEl);

            this.selected = contains.getElements('.' + selectedCls);
            this.specSelectedCall(specvid, specid, specEl);
        },
        specSelectedCall: function(specvid, specid, spec) {
            var selectedHS = this.selectedHS = {},
                specItems = this.specItems,
                options = this.options,
                specEl = this.specEl,
                selectedCls = options.selectedCls,
                specBtn = options.specBtn,
                selected = this.selected,
                lockCls = options.lockCls,
                num = this.specItems.length,
                selectedBtn = this.selectedBtn = [];

            specItems.each(function(item, i) {
                var el;
                if ((el = item.getElement('.' + selectedCls))) {
                    selectedBtn.push(el);
                    selectedHS[i] = el.get('specvid');
                }
            });

            if (!this.specArr) {
                var v;
                this.specArr = [];
                this.specItems.each(function(item, index) {
                    if ((v = item.getElements(specBtn))) this.specArr.push(v.get('specvid'));
                }, this);
            }

            var specAll = specExtend.init.call(specExtend, this, specvid, this.specArr);
            var pid = this.pid = specExtend.pid;

            this.specBtn.each(function(s) {
                var bool = specAll.indexOf(s.get('specvid')) != - 1;
                s[bool ? 'removeClass': 'addClass'](lockCls);
            });

            this.fireEvent('selected', spec).selectedcall(spec,selected);
            if (!selectedBtn.length) return this.fireEvent('updatedefault', this).updatedefault();

            if (num == selected.length) this.update(pid, this.productData).complete(pid, this.productData);
            return this;
        },
        selectedcall: function(target,selected) {
            var selectUpdate = this.options.selectItems.combine(['selectedItem', 'updateBtn', 'updatePic']);
            selectUpdate.each(function(n) {
                var fn;
                if ((fn = Goods_spec._selectUpdate[n])) fn.call(this, target,selected);
            }, this);
        },
        update: function(pid, pdata) {
            var specUpdate = this.specUpdate = this.contains.getElements('[updatespec]');
            if ( !! specUpdate.length && pid) specUpdate.each(function(el) {
                var _k = el.get('updatespec').split('_'),
                    up,
                    _v = pdata[pid][_k[1]];
                if ((up = Goods_spec._selectedUpdate[_k[0]])) {
                    up.call(this, el, _v, pid, pdata);
                }
            }, this);
            return this;
        },
        updatedefault: function() {
            var specUpdate = this.contains.getElements('[updatespec]'),
                value;
            specUpdate.each(function(el) {
                if ((value = el.retrieve('default:html'))) el.set(el.tagName == 'INPUT' ? 'value': 'html', value);
                if ((value = el.retrieve('default:callback'))) value();
            });
        },
        complete: function(pid, data) {
            return this.fireEvent('complete', this, pid, data);
        }
    });

    Goods_spec._selectUpdate = {
        'selectedItem': function(spec) {
            var em = (spec.getParent('.content') && spec.getParent('.content').retrieve('handle') || spec.getParent('.spec-item')).getElement('em');
            em[spec.hasClass('selected') ? 'addClass': 'removeClass']('check');
        },
        'check': function(contains, updateBtn) {
            var spec_item_nocheck = [];
            var spec_item_nocheck_el = [];
            var spec_item_check = [];
            var spec_item_selected = [];
            contains.getElements('.spec-item em').each(function(em, i) {
                if (!em.hasClass('check')) {
                    spec_item_nocheck.push(em.get('text'));
                    spec_item_nocheck_el.push(em);
                }
                else spec_item_check.push(em.get('text'));
                spec_item_selected.push(contains.getElements('.spec-item .selected')[i]);
            });
            // if(spec_item_nocheck.length) updateBtn.store('tip:text',LANG_spec['select_spec']+spec_item_nocheck.join(','));
            // else updateBtn.eliminate('tip:text');
            return {
                check: spec_item_check,
                nocheck: spec_item_nocheck,
                nocheckel: spec_item_nocheck_el,
                selected: spec_item_selected
            };
        },
        // 改造：不选中规格值时显示提示信息 --by Tyler Chao 2012.6.21 alter for display tips if haven't select spec value.
        'updateBtn': function(contains) {
            contains = contains || this.contains;
            var updateBtn = contains.getElements('.updateBtn').eliminate('tip:text');

            updateBtn && updateBtn.addEvent('click', function(e) {
                e.stop();
                this.blur();
                var spec_item = Goods_spec._selectUpdate.check(contains, this);
                var form = this.getParent('form');
                if (!spec_item.nocheck.length) {
                    if (!form) this.fireEvent('_update_spec', [spec_item.check, spec_item.selected]);
                    else{
                        if (form.target === '_dialog_minicart') form.fireEvent('submit', [e, this]);
                        else form.submit();
                    }
                } else {
                    $$(spec_item.nocheckel).length && $$(spec_item.nocheckel).addClass('warn');
                    Message.error(LANG_spec['select_spec'] + spec_item.nocheck.join(','));
                }
            });
        }
    };

    Goods_spec._selectedUpdate = {
        'text': function(el, v) {
            var key = el.tagName == 'INPUT' ? 'value': 'html',
                dfv = el.retrieve('default:html', el.get(key));
            return el.store('default:html', dfv).set(key, v);
        },
        'updatepid': function(el, v, pid) {
            el.store('default:html', el.get('value')).set('value', pid);
        }
    };
})();

