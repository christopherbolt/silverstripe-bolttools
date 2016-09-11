// My attempt at a base framework for simple websites....
(function ( $ ) {
	BoltSite = function ( options ) {
		
		var SELF = this;
		
		// This is the easiest way to have default options.
		SELF.settings = $.extend({
			breakpoints : {
				mobile: 0,
				tablet: 768,
				desktop: 960,
				bigdesktop: 1090
			},
			touchBreakpoint : 'desktop',
			retinaBreakpoint : null,
			wrapTablesQuery : '.typography table',
			wrapTablesExcludeQuery : null,
			wrapTables: '<div class="tableWrapper"></div>',
			placholderFieldsQuery : 'input.text, textarea',
			inlineSvgQuery : 'img.svg',
			inlineSvgExcludeQuery : null,
			placholderFieldsExcludeQuery : '.attributeForm input.text, .attributeForm textarea, input[name=Captcha]'
		}, options );
		
		var onReadyFunctions = [];
		SELF.ready = function(f) {
			onReadyFunctions.push(f);
		}
		
		var onResizeFunctions = [];
		SELF.resize = function(f) {
			onResizeFunctions.push(f);
			if (SELF.readyDone) f();
		}
		
		SELF.breakpoint = function(n) {
			if (n == 'touch') {
				return SELF.settings.breakpoints[SELF.settings.touchBreakpoint];
			} else if (n == 'retina') {
				return SELF.settings.breakpoints[SELF.settings.retinaBreakpoint];
			} else {
				return SELF.settings.breakpoints[n];
			}
		}
		
		SELF.touchDevicesTest = function() {
			if(/KHTML|WebKit/i.test(navigator.userAgent) && ('ontouchstart' in window)) {
				return true;
			}else{
				return false;
			}
		}
		SELF.retinaDevicesTest = function() {
			return ((window.matchMedia && (window.matchMedia('only screen and (min-resolution: 124dpi), only screen and (min-resolution: 1.3dppx), only screen and (min-resolution: 48.8dpcm)').matches || window.matchMedia('only screen and (-webkit-min-device-pixel-ratio: 1.3), only screen and (-o-min-device-pixel-ratio: 2.6/2), only screen and (min--moz-device-pixel-ratio: 1.3), only screen and (min-device-pixel-ratio: 1.3)').matches)) || (window.devicePixelRatio && window.devicePixelRatio > 1.3));
		}
		
		SELF.isTouch = function() {
			return (SELF.touchDevicesTest() || (SELF.breakpoint('touch') && $(window).width() < SELF.breakpoint('touch')));
		}
		
		SELF.isRetina = function() {
			return (SELF.retinaDevicesTest() || (SELF.breakpoint('retina') && $(window).width() < SELF.breakpoint('retina')));
		}
		
		SELF.readyDone = false;
		
		$(document).ready(function() {		
			///////////////////////
			// Window size setup
			///////////////////////
			$(window).resize(function() {
				setupForWindowSize();
			});
			setupForWindowSize();
						
			///////////////////////
			// Everything else....
			///////////////////////
			
			// Placeholder forms?
			if (typeof(jQuery.fn.addPlaceholders) === "function") {
				jQuery(SELF.settings.placholderFieldsQuery).not(SELF.settings.placholderFieldsExcludeQuery).addPlaceholders({forceScripted:true});
			}
			// Focuspoint
			if (typeof(jQuery.fn.focusPoint) === "function") {
				$('.focuspoint').focusPoint({
					reCalcOnWindowResize: true
				});
			}
			// Wrap tables in wrapper for easy mobile scrolling
			if (SELF.settings.wrapTablesQuery && SELF.settings.wrapTables) $(SELF.settings.wrapTablesQuery).not(SELF.settings.wrapTablesExcludeQuery).wrap(SELF.settings.wrapTables);
			
			// Inine SVG so we can style it with CSS
			if (SELF.settings.inlineSvgQuery) $(SELF.settings.inlineSvgQuery).not(SELF.settings.inlineSvgExcludeQuery).inlineSVG();
			
			// READY
			for (var i=0;i<onReadyFunctions.length;i++) {
				onReadyFunctions[i]();
			}
			
			SELF.readyDone = true;
		});
		
		function setupForWindowSize () {
			// Detect touchscreens, we have the option to also assume that all small devices need touch, that's lazy though
			if (SELF.isTouch()) {
				$('body').addClass('touch');
				$('body').removeClass('no-touch');
			} else {
				$('body').removeClass('touch');
				$('body').addClass('no-touch');
			}
			// Detect retina screens, we have the option to also assume that all small devices need retina, that's lazy though
			if (SELF.isRetina()) {
				$('body').addClass('retina');
			} else {
				$('body').removeClass('retina');
			}
			
			// RESIZE
			for (var i=0;i<onResizeFunctions.length;i++) {
				onResizeFunctions[i]();
			}
		}
		
	}
	
	$.fn.inlineSVG = function() {
		/*
		* Replace SVG image with inline SVG
		*/
		this.each(function(){
			var $img = jQuery(this);
			var imgURL = $img.attr('src');
			var copy = ['id','class','width','height'];
			var attrs = {};
			for(var i=0;i<copy.length;i++) {
				attrs[copy[i]] = $img.attr(copy[i]);
			}
			jQuery.get(imgURL, function(data) {
				// Get the SVG tag, ignore the rest
				var $svg = jQuery(data).find('svg');
		
				// Add replaced image's attributes to the new SVG
				for(x in attrs) {
					$svg = $svg.attr(x, attrs[x]);
				}
		
				// Remove any invalid XML tags as per http://validator.w3.org
				$svg = $svg.removeAttr('xmlns:a');
		
				// Replace image with new SVG
				$img.replaceWith($svg);
		
			}, 'xml');
		});
	}
	
}( jQuery ));