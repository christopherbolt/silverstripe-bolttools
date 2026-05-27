// JavaScript Document
(function ( $ ) {
	App = new BoltSite({
		breakpoints : {
			xxsmall: 350,
            xsmall: 500,
            small: 767,
            medium: 920,
            large: 1400,
            xlarge: 1760,
            xxlarge: 2560,
		}
	});
	App.ready(function() {
		App.resize(function() {
			setupForWindowSize();
		});
		setupForWindowSize();
	});
	
	function setupForWindowSize () {
		var windowWidth = $(window).width();
		var windowHeight = $(window).height();
	}
}( jQuery ));