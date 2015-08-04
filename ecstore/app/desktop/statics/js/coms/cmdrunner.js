(function(){
    var TaskRunner = new Class({
        Implements: [Events,Options],
        options:{
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
            this.iframe = this.options.iframe?this.options.iframe: new Element('iframe',{src:'blank.html',name:'_TASK_IFRM_',style:'display:none;height:100%;width:100%'});
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
        getText:function(){
            var temp = document.createElement('div');
            return this[(temp.innerText == null) ? 'textContent' : 'innerText'];
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
            if(text)alert(text);

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
                messageText=this.getText.call(body);
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
            if(this.form)this.form.destroy();
            return this.fireEvent('success');
        }
    });



    var cmdrunner = this.cmdrunner = new Class({
        Extends:TaskRunner,
        options:{
            title:'',
            singlon:true
        },
        delayT:function(){

            if(!this.iframe.contentWindow)return this;

            var doc=this.iframe.contentWindow.document.body;

            if(!doc)return this;

            var text=this.getText.call(doc);

            text.replace(/[>|\n]([^\n]+)/gi,function($1,$2){
                if($2.trim().length>1)
                this.csolinfo.set('text',$2);
            }.bind(this));

            return this;
        },
        startTimer:function(){
            this.timer= this.delayT.periodical(200,this);
            return this;
        },
        stopTimer:function(){
            if(this.timer) $clear(this.timer);
            return this;
        },
        check:function(iframe){
            this.stopTimer().delayT();
            return this.parent(iframe);
        },
        createTpl:function(){
            var queue=this.tasks,type=queue.filter(function(el){return el.type!=='dialog';}),html=this.options.singlon?'':'<h5>'+LANG_Cmdrunner['title']+'</h5>';
            html+='<ul class="division apptip">';
            if(type.length)
            type.each(function(t,i){
                var n=t.name||this.options.title;
                html+='<li class="box" appname="'+n+'">'+n+'</li>';
            },this);
            var num=queue.filter(function(q){return q.type!='dialog';}).length;
            html+='</ul>';
            this.theme=this.options.singlon?html:html+'<div class="division loader"><em class="appNum">0</em>/<em>'+num+'</em><span class="appName"></span></div>';
            var csol='<div class="console"><p class="csolinfo">loading...</p><span class="lnk csol" onClick="_open(\'index.php?app=desktop&ctl=appmgr&act=app_console\')">'+LANG_Cmdrunner['console']+'</span></div>';
            this.container.innerHTML=this.theme+csol;
            this.csolinfo=$E('.csolinfo',this.container);
            return this;
        },
        close:function(d){
            var dialog=d||this.dialog;
            if(dialog)dialog.close.delay(800,dialog);return this;
        },
        adddialog:function(url){
           return new Dialog(url,{title:this.options.title,height:200,width:600,modal:true,resizeable:false,onClose:function(){this.stopTimer();}.bind(this)});
        },
        progress:function(task){
            var url;
            switch(task.type){
                case 'command':
                url='index.php?app=desktop&ctl=appmgr&act=command&command_id='+task.command_id +'&data='+ encodeURIComponent(task.data);
                this.startTimer().parent(url);
                break;
                case 'dialog':
                url = 'index.php?app=desktop&ctl=appmgr&act='+task.action +'&data='+encodeURIComponent(task.data);
                this.tasks.splice(this.prestep,1);
                this.num=this.tasks.length;
                this.stopTimer();
                new Request.HTML({url:url,append:this.appinfo,
                    onComplete:function(){
                        var appbtn=this.appinfo.getElement('.appbtn');
                        if(appbtn){
                            this.container.hide();
                            appbtn.addEvent('click',function(){
                                this.container.show();
                                this.appinfo.hide();
                                this.start(this.step);
                            }.bind(this));
                        }else{
                            this.start(this.step);
                        }
                }.bind(this)}).get();
                break;
                default:this.startTimer().parent(task); break;
            }
        },
        init:function(){
            this.container=this.container||new Element('div',{'class':'appbox','html':this.theme||''});
            this.dialog =this.adddialog.call(this,this.container);
            this.appinfo=new Element('div',{'class':'appinfo'}).inject(this.container,'before');
            this.createTpl().parent(this.container);
            return this;
        },
        success:function(){
            this.parent();
            if(this.options.singlon)this.close();
            MessageBox.success(LANG_Cmdrunner['success']);
        }
    });

    this.appmgr =function(appdata){
        return new ApplicationManager(appdata,{'onSuccess':function(){
                    if(!finderGroup)return;
                    for(var f in finderGroup){
                        if(finderGroup[f])finderGroup[f].refresh();
                    }
                }});
    }

    var ApplicationManager = new Class({
        Extends:TaskRunner,
        type:{
            install:LANG_Cmdrunner['install'],
            uninstall:LANG_Cmdrunner['uninstall'],
            update:LANG_Cmdrunner['update'],
            pause:LANG_Cmdrunner['stop'],
            active:LANG_Cmdrunner['start'],
            download:LANG_Cmdrunner['download']
        },
        run:function(app_id){
            this.app_id=app_id;
            this.parent();
        },
        init:function(){return this;},
        progress:function(actions){
            new Request.JSON({url: 'index.php?app=desktop&ctl=appmgr&act=prepare&action='+actions,
            onSuccess: this.prepare.bind(this)}).post({'action':actions,'app_id':this.app_id});
        },
        prepare:function(prepare_result){
            if(!prepare_result)return this.error();
            switch(prepare_result.status){
                case 'confirm':
                var confirm_result = window.confirm(prepare_result.message);
                if(!confirm_result){if(this.dialog)this.dialog.close();return this;}
                break;
                case 'alert':
                alert(prepare_result.message);return;
                break;
                case 'error': break;
                default:break;
            }
            var type=this.type;
            this.queue= prepare_result.queue;
            if(this.dialog)this.dialog.close();
            this.runner=new cmdrunner(this.queue,{
                title:type[this.tasks[this.prestep]]+':'+this.app_id,
                singlon:false,onSuccess:this.complete.bind(this)
            });
            this.runner.run();
            return this.dialog=this.runner.dialog;
        },
        success:function(){
            this.parent().runner.close(this.dialog);
        }
    });

})();
