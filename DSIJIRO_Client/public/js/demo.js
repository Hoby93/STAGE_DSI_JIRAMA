/* =================================
===  EXPAND COLLAPSE            ====
=================================== */
 $(document).ready(function(){
 	$('#toggle-switcher').click(function(){
		if($(this).hasClass('open')){
			$(this).removeClass('open');
			$('#switch-style').animate({'left':'-220px'});
		}else{
			$(this).addClass('open');
			$('#switch-style').animate({'left':'0'});
		}
	});
	$('#toggle-switcher-right').click(function(){
        if($(this).hasClass('open')){
            $(this).removeClass('open');
            $('#switch-style-right').animate({'right':'-369px'}, 600);
        }else{
            $(this).addClass('open');
            $('#switch-style-right').animate({'right':'0'}, 600);
        }
    });
});