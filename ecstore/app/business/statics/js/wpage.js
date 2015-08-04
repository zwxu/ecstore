Element.extend({
  fixEmpty:function(){
    if(this.get('html').trim()===''||this.get('html')=='&nbsp;'){
      return this.setStyle('font-size',0);
    }
    if(this.style.height.toInt() === 0){this.setStyle('height','');}
    return this.setStyle('font-size','');
  },
  amongTo:function(elp,opts){
    var el=this;
    var elSize=el.getSize(),
        elpSize=elp.getSize();
    var options={width:2,height:2};

    if(opts){options=Object.merge(options,opts);}

    el.setStyle('position','absolute');

    var pos={
        'top':Math.abs(((elpSize.size.y / options.height).toInt())-((elSize.size.y / options.height).toInt())+elp.getPosition().y+elpSize.scroll.y),
        'left':Math.abs(((elpSize.size.x / options.width).toInt())-((elSize.size.x / options.width).toInt())+elp.getPosition().x+elpSize.scroll.x)
    };
    el.setStyles(pos);

    if(el.getStyle('opacity')<1)el.setOpacity(1);
    if(el.getStyle('visibility')!='visible')el.setStyle('visibility','visible');
    if(el.getStyle('display')=='none')el.setStyle('display','');
    return this;
  }
});
(function() {
  this.$globalEval = Browser.exec;
  var processLinks = function() {
    $(document.body).addEvent('click',function(e) {
        var clickElement = $(e.target);
        for (var i = 0; i < 3; i++) {
          if (!clickElement || $chk(clickElement.get('href'))) continue;
          clickElement = clickElement.getParent();
        }
        if (!clickElement || !clickElement.get('href')) {
          return null;
        }
        var ceTarget = clickElement.get('target') || '';
        var ceHref = clickElement.href || '';
        var ceLabel = clickElement.get('text') || '';
        var _matchFail = !((!$chk(ceTarget) || ceTarget.match(/({|:)/)) && !ceHref.match(/^javascript.+/i) && !clickElement.onclick);
        var regexp = new RegExp('' + SHOPADMINDIR + '');
        if (ceTarget.match(/blank/) && ceHref.match(regexp)) {
          e.stop();
          return _open(ceHref);
        }
        if (_matchFail) {
          return null;
        }
        e.stop();
        if (ceTarget.match(/::/)) {
          var clickOpt = ceTarget.split('::');
          switch (clickOpt[0]) {
          case 'dialog':
            return new Dialog(ceHref, JSON.decode(clickOpt[1] || {}));
          case 'open':
            return _open(ceHref, JSON.decode(clickOpt[1] || {}));
          case 'command':
            return Ex_Loader('cmdrunner', function() {
              new cmdrunner(ceHref, JSON.decode(clickOpt[1] || {})).run();
            });
          }
        }
        if (e.shift) return open(ceHref.replace('?', '#'));
        W.page(ceHref, $extend({
            method: 'get'
            /*,data:$H({x_navlabel:ceLabel})*/
        },
        JSON.decode(ceTarget)), clickElement);
    });
  };
  var Wpage = this.Wpage = new Class({
    Extends: Request,
    exoptions: {
      evalScripts: true,
      link: 'cancel',
      message: false,
      render: true,
      sponsor: false,
      clearUpdateMap: true,
      updateMap: {},
      url: '',
      data: '',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'text/javascript, text/html, application/xml, text/xml, */*'
      },
      async: true,
      format: false,
      method: 'post',
      emulation: true,
      urlEncoded: true,
      encoding: 'utf-8',
      evalResponse: false,
      noCache: false,
      update: false
    },
    initialize: function(options, defaultAction) {
      options = $merge(this.exoptions, options);
      this.parent(options);
      //processLinks();
    },
    eventClear: function() {
      for (e in this.$events) {
        var fns = this.$events[e].clean();
        for (var i = fns.length; i--;) {
          this.removeEvent(e, fns[i]);
        }
      }
      return this;
    },
    page: function() {
      var update;
      var params = Array.flatten(arguments).link({
        'url': String.type, // 请求地址
        'options': Object.type, // 请求配置
        'sponsor': Element.type // 发起请求事件的元素
      });
      this.eventClear();
      update = (params.options && params.options.update) ? $(params.options.update) : '';
      if ( !! update) {
        for (e in update.retrieve('events', {})) {
          update.removeEvents(e);
        }
      }
      delete(this.options.updateMap);
      params.options = params.options || {};
      this.setOptions(this.exoptions);
      if (params.options) {
        this.setOptions(params.options);
      }
      if (params.sponsor) {
        this.options.sponsor = params.sponsor;
      }
      if (params.url) {
        this.options.url = params.url;
      }
      if (!this.options.update && this.options.sponsor && $type(this.options.sponsor) == 'element') {
        this.options.update = this.options.sponsor.getContainer();
      }
      update = this.options.update = $(this.options.update || LAYOUT.content_main);
      var exUpdateMap = {
        '.side-r-content': LAYOUT.side_r_content,
        '.mainHead': update.getPrevious(),
        '.mainFoot': update.getNext()
      };
      this.options.updateMap = $merge(exUpdateMap, this.options.updateMap);
      this.send(this.options);
    },
    success: function(html, xml) {
      if ((/text\/jcmd/).test(this.getHeader('Content-type'))) {
        //return this.doCommand.apply(this, $splat(arguments));
      }
      html = html.stripScripts(function(s) {
        this.response.javascript = s;
      }.bind(this));
      var match = html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
      if (match) {
        html = match[1];
      }
      var update = this.options.update;
      var updateMap = this.options.updateMap;
      html = html.replace(/<\!-{5}(.*?)-{5}([\s\S]*?)-{5}(.*?)-{5}>/g,
      function() {
        var $k = arguments[1];
        $k = updateMap[$k] || $($k);
        var $v = arguments[2] || null;
        if ($v && $k) {
          $k.empty().set('html', $v).fixEmpty();
        }
        return '';
      });
      update.empty().set('html', html);
      if (update == LAYOUT.content_main) {
        var _v = this.options.url.match('index\\.php\\?(.*)');
        if (_v && _v[1]) {
          if (typeof history.pushState != 'undefined') {
            /*html5 history plus*/
            history[this.popstate ? 'replaceState': 'pushState']({
              go: _v[1]
            },
            '', '#' + _v[1]);
            this.popstate = false;
          } else if (this.hstMan) {
            this.hstMan.setValue(0, _v[1]);
          }
        }
      }
      this.render(update);
      if (this.options.evalScripts) {
        Browser.exec(this.response.javascript);
      }
      this.onSuccess(html, xml, this.response.javascript);
      this.onComplete();
    },
    onFailure: function() {
      switch (this.status) {
        case 404:
          Message.error('页面末找到');
          break;
        case 401:
          Message.error('需要重新登录.<a href="javascript:void(0);" onclick="location.reload();">点此重新登录</a>');
          break;
        case 403:
          Message.show('需要重新登录.<a href="javascript:void(0);" onclick="location.reload();">点此重新登录</a>');
          break;
        default:
      }
      this.parent();
    },
    render: function(scope) {
      if (!this.options.render) return this;
      scope = (scope || this.options.update);
      var _this = this;
      $(scope).getElements('form').each(function(f) {
        f.addEvent('submit',function(e) {
          var _form = this;
          if (_form.retrieve('submiting')) {
            return e.stop();
          }
          if (!validate(_form)) {
              e.stop();
              return Message.error('表单验证失败');
          }
          $ES('textarea[ishtml=true]', _form).getValue();
          var _formtarget = _form.get('target');
          if (_formtarget == '_blank') {
              return true;
          }
          if (_form.get('enctype') == 'multipart/form-data' || _form.get('encoding') == 'multipart/form-data') {
            _form.target = 'upload';
            $('uploadframe').addEvent('load',function() {
              if (_form && Slick.uidOf(_form)) $(_form).eliminate('submiting');
              var doc = this.contentWindow.document;
              var response = doc.body[(doc.body.innerText ? 'innerText': 'textContent')];
              var cmd = null;
              try {
                if ((cmd = JSON.decode(response)) && cmd.splash) {
                  _this.eventClear().doCommand.call(_this, cmd);
                }
              } catch(e) {}
              var targetobj = $(_form).retrieve('target', {});
              if (('onComplete' in targetobj) && 'function' == $type(targetobj.onComplete)) {
                try {
                  targetobj.onComplete(response);
                } catch(e) {}
              }
              $('uploadframe').removeEvent('load', arguments.callee).set('src', $('uploadframe').retrieve('default:src'));
            }).store('default:src', $('uploadframe').src);
            _form.store('submiting', true).submit();
            return true;
          }
          e.stop();
          var _options = $merge(_form.retrieve('target', {}), JSON.decode(_formtarget)),
          _onComplete = _options.onComplete || $empty;
          _options.onComplete = function() {
            _onComplete.apply(_this, $splat(arguments));
            if (_form &&Slick.uidOf(_form)) $(_form).eliminate('submiting');
          };
          _this.page(_form.store('submiting', true).action, $merge({
            method: _form.method,
            data: _form,
            message: LANG_Wpage['form']['loading']
          },
          _options), _form);
        });
      });
    },
    onComplete: function(re) {
      var scope = this.options.update;
      var dpInputs = $(scope).getElements('input[date]');
      if (dpInputs&&dpInputs.length)
        Ex_Loader("picker",
          function() {
            dpInputs.each(function(dpi) {
              dpi.makeCalable();
            });
        });
      /*BREADCRUMBS*/
      // BREADCRUMBS =window.BREADCRUMBS;
      if (BREADCRUMBS){
        var _BREADCRUMBS = BREADCRUMBS.split(':');
        [LAYOUT.head, LAYOUT.side].clean().each(function(layout) {
          layout.getElements('.current').removeClass('current');
          _BREADCRUMBS.each(function(item) {
            var i = layout.getElement('a[mid=' + item + ']');
            if ( !! i) {
              i.addClass('current');
            }
          });
        });
      }
      /*autocompleter*/
      var autocpt = $(scope).getElements('input[autocompleter], textarea[autocompleter]');
      // var autocpt = $$(autocpt, $(scope).getElements('textarea[autocompleter]'));
      if (autocpt&&autocpt.length)
        Ex_Loader('autocompleter',
          function() {
            autocpt.each(function(item) {
              //item.setAttribute('autocomplete','off');
              var params = item.get('autocompleter');
              var callUrl = '?app=desktop&ctl=autocomplete&params=' + params;
              var _getVar = params.match(/:([^,]*)/)[1];
              item.addEvent('keydown',
              function(e) {
                if (e.code == 13) e.stop();
              });
              var _base_options = {
                getVar: _getVar,
                fxOptions: false,
                delay: 300,
                callJSON: function() {
                  return window.autocompleter_json;
                },
                injectChoice: function(json) {
                  var token = json[this.options.getVar];
                  var choice = new Element('li', {
                    'html': this.markQueryValue(token)
                  });
                  choice.inputValue = token;
                  this.addChoiceEvents(choice).inject(this.choices);
                }
              };
              var _options = $merge(_base_options, JSON.decode(item.get('ac_options')));
              new Autocompleter.script(item, callUrl, _options);
          });
        });
    }
  });
})();