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
			wrapTablesExcludeQuery : '.tableWrapper table',
			wrapTables: '<div class="tableWrapper"></div>',
			placholderFieldsQuery : 'input.text, textarea',
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
				jQuery(SELF.settings.placholderFieldsQuery).not(SELF.settings.placholderFieldsExcludeQuery).addPlaceholders();
			}
			// Focuspoint
			if (typeof(jQuery.fn.focusPoint) === "function") {
				$('.focuspoint').focusPoint({
					reCalcOnWindowResize: true
				});
			}
			// Wrap tables in wrapper for easy mobile scrolling
			if (SELF.settings.wrapTablesQuery && SELF.settings.wrapTables) $(SELF.settings.wrapTablesQuery).not(SELF.settings.wrapTablesExcludeQuery).wrap(SELF.settings.wrapTables);
						
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
		
		SELF.removeHoverOnTouch = function() {
			if (SELF.touchDevicesTest()) { // remove all :hover stylesheets
				try { // prevent exception on browsers not supporting DOM styleSheets properly
					for (var si in document.styleSheets) {
						var styleSheet = document.styleSheets[si];
						if (!styleSheet.rules) continue;
			
						for (var ri = styleSheet.rules.length - 1; ri >= 0; ri--) {
							if (!styleSheet.rules[ri].selectorText) continue;
			
							if (styleSheet.rules[ri].selectorText.match(':hover') && !styleSheet.rules[ri].selectorText.match('.dontTouchRemove')) {
								styleSheet.deleteRule(ri);
							}
						}
					}
				} catch (ex) {}
			}
		}
		
	}
	
}( jQuery ));