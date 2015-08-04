// MooTools: the javascript framework.
// Load this file's selection again by visiting: http://mootools.net/more/46ef6122ea474ead1e8982c001f0c0f0
// Or build this file again with packager using: packager build More/Class.Refactor More/Fx.Elements More/Fx.Accordion More/Fx.Scroll More/Drag More/Drag.Move More/Sortables More/Assets More/Tips
/*
---

script: More.js

name: More

description: MooTools More

license: MIT-style license

authors:
  - Guillermo Rauch
  - Thomas Aylott
  - Scott Kyle
  - Arian Stolwijk
  - Tim Wienk
  - Christoph Pojer
  - Aaron Newton

requires:
  - Core/MooTools

provides: [MooTools.More]

...
*/

/*
---

script: Class.Refactor.js

name: Class.Refactor

description: Extends a class onto itself with new property, preserving any items attached to the class's namespace.

license: MIT-style license

authors:
  - Aaron Newton

requires:
  - Core/Class
  - /MooTools.More

# Some modules declare themselves dependent on Class.Refactor
provides: [Class.refactor, Class.Refactor]

...
*/

Class.refactor = function(original, refactors){

    Object.each(refactors, function(item, name){
        var origin = original.prototype[name];
        if (origin && origin.$origin) origin = origin.$origin;
        if (origin && typeof item == 'function'){
            original.implement(name, function(){
                var old = this.previous;
                this.previous = origin;
                var value = item.apply(this, arguments);
                this.previous = old;
                return value;
            });
        } else {
            original.implement(name, item);
        }
    });

    return original;

};


/*
---

script: Fx.Elements.js

name: Fx.Elements

description: Effect to change any number of CSS properties of any number of Elements.

license: MIT-style license

authors:
  - Valerio Proietti

requires:
  - Core/Fx.CSS
  - /MooTools.More

provides: [Fx.Elements]

...
*/

Fx.Elements = new Class({

    Extends: Fx.CSS,

    initialize: function(elements, options){
        this.elements = this.subject = $$(elements);
        this.parent(options);
    },

    compute: function(from, to, delta){
        var now = {};

        for (var i in from){
            var iFrom = from[i], iTo = to[i], iNow = now[i] = {};
            for (var p in iFrom) iNow[p] = this.parent(iFrom[p], iTo[p], delta);
        }

        return now;
    },

    set: function(now){
        for (var i in now){
            if (!this.elements[i]) continue;

            var iNow = now[i];
            for (var p in iNow) this.render(this.elements[i], p, iNow[p], this.options.unit);
        }

        return this;
    },

    start: function(obj){
        if (!this.check(obj)) return this;
        var from = {}, to = {};

        for (var i in obj){
            if (!this.elements[i]) continue;

            var iProps = obj[i], iFrom = from[i] = {}, iTo = to[i] = {};

            for (var p in iProps){
                var parsed = this.prepare(this.elements[i], p, iProps[p]);
                iFrom[p] = parsed.from;
                iTo[p] = parsed.to;
            }
        }

        return this.parent(from, to);
    }

});


/*
---

script: Fx.Accordion.js

name: Fx.Accordion

description: An Fx.Elements extension which allows you to easily create accordion type controls.

license: MIT-style license

authors:
  - Valerio Proietti

requires:
  - Core/Element.Event
  - /Fx.Elements

provides: [Fx.Accordion]

...
*/

Fx.Accordion = new Class({

    Extends: Fx.Elements,

    options: {/*
        onActive: function(toggler, section){},
        onBackground: function(toggler, section){},*/
        fixedHeight: false,
        fixedWidth: false,
        display: 0,
        show: false,
        height: true,
        width: false,
        opacity: true,
        alwaysHide: false,
        trigger: 'click',
        initialDisplayFx: true,
        returnHeightToAuto: true
    },

    initialize: function(){
        var defined = function(obj){
            return obj != null;
        };

        var params = Array.link(arguments, {
            'container': Type.isElement, //deprecated
            'options': Type.isObject,
            'togglers': defined,
            'elements': defined
        });
        this.parent(params.elements, params.options);

        this.togglers = $$(params.togglers);
        this.previous = -1;
        this.internalChain = new Chain();

        if (this.options.alwaysHide) this.options.wait = true;

        if (this.options.show || this.options.show === 0){
            this.options.display = false;
            this.previous = this.options.show;
        }

        if (this.options.start){
            this.options.display = false;
            this.options.show = false;
        }

        this.effects = {};

        if (this.options.opacity) this.effects.opacity = 'fullOpacity';
        if (this.options.width) this.effects.width = this.options.fixedWidth ? 'fullWidth' : 'offsetWidth';
        if (this.options.height) this.effects.height = this.options.fixedHeight ? 'fullHeight' : 'scrollHeight';

        for (var i = 0, l = this.togglers.length; i < l; i++) this.addSection(this.togglers[i], this.elements[i]);

        this.elements.each(function(el, i){
            if (this.options.show === i){
                this.fireEvent('active', [this.togglers[i], el]);
            } else {
                for (var fx in this.effects) el.setStyle(fx, 0);
            }
        }, this);

        if (this.options.display || this.options.display === 0 || this.options.initialDisplayFx === false){
            this.display(this.options.display, this.options.initialDisplayFx);
        }

        if (this.options.fixedHeight !== false) this.options.returnHeightToAuto = false;
        this.addEvent('complete', this.internalChain.callChain.bind(this.internalChain));
    },

    addSection: function(toggler, element){
        toggler = document.id(toggler);
        element = document.id(element);
        this.togglers.include(toggler);
        this.elements.include(element);

        var test = this.togglers.contains(toggler);
        var idx = this.togglers.indexOf(toggler);
        var displayer = this.display.pass(idx, this);

        toggler.store('accordion:display', displayer).addEvent(this.options.trigger, displayer);

        if (this.options.height) element.setStyles({'padding-top': 0, 'border-top': 'none', 'padding-bottom': 0, 'border-bottom': 'none'});
        if (this.options.width) element.setStyles({'padding-left': 0, 'border-left': 'none', 'padding-right': 0, 'border-right': 'none'});

        element.fullOpacity = 1;
        if (this.options.fixedWidth) element.fullWidth = this.options.fixedWidth;
        if (this.options.fixedHeight) element.fullHeight = this.options.fixedHeight;
        element.setStyle('overflow', 'hidden');

        if (!test){
            for (var fx in this.effects) element.setStyle(fx, 0);
        }
        return this;
    },

    removeSection: function(toggler, displayIndex){
        var idx = this.togglers.indexOf(toggler);
        var element = this.elements[idx];
        var remover = function(){
            this.togglers.erase(toggler);
            this.elements.erase(element);
            this.detach(toggler);
        }.bind(this);

        if (this.now == idx || displayIndex != null){
            this.display(displayIndex != null ? displayIndex : (idx - 1 >= 0 ? idx - 1 : 0)).chain(remover);
        } else {
            remover();
        }
        return this;
    },

    detach: function(toggler){
        var remove = function(toggler){
            toggler.removeEvent(this.options.trigger, toggler.retrieve('accordion:display'));
        }.bind(this);

        if (!toggler) this.togglers.each(remove);
        else remove(toggler);
        return this;
    },

    display: function(index, useFx){
        if (!this.check(index, useFx)) return this;
        useFx = useFx != null ? useFx : true;
        index = (typeOf(index) == 'element') ? this.elements.indexOf(index) : index;
        if (index == this.previous && !this.options.alwaysHide) return this;
        if (this.options.returnHeightToAuto){
            var prev = this.elements[this.previous];
            if (prev && !this.selfHidden){
                for (var fx in this.effects){
                    prev.setStyle(fx, prev[this.effects[fx]]);
                }
            }
        }

        if ((this.timer && this.options.wait) || (index === this.previous && !this.options.alwaysHide)) return this;
        this.previous = index;
        var obj = {};
        this.elements.each(function(el, i){
            obj[i] = {};
            var hide;
            if (i != index){
                hide = true;
            } else if (this.options.alwaysHide && ((el.offsetHeight > 0 && this.options.height) || el.offsetWidth > 0 && this.options.width)){
                hide = true;
                this.selfHidden = true;
            }
            this.fireEvent(hide ? 'background' : 'active', [this.togglers[i], el]);
            for (var fx in this.effects) obj[i][fx] = hide ? 0 : el[this.effects[fx]];
        }, this);

        this.internalChain.clearChain();
        this.internalChain.chain(function(){
            if (this.options.returnHeightToAuto && !this.selfHidden){
                var el = this.elements[index];
                if (el) el.setStyle('height', 'auto');
            };
        }.bind(this));
        return useFx ? this.start(obj) : this.set(obj);
    }

});

