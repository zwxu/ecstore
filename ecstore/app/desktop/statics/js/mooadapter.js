




/*Mootools 1.1 Adapter
* 
*/

Window.implement({
        ie:Browser.Engine.trident,
        ie6:Browser.Engine.trident4,
        ie7:Browser.Engine.trident5,
        gecko:Browser.Engine.gecko,
        webkit:Browser.Engine.webkit,
        webkit419:Browser.Engine.webkit419,
        webkit420:Browser.Engine.webkit420,
        opera:Browser.Engine.presto,
        xpath:Browser.Features.xpath
        });
/*        
Object.toQueryString=function(source){
  return Hash.toQueryString(new Hash(source));
}        
        
Class adapter*/
    Class.empty=$empty;



/*Element Adapter*/
Window.implement({
   $E:function(selector,scope){
      return ($(scope)||document).getElement(selector);
   },
   $ES:function(selector,scope){
      return ($(scope)||document).getElements(selector);
   }
});
Element.implement({
   setHTML:function(){
     return this.set('html',Array.flatten($A(arguments)).join('\n'));
   },
   setText:function(text){
     return this.set('text',text);
   },
   getText:function(){
     return this.get('text');
   },
   getHTML:function(){
    return this.get('html');
   },
    setOpacity:function(value){
        return this.set('opacity', value, false);
   },
   setStyles:function(styles){
      switch($type(styles)){
            case 'object': 
            for (var style in styles)this.setStyle(style, styles[style]);break;
            case 'string': this.style.cssText = styles;
        }
        return this;
   },
   getTag:function(){
   return this.tagName.toLowerCase();
   },
   replaceWith:function(el){
        var newEL=$(el, true);
        var oEL=$(this);
        this.parentNode.replaceChild(newEL, oEL);
        return newEL;
    },
    getValue: function(){
        switch(this.getTag()){
            case 'select':
                var values = [];
                for(i=0,L=this.options.length;i<L;i++){
                if (this.options[i].selected) values.push($pick(this.options[i].value, this.options[i].text));
                }
                return (this.multiple) ? values : values[0];
            case 'input': if (!(this.checked && ['checkbox', 'radio'].contains(this.type)) && !['hidden', 'text', 'password','number', 'search', 'tel', 'url', 'email'].contains(this.type)) break;
            case 'textarea': return this.value;
        }
        return false;
    },
    getFormElements: function(){
        return $$(this.getElementsByTagName('input'), this.getElementsByTagName('select'), this.getElementsByTagName('textarea'))||[];
    },
    remove:function(){
      return this.destroy();
    }
});
/*Json Adapter*/
var Json={
   'toString':function(json){
     return JSON.encode(json)||"";
   },
   'evaluate':function(json,secure){
     return JSON.decode(json,secure)||{};
   }
}
Json.Remote=new Class({
      Extends:Request.JSON,
      initialize:function(url,options){
         this.parent($extend(options,{'url':url}));
      }
});

/*Cookie Adapter*/
Cookie.set=Cookie.write;
Cookie.get=Cookie.read;
Cookie.remove=Cookie.dispose;


Element.implement({
    send: function(options){
        var type=$type(options);
        var sender = this.get('send');
        if(type=='object'){
            new Request(options).send(this);
            return this;
        } else{
        sender.send({data: this, url: options || sender.options.url});
        return this;
        }
    }, 
	toQueryString: function(filterEl,abs){
		var queryString = [];
		this.getElements('input, select, textarea', true).each(function(el){
			if (!el.name || el.disabled || el.type == 'submit' || el.type == 'reset' || el.type == 'file') return;
			if(filterEl){if(!filterEl(el))return;}
			var value = (el.tagName.toLowerCase() == 'select') ? Element.getSelected(el).map(function(opt){
				return opt.value;
			}) : ((el.type == 'radio' || el.type == 'checkbox') && !el.checked) ? null : el.value;
			
		    if(el.getAttribute('filterhidden')){
				 el=$(el);
				 var filterBox=el.getParent('.filter_panel').getElement('.filter_box');
				 value=filterBox.toQueryString();   
			}
			if(!value&&abs)return;
			$splat(value).each(function(val){
				if (typeof val != 'undefined') queryString.push(el.name + '=' + encodeURIComponent(val));
			});
		});
		return queryString.join('&');
	}
});



