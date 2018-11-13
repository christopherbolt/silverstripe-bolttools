// Adds placeholders to matched elements in a form
// Query must be form elements else you will get an error

(function ( $ ) {
 
    $.fn.addPlaceholders = function( options ) {
		
		// This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
            ignoreClass: "noPlaceholder",
			forceScripted: false
        
		}, options );
		
		// Input types that may be parsed as invalid values when converted to a placeholder
		var validatedInputTypes = /^(color|date|datetime|datetime-local|email|month|number|password|range|search|tel|time|url|week)$/i;
		
		// functions
		if (!$.addPlaceholders) { $.addPlaceholders = {}; }
		$.addPlaceholders.attributeSupported = function(element, attribute) {
			var test = document.createElement(element);
   			return (attribute in test);
		}

		// Label fields and forms with label fields
		var s = this;
		if (s.length) {
						
			// Check for placeholder support in browser.
			if ($.addPlaceholders.attributeSupported('input', 'placeholder') && !settings.forceScripted) {
				// Placeholders are supported!
				// add placeholder attribute to fields and then hide labels
				s.each(function() {
					var t = $(this);
					if (!t.attr('placeholder') && !t.hasClass(settings.ignoreClass)) {
						var label = $('label[for="'+t.attr('id')+'"]');
						if (label.length) {
							t.attr('placeholder', label.text());
							t.attr('title', label.text());
							label.hide();
						}
					}
				});
				
			} else {
				// No placeholder support so script it.
				// setup
				s.focus(function() {
					var t = $(this);
					if (!t.hasClass(settings.ignoreClass)) {
						if (t.val() == t.attr('placeholder')) {
							t.each(function(){this.value='';});
							t.removeClass('labelTextFieldInactive');
							if (t.hasClass('labelNonTextField')) {
								try{this.type = t.attr('data-labelrealtype')}catch(e){};	
							}
						}
					}
				});
				s.blur(function() {
					var t = $(this);
					if (!t.hasClass(settings.ignoreClass)) {
						if (t.val() == t.attr('placeholder') || t.val() == '') {
							//t.prop('value', t.attr('placeholder'))
							t.each(function(){this.value=t.attr('placeholder');});
							t.addClass('labelTextFieldInactive');
							if (t.hasClass('labelNonTextField')) {
								try{this.type = 'text'}catch(e){};	
							}
							t.parent().find('span.required').css('display', 'block');
						} else {
							t.parent().find('span.required').css('display', 'none');	
						}
					}
				});
				// init
				s.each(function() {
					var t = $(this);
					if (!t.hasClass(settings.ignoreClass)) {
						//if (t.val() == '') {
							var label = $('label[for="'+t.attr('id')+'"]')
							var placeholder = t.attr('placeholder') ? t.attr('placeholder') : label.text();
							
							if ($.addPlaceholders.attributeSupported(jQuery(this).prop('tagName'), 'type')) {
								var type = t.attr('type');
								if (validatedInputTypes.test(type)) {
									t.addClass('labelNonTextField');
									t.attr('data-labelrealtype', type);
									if (t.val() == '') try{this.type = 'text';}catch(e){};
								}
							}
							if (t.val() == '') {
								//t.prop('value', placeholder)
								t.each(function(){this.value=placeholder;});
								t.addClass('labelTextFieldInactive');
							}
							
							t.attr('placeholder', placeholder).addClass('labelTextField');
							t.attr('title', placeholder);
							label.hide();
						//}
						if (this.form) {
							$(this.form).submit(function() {
								$(this).find('.labelTextField').each(function() {
									if ($(this).val() == $(this).attr('placeholder')) this.value='';
								});
							});
						}
					}
					if (t.is(':focus')) {
						t.focus();
					}
				});
			}
		}
		
        return this;
    };
 
}( jQuery ));