/*<1.2compat>*/
/*
    Compatibility with 1.2.0
*/
var Accordion = new Class({

    Extends: Fx.Accordion,

    initialize: function(){
        this.parent.apply(this, arguments);
        var params = Array.link(arguments, {'container': Type.isElement});
        this.container = params.container;
    },

    addSection: function(toggler, element, pos){
        toggler = document.id(toggler);
        element = document.id(element);

        var test = this.togglers.contains(toggler);
        var len = this.togglers.length;
        if (len && (!test || pos)){
            pos = pos != null ? pos : len - 1;
            toggler.inject(this.togglers[pos], 'before');
            element.inject(toggler, 'after');
        } else if (this.container && !test){
            toggler.inject(this.container);
            element.inject(this.container);
        }
        return this.parent.apply(this, arguments);
    }

});
/*</1.2compat>*/


/*
---

script: Fx.Scroll.js

name: Fx.Scroll

description: Effect to smoothly scroll any element, including the window.

license: MIT-style license

authors:
  - Valerio Proietti

requires:
  - Core/Fx
  - Core/Element.Event
  - Core/Element.Dimensions
  - /MooTools.More

provides: [Fx.Scroll]

...
*/

(function(){

Fx.Scroll = new Class({

    Extends: Fx,

    options: {
        offset: {x: 0, y: 0},
        wheelStops: true
    },

    initialize: function(element, options){
        this.element = this.subject = document.id(element);
        this.parent(options);

        if (typeOf(this.element) != 'element') this.element = document.id(this.element.getDocument().body);

        if (this.options.wheelStops){
            var stopper = this.element,
                cancel = this.cancel.pass(false, this);
            this.addEvent('start', function(){
                stopper.addEvent('mousewheel', cancel);
            }, true);
            this.addEvent('complete', function(){
                stopper.removeEvent('mousewheel', cancel);
            }, true);
        }
    },

    set: function(){
        var now = Array.flatten(arguments);
        if (Browser.firefox) now = [Math.round(now[0]), Math.round(now[1])]; // not needed anymore in newer firefox versions
        this.element.scrollTo(now[0] + this.options.offset.x, now[1] + this.options.offset.y);
    },

    compute: function(from, to, delta){
        return [0, 1].map(function(i){
            return Fx.compute(from[i], to[i], delta);
        });
    },

    start: function(x, y){
        if (!this.check(x, y)) return this;
        var element = this.element,
            scrollSize = element.getScrollSize(),
            scroll = element.getScroll(),
            size = element.getSize();
            values = {x: x, y: y};

        for (var z in values){
            if (!values[z] && values[z] !== 0) values[z] = scroll[z];
            if (typeOf(values[z]) != 'number') values[z] = scrollSize[z] - size[z];
            values[z] += this.options.offset[z];
        }

        return this.parent([scroll.x, scroll.y], [values.x, values.y]);
    },

    toTop: function(){
        return this.start(false, 0);
    },

    toLeft: function(){
        return this.start(0, false);
    },

    toRight: function(){
        return this.start('right', false);
    },

    toBottom: function(){
        return this.start(false, 'bottom');
    },

    toElement: function(el){
        var position = document.id(el).getPosition(this.element),
            scroll = isBody(this.element) ? {x: 0, y: 0} : this.element.getScroll();
        return this.start(position.x + scroll.x, position.y + scroll.y);
    },

    scrollIntoView: function(el, axes, offset){
        axes = axes ? Array.from(axes) : ['x','y'];
        el = document.id(el);
        var to = {},
            position = el.getPosition(this.element),
            size = el.getSize(),
            scroll = this.element.getScroll(),
            containerSize = this.element.getSize(),
            edge = {
                x: position.x + size.x,
                y: position.y + size.y
            };

        ['x','y'].each(function(axis){
            if (axes.contains(axis)){
                if (edge[axis] > scroll[axis] + containerSize[axis]) to[axis] = edge[axis] - containerSize[axis];
                if (position[axis] < scroll[axis]) to[axis] = position[axis];
            }
            if (to[axis] == null) to[axis] = scroll[axis];
            if (offset && offset[axis]) to[axis] = to[axis] + offset[axis];
        }, this);

        if (to.x != scroll.x || to.y != scroll.y) this.start(to.x, to.y);
        return this;
    },

    scrollToCenter: function(el, axes, offset){
        axes = axes ? Array.from(axes) : ['x', 'y'];
        el = document.id(el);
        var to = {},
            position = el.getPosition(this.element),
            size = el.getSize(),
            scroll = this.element.getScroll(),
            containerSize = this.element.getSize();

        ['x','y'].each(function(axis){
            if (axes.contains(axis)){
                to[axis] = position[axis] - (containerSize[axis] - size[axis])/2;
            }
            if (to[axis] == null) to[axis] = scroll[axis];
            if (offset && offset[axis]) to[axis] = to[axis] + offset[axis];
        }, this);

        if (to.x != scroll.x || to.y != scroll.y) this.start(to.x, to.y);
        return this;
    }

});

function isBody(element){
    return (/^(?:body|html)$/i).test(element.tagName);
};

})();


