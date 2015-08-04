/*(function() {
    if (window.google && google.gears) {
        return;
    }
    var factory = null;

    // Firefox
    if (typeof GearsFactory != 'undefined') {
        factory = new GearsFactory();
    } else {
        // IE
        try {
            factory = new ActiveXObject('Gears.Factory');
            // privateSetGlobalObject is only required and supported on IE Mobile on
            // WinCE.
            if (factory.getBuildInfo().indexOf('ie_mobile') != - 1) {
                factory.privateSetGlobalObject(this);
            }
        } catch(e) {
            // Safari
            if ((typeof navigator.mimeTypes != 'undefined') && navigator.mimeTypes["application/x-googlegears"]) {
                factory = document.createElement("object");
                factory.style.display = "none";
                factory.width = 0;
                factory.height = 0;
                factory.type = "application/x-googlegears";
                document.documentElement.appendChild(factory);
            }
        }
    }

    // *Do not* define any objects if Gears is not installed. This mimics the
    // behavior of Gears defining the objects in the future.
    if (!factory) {
        return;
    }

    // Now set up the objects, being careful not to overwrite anything.
    //
    // Note: In Internet Explorer for Windows Mobile, you can't add properties to
    // the window object. However, global objects are automatically added as
    // properties of the window object in all browsers.
    if (!window.google) {
        google = {};
    }

    if (!google.gears) {
        google.gears = {
            factory: factory
        };
    }
})();
*/
/** BrowserStore
*
*
*/

