//<mootools 1.2compat>
(function() {
	var Hash = this.Hash = new Type('Hash', function(object) {
		if (typeOf(object) == 'hash') object = Object.clone(object.getClean());
		for (var key in object) this[key] = object[key];
		return this;
	});

	Hash.implement({

		forEach: function(fn, bind) {
			Object.forEach(this, fn, bind);
		},

		getClean: function() {
			var clean = {};
			for (var key in this) {
				if (this.hasOwnProperty(key)) clean[key] = this[key];
			}
			return clean;
		},

		getLength: function() {
			var length = 0;
			for (var key in this) {
				if (this.hasOwnProperty(key)) length++;
			}
			return length;
		},

		has: Object.prototype.hasOwnProperty,

		keyOf: function(value) {
			return Object.keyOf(this, value);
		},

		hasValue: function(value) {
			return Object.contains(this, value);
		},

		extend: function(properties) {
			Hash.each(properties || {},	function(value, key) {
				Hash.set(this, key, value);
			}, this);
			return this;
		},

		combine: function(properties) {
			Hash.each(properties || {}, function(value, key) {
				Hash.include(this, key, value);
			}, this);
			return this;
		},

		erase: function(key) {
			if (this.hasOwnProperty(key)) delete this[key];
			return this;
		},

		get: function(key) {
			return (this.hasOwnProperty(key)) ? this[key] : null;
		},

		set: function(key, value) {
			if (!this[key] || this.hasOwnProperty(key)) this[key] = value;
			return this;
		},

		empty: function() {
			Hash.each(this, function(value, key) {
				delete this[key];
			}, this);
			return this;
		},

		include: function(key, value) {
			if (this[key] == null) this[key] = value;
			return this;
		},

		map: function(fn, bind) {
			return new Hash(Object.map(this, fn, bind));
		},

		filter: function(fn, bind) {
			return new Hash(Object.filter(this, fn, bind));
		},

		every: function(fn, bind) {
			return Object.every(this, fn, bind);
		},

		some: function(fn, bind) {
			return Object.some(this, fn, bind);
		},

		getKeys: function() {
			return Object.keys(this);
		},

		getValues: function() {
			return Object.values(this);
		},

		toQueryString: function(base) {
			return Object.toQueryString(this, base);
		}

	});

	Hash.alias('each', 'forEach');

	Hash.extend = Object.append;

	Hash.alias({
		indexOf: 'keyOf',
		contains: 'hasValue'
	});

	Object.type = Type.isObject;

	var Native = this.Native = function(properties) {
		return new Type(properties.name, properties.initialize);
	};

	Native.type = Type.type;

	Native.implement = function(objects, methods) {
		for (var i = 0; i < objects.length; i++) objects[i].implement(methods);
		return Native;
	};

	var arrayType = Array.type;
	Array.type = function(item) {
		return instanceOf(item, Array) || arrayType(item);
	};

	this.$A = function(item) {
		return Array.from(item).slice();
	};

	this.$arguments = function(i) {
		return function() {
			return arguments[i];
		};
	};

	this.$chk = function(obj) {
		return !! (obj || obj === 0);
	};

	this.$clear = function(timer) {
		clearTimeout(timer);
		clearInterval(timer);
		return null;
	};

	this.$defined = function(obj) {
		return (obj != null);
	};

	this.$each = function(iterable, fn, bind) {
		var type = typeOf(iterable);
		((type == 'arguments' || type == 'collection' || type == 'array' || type == 'elements') ? Array : Object).each(iterable, fn, bind);
	};

	this.$empty = function() {};

	this.$extend = function(original, extended) {
		return Object.append(original, extended);
	};

	this.$H = function(object) {
		return new Hash(object);
	};

	this.$merge = function() {
		var args = Array.slice(arguments);
		args.unshift({});
		return Object.merge.apply(null, args);
	};

	this.$lambda = Function.from;
	this.$mixin = Object.merge;
	this.$random = Number.random;
	this.$splat = Array.from;
	this.$time = Date.now;

	this.$type = function(object) {
		var type = typeOf(object);
		if (type == 'elements') return 'array';
		return (type == 'null') ? false : type;
	};

	Array.alias('extend', 'append');

	this.$pick = function() {
		return Array.from(arguments).pick();
	};

	delete Function.prototype.bind;

	Function.implement({

		create: function(options) {
			var self = this;
			options = options || {};
			return function(event) {
				var args = options.arguments;
				args = (args != null) ? Array.from(args) : Array.slice(arguments, (options.event) ? 1 : 0);
				if (options.event) args = [event || window.event].extend(args);
				var returns = function() {
					return self.apply(options.bind || null, args);
				};
				if (options.delay) return setTimeout(returns, options.delay);
				if (options.periodical) return setInterval(returns, options.periodical);
				if (options.attempt) return Function.attempt(returns);
				return returns();
			};
		},

		bind: function(bind, args) {
			var self = this;
			if (args != null) args = Array.from(args);
			return function() {
				return self.apply(bind, args || arguments);
			};
		},

		bindWithEvent: function(bind, args) {
			var self = this;
			if (args != null) args = Array.from(args);
			return function(event) {
				return self.apply(bind, (args == null) ? arguments : [event].concat(args));
			};
		},

		run: function(args, bind) {
			return this.apply(bind, Array.from(args));
		}

	});

	this.$try = Function.attempt;

})();