/*
---

script: Drag.js

name: Drag

description: The base Drag Class. Can be used to drag and resize Elements using mouse events.

license: MIT-style license

authors:
  - Valerio Proietti
  - Tom Occhinno
  - Jan Kassens

requires:
  - Core/Events
  - Core/Options
  - Core/Element.Event
  - Core/Element.Style
  - Core/Element.Dimensions
  - /MooTools.More

provides: [Drag]
...

*/

var Drag = new Class({

    Implements: [Events, Options],

    options: {/*
        onBeforeStart: function(thisElement){},
        onStart: function(thisElement, event){},
        onSnap: function(thisElement){},
        onDrag: function(thisElement, event){},
        onCancel: function(thisElement){},
        onComplete: function(thisElement, event){},*/
        snap: 6,
        unit: 'px',
        grid: false,
        style: true,
        limit: false,
        handle: false,
        invert: false,
        preventDefault: false,
        stopPropagation: false,
        modifiers: {x: 'left', y: 'top'}
    },

    initialize: function(){
        var params = Array.link(arguments, {
            'options': Type.isObject,
            'element': function(obj){
                return obj != null;
            }
        });

        this.element = document.id(params.element);
        this.document = this.element.getDocument();
        this.setOptions(params.options || {});
        var htype = typeOf(this.options.handle);
        this.handles = ((htype == 'array' || htype == 'collection') ? $$(this.options.handle) : document.id(this.options.handle)) || this.element;
        this.mouse = {'now': {}, 'pos': {}};
        this.value = {'start': {}, 'now': {}};

        this.selection = (Browser.ie) ? 'selectstart' : 'mousedown';


        if (Browser.ie && !Drag.ondragstartFixed){
            document.ondragstart = Function.from(false);
            Drag.ondragstartFixed = true;
        }

        this.bound = {
            start: this.start.bind(this),
            check: this.check.bind(this),
            drag: this.drag.bind(this),
            stop: this.stop.bind(this),
            cancel: this.cancel.bind(this),
            eventStop: Function.from(false)
        };
        this.attach();
    },

    attach: function(){
        this.handles.addEvent('mousedown', this.bound.start);
        return this;
    },

    detach: function(){
        this.handles.removeEvent('mousedown', this.bound.start);
        return this;
    },

    start: function(event){
        var options = this.options;

        if (event.rightClick) return;

        if (options.preventDefault) event.preventDefault();
        if (options.stopPropagation) event.stopPropagation();
        this.mouse.start = event.page;

        this.fireEvent('beforeStart', this.element);

        var limit = options.limit;
        this.limit = {x: [], y: []};

        var styles = this.element.getStyles('left', 'right', 'top', 'bottom');
        this._invert = {
            x: options.modifiers.x == 'left' && styles.left == 'auto' && !isNaN(styles.right.toInt()) && (options.modifiers.x = 'right'),
            y: options.modifiers.y == 'top' && styles.top == 'auto' && !isNaN(styles.bottom.toInt()) && (options.modifiers.y = 'bottom')
        };

        var z, coordinates;
        for (z in options.modifiers){
            if (!options.modifiers[z]) continue;

            var style = this.element.getStyle(options.modifiers[z]);

            // Some browsers (IE and Opera) don't always return pixels.
            if (style && !style.match(/px$/)){
                if (!coordinates) coordinates = this.element.getCoordinates(this.element.getOffsetParent());
                style = coordinates[options.modifiers[z]];
            }

            if (options.style) this.value.now[z] = (style || 0).toInt();
            else this.value.now[z] = this.element[options.modifiers[z]];

            if (options.invert) this.value.now[z] *= -1;
            if (this._invert[z]) this.value.now[z] *= -1;

            this.mouse.pos[z] = event.page[z] - this.value.now[z];

            if (limit && limit[z]){
                var i = 2;
                while (i--){
                    var limitZI = limit[z][i];
                    if (limitZI || limitZI === 0) this.limit[z][i] = (typeof limitZI == 'function') ? limitZI() : limitZI;
                }
            }
        }

        if (typeOf(this.options.grid) == 'number') this.options.grid = {
            x: this.options.grid,
            y: this.options.grid
        };

        var events = {
            mousemove: this.bound.check,
            mouseup: this.bound.cancel
        };
        events[this.selection] = this.bound.eventStop;
        this.document.addEvents(events);
    },

    check: function(event){
        if (this.options.preventDefault) event.preventDefault();
        var distance = Math.round(Math.sqrt(Math.pow(event.page.x - this.mouse.start.x, 2) + Math.pow(event.page.y - this.mouse.start.y, 2)));
        if (distance > this.options.snap){
            this.cancel();
            this.document.addEvents({
                mousemove: this.bound.drag,
                mouseup: this.bound.stop
            });
            this.fireEvent('start', [this.element, event]).fireEvent('snap', this.element);
        }
    },

    drag: function(event){
        var options = this.options;

        if (options.preventDefault) event.preventDefault();
        this.mouse.now = event.page;

        for (var z in options.modifiers){
            if (!options.modifiers[z]) continue;
            this.value.now[z] = this.mouse.now[z] - this.mouse.pos[z];

            if (options.invert) this.value.now[z] *= -1;
            if (this._invert[z]) this.value.now[z] *= -1;

            if (options.limit && this.limit[z]){
                if ((this.limit[z][1] || this.limit[z][1] === 0) && (this.value.now[z] > this.limit[z][1])){
                    this.value.now[z] = this.limit[z][1];
                } else if ((this.limit[z][0] || this.limit[z][0] === 0) && (this.value.now[z] < this.limit[z][0])){
                    this.value.now[z] = this.limit[z][0];
                }
            }

            if (options.grid[z]) this.value.now[z] -= ((this.value.now[z] - (this.limit[z][0]||0)) % options.grid[z]);

            if (options.style) this.element.setStyle(options.modifiers[z], this.value.now[z] + options.unit);
            else this.element[options.modifiers[z]] = this.value.now[z];
        }

        this.fireEvent('drag', [this.element, event]);
    },

    cancel: function(event){
        this.document.removeEvents({
            mousemove: this.bound.check,
            mouseup: this.bound.cancel
        });
        if (event){
            this.document.removeEvent(this.selection, this.bound.eventStop);
            this.fireEvent('cancel', this.element);
        }
    },

    stop: function(event){
        var events = {
            mousemove: this.bound.drag,
            mouseup: this.bound.stop
        };
        events[this.selection] = this.bound.eventStop;
        this.document.removeEvents(events);
        if (event) this.fireEvent('complete', [this.element, event]);
    }

});

Element.implement({

    makeResizable: function(options){
        var drag = new Drag(this, Object.merge({
            modifiers: {
                x: 'width',
                y: 'height'
            }
        }, options));

        this.store('resizer', drag);
        return drag.addEvent('drag', function(){
            this.fireEvent('resize', drag);
        }.bind(this));
    }

});