/*FX Adapter*/
Fx.implement({
 stop:function(){
   return this.cancel();
 }
});
Fx.Style=new Class({
   Extends:Fx.Tween,
   initialize:function(el,property,options){
      this._property=property;
      this.parent(el,options);
   },
   set:function(v){
     return this.parent(this._property,v);
   },
   start:function(f,t){
     return this.parent(this._property,f,t);
   }
});
Fx.Styles=new Class({
   Extends:Fx.Morph
});

if(Fx.Scroll){
    Fx.Scroll.implement({
      scrollTo:function(x,y,effect){
         if(effect)return this.start(x,y);
         return this.set(x,y);
      } 
    });
}

Element.implement({
   effect:function(p,o){
   return new Fx.Style(this,p,o);
   },
   effects:function(o){
   return new Fx.Styles(this,o);
   }
});

/*Abstract*/
var Abstract = function(obj){
    obj = obj || {};
    obj.extend = $extend;
    return obj;
};



/*getSize Adapter*/
(function(){
Element.implement({
    getSize: function(){
        if (isBody(this)) return this.getWindow().getSize();
        return {
        x: this.offsetWidth,
        y: this.offsetHeight,
        'size':{x:this.offsetWidth,y:this.offsetHeight},
        'scroll':{x: this.scrollLeft, y: this.scrollTop},
        'scrollSize':{x: this.scrollWidth, y: this.scrollHeight}
        };
    }
});

Native.implement([Document, Window], {
    getSize: function(){
        var win = this.getWindow();
        var doc = getCompatElement(this);
        if (Browser.Engine.presto || Browser.Engine.webkit)
        return {
                x: win.innerWidth, 
                y: win.innerHeight,
                'size':{'x':win.innerWidth,'y':win.innerHeight},
                'scroll':{x: win.pageXOffset,y: win.pageYOffset},
                    'scrollSize':{
                    x: Math.max(doc.scrollWidth, win.innerWidth),
                    y: Math.max(doc.scrollHeight, win.innerHeight)
                    }
               };
        return {
        x: doc.clientWidth,
        y: doc.clientHeight,
        'size':{'x':doc.clientWidth,'y':doc.clientHeight},
        'scroll':{x: win.pageXOffset || doc.scrollLeft, y: win.pageYOffset || doc.scrollTop},
          'scrollSize':{
                    x: Math.max(doc.scrollWidth, win.innerWidth),
                    y: Math.max(doc.scrollHeight, win.innerHeight)
                    }
        };
    }
});

// private methods
function isBody(element){
    return (/^(?:body|html)$/i).test(element.tagName);
};

function getCompatElement(element){
    var doc = element.getDocument();
    return (!doc.compatMode || doc.compatMode == 'CSS1Compat') ? doc.html : doc.body;
};

})();


/*Array Adapter*/

Array.implement({
  copy:function(){
     return $A(this);
  }
});
Array.alias('remove','erase');

/*Hash Adapter*/

Hash.implement({
  merge:function(){
   return  $merge.apply(null,[this].include(arguments));
  }
});



/*Drag.base Adapter*/
try{
    Drag.implement({
      options: {/*
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
            modifiers: {x: 'left', y: 'top'}
        }
    });
    Drag.Base=Drag;
}catch(e){}

/*Extends*/
[Element,Number,String].each(function(o){
    o.extend=o.implement;
 })
 
 
 /*bindwithEventL..*/
 
 Function.implement({
  bindAsEventListener: function(bind, args){
        return this.create({'bind': bind, 'event': true, 'arguments': args});
    }
 });

 /*each bug*/
 
 function $each(iterable, fn, bind){
    var type = $type(iterable);
    ((type == 'arguments' || type == 'collection' || type == 'array'||type=='element') ? Array : Hash).each(iterable, fn, bind);
};
 

if(!window.console){
 window.console={info:$empty,log:$empty};
}




/*Mootools 1.1 Adapter Define End*/
