var onanimate = false;

jQuery(function($){
	// Scroll to top button
	var scrollTimeout;
	
	$('a.scroll-top').click(function(){
		$('html,body').animate({scrollTop:0},500);
		return false;
	});

	$(window).scroll(function(){
		clearTimeout(scrollTimeout);
		if($(window).scrollTop()>400){
			scrollTimeout = setTimeout(function(){$('a.scroll-top:hidden').fadeIn()},100);
		}
		else{
			scrollTimeout = setTimeout(function(){$('a.scroll-top:visible').fadeOut()},100);	
	}
	});
	
	//Initial ScrollSpy, Carousel, Images grayscale
	$('.nav-collapse').scrollspy();
	$('.carousel').carousel({interval:false});
	
	// Scroll to section onclick to menu
	
	$('#nav .nav a').click(function(e){
		e.preventDefault();
		var des = $(this).attr('href');
		goToSectionID(des);
	})

	//Fix dropdown bootstrap
	$('body').on('touchstart.dropdown', '.dropdown-menu', function (e) { e.stopPropagation(); })
				.on('touchstart.dropdown', '.dropdown-submenu', function (e) {e.preventDefault();});
	if( 'ontouchstart' in document.documentElement ) {
		var clickable = null;
		$('#access .menu-item').each(function(){
			var $this = $(this);

			if( $this.find('ul.sub-menu').length > 0 ) {

				$this.find('a:first').unbind('click').bind('touchstart',function(event){
					if( clickable != this ) {
						clickable = this;
						event.preventDefault();
					} else {
						clickable = null;
					}
				});
			}
		});
	}


	//Trigger change url on scroll
	$('.nav-collapse').on('activate',function(e){
		if(onanimate) return;
		
		if(history.pushState) {
		    history.pushState(null, null, $('.nav-collapse .active a').attr('href'));
		}
		else {
			//var currenttop = $(window).scrollTop();
		  //  location.hash = $('.nav-collapse .active a').attr('href');
		  //$(window).scrollTop(currenttop);
		}
		
	})

	// Carousel Slider spy
	$('.carousel').on('slid',function(e){
		var t = $(this),
			item = t.find('.carousel-inner .active'),
			idx =	t.find('.carousel-inner .item').index(item);
		t.find('.carousel-nav > ul li').removeClass('active')
		t.find('.carousel-nav > ul li:eq('+idx+')').addClass('active')
	})

	// Generate slider pagination 
	
	$('.carousel').each(function(){
		var t = $(this);
		t.find('.carousel-nav > ul li').live('click',function(e){
			e.preventDefault();
			var idx = t.find('.carousel-nav > ul li').index($(this));
			t.carousel(idx);
		});
	}) 

	$('.carousel').each(function(){
		var t = $(this),
			nav = t.find('.carousel-nav > ul');
		t.find('.carousel-inner .item').each(function(i,j){
			var clss = (i==0)?'active':'';
			nav.append('<li class="img-circle '+clss+'"><a class="img-circle" href="#'+i+'"><span></span></a></li> ');
		});
	});

	$('.arrow-down').on('click',function(event){
		event.preventDefault();
		var des = '#'+$(this).closest('.section').next().attr('id');
		goToSectionID(des);
	});

	$('.team .personal').hover(
		function(){
			$(this).find('.img_wrapper .img_grayscale').stop().animate({opacity:1},200);

		},function(){
			$(this).find('.img_wrapper .img_grayscale').stop().animate({opacity:0},200);
		}
	)

	///Nawras////
	$('.introducing > .block').click(function() {
		 $(this).parent().find('.span3').removeClass('active');
     	 $(this).addClass('active');
     	 var currentID=$(this).attr('id');
     	 $('.allservices').addClass('expand');
   	$('#'+currentID+'_cont').parent().find('.allcont').removeClass('acc');
    $('#'+currentID+'_cont').toggleClass('acc');
    return false;
  });

	$('#brand').click(function() {
    $('.brand_cont').toggle('slow');
    return false;
  });
	$('#event').click(function() {
    $('.event_cont').toggle('slow');
    return false;
  });
	$('#media').click(function() {
    $('.media_cont').toggle('slow');
    return false;
  });

    $('.tabs .tab-links a').on('click', function(e)  {
        var currentAttrValue = $(this).attr('href');
 
        // Show/Hide Tabs
        $('.tabs ' + currentAttrValue).show().siblings().hide();
 
        // Change/remove current tab to active
        $(this).parent('li').addClass('active').siblings().removeClass('active');
 
        e.preventDefault();
    });



});

/**
 * Scroll to section
 * @param  string des HTML identity of section block
 * @return void
 */
function goToSectionID(des){
	var os = (history.pushState)?51:0;
	os = (jQuery(window).width()>800)?os:0;

	var pos = (jQuery(des).length>0 )?jQuery(des).offset().top-os:0;
	onanimate = true;
	jQuery('html,body').animate({scrollTop:pos},1000,function(){
		if(history.pushState){
			history.pushState(null,null,des);
		}else		window.location.hash = des;
		jQuery(window).scrollTop(pos);
		onanimate=false
	});
}