/*
---

script: Drag.Move.js

name: Drag.Move

description: A Drag extension that provides support for the constraining of draggables to containers and droppables.

license: MIT-style license

authors:
  - Valerio Proietti
  - Tom Occhinno
  - Jan Kassens
  - Aaron Newton
  - Scott Kyle

requires:
  - Core/Element.Dimensions
  - /Drag

provides: [Drag.Move]

...
*/

Drag.Move = new Class({

    Extends: Drag,

    options: {/*
        onEnter: function(thisElement, overed){},
        onLeave: function(thisElement, overed){},
        onDrop: function(thisElement, overed, event){},*/
        droppables: [],
        container: false,
        precalculate: false,
        includeMargins: true,
        checkDroppables: true
    },

    initialize: function(element, options){
        this.parent(element, options);
        element = this.element;

        this.droppables = $$(this.options.droppables);
        this.container = document.id(this.options.container);

        if (this.container && typeOf(this.container) != 'element')
            this.container = document.id(this.container.getDocument().body);

        if (this.options.style){
            if (this.options.modifiers.x == "left" && this.options.modifiers.y == "top"){
                var parentStyles,
                    parent = element.getOffsetParent();
                var styles = element.getStyles('left', 'top');
                if (parent && (styles.left == 'auto' || styles.top == 'auto')){
                    element.setPosition(element.getPosition(parent));
                }
            }

            if (element.getStyle('position') == 'static') element.setStyle('position', 'absolute');
        }

        this.addEvent('start', this.checkDroppables, true);
        this.overed = null;
    },

    start: function(event){
        if (this.container) this.options.limit = this.calculateLimit();

        if (this.options.precalculate){
            this.positions = this.droppables.map(function(el){
                return el.getCoordinates();
            });
        }

        this.parent(event);
    },

    calculateLimit: function(){
        var element = this.element,
            container = this.container,

            offsetParent = document.id(element.getOffsetParent()) || document.body,
            containerCoordinates = container.getCoordinates(offsetParent),
            elementMargin = {},
            elementBorder = {},
            containerMargin = {},
            containerBorder = {},
            offsetParentPadding = {};

        ['top', 'right', 'bottom', 'left'].each(function(pad){
            elementMargin[pad] = element.getStyle('margin-' + pad).toInt();
            elementBorder[pad] = element.getStyle('border-' + pad).toInt();
            containerMargin[pad] = container.getStyle('margin-' + pad).toInt();
            containerBorder[pad] = container.getStyle('border-' + pad).toInt();
            offsetParentPadding[pad] = offsetParent.getStyle('padding-' + pad).toInt();
        }, this);

        var width = element.offsetWidth + elementMargin.left + elementMargin.right,
            height = element.offsetHeight + elementMargin.top + elementMargin.bottom,
            left = 0,
            top = 0,
            right = containerCoordinates.right - containerBorder.right - width,
            bottom = containerCoordinates.bottom - containerBorder.bottom - height;

        if (this.options.includeMargins){
            left += elementMargin.left;
            top += elementMargin.top;
        } else {
            right += elementMargin.right;
            bottom += elementMargin.bottom;
        }

        if (element.getStyle('position') == 'relative'){
            var coords = element.getCoordinates(offsetParent);
            coords.left -= element.getStyle('left').toInt();
            coords.top -= element.getStyle('top').toInt();

            left -= coords.left;
            top -= coords.top;
            if (container.getStyle('position') != 'relative'){
                left += containerBorder.left;
                top += containerBorder.top;
            }
            right += elementMargin.left - coords.left;
            bottom += elementMargin.top - coords.top;

            if (container != offsetParent){
                left += containerMargin.left + offsetParentPadding.left;
                top += ((Browser.ie6 || Browser.ie7) ? 0 : containerMargin.top) + offsetParentPadding.top;
            }
        } else {
            left -= elementMargin.left;
            top -= elementMargin.top;
            if (container != offsetParent){
                left += containerCoordinates.left + containerBorder.left;
                top += containerCoordinates.top + containerBorder.top;
            }
        }

        return {
            x: [left, right],
            y: [top, bottom]
        };
    },

    checkDroppables: function(){
        var overed = this.droppables.filter(function(el, i){
            el = this.positions ? this.positions[i] : el.getCoordinates();
            var now = this.mouse.now;
            return (now.x > el.left && now.x < el.right && now.y < el.bottom && now.y > el.top);
        }, this).getLast();

        if (this.overed != overed){
            if (this.overed) this.fireEvent('leave', [this.element, this.overed]);
            if (overed) this.fireEvent('enter', [this.element, overed]);
            this.overed = overed;
        }
    },

    drag: function(event){
        this.parent(event);
        if (this.options.checkDroppables && this.droppables.length) this.checkDroppables();
    },

    stop: function(event){
        this.checkDroppables();
        this.fireEvent('drop', [this.element, this.overed, event]);
        this.overed = null;
        return this.parent(event);
    }

});

Element.implement({

    makeDraggable: function(options){
        var drag = new Drag.Move(this, options);
        this.store('dragger', drag);
        return drag;
    }

});


/*
Script: Scroller.js
    Class which scrolls the contents of any Element (including the window) when the mouse reaches the Element's boundaries.

License:
    MIT-style license.
*/

var Scroller = new Class({

    Implements: [Events, Options],

    options: {
        area: 20,
        velocity: 1,
        onChange: function(x, y){
            this.element.scrollTo(x, y);
        }
    },

    initialize: function(element, options){
        this.setOptions(options);
        this.element = $(element);
        this.listener = ($type(this.element) != 'element') ? $(this.element.getDocument().body) : this.element;
        this.timer = null;
        this.coord = this.getCoords.bind(this);
    },

    start: function(){
        this.listener.addEvent('mousemove', this.coord);
    },

    stop: function(){
        this.listener.removeEvent('mousemove', this.coord);
        this.timer = $clear(this.timer);
    },

    getCoords: function(event){
        this.page = (this.listener.get('tag') == 'body') ? event.client : event.page;
        if (!this.timer) this.timer = this.scroll.periodical(50, this);
    },

    scroll: function(){
        var size = this.element.getSize(), scroll = this.element.getScroll(), pos = this.element.getPosition(), change = {'x': 0, 'y': 0};
        for (var z in this.page){
            if (this.page[z] < (this.options.area + pos[z]) && scroll[z] != 0)
                change[z] = (this.page[z] - this.options.area - pos[z]) * this.options.velocity;
            else if (this.page[z] + this.options.area > (size[z] + pos[z]) && size[z] + size[z] != scroll[z])
                change[z] = (this.page[z] - size[z] + this.options.area - pos[z]) * this.options.velocity;
        }
        if (change.y || change.x) this.fireEvent('change', [scroll.x + change.x, scroll.y + change.y]);
    }

});

