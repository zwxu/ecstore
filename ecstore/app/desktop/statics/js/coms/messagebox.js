var MessageBox = new Class({
      Implements: [Events, Options],
      options:{
         /*onShow:$empty,
         onHide:$empty,*/
         element:'messagebox',
         type:'default',
         autohide:false,
         type2class:{
             'default':'default',
             'error':'exception',
             'notice':'warning'
         }
      },
      initialize:function(content,options){
        
         this.setOptions(options);
         $clear(MessageBox.delay);
		 this.options.element=$(this.options.element);
         for(e in this.options.element.$events){
             this.options.element.removeEvents(e);
         }
         
         this.options.element.className = this.options.element.className.replace(/(default|warning|exception)/,'');
         /*
		 if(content.length>25){
		
			this.hide();
			var bigmsgbox =  new Element('div',{'class':'msgbox '+this.options.type2class[this.options.type],styles:{
				position:'absolute',
				width:window.getSize().x*.35,
				zIndex:65535,
				'word-wrap':'break-word',
				'white-space':'normal'
			}});
				bigmsgbox.adopt(
						new Element('div',{'class':'clearfix',styles:{'line-height':24}}).adopt(
								new Element('div',{text:LANG_MessageBox['close'],'class':'frt'}).addEvent('click',function(){
									bigmsgbox.dispose();
									MODALPANEL.hide();
								})
							),
						new Element('div',{html:content})
					);
				MODALPANEL.show();	
				bigmsgbox.inject(document.body).amongTo(window);
			return;
		 } */
		
		
		 this.options.element.set('html',content);
         
         var ah = this.options.autohide;

         if(!!ah){
         
             ah = ($type(ah)=='number')?ah:(3*1000);
             
             MessageBox.delay = this.hide.delay(ah,this);
         
         }
         
      
         return this.show();
      },
      show:function(){ 
        this.options.element.addClass(this.options.type2class[this.options.type]); 
        return this.fireEvent('onShow',arguments);
      },
      hide:function(){
        this.options.element.removeClass(this.options.type2class[this.options.type]); 
        return this.fireEvent('onHide',arguments);
      }
});

MessageBox.delay=0;

$extend(MessageBox,{

   error:function(msg){
        
        new MessageBox(msg,{type:'error',autohide:true});
   
   },
   success:function(msg){
       
        new MessageBox(msg,{autohide:true});
   
   },
   show:function(msg){
        
        new MessageBox(msg,{type:'notice',autohide:true});
   
   }
  
});