(function() {

/*  var Gears = {
        name: 'Google Gears',
        init: function() {
            var db;
            try {
                db = google.gears.factory.create('beta.database');
                if (db) {
                    db.open('database-shopex_viewstatus');
                    db.execute('create table if not exists status' + ' (skey text, sval text)');
                    this.db = db;
                    this.Master = 'gears';
                } else {
                    return false;
                }
            } catch(ex) {

                return false;
            }

            return this;
        },
        setStorage: function(key, vl) {
            var rs = this.db.execute('select * from status where skey=?', [key]);
            if (rs.isValidRow()) {
                var update = this.db.execute('update status set sval=? where skey=?', [vl, key]);
                rs.close();
            } else {
                var insert = this.db.execute('insert into status values (?,?)', [key, vl]);

            }

        },
        getStorage: function(key, callback) {

            var rs = this.db.execute('select * from status where skey=?', [key]);
            if (rs.isValidRow()) {
                callback(rs.field(1));
            } else {
                callback();
            }
            rs.close();

        },
        removeStorage: function(key) {
            this.db.execute('delete from status where skey=?', [key]);
        },
        clearStorage: function() {
            this.db.execute('drop table status');
        }
    };*/

    var LocalStorage = {

        name: 'localStorage',
        init: function() {
            if (!window.localStorage) {
                return false;
            }
            this._storage = window.localStorage;
            return this;
        },
        setStorage: function(key, value) {
            this._storage.setItem(key, value);
            return true;
        },
        getStorage: function(key, callback) {
            var item = this._storage.getItem(key);
            var value = item ? item: null;
            callback(value);
        },
        removeStorage: function(key) {
            this._storage.removeItem(key);
            return true;
        },
        clearStorage: function() {
            if (this._storage.clear) {
                this._storage.clear();
            } else {
                for (i in this._storage) {
                    if (this._storage[i].value) {
                        this.remove(i);
                    }
                }
            }
            return true;
        }

    };

    var GlobalStorage = {
        name: 'globalStorage',
        init: function() {
            if (!Browser.firefox || !window.globalStorage) {
                return false;
            }
            this._storage = globalStorage[location.hostname];
            return this;
        },
        setStorage: function(key, value) {
            this._storage.setItem(key, value);
            return true;
        },
        getStorage: function(key, callback) {
            var item = this._storage.getItem(key);
            var value = item ? item.value: null;
            callback(value);
        },
        removeStorage: function(key) {
            this._storage.removeItem(key);
            return true;
        },
        clearStorage: function() {
            if (this._storage.clear) {
                this._storage.clear();
            } else {
                for (i in this._storage) {
                    if (this._storage[i].value) {
                        this.remove(i);
                    }
                }
            }
            return true;
        }
    };

    var UserData = {
        name: 'userdata',
        init: function() {
            this.Master = "ie6+";
            if (!Browser.ie) return false;
            this._storage = new Element('span').setStyles({
                'display': 'none',
                'behavior': "url('#default#userData')"
            }).inject(document.body);
            return this;
        },
        setStorage: function(key, value) {
            this._storage.setAttribute(key, value);
            this._storage.save('shopEX_VS');
            return true;
        },
        getStorage: function(key, callback) {
            this._storage.load('shopEX_VS');
            callback(this._storage.getAttribute(key));
        },
        removeStorage: function(key) {
            this._storage.removeAttribute(key);
            this._storage.save('shopEX_VS');
            return true;
        },
        clearStorage: function() {
            var date = new Date();
            date.setMinutes(date.getMinutes() - 1);
            this._storage.expires = date.toUTCString();
            this._storage.save("shopEX_VS");
            this._storage.load("shopEX_VS");
            return true;
        }
    };

    var OpenDatabase = {
        name: 'openDatabase',
        init: function() {
            if (!window.openDatabase) return false;
            this._storage = window.openDatabase("viewState", "1.0", "ShopEX48 ViewState Storage", 20000);

            this._createTable();
            return this;
        },
        setStorage: function(key, value) {
            this._storage.transaction(function(tx) {
                tx.executeSql("SELECT v FROM SessionStorage WHERE k = ?", [key], function(tx, result) {
                    if (result.rows.length) {
                        tx.executeSql("UPDATE SessionStorage SET v = ?  WHERE k = ?", [value, key]);
                    } else {
                        tx.executeSql("INSERT INTO SessionStorage (k, v) VALUES (?, ?)", [key, value]);
                    }
                });
            });
            return true;
        },
        getStorage: function(key, callback) {
            this._storage.transaction(function(tx) {
                v = tx.executeSql("SELECT v FROM SessionStorage WHERE k = ?", [key], function(tx, result) {
                    if (result.rows.length) return callback(result.rows.item(0).v);
                    callback(null);
                });
            });
        },
        removeStorage: function(key) {
            this._storage.transaction(function(tx) {
                tx.executeSql("DELETE FROM SessionStorage WHERE k = ?", [key]);
            });
            return true;
        },
        clearStorage: function() {
            this._storage.transaction(function(tx) {
                tx.executeSql("DROP TABLE SessionStorage", []);
            });
            return true;
        },
        _createTable: function() {
            this._storage.transaction(function(tx) {
                tx.executeSql("SELECT COUNT(*) FROM SessionStorage", [], function() {},
                function(tx, error) {
                    tx.executeSql("CREATE TABLE SessionStorage (k TEXT, v TEXT)", [], function() {});
                });
            });
        }
    };

    var empty = {
        setStorage: function(){},
        getStorage: function(){},
        removeStorage: function(){},
        clearStorage: function(){}
    };

    BrowserStore = new Class({
        initialize: function() {
            this.storage = OpenDatabase.init() || GlobalStorage.init() || LocalStorage.init() || UserData.init() || empty;
            return this;
        },
        set: function(key, vl) {
            vl && typeOf(vl) === 'string' && this.storage.setStorage(key, vl);
            return this;
        },
        get: function(key, callback) {
            this.storage.getStorage(key, callback);
        },
        remove: function(key) {
            if (!key || ! this.storage) return false;
            key && this.storage.removeStorage(key);
            return this;
        },
        clear: function() {
            if (!this.storage) return false;
            this.storage.clearStorage();
            return this;
        }
    });

})();

