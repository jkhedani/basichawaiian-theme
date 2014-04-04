jQuery(document).ready(function($){
	/**
	 *	Chart.js
	 *	http://www.chartjs.org/docs/
	 *	Note: Be sure to enable IE support via explorerCanvas
	 *	https://code.google.com/p/explorercanvas/
	 */

	 // Only run on statistics page...
	 if ( $('body').hasClass('page-template-statistics-php') ) {
	 	// Testing chart data... 
		var myFirstChartData = [
			{ value : studentsCompletedModuleOne, color: "#949FB1" },
			{ value : studentsCompletedModuleThree, color: "#749FB1" },
			{ value : studentsCompletedModuleSix, color: "#947FC1" },
			{ value : studentsCompletedModuleEight, color: "#545FC1" },
			{ value : activeStudents, color: "#4D5360" },
			{ value : studentsNotParticipating, color: "#F7464A" },
			
			// { value : 20, color: "#E2EAE9" },
			// { value : 50, color: "#D4CCC5" },

		];
		var ctx = $("#canvas-chart").get(0).getContext("2d");
		var myFirstChart 		 = new Chart(ctx).Doughnut( myFirstChartData );
	}

	/**
	 *	Coverflow
	 *	By Justin Hedani
	 */
	$.fn.coverflow = function ( options ) {

		// INIT & SETTINGS
		var coverflow = this;
		// Prevent horizontal scrolling on page.
		$('html, body').css('overflow-x','hidden');
		$('html, body').css('overflow-y','hidden');
		// Find the width and margins of all items in the coverflow
		var flowwidth = 0;
		var flowslidemargin = 0; // Allows us define "true" position for use laster
		coverflow.find('li').each( function(){
			var flowitemwidth = $(this).width();
			var flowitemmargin = parseInt($(this).css('marginLeft'));
			flowslidemargin = flowitemmargin;
			flowwidth = flowwidth + flowitemwidth + flowitemmargin;
		});
		
		// FUNCTIONS
		// Apply total width to list wrapper
		coverflow.find('ul').width( flowwidth );

		// Scroll to active
		var activeSlidePosition = coverflow.find('ul li.active').position();
		var activeSlideTruePosition = activeSlidePosition.left + flowslidemargin; // Don't forget to compensate for margins
		function scrolltoactive( scrollLocation ) {
			coverflow.find('ul').animate({
				marginLeft: -scrollLocation,
			}, 500);
		}
		scrolltoactive( activeSlideTruePosition );

		// EVENTS
		$(document).on('click', '.coverflow-controls a', function() {
			// Determine direction in which to flow to
			var flowdirection = $(this).data('slide-to');

			if ( flowdirection == 'next' ) {

				var nextitemposition = $(this).parents('.coverflow').find('ul li.active').next().position();
				var nextitemmargin = parseInt( $(this).parents('.coverflow').find('ul li.active').next().css('marginLeft') );
				var nextscrollposition = nextitemposition.left + nextitemmargin;

				// Remove active class from current slide
				coverflow.find('ul li.active').removeClass('active').next().addClass('active');
				// Remove active class from current counter
				coverflow.find('.coverflow-counter-container div.active').removeClass('active').next().addClass('active');

				// Find the width and margin of the 'next' slide and translate list left by this amount
				scrolltoactive( nextscrollposition );

			} else if ( flowdirection == 'prev' ) {

				var previtemposition = $(this).parents('.coverflow').find('ul li.active').prev().position();
				var previtemmargin = parseInt( $(this).parents('.coverflow').find('ul li.active').prev().css('marginLeft') );
				var prevscrollposition = previtemposition.left + previtemmargin;

				// Remove active class from current slide
				coverflow.find('ul li.active').removeClass('active').prev().addClass('active');
				// Remove active class from current counter
				coverflow.find('.coverflow-counter-container div.active').removeClass('active').prev().addClass('active');

				// Find the width and margin of the 'next' slide and translate list left by this amount
				scrolltoactive( prevscrollposition );

			}
			event.preventDefault();
		});
	}
	if ( $('body').hasClass('logged-in') && $('body').hasClass('home') ) {
		$('.coverflow').coverflow();
	}

	/**
	 *	Menu Drawer
	 */
	$.fn.drawer = function( options ) {
		
		// INIT
		var drawer = this; // define drawer
		var drawerWidth = drawer.width(); // Calculate the width of the drawer
    var settings = $.extend({
      position: 'right', // Define the drawer position
    }, options );
    // Prevent horizontal scrolling on page.
		$('html, body').css('overflow-x','hidden');
		//$('html, body').css('overflow-y','hidden');

		// FUNCTIONS
		// Move drawer's position equal to the inverse value of its width in the direction of the specified position.
		drawer.css( settings.position, -drawerWidth );

		// EVENTS
		// Open Drawer
		// Passing variables to animate();
		// http://stackoverflow.com/questions/19358423/passing-direction-as-a-variable-to-jquery-animate-function
		var pageAnimateOptions = {};
		var paddingDirection = 'padding-' + settings.position;
		pageAnimateOptions[paddingDirection] = drawerWidth + 'px'; // Set the drawer open width to the size of the drawer
		var drawerAnimateOptions = {};
		var slideDirection = settings.position;
		drawerAnimateOptions[slideDirection] = '0px';
		$(document).on('click', 'a[data-toggle="drawer"].drawer-closed', function() {
			// Animate page padding to "open drawer"
			$('html').animate( pageAnimateOptions, 500, function() {
				$('a[data-toggle="drawer"]').removeClass('drawer-closed').addClass('drawer-open');
				pageAnimateOptions[paddingDirection] = '0px'; // After opening, reset padding to 0
			});
			// Animate drawer sliding out
			drawer.animate( drawerAnimateOptions, 500, function() {
				drawerAnimateOptions[slideDirection] = -drawerWidth + 'px'; // Set the drawer position back
			});
			event.preventDefault();
		});
		// Close Drawer
		$(document).on('click', 'a[data-toggle="drawer"].drawer-open', function() {
			// Animate page padding to "open drawer"
			$('html').animate( pageAnimateOptions, 500, function() {
				$('a[data-toggle="drawer"]').removeClass('drawer-open').addClass('drawer-closed');
				pageAnimateOptions[paddingDirection] = drawerWidth + 'px'; // Set the drawer open width to the size of the drawer
			});
			// Animate drawer sliding out
			drawer.animate( drawerAnimateOptions, 500, function() {
				drawerAnimateOptions[slideDirection] = '0px'; // Set the drawer position back
			});
			event.preventDefault();
		});

	}
	$('.drawer').drawer();

	/**
	 *	Close Alert
	 */
	$('a[data-toggle="close-alert"]').on('click', function() {
		var alert = $(this).attr('href');
		// Gently close the alert
		$(alert).toggle(500);
		event.preventDefault();
	});

});