/*
---
script: Element.Measure.js
name: Element.Measure
description: Extends the Element native object to include methods useful in measuring dimensions.
credits: "Element.measure / .expose methods by Daniel Steigerwald License: MIT-style license. Copyright: Copyright (c) 2008 Daniel Steigerwald, daniel.steigerwald.cz"
license: MIT-style license
authors:
  - Aaron Newton
requires:
  - Core/Element.Style
  - Core/Element.Dimensions
  - /MooTools.More
provides: [Element.Measure]
...
*/

(function(){
var getStylesList = function(styles, planes){
    var list = [];
    Object.each(planes, function(directions){
        Object.each(directions, function(edge){
            styles.each(function(style){
                list.push(style + '-' + edge + (style == 'border' ? '-width' : ''));
            });
        });
    });
    return list;
};
var calculateEdgeSize = function(edge, styles){
    var total = 0;
    Object.each(styles, function(value, style){
        if (style.test(edge)) total = total + value.toInt();
    });
    return total;
};
var isVisible = function(el){
    return !!(!el || el.offsetHeight || el.offsetWidth);
};
Element.implement({
    measure: function(fn){
        if (isVisible(this)) return fn.call(this);
        var parent = this.getParent(),
            toMeasure = [];
        while (!isVisible(parent) && parent != document.body){
            toMeasure.push(parent.expose());
            parent = parent.getParent();
        }
        var restore = this.expose(),
            result = fn.call(this);
        restore();
        toMeasure.each(function(restore){
            restore();
        });
        return result;
    },
    expose: function(){
        if (this.getStyle('display') != 'none') return function(){};
        var before = this.style.cssText;
        this.setStyles({
            display: 'block',
            position: 'absolute',
            visibility: 'hidden'
        });
        return function(){
            this.style.cssText = before;
        }.bind(this);
    },
    getDimensions: function(options){
        options = Object.merge({computeSize: false}, options);
        var dim = {x: 0, y: 0};

        var getSize = function(el, options){
            return (options.computeSize) ? el.getComputedSize(options) : el.getSize();
        };

        var parent = this.getParent('body');

        if (parent && this.getStyle('display') == 'none'){
            dim = this.measure(function(){
                return getSize(this, options);
            });
        } else if (parent){
            try { //safari sometimes crashes here, so catch it
                dim = getSize(this, options);
            }catch(e){}
        }

        return Object.append(dim, (dim.x || dim.x === 0) ? {
                width: dim.x,
                height: dim.y
            } : {
                x: dim.width,
                y: dim.height
            }
        );
    },
    getComputedSize: function(options){
        //<1.2compat>
        //legacy support for my stupid spelling error
        if (options && options.plains) options.planes = options.plains;
        //</1.2compat>

        options = Object.merge({
            styles: ['padding','border'],
            planes: {
                height: ['top','bottom'],
                width: ['left','right']
            },
            mode: 'both'
        }, options);

        var styles = {},
            size = {width: 0, height: 0},
            dimensions;

        if (options.mode == 'vertical'){
            delete size.width;
            delete options.planes.width;
        } else if (options.mode == 'horizontal'){
            delete size.height;
            delete options.planes.height;
        }

        getStylesList(options.styles, options.planes).each(function(style){
            styles[style] = this.getStyle(style).toInt();
        }, this);

        Object.each(options.planes, function(edges, plane){
            var capitalized = plane.capitalize(),
                style = this.getStyle(plane);

            if (style == 'auto' && !dimensions) dimensions = this.getDimensions();

            style = styles[plane] = (style == 'auto') ? dimensions[plane] : style.toInt();
            size['total' + capitalized] = style;

            edges.each(function(edge){
                var edgesize = calculateEdgeSize(edge, styles);
                size['computed' + edge.capitalize()] = edgesize;
                size['total' + capitalized] += edgesize;
            });
        }, this);
        return Object.append(size, styles);
    }
});

})();

/*
---
script: Fx.Sort.js
name: Fx.Sort
description: Defines Fx.Sort, a class that reorders lists with a transition.
license: MIT-style license
authors:
  - Aaron Newton
requires:
  - Core/Element.Dimensions
  - /Fx.Elements
  - /Element.Measure
provides: [Fx.Sort]
*/

Fx.Sort = new Class({
    Extends: Fx.Elements,
    options: {
        mode: 'vertical'
    },
    initialize: function(elements, options){
        this.parent(elements, options);
        this.elements.each(function(el){
            if (el.getStyle('position') == 'static') el.setStyle('position', 'relative');
        });
        this.setDefaultOrder();
    },
    setDefaultOrder: function(){
        this.currentOrder = this.elements.map(function(el, index){
            return index;
        });
    },
    sort: function(){
        if (!this.check(arguments)) return this;
        var newOrder = Array.flatten(arguments);

        var top = 0,
            left = 0,
            next = {},
            zero = {},
            vert = this.options.mode == 'vertical';

        var current = this.elements.map(function(el, index){
            var size = el.getComputedSize({styles: ['border', 'padding', 'margin']});
            var val;
            if (vert){
                val = {
                    top: top,
                    margin: size['margin-top'],
                    height: size.totalHeight
                };
                top += val.height - size['margin-top'];
            } else {
                val = {
                    left: left,
                    margin: size['margin-left'],
                    width: size.totalWidth
                };
                left += val.width;
            }
            var plane = vert ? 'top' : 'left';
            zero[index] = {};
            var start = el.getStyle(plane).toInt();
            zero[index][plane] = start || 0;
            return val;
        }, this);

        this.set(zero);
        newOrder = newOrder.map(function(i){ return i.toInt(); });
        if (newOrder.length != this.elements.length){
            this.currentOrder.each(function(index){
                if (!newOrder.contains(index)) newOrder.push(index);
            });
            if (newOrder.length > this.elements.length)
                newOrder.splice(this.elements.length-1, newOrder.length - this.elements.length);
        }
        var margin = 0;
        top = left = 0;
        newOrder.each(function(item){
            var newPos = {};
            if (vert){
                newPos.top = top - current[item].top - margin;
                top += current[item].height;
            } else {
                newPos.left = left - current[item].left;
                left += current[item].width;
            }
            margin = margin + current[item].margin;
            next[item]=newPos;
        }, this);
        var mapped = {};
        Array.clone(newOrder).sort().each(function(index){
            mapped[index] = next[index];
        });
        this.start(mapped);
        this.currentOrder = newOrder;

        return this;
    },
    rearrangeDOM: function(newOrder){
        newOrder = newOrder || this.currentOrder;
        var parent = this.elements[0].getParent();
        var rearranged = [];
        this.elements.setStyle('opacity', 0);
        //move each element and store the new default order
        newOrder.each(function(index){
            rearranged.push(this.elements[index].inject(parent).setStyles({
                top: 0,
                left: 0
            }));
        }, this);
        this.elements.setStyle('opacity', 1);
        this.elements = $$(rearranged);
        this.setDefaultOrder();
        return this;
    },
    getDefaultOrder: function(){
        return this.elements.map(function(el, index){
            return index;
        });
    },
    getCurrentOrder: function(){
        return this.currentOrder;
    },
    forward: function(){
        return this.sort(this.getDefaultOrder());
    },
    backward: function(){
        return this.sort(this.getDefaultOrder().reverse());
    },
    reverse: function(){
        return this.sort(this.currentOrder.reverse());
    },
    sortByElements: function(elements){
        return this.sort(elements.map(function(el){
            return this.elements.indexOf(el);
        }, this));
    },
    swap: function(one, two){
        if (typeOf(one) == 'element') one = this.elements.indexOf(one);
        if (typeOf(two) == 'element') two = this.elements.indexOf(two);

        var newOrder = Array.clone(this.currentOrder);
        newOrder[this.currentOrder.indexOf(one)] = two;
        newOrder[this.currentOrder.indexOf(two)] = one;

        return this.sort(newOrder);
    }
});
/*
---

script: Sortables.js

name: Sortables

description: Class for creating a drag and drop sorting interface for lists of items.

license: MIT-style license

authors:
  - Tom Occhino

requires:
  - /Drag.Move

provides: [Sortables]

...
*/

