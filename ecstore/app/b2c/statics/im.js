window.addEvent('domready', function() {
    $('siderIMchatStyle').inject(document.head.getElement('link'), 'before');


   $('siderIMchat_hiddenbar').addEvent('mouseover',function(){
				         this.setStyle('display','none');
						 $('siderIMchat-main').setStyle('display','block')
				         });

   $('closeSiderIMchat').addEvent('click',function(){
				         $('siderIMchat-main').setStyle('display','none');
						 $('siderIMchat_hiddenbar').setStyle('display','block')
				         })	;



siderIMchatsetGoTop();
});

function siderIMchatsetGoTop(){
	$('siderIMchat').tween('top',document.body.getScroll().y+100)
  }

window.addEvent('scroll',function(){
	  siderIMchatsetGoTop();
	})