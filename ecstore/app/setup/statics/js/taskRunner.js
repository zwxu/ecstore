var TaskRunner = new Class({
	Implements: [Events,Options],
	options:{
		onError:function(text){
			alert(text);
		},
		stateClass:{
			error:'error2',
			loading:'loading',
			complete:'complete',	
			success:''
		},
		showStep:'.appNum',
		showAppName:'.appName',
		container:false,	
		dataClass:'.tasks_ipt'
	},	
	initialize:function(tasks,options){
		this.setOptions(options);
		this.tasks=$splat(tasks);
		if(!this.tasks)return;
		this.num=this.tasks.length||0;
		this.container=$(this.options.container);
	},
	init:function(container){
		if(!this.iframe)
		this.iframe = this.options.iframe?this.options.iframe: new Element('iframe',{scr:'blank.html',name:'_TASK_IFRM_',style:'display:none;height:100%;width:100%'});
		container=container||this.container;
		this.iframe.inject(container||document.body);    
		this.form = this.form||new Element('form',{style:'display:none',method:'post',target:this.iframe.name}).inject(document.body);
		
		var options=this.options;
		if(!container)return this;
		this.showStep=container.getElement(options.showStep);
		this.appName=container.getElement(options.showAppName);
		this.items=container.getElements('.box');
	
		return this;
	},
	createFormData:function(ipt){
		if(!this.form)return this;
		this.form.empty();
		var options=this.options,fdoc=document.createDocumentFragment(),  
			data=ipt?ipt:$ES(options.dataClass);
		if(data&&data.length)
		data.each(function(ipt,k){
			var n=ipt.name,v=ipt.value;
			fdoc.appendChild(new Element('input',{type:'hidden','name':n,value:v}));		
		});
		this.form.appendChild(fdoc);
		return this;			   
    },
	loader:function(){ 
		if(this.items&&this.items.length)this.items[this.prestep].addClass(this.options.stateClass.loading);
		if(this.showStep)this.showStep.setText(this.step);
		if(this.appName)this.appName.setText(this.items[this.prestep].get('appname'));

	    return this.fireEvent('loader'); 
    },
    cancel:function(){ 
		return this.fireEvent('cancel',this.step);
	},
	run:function(actions){
		this.extra_action=actions;
		this.init(this.container).fireEvent('load',[this.tasks]).createFormData();
		if(actions)return this.progress(actions);
		return this.start();
	},
	start:function(step,actions){
		this.step=step||1;
		this.prestep=this.step-1;
		this.actions=actions||this.tasks[this.prestep];
		return this.fireEvent('start').loader().progress(this.actions);
    },
	error:function(text){
		var stateClass=this.options.stateClass;
		if(this.items&&this.items.length&&this.items[this.prestep])
		this.items[this.prestep].removeClass(stateClass.loading).addClass(stateClass.error);

		return this.fireEvent('error',text).cancel();		  
    },
	next:function(step){
		var steps=(step||0)+1;
		return this.start(steps);
	},
	progress:function(actions){
		if(!actions)return this;
		var action=actions||this.actions,iframe=this.iframe;
		this.form.action=action;
		this.createFormData().form.submit();
		iframe.addEvent('load',this.check.bind(this,iframe));
		return this.fireEvent('progress');
	},
	check:function(iframe){
	    iframe.removeEvents('load');
	    var body=iframe.contentWindow.document.body,
			messageText=$(body).getText();	
		this.result=/(\s*)ok\.(\s*)/.test(messageText.slice(-4));

		this.fireEvent('check',messageText);
		var error=messageText.slice(-500);
		messageText.slice(-500).replace(/Error:(\s\S+)/,function(){
			  var arg=arguments;error=arg[1];
	    });		
		return this.result?this.complete():this.error(error);
	},
	complete:function(){		
		this.fireEvent('complete');
		if(this.extra_action){delete this.extra_action;return this.start(1);}

		var stateClass=this.options.stateClass;
		if(this.items&&this.items.length)
		this.items[this.prestep].removeClass(stateClass.loading).addClass(stateClass.complete);
	
		if(!this.step&&!this.num)return this.success();
		var step=this.step||0;
		return this[step==this.num?'success':'next'](step);
    },
	success:function(){
		return this.fireEvent('success');	
	}
});





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
			var iNow = now[i];
			for (var p in iNow) this.render(this.elements[i], p, iNow[p], this.options.unit);
		}
		return this;
	},

	start: function(obj){
		if (!this.check(obj)) return this;
		var from = {}, to = {};
		for (var i in obj){
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
var Accordion = Fx.Accordion = new Class({

	Extends: Fx.Elements,

	options: {/*
		onActive: $empty(toggler, section),
		onBackground: $empty(toggler, section),
		fixedHeight: false,
		fixedWidth: false,
		*/
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
		var params = Array.link(arguments, {'container': Element.type, 'options': Object.type, 'togglers': $defined, 'elements': $defined});
		this.parent(params.elements, params.options);
		this.togglers = $$(params.togglers);
		this.container = document.id(params.container);
		this.previous = -1;
		this.internalChain = new Chain();
		if (this.options.alwaysHide) this.options.wait = true;
		if ($chk(this.options.show)){
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
		if ($chk(this.options.display)) this.display(this.options.display, this.options.initialDisplayFx);
		this.addEvent('complete', this.internalChain.callChain.bind(this.internalChain));
	},

	addSection: function(toggler, element){
		toggler = document.id(toggler);
		element = document.id(element);
		var test = this.togglers.contains(toggler);
		this.togglers.include(toggler);
		this.elements.include(element);
		var idx = this.togglers.indexOf(toggler);
		var displayer = this.display.bind(this, idx);
		toggler.store('accordion:display', displayer);
		toggler.addEvent(this.options.trigger, displayer);
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

	detach: function(){
		this.togglers.each(function(toggler) {
			toggler.removeEvent(this.options.trigger, toggler.retrieve('accordion:display'));
		}, this);
	},

	display: function(index, useFx){
		
		if (!this.check(index, useFx)) return this;
		useFx = $pick(useFx, true);
		if (this.options.returnHeightToAuto){
			var prev = this.elements[this.previous];
			if (prev && !this.selfHidden){
				for (var fx in this.effects){
					prev.setStyle(fx, prev[this.effects[fx]]);
				}
			}
		}
		index = ($type(index) == 'element') ? this.elements.indexOf(index) : index;
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
		this.internalChain.chain(function(){
			if (this.options.returnHeightToAuto && !this.selfHidden){
				var el = this.elements[index];
				if (el) el.setStyle('height', 'auto');
			}
		}.bind(this));
		return useFx ? this.start(obj) : this.set(obj);
	}

});



var validate = function(_form,match){
   
    if(!_form)return true;

    var formElements = _form.match(match||'form')?_form.getElements('[vtype]'):[_form];    
    
    
    var err_log = false;
   
    
    var _return = formElements.every(function(element){
         
         var vtype = element.get('vtype');
         
         if(!$chk(vtype))return true;
        
         if(!element.isDisplay()&&(element.getAttribute('type')!='hidden'))return true;
         
         var valiteArr  = vtype.split('&&');
         
         if(element.get('required')){
             valiteArr = ['required'].combine(valiteArr.clean());
         }
         return vtype.split('&&').every(function(key){
                if(!validateMap[key])return true;
                var _caution = element.getNext();
                var cautionInnerHTML = element.get('caution')||validateMap[key][0];
                
                if(validateMap[key][1](element,element.getValue())){
                        
                        if(_caution&&_caution.hasClass('error')){_caution.remove();};
                       
                        return true;
                
                }
                
                
                
                if(!_caution||!_caution.hasClass('caution')){
                
                    new Element('span',{'class':'error caution notice-inline','html':cautionInnerHTML}).injectAfter(element);
                    
                    element.removeEvents('blur').addEvent('blur',function(){
                        
                       if(validate(element)){
                            
                           if(_caution&&_caution.hasClass('error')){_caution.remove()};
                           
                           element.removeEvent('blur',arguments.callee);
                       } 
                    
                    });
                    
                    
                }else if(_caution&&_caution.hasClass('caution')&&_caution.get('html')!=cautionInnerHTML){
                    
                    _caution.set('html',cautionInnerHTML);
                
                }
                
                return false;
         
         });
    
    
    });
    if(!_return){}
    
    return _return;

}
