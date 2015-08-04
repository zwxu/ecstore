var AutoFloatGrid = new Class({
    Implements: [Events, Options],
    options: {
        fixResize: true,
        calcSize: false,
        autoRowSize: {
            'h3': 'height'
        },
        hasEdge: false,
        cols: 4
    },
    initialize: function (container, gridlist, options) {
        container = this.container = $(container) || $(document.body);
        this.setOptions(options);
        var gl = this.gridlist = (typeOf(gridlist) == 'string' ? container.getElements(gridlist) : gridlist);
        if (!gl.length) return;
        this.fireEvent('init', this);
        this.autoColsWidth();
        this.autoRow(this.options.autoRowSize);

        if (this.options.fixResize) window.addEvent('resize', function() {
            this.options.fixResize = (function() {
                clearTimeout(this.options.fixResize);
                this.autoColsWidth();
                this.autoRow(this.options.autoRowSize);
            }).delay(200, this);
        }.bind(this));

        this.fireEvent('grid', this);
    },
    autoColsWidth: function() {
        var glItem = this.gridlist[1] || this.gridlist[0],
            glSize = glItem.measure(function(){return this.getSize();}),
            parent = this.container.getParent() || document.body,
            containerSize,
            containerPatch = this.container.getPatch('padding', 'border'),
            glItemParents = glItem.retrieve('els:parent'),
            pSize = parent.measure(function(){return this.getSize();});
        if(this.gridlist.length < this.options.cols && this.gridlist.length * glSize.x < pSize.x - parent.getPatch('margin').x) {
            this.manal = true;
            return;
        }
        if(this.options.calcSize) {
            containerSize = this.containerSize = {
                x : pSize.x - parent.getPatch('margin').x - this.container.getPatch().x,
                y : pSize.y - parent.getPatch('margin').y - this.container.getPatch().y
            }
            this.container.setStyle('width', containerSize.x);
        }else {
            containerSize = this.containerSize = this.container.measure(function(){return this.getSize();});
        }
        if (!glItemParents) {
            glItemParents = glItem.getParents((this.container.id ? '#' + this.container.id: false) || (this.container.className ? '.' + this.container.className.split(' ')[0] : false));
            glItem.store('els:parent', glItemParents);
        }
        if (glItemParents.length > 1) {
            glItemParents.shift();
            glItemParents.each(function(item) {
                var _patch = $(item).getPatch();
                containerPatch.x += _patch.x;
                containerPatch.y += _patch.y;
            });
        }

        var glItemPatch = glItem.getPatch('padding', 'border');
        var glItemMargin = glItem.getPatch('margin');

        var glAbsoluteRangeWidth = containerSize.x - containerPatch.x - glItemMargin.x * (this.options.hasEdge ? this.options.cols : this.options.cols - 1);
        
		this.gridlist.setStyle('width', Math.floor(glAbsoluteRangeWidth / this.options.cols) - glItemPatch.x-(Browser.ie? 3:0));
        var colLast = this.gridlist[this.options.cols - 1];
        if (!this.options.hasEdge && colLast && colLast.getStyle('margin-right').toInt() > 0) colLast.setStyle('margin-right', 0);
    },
    autoRow: function(forsize) {
        if (!forsize) return;
        var gridArr = [];
        var colsCount = this.options.cols;
        for (i = 0; i <= this.gridlist.length / colsCount; i++) {
            var _arr = this.gridlist.slice(i * colsCount, i * colsCount + colsCount);
            if (_arr.length) {
                var first = _arr[0];
                var last = _arr.getLast();
                for (key in forsize) {
                    first.addClass('row-first');
                    last.addClass('row-last');
                    if (!this.options.hasEdge) {
                        if (first.getStyle('margin-left').toInt() > 0) first.setStyle('margin-left', 0);
                        if (last.getStyle('margin-right').toInt() > 0) last.setStyle('margin-right', 0);
                    }
                    new AutoSize(_arr.invoke('getElement', key), forsize[key]);
                }
            }
        }
    }
});

/*fix Image size*/
var fixProductImageSize = function(images, ptag) {
    if (!images || ! images.length) return;
    images.each(function(img) {
        if (!img.src) return;
        new Asset.image(img.src, {
            onload: function() {
                var imgparent = img.getParent((ptag || 'a'));
                if (!this || ! this.get('width')) return imgparent.adopt(img);
                var imgpsize = {
                    x: imgparent.outerSize().x - imgparent.getPatch().x,
                    y: imgparent.outerSize().y - imgparent.getPatch().y
                };
                if (imgpsize.x <= 0 || imgpsize.y <= 0) return;
                var nSize = this.zoomImg(imgpsize.x, imgpsize.y, true);
                img.set(nSize);
                var _style = {
                    'margin-top': ''
                };
                if (img && img.get('height') && img.get('height').toInt() < imgpsize.y) {
                    _style = Object.merge(_style, {
                        'margin-top': Math.round((imgpsize.y - img.get('height').toInt()) / 2)
                    });
                }
                img.setStyles(_style);
                return true;
            },
            onerror: function() {

            }
        });
    });
};

/*AutoSize*/
var AutoSize = new Class({
    initialize: function(elements, hw) {
        this.elements = $$(elements);
        this.doAuto(hw);
    },
    doAuto: function(hw) {
        if (!hw) {
            hw = 'height';
        }
        var max = 0,
            prop = (!Browser.ie6 ? 'min-': '') + hw, //ie6 ftl
            offset = 'offset' + hw.capitalize();
        this.elements.each(function(element, i) {
            var calc = element[offset];
            if (calc > max) {
                max = calc;
            }
        }, this);
        this.elements.each(function(element, i) {
            element.setStyle(prop, max - (element[offset] - element.getStyle(hw).toInt()));
        });
        return max;
    }
});
/*check inline box*/
var InlineCheck = function(elements,callback){
        elements = $$(elements);
        var y = 0;
        var result = true;
        var columns = 0;
        for(var i = 0 ;i<elements.length;i++){
            var tmpY = elements[i].getPosition().y;
            if(y != 0 && y != tmpY){
                result = false;
                break;
            }
            columns = columns + 1;
            y =tmpY;
        }
        if(typeOf(callback) == 'function'){
            callback(result,columns);
        }else{
            return result;
        };
};

/*check box height*/
var HeightCheck = function(elements,callback){
    elements = $$(elements);
    var height = 0;
    var result = true;
    for(var i = 0;i<elements.length;i++){
        var tmpH = elements[i].getHeight;
        if(height != 0 && height != tmpH){
            result = false;
            break;
        }
        height = tmpH;
    }
    if(typeOf(callback) == 'function'){
        callback(result);
    }else{
        return result;
    };
};
(function() {
    browserStore = null;
    withBrowserStore = function(callback) {
        if (browserStore) return callback(browserStore);
        window.addEvent('domready', function() {
            if ((browserStore = new BrowserStore())) {
                callback(browserStore);
            } else {
                window.addEvent('load', function() {
                    callback(browserStore = new BrowserStore());
                });
            }
        });
    };
})();