String.type = Type.isString;

window.$$ = null;
Window.implement('$$', function(selector){
	var elements = new Elements;
	if (arguments.length == 1 && typeof selector == 'string') return Slick.search(this.document, selector, elements);
	var args = Array.flatten(arguments);
	for (var i = 0, l = args.length; i < l; i++){
		var item = args[i];
		switch (typeOf(item)){
			case 'element': elements.push(item); break;
			case 'string': Slick.search(this.document, item, elements);
		}
	}
	return elements;
});

if (!!window.validatorMap) validatorMap = new Hash(validatorMap);
/*Event.Keys = new Hash(Event.Keys);
Element.Constructors = new Hash;
Element.Properties = new Hash;
Element.Styles = new Hash(Element.Styles);
Element.Events = new Hash(Element.Events);
Fx.CSS.Parsers = new Hash(Fx.CSS.Parsers);
Fx.Transitions = new Hash(Fx.Transitions);
JSON = new Hash({
	stringify: JSON.stringify,
	parse: JSON.parse
});*/

//<1.2compat>
/*
---

script: Hash.Cookie.js

name: Hash.Cookie

description: Class for creating, reading, and deleting Cookies in JSON format.

license: MIT-style license

authors:
  - Valerio Proietti
  - Aaron Newton

requires:
  - Core/Cookie
  - Core/JSON
  - /MooTools.More
  - /Hash

provides: [Hash.Cookie]

...
*/

Hash.Cookie = new Class({

	Extends: Cookie,

	options: {
		autoSave: true
	},

	initialize: function(name, options) {
		this.parent(name, options);
		this.load();
	},

	save: function() {
		var value = JSON.encode(this.hash);
		if (!value || value.length > 4096) return false; //cookie would be truncated!
		if (value == '{}') this.dispose();
		else this.write(value);
		return true;
	},

	load: function() {
		this.hash = new Hash(JSON.decode(this.read(), true));
		return this;
	}

});

Hash.each(Hash.prototype, function(method, name) {
	if (typeof method == 'function') Hash.Cookie.implement(name, function() {
		var value = method.apply(this.hash, arguments);
		if (this.options.autoSave) this.save();
		return value;
	});
});

/*Mootools 1.1 Adapter
*
*/

Window.implement({
	ie: Browser.ie,
	ie6: Browser.ie6,
	ie7: Browser.ie7,
	gecko: Browser.firefox,
	webkit: Browser.chrome || Browser.safari,
	webkit419: Browser.safari2,
	webkit420: Browser.safari3,
	opera: Browser.opera
});
/*
Object.toQueryString=function(source){
  return Hash.toQueryString(new Hash(source));
}
 */

/*Element Adapter*/
Window.implement({
	$E: function(selector, scope) {
		return ($(scope) || document).getElement(selector);
	},
	$ES: function(selector, scope) {
		return ($(scope) || document).getElements(selector);
	}
});
Element.implement({
	setHTML: function() {
		return this.set('html', Array.flatten($A(arguments)).join('\n'));
	},
	setText: function(text) {
		return this.set('text', text);
	},
	getText: function() {
		return this.get('text');
	},
	getHTML: function() {
		return this.get('html');
	},
	setOpacity: function(value) {
		return this.set('opacity', value, false);
	},
	setStyles: function(styles) {
		switch (typeof(styles)) {
		case 'object':
			for (var style in styles) this.setStyle(style, styles[style]);
			break;
		case 'string':
			this.style.cssText = styles;
		}
		return this;
	},
	getTag: function() {
		return this.tagName.toLowerCase();
	},
	replaceWith: function(el) {
		var newEL = $(el, true);
		var oEL = $(this);
		this.parentNode.replaceChild(newEL, oEL);
		return newEL;
	},
	getValue: function() {
		switch (this.getTag()) {
		case 'select':
			var values = [];
			for (i = 0, L = this.options.length; i < L; i++) {
				if (this.options[i].selected) values.push($pick(this.options[i].value, this.options[i].text));
			}
			return (this.multiple) ? values : values[0];
		case 'input':
			if (! (this.checked && ['checkbox', 'radio'].contains(this.type)) && ! ['hidden', 'text', 'password'].contains(this.type)) break;
		case 'textarea':
			return this.value;
		}
		return false;
	},
	getFormElements: function() {
		return $$(this.getElementsByTagName('input'), this.getElementsByTagName('select'), this.getElementsByTagName('textarea')) || [];
	},
	remove: function() {
		return this.destroy();
	},
	getCis: function(rel) {
		return this.getCoordinates(rel);
	},
	amongTo: function(rel, options) {
		options = Object.merge(options || {}, {
			target: rel
		});
		return this.position(options);
	}
});
/*Json Adapter*/
var Json = {
	'toString': function(json) {
		return JSON.encode(json) || "";
	},
	'evaluate': function(json, secure) {
		return JSON.decode(json, secure) || {};
	}
};
Json.Remote = new Class({
	Extends: Request.JSON,
	initialize: function(url, options) {
		this.parent($extend(options, {
			'url': url
		}));
	}
});

