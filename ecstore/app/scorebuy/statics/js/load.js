(function(win) {
var _doc = win['document'], _loaded = {},
    _loading = {}, _config = {mods: {}},
    loader = {
        load : function(url, type, charset, callback, context) {
            if (!url) return;
            var refFile = _doc.getElementsByTagName('script')[0];
            if (_loaded[url]) {
                _loading[url] = false;
                if (callback) callback(url, context);
                return;
            }

            if (_loading[url]) {
                setTimeout(function() {
                    loader.load(url, type, charset, callback, context);
                }, 1);
                return;
            }
            _loading[url] = true;
            var n, t = type || url.toLowerCase().substring(url.lastIndexOf('.') + 1);

            n =_doc.createElement('css'==t ? 'link' : 'script');
            if (charset) n.charset = charset;

            if('css'===t){
                n.type='text/css';
                n.setAttribute('rel', 'stylesheet');
                n.href=url;
                _loaded[url] = true;
                refFile.parentNode.insertBefore(n, refFile);
                if (cb) cb(url, context);
                return;
            }

            n.src= url;
            n.setAttribute('async', true);
            n.onload = n.onreadystatechange = function() {
                if (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete') {
                    _loaded[this.getAttribute('src')] = true;
                    if (callback) callback(this.getAttribute('src'), context);
                    n.onload = n.onreadystatechange = null;
                }
            };
            refFile.parentNode.insertBefore(n, refFile);
    },

    clac : function(e) {
        if (!e || !e.length) return;
        var i = 0, item, result = [],
            mods = _config.mods, depeList = [],
            hasAdded = {},

        getDepeList = function(e) {
            var j = 0, m, reqs;
            if (hasAdded[e]) return depeList;
            hasAdded[e] = true;

            if (mods[e].requires) {
                reqs = mods[e].requires;
                for (; typeof (m = reqs[j++]) !== 'undefined';) {
                  if (mods[m]) getDepeList(m);
                  depeList.push(m);
                }
                return depeList;
            }
            return depeList;
        };

        for (; typeof (item = e[i++]) !== 'undefined'; ) {
            if (mods[item] && mods[item].requires && mods[item].requires[0]) {
                depeList = []; hasAdded = {};
                result = result.concat(getDepeList(item));
            }
            result.push(item);
        }
        return result;
    }
};

var Thread = function(e) {
    if (!e || !e.length) return;
    this.queue = e;
    this.current = null;
};

Thread.prototype = {
    start: function() {
        this.current = this.next();
        if (!this.current) return;
        this.run();
    },
    run: function() {
        var o = this, mod, currentMod = this.current;

        if (typeof currentMod === 'function') {
            currentMod();
            return this.start();
        } else if (typeof currentMod === 'string') {
            if (_config.mods[currentMod]) {
              mod = _config.mods[currentMod];
              loader.load(mod.path, mod.type, mod.charset, function(e) {
                 o.start();
              }, o);
            } else if (/\.js|\.css/i.test(currentMod)) {
              loader.load(currentMod, '', '', function(e, o) {
                 o.start();
              }, o);
            } else {
              this.start();
           }
        }
    },
    next: function() { return this.queue.shift(); }
};


this.Ex_Loader  = function() {
    new Thread(loader.clac(Array.prototype.slice.call(arguments))).start();
};

this.Ex_Loader.add = function(ModName, config) {
    if (!ModName || !config || !config.path) return;
    _config.mods[ModName] = config;
};

})(this);
