$(function(){
	$('.help-box').each(function(){
		initHelpBox($(this));
	});
	
});
function initHelpBox(Obj){
	var position = Obj.position();
	//data-orientation='left'||data-orientation='right'
	// var orientation =$(Obj).data('orientation');
	// if(orientation==null){orientation ='right';}//default value; 

	var content = $(Obj).data('content');
	var html=[];
	html.push("<div class='help-icon'><i class='fa fa-question' ></i></div>");
	html.push("<div class='help-info' ><i class='fa fa-play fa-rotate-180'></i>");
	html.push(content,"</div>");
	$(Obj).html(html.join(''));
	
	//auto detect left or right
	if($( window ).width()-position.left<200){
		$(Obj).find('.help-info').css('left','-226px');
		$(Obj).find('i').css('left','208px');
		$(Obj).find('i').removeClass('fa-rotate-180');
	}

	//trigger type  data-trigger='click'||data-trigger='hover'
	var trigger =$(Obj).data('trigger');

	if(trigger==null){trigger ='hover';}//default value;

	if(trigger == 'hover'){
		$(Obj).find('.help-icon').on('mouseenter',function(){
			$(this).next('.help-info').show();
		});
		$(Obj).find('.help-icon').on('mouseleave',function(){
			$(this).next('.help-info').hide();
		})
	}else if(trigger=='click'){
		$(Obj).find('.help-icon').on('click',function(){
			$(this).next('.help-info').toggle();
		})
	}
}