/*Cookie Adapter*/
Cookie.set = Cookie.write;
Cookie.get = Cookie.read;
Cookie.remove = Cookie.dispose;

Element.implement({
	send: function(options) {
		var type = typeof(options);
		var sender = this.get('send');
		if (type == 'object') {
			new Request(options).send(this);
			return this;
		} else {
			sender.send({
				data: this,
				url: options || sender.options.url
			});
			return this;
		}
	},
	toQueryString: function(filterEl, abs) {
		var queryString = [];
		this.getElements('input, select, textarea').each(function(el) {
			var type = el.type;
			if (!el.name || el.disabled || type == 'submit' || type == 'reset' || type == 'file' || type == 'image') return;
			if (filterEl) {
				if (!filterEl(el)) return;
			}
			var value = (el.get('tag') == 'select') ? el.getSelected().map(function(opt) {
                //IE->document.id
				return document.id(opt).value;
			}) : ((type == 'radio' || type == 'checkbox') && ! el.checked) ? null : el.get('value');

			if (el.getAttribute('filterhidden')) {
				el = $(el);
				var filterBox = el.getParent('.filter_panel').getElement('.filter_box');
				value = filterBox.toQueryString();
			}
			if (!value && abs) return;
			Array.from(value).each(function(val) {
				if (typeof val != 'undefined') queryString.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(val));
			});
		});
		return queryString.join('&');
	}
});

/*FX Adapter*/
Fx.Style = new Class({
	Extends: Fx.Tween,
	initialize: function(el, property, options) {
		this._property = property;
		this.parent(el, options);
	},
	set: function(v) {
		return this.parent(this._property, v);
	},
	start: function(f, t) {
		return this.parent(this._property, f, t);
	}
});
Fx.Styles = new Class({
	Extends: Fx.Morph
});

if (Fx.Scroll) {
	Fx.Scroll.implement({
		scrollTo: function(x, y, effect) {
			if (effect) return this.start(x, y);
			return this.set(x, y);
		}
	});
}

Element.implement({
	effect: function(p, o) {
		return new Fx.Style(this, p, o);
	},
	effects: function(o) {
		return new Fx.Styles(this, o);
	}
});

/*getSize Adapter*/
(function() {
	Element.implement({
		getSize: function() {
			if (isBody(this)) return this.getWindow().getSize();
			return {
				x: this.offsetWidth,
				y: this.offsetHeight,
				'size': {
					x: this.offsetWidth,
					y: this.offsetHeight
				},
				'scroll': {
					x: this.scrollLeft,
					y: this.scrollTop
				},
				'scrollSize': {
					x: this.scrollWidth,
					y: this.scrollHeight
				}
			};
		}
	});

	Native.implement([Document, Window], {
		getSize: function() {
			var win = this.getWindow();
			var doc = getCompatElement(this);
			return {
				x: doc.clientWidth,
				y: doc.clientHeight,
				'size': {
					'x': doc.clientWidth,
					'y': doc.clientHeight
				},
				'scroll': {
					x: win.pageXOffset || doc.scrollLeft,
					y: win.pageYOffset || doc.scrollTop
				},
				'scrollSize': {
					x: Math.max(doc.scrollWidth, win.innerWidth),
					y: Math.max(doc.scrollHeight, win.innerHeight)
				}
			};
		}
	});

	// private methods
	function isBody(element) {
		return (/^(?:body|html)$/i).test(element.tagName);
	}

	function getCompatElement(element) {
		var doc = element.getDocument();
		return (!doc.compatMode || doc.compatMode == 'CSS1Compat') ? doc.html : doc.body;
	}

})();

/*Array Adapter*/

Array.implement({
	copy: function() {
		return $A(this);
	}
});
Array.alias('remove', 'erase');

Hash.implement({
	merge: function() {
		return $merge.apply(null, [this].include(arguments));
	}
});

/*Drag.base Adapter*/
try {
	Drag.implement({
		options: {
			/*
            onBeforeStart: $empty,
            onStart: $empty,
            onDrag: $empty,
            onCancel: $empty,
            onComplete: $empty,*/
			snap: 0,
			unit: 'px',
			grid: false,
			style: true,
			limit: false,
			handle: false,
			invert: false,
			preventDefault: true,
			modifiers: {
				x: 'left',
				y: 'top'
			}
		}
	});
	Drag.Base = Drag;
} catch(e) {}

/*Extends*/
[Element, Number, String].each(function(o) {
	o.extend = o.implement;
});

/*bindwithEventL..*/

Function.implement({
	bindAsEventListener: function(bind, args) {
		return this.create({
			'bind': bind,
			'event': true,
			'arguments': args
		});
	}
});

/*each bug*/

function $each(iterable, fn, bind) {
	var type = $type(iterable);
	((type == 'arguments' || type == 'collection' || type == 'array' || type == 'element') ? Array : Hash).each(iterable, fn, bind);
}

/*Mootools 1.1 Adapter Define End*/