var Sortables = new Class({

    Implements: [Events, Options],

    options: {/*
        onSort: function(element, clone){},
        onStart: function(element, clone){},
        onComplete: function(element){},*/
        snap: 4,
        opacity: 1,
        clone: false,
        revert: false,
        handle: false,
        constrain: false,
        preventDefault: false
    },

    initialize: function(lists, options){
        this.setOptions(options);

        this.elements = [];
        this.lists = [];
        this.idle = true;

        this.addLists($$(document.id(lists) || lists));

        if (!this.options.clone) this.options.revert = false;
        if (this.options.revert) this.effect = new Fx.Morph(null, Object.merge({
            duration: 250,
            link: 'cancel'
        }, this.options.revert));
    },

    attach: function(){
        this.addLists(this.lists);
        return this;
    },

    detach: function(){
        this.lists = this.removeLists(this.lists);
        return this;
    },

    addItems: function(){
        Array.flatten(arguments).each(function(element){
            this.elements.push(element);
            var start = element.retrieve('sortables:start', function(event){
                this.start.call(this, event, element);
            }.bind(this));
            (this.options.handle ? element.getElement(this.options.handle) || element : element).addEvent('mousedown', start);
        }, this);
        return this;
    },

    addLists: function(){
        Array.flatten(arguments).each(function(list){
            this.lists.push(list);
            this.addItems(list.getChildren());
        }, this);
        return this;
    },

    removeItems: function(){
        return $$(Array.flatten(arguments).map(function(element){
            this.elements.erase(element);
            var start = element.retrieve('sortables:start');
            (this.options.handle ? element.getElement(this.options.handle) || element : element).removeEvent('mousedown', start);

            return element;
        }, this));
    },

    removeLists: function(){
        return $$(Array.flatten(arguments).map(function(list){
            this.lists.erase(list);
            this.removeItems(list.getChildren());

            return list;
        }, this));
    },

    getClone: function(event, element){
        if (!this.options.clone) return new Element(element.tagName).inject(document.body);
        if (typeOf(this.options.clone) == 'function') return this.options.clone.call(this, event, element, this.list);
        var clone = element.clone(true).setStyles({
            margin: 0,
            position: 'absolute',
            visibility: 'hidden',
            width: element.getStyle('width')
        });
        //prevent the duplicated radio inputs from unchecking the real one
        if (clone.get('html').test('radio')){
            clone.getElements('input[type=radio]').each(function(input, i){
                input.set('name', 'clone_' + i);
                if (input.get('checked')) element.getElements('input[type=radio]')[i].set('checked', true);
            });
        }

        return clone.inject(this.list).setPosition(element.getPosition(element.getOffsetParent()));
    },

    getDroppables: function(){
        var droppables = this.list.getChildren().erase(this.clone).erase(this.element);
        if (!this.options.constrain) droppables.append(this.lists).erase(this.list);
        return droppables;
    },

    insert: function(dragging, element){
        var where = 'inside';
        if (this.lists.contains(element)){
            this.list = element;
            this.drag.droppables = this.getDroppables();
        } else {
            where = this.element.getAllPrevious().contains(element) ? 'before' : 'after';
        }
        this.element.inject(element, where);
        this.fireEvent('sort', [this.element, this.clone]);
    },

    start: function(event, element){
        if (
            !this.idle ||
            event.rightClick ||
            ['button', 'input'].contains(event.target.get('tag'))
        ) return;

        this.idle = false;
        this.element = element;
        this.opacity = element.get('opacity');
        this.list = element.getParent();
        this.clone = this.getClone(event, element);

        this.drag = new Drag.Move(this.clone, {
            preventDefault: this.options.preventDefault,
            snap: this.options.snap,
            container: this.options.constrain && this.element.getParent(),
            droppables: this.getDroppables(),
            onSnap: function(){
                event.stop();
                this.clone.setStyle('visibility', 'visible');
                this.element.set('opacity', this.options.opacity || 0);
                this.fireEvent('start', [this.element, this.clone]);
            }.bind(this),
            onEnter: this.insert.bind(this),
            onCancel: this.reset.bind(this),
            onComplete: this.end.bind(this)
        });

        this.clone.inject(this.element, 'before');
        this.drag.start(event);
    },

    end: function(){
        this.drag.detach();
        this.element.set('opacity', this.opacity);
        if (this.effect){
            var dim = this.element.getStyles('width', 'height');
            var pos = this.clone.computePosition(this.element.getPosition(this.clone.getOffsetParent()));
            this.effect.element = this.clone;
            this.effect.start({
                top: pos.top,
                left: pos.left,
                width: dim.width,
                height: dim.height,
                opacity: 0.25
            }).chain(this.reset.bind(this));
        } else {
            this.reset();
        }
    },

    reset: function(){
        this.idle = true;
        this.clone.destroy();
        this.fireEvent('complete', this.element);
    },

    serialize: function(){
        var params = Array.link(arguments, {
            modifier: Type.isFunction,
            index: function(obj){
                return obj != null;
            }
        });
        var serial = this.lists.map(function(list){
            return list.getChildren().map(params.modifier || function(element){
                return element.get('id');
            }, this);
        }, this);

        var index = params.index;
        if (this.lists.length == 1) index = 0;
        return (index || index === 0) && index >= 0 && index < this.lists.length ? serial[index] : serial;
    }

});


/*
---

script: Assets.js

name: Assets

description: Provides methods to dynamically load JavaScript, CSS, and Image files into the document.

license: MIT-style license

authors:
  - Valerio Proietti

requires:
  - Core/Element.Event
  - /MooTools.More

provides: [Assets]

...
*/

