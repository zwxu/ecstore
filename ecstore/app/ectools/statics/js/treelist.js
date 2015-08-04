var TreeList=new Class({
     options:{
        showStep:3,
        container:'',
        checkboxName:false,
        nodeClass:{
           clazz:'node',
           handle:'node-handle',
           first:'first-node',
           last:'last-node',
           close:'node-close',
           open:'node-open',
           hasc:'node-hasc',
           nl:'node-line',
           icon:'node-icon',
           name:'node-name',
           loading:'node-loading',
           cbox:'node-child-box'
        },
        remoteURL:'',
        remoteParamKey:'p[0]',
        dataMap:{
          PID:'parent_id',
          NID:'cat_id',
          CNAME:'cat_name',
          HASC:'isleaf'
        }
     },
     initialize:function(options){
       $extend(this.options,options);
       this.container=$(this.options.container);
       if(!this.container)return;
       this.initTree();
     },
     createNode:function(data){
          var options=this.options;
          var nc=options.nodeClass;
          var node_handle=new Element('span',{'class':nc.handle})
                          .set({'pid':data['PID'],
                                'nid':data['NID'],
                                'hasc':data['HASC']
                                })
                           .setHTML('&nbsp;');
          var node_line=new Element('span',{'class':nc.nl}).setHTML('&nbsp;');
          var node_checkbox=new Element('input',{
          type:'checkbox',
          name:options.checkboxName,
          value:data['NID'],pid:data['PID']}
          );
          var node_icon=new Element('span',{'class':nc.icon}).setHTML('&nbsp;');
          var node_name=  new Element('span',{'class':nc.name}).setText(data['CNAME']);
          
          var node=new Element('span',{'class':nc.clazz})
               .adopt([node_handle,node_line,node_checkbox,node_icon,node_name]);
               
          
         
        
          
          if(!!data['HASC'].toInt()){
             var _this=this;
             node_handle.addClass(nc.close);
             node_checkbox.set('value',node_checkbox.get('value')+'|close');
             node_handle.addEvent('click',function(e){
                 var node=this.getParent('.'+nc.clazz);
                 if(this.hasClass(nc.close)){
                   if(!node.getNext()||node.getNext().getTag()!=='div'){
                     var ncontainer=new Element('div',{'class':nc.cbox}).injectAfter(node);
                     _this.loadNodes(this.get('nid'),ncontainer);
                     this.addClass(nc.loading);
                     node_checkbox.set('value',node_checkbox.get('value').toInt());
                
                   }else{
                     if(node.getNext()&&node.getNext().getTag()=='div'){
                        node.getNext().show();   
                      }
                   }
                    this.removeClass(nc.close);  
                 }else{
                    if(node.getNext()&&node.getNext().getTag()=='div'){
                            node.getNext().hide(); 
                            this.addClass(nc.close);                    
                    }
                 }
             });
             node_checkbox.addEvent('click',function(){
                 var node=this.getParent('.'+nc.clazz);
                 var nodeNext=node.getNext();
                 if(nodeNext&&nodeNext.getTag()=='div')
                 $ES('input[type=checkbox]',nodeNext).set('checked',this.checked==true?true:false);
             });
          }
              if($E('input[value='+node_checkbox.get('pid')+']',this.container))
              if($E('input[value='+node_checkbox.get('pid')+']',this.container).checked){
                             node_checkbox.set('checked',true);
                        }
         return node;
     }, 
     initTree:function(){
         this.loadNodes(0);
     },
     loadNodes:function(pid,c){
       var nodes;
       var options=this.options;
       var d=options.dataMap;
       new Request.JSON({
       url:this.options.remoteURL.substitute({param:this.options.remoteParamKey,value:pid})+"&v="+$time(),
       onRequest:function(){
       },
       onSuccess:function(data){
        
         var options=this.options;
         var dmap=$H(options.dataMap);
         if($E('span[nid='+pid+']',this.container))
         $E('span[nid='+pid+']',this.container).removeClass(options.nodeClass.loading);
         
         data.each(function(item,index){
               
               var node_pro={};
               dmap.each(function(v,k){
                 node_pro[k]=item[v];
               });
            var node=this.createNode(node_pro);
                 if(index==0){
                   node.addClass(options.nodeClass.first);
                 }
                 if(data.length==index+1){
                   node.addClass(options.nodeClass.last);
                 }
                 if(node_pro.HASC.toInt()){
                    node.addClass(options.nodeClass.hasc);
                 }
                 this.addNode(node,c);
         
         }.bind(this));
        
            
        
        
       }.bind(this)}).get();
     },
     addNode:function(node,container){
       if(!container)
       $(node).inject(this.container);
       else
       $(node).inject(container);
       
       var ckbox=node.getElement('input[type=checkbox]');
           if(ckbox&&ckbox.retrieve('check')){
              ckbox.set('checked',true);
           }
       
       switch(this.options.showStep){
          case 1:
           return;
          case 2:
          if(!container){
            $E('.'+this.options.nodeClass.handle,node).fireEvent('click')
          }
           return;
          case 3:
          if(!container||(container&&!container.getParent().hasClass(this.options.nodeClass.cbox))){
            $E('.'+this.options.nodeClass.handle,node).fireEvent('click')
          }
           return;
          case 4:
          return $E('.'+this.options.nodeClass.handle,node).fireEvent('click');
       }
       
       
       
     
     },
     removeNode:function(){
        
     }
});