var Asset = {

    javascript: function(source, properties){
        properties = Object.append({
            document: document
        }, properties);

        if (properties.onLoad){
            properties.onload = properties.onLoad;
            delete properties.onLoad;
        }

        var script = new Element('script', {src: source, type: 'text/javascript'});
        var load = properties.onload || function(){},
            doc = properties.document;
        delete properties.onload;
        delete properties.document;

        return script.addEvents({
            load: load,
            readystatechange: function(){
                if (['loaded', 'complete'].contains(this.readyState)) load.call(this);
            }
        }).set(properties).inject(doc.head);
    },

    css: function(source, properties){
        properties = properties || {};
        var onload = properties.onload || properties.onLoad;
        if (onload){
            properties.events = properties.events || {};
            properties.events.load = onload;
            delete properties.onload;
            delete properties.onLoad;
        }
        return new Element('link', Object.merge({
            rel: 'stylesheet',
            media: 'screen',
            type: 'text/css',
            href: source
        }, properties)).inject(document.head);
    },

    image: function(source, properties){
        properties = Object.merge({
            onload: function(){},
            onabort: function(){},
            onerror: function(){}
        }, properties);
        var image = new Image();
        var element = document.id(image) || new Element('img');
        ['load', 'abort', 'error'].each(function(name){
            var type = 'on' + name;
            var cap = name.capitalize();
            if (properties['on' + cap]){
                properties[type] = properties['on' + cap];
                delete properties['on' + cap];
            }
            var event = properties[type];
            delete properties[type];
            image[type] = function(){
                if (!image) return;
                if (!element.parentNode){
                    element.width = image.width;
                    element.height = image.height;
                }
                image = image.onload = image.onabort = image.onerror = null;
                event.delay(1, element, element);
                element.fireEvent(name, element, 1);
            };
        });
        image.src = element.src = source;
        if (image && image.complete) image.onload.delay(1);
        return element.set(properties);
    },

    images: function(sources, options){
        options = Object.merge({
            onComplete: function(){},
            onProgress: function(){},
            onError: function(){},
            properties: {}
        }, options);
        sources = Array.from(sources);
        var counter = 0;
        return new Elements(sources.map(function(source, index){
            return Asset.image(source, Object.append(options.properties, {
                onload: function(){
                    counter++;
                    options.onProgress.call(this, counter, index, source);
                    if (counter == sources.length) options.onComplete();
                },
                onerror: function(){
                    counter++;
                    options.onError.call(this, counter, index, source);
                    if (counter == sources.length) options.onComplete();
                }
            }));
        }));
    }

};


/*
---

script: Tips.js

name: Tips

description: Class for creating nice tips that follow the mouse cursor when hovering an element.

license: MIT-style license

authors:
  - Valerio Proietti
  - Christoph Pojer
  - Luis Merino

requires:
  - Core/Options
  - Core/Events
  - Core/Element.Event
  - Core/Element.Style
  - Core/Element.Dimensions
  - /MooTools.More

provides: [Tips]

...
*/

(function(){

var read = function(option, element){
    return (option) ? (typeOf(option) == 'function' ? option(element) : element.get(option)) : '';
};

this.Tips = new Class({
    Implements: [Events, Options],
    options: {/*
        id: null,
        onAttach: function(element){},
        onDetach: function(element){},
        onBound: function(coords){},*/
        onShow: function(){
            this.tip.setStyle('display', 'block');
        },
        onHide: function(){
            this.tip.setStyle('display', 'none');
        },
        title: 'title',
        text: function(element){
            return element.get('rel') || element.get('href');
        },
        showDelay: 100,
        hideDelay: 100,
        className: 'tip-wrap',
        offset: {x: 16, y: 16},
        windowPadding: {x:0, y:0},
        fixed: false,
        waiAria: true
    },
    initialize: function(){
        var params = Array.link(arguments, {
            options: Type.isObject,
            elements: function(obj){
                return obj != null;
            }
        });
        this.setOptions(params.options);
        if (params.elements) this.attach(params.elements);
        this.container = new Element('div', {'class': 'tip'});

        if (this.options.id){
            this.container.set('id', this.options.id);
            if (this.options.waiAria) this.attachWaiAria();
        }
    },
    toElement: function(){
        if (this.tip) return this.tip;

        this.tip = new Element('div', {
            'class': this.options.className,
            styles: {
                position: 'absolute',
                top: 0,
                left: 0
            }
        }).adopt(
            new Element('div', {'class': 'tip-top'}),
            this.container,
            new Element('div', {'class': 'tip-bottom'})
        );

        return this.tip;
    },
    attachWaiAria: function(){
        var id = this.options.id;
        this.container.set('role', 'tooltip');

        if (!this.waiAria){
            this.waiAria = {
                show: function(element){
                    if (id) element.set('aria-describedby', id);
                    this.container.set('aria-hidden', 'false');
                },
                hide: function(element){
                    if (id) element.erase('aria-describedby');
                    this.container.set('aria-hidden', 'true');
                }
            };
        }
        this.addEvents(this.waiAria);
    },
    detachWaiAria: function(){
        if (this.waiAria){
            this.container.erase('role');
            this.container.erase('aria-hidden');
            this.removeEvents(this.waiAria);
        }
    },
    attach: function(elements){
        $$(elements).each(function(element){
            var title = read(this.options.title, element),
                text = read(this.options.text, element);

            element.set('title', '').store('tip:native', title).retrieve('tip:title', title);
            element.retrieve('tip:text', text);
            this.fireEvent('attach', [element]);

            var events = ['enter', 'leave'];
            if (!this.options.fixed) events.push('move');

            events.each(function(value){
                var event = element.retrieve('tip:' + value);
                if (!event) event = function(event){
                    this['element' + value.capitalize()].apply(this, [event, element]);
                }.bind(this);

                element.store('tip:' + value, event).addEvent('mouse' + value, event);
            }, this);
        }, this);

        return this;
    },
    detach: function(elements){
        $$(elements).each(function(element){
            ['enter', 'leave', 'move'].each(function(value){
                element.removeEvent('mouse' + value, element.retrieve('tip:' + value)).eliminate('tip:' + value);
            });

            this.fireEvent('detach', [element]);

            if (this.options.title == 'title'){ // This is necessary to check if we can revert the title
                var original = element.retrieve('tip:native');
                if (original) element.set('title', original);
            }
        }, this);

        return this;
    },
    elementEnter: function(event, element){
        clearTimeout(this.timer);
        this.timer = (function(){
            this.container.empty();

            ['title', 'text'].each(function(value){
                var content = element.retrieve('tip:' + value);
                var div = this['_' + value + 'Element'] = new Element('div', {
                        'class': 'tip-' + value
                    }).inject(this.container);
                if (content) this.fill(div, content);
            }, this);
            this.show(element);
            this.position((this.options.fixed) ? {page: element.getPosition()} : event);
        }).delay(this.options.showDelay, this);
    },
    elementLeave: function(event, element){
        clearTimeout(this.timer);
        this.timer = this.hide.delay(this.options.hideDelay, this, element);
        this.fireForParent(event, element);
    },
    setTitle: function(title){
        if (this._titleElement){
            this._titleElement.empty();
            this.fill(this._titleElement, title);
        }
        return this;
    },
    setText: function(text){
        if (this._textElement){
            this._textElement.empty();
            this.fill(this._textElement, text);
        }
        return this;
    },
    fireForParent: function(event, element){
        element = element.getParent();
        if (!element || element == document.body) return;
        if (element.retrieve('tip:enter')) element.fireEvent('mouseenter', event);
        else this.fireForParent(event, element);
    },
    elementMove: function(event, element){
        this.position(event);
    },
    position: function(event){
        if (!this.tip) document.id(this);

        var size = window.getSize(), scroll = window.getScroll(),
            tip = {x: this.tip.offsetWidth, y: this.tip.offsetHeight},
            props = {x: 'left', y: 'top'},
            bounds = {y: false, x2: false, y2: false, x: false},
            obj = {};

        for (var z in props){
            obj[props[z]] = event.page[z] + this.options.offset[z];
            if (obj[props[z]] < 0) bounds[z] = true;
            if ((obj[props[z]] + tip[z] - scroll[z]) > size[z] - this.options.windowPadding[z]){
                obj[props[z]] = event.page[z] - this.options.offset[z] - tip[z];
                bounds[z+'2'] = true;
            }
        }

        this.fireEvent('bound', bounds);
        this.tip.setStyles(obj);
    },
    fill: function(element, contents){
        if (typeof contents == 'string') element.set('html', contents);
        else element.adopt(contents);
    },
    show: function(element){
        if (!this.tip) document.id(this);
        if (!this.tip.getParent()) this.tip.inject(document.body);
        this.fireEvent('show', [this.tip, element]);
    },
    hide: function(element){
        if (!this.tip) document.id(this);
        this.fireEvent('hide', [this.tip, element]);
    }
});

})();


var Acc = function(toggles,elements,options){
      var opt = options||{};
      var acc = new Accordion(toggles, elements,$extend({
                     height:false,
                     opacity:false,
                     alwaysHide: true,
                     onActive:function(t,i){
                        t.addClass('current');
                        i.setStyle('display','block');
                     },
                     onBackground:function(t,i){
                        t.removeClass('current');
                        i.setStyle('display','none');
                     }
        },opt));

    return acc;

};

(function(){
var progressSupport = ('onprogress' in new Browser.Request);

    Request.implement({
        send: function(options){
            if (!this.check(options)) return this;

            this.options.isSuccess = this.options.isSuccess || this.isSuccess;
            this.running = true;

            var type = typeOf(options);
            if (type == 'string' || type == 'element') options = {data: options};

            var old = this.options;
            options = Object.append({data: old.data, url: old.url, method: old.method}, options);
            var data = options.data, url = String(options.url), method = options.method.toLowerCase();

            switch (typeOf(data)){
                case 'element': data = document.id(data).toQueryString(); break;
                case 'object': case 'hash': data = Object.toQueryString(data);
            }

            if (this.options.format){
                var format = 'format=' + this.options.format;
                data = (data) ? format + '&' + data : format;
            }

            if (this.options.emulation && !['get', 'post'].contains(method)){
                var _method = '_method=' + method;
                data = (data) ? _method + '&' + data : _method;
                method = 'post';
            }

            if (this.options.urlEncoded && ['post', 'put'].contains(method)){
                var encoding = (this.options.encoding) ? '; charset=' + this.options.encoding : '';
                this.headers['Content-type'] = 'application/x-www-form-urlencoded' + encoding;
            }

            if (!url) url = document.location.pathname;

            var trimPosition = url.lastIndexOf('/');
            if (trimPosition > -1 && (trimPosition = url.indexOf('#')) > -1) url = url.substr(0, trimPosition);

            if (this.options.noCache)
                url += (url.contains('?') ? '&' : '?') + String.uniqueID();

            if (data && method == 'get'){
                url += (url.contains('?') ? '&' : '?') + data;
                data = null;
            }

            var xhr = this.xhr;
            if (progressSupport){
                xhr.onloadstart = this.loadstart.bind(this);
                xhr.onprogress = this.progress.bind(this);
            }
            data=this.options.extraData?this.options.extraData+'&'+data:data;

            xhr.open(method.toUpperCase(), url, this.options.async, this.options.user, this.options.password);
            if (this.options.user && 'withCredentials' in xhr) xhr.withCredentials = true;

            xhr.onreadystatechange = this.onStateChange.bind(this);

            Object.each(this.headers, function(value, key){
                try {
                    xhr.setRequestHeader(key, value);
                } catch (e){
                    this.fireEvent('exception', [key, value]);
                }
            }, this);

            this.fireEvent('request');
            xhr.send(data);
            if (!this.options.async) this.onStateChange();
            if (this.options.timeout) this.timer = this.timeout.delay(this.options.timeout, this);
            return this;
        }
    });
})();

var Equalizer = new Class({
    initialize: function(elements,stop,prevent) {
        this.elements = $$(elements);

    },
    equalize: function(hw) {
        if(!hw) { hw = 'height'; }
        var max = 0,
            prop = (typeof document.body.style.maxHeight != 'undefined' ? 'min-' : '') + hw; //ie6 ftl
            offset = 'offset' + hw.capitalize();
        this.elements.each(function(element,i) {
            var calc = element[offset];
            if(calc > max) { max = calc; }
        },this);
        this.elements.each(function(element,i) {
            element.setStyle(prop,max - (element[offset] - element.getStyle(hw).replace('px','')));
        });
        return max;
    }
});

/*Element */
Element.implement({
    makeDraggable: function(options){
            var drag = new Drag.Move(this, options);
            this.store('dragger', drag);
            return drag;
    },
    getPatch: function() {
        var args = arguments.length ? Array.from(arguments) : ['margin', 'padding', 'border'];
        var _return = {
            x: 0,
            y: 0
        };

        Object.each({x: ['left', 'right'], y: ['top', 'bottom']}, function(p2, p1) {
            p2.each(function(p) {
                try {
                    args.each(function(arg) {
                        arg += '-' + p;
                        if (arg == 'border') arg += '-width';
                        _return[p1] += this.getStyle(arg).toInt() || 0;
                    }, this);
                } catch(e) {}
            }, this);
        }, this);
        return _return;
    },
    'empty':function(element){
        Array.from(this.childNodes).each(function(node){
            if(node.retrieve&&node.retrieve('events',{})['dispose']){node.fireEvent('dispose');}
            Element.dispose(node);
        });
        return this;
    }
});
