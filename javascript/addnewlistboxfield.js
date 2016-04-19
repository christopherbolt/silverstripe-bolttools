 // JavaScript Document

jQuery.entwine("addnewlistboxfield", function($) {

	$(".addnewlistboxfield-button").entwine({
		Loading: null,
		Dialog:  null,
		URL:  null,
		FieldName: null,
		onmatch: function() {
			var self = this;
			
			// make sure that select has chosen
			if (self.siblings('select:first')) {
				self.siblings('select:first').hide();
				self.siblings('select:first').chosen({disable_search_threshold: 10});
				
			}
			
			this.setDialog(self.siblings('.addnewlistboxfield-dialog:first'));
			// Chris Bolt, modified to cope with the translatable query
			//var url = this.parents('form').attr('action') + '/field/' + this.attr('name') + '/LinkFormHTML';
			this.setFieldName(self.parents('div.field:first').find('select').attr('name').replace(/\[\]/,''));
			var fa = this.parents('form').attr('action').split('?');
			var url = fa[0] + '/field/' + this.getFieldName() + '/AddNewListboxFormHTML' + (fa[1] ? '?'+fa[1] : '');
			// End Chris Bolt
			
			this.setURL(url);

			// configure the dialog
			var windowHeight = $(window).height();

			this.getDialog().data("field", this).dialog({
				autoOpen: 	false,
				width:   	$(window).width()  * 80 / 100,
				height:   	$(window).height() * 80 / 100,
				modal:    	true,
				title: 		this.data('dialog-title'),
				position: 	{ my: "center", at: "center", of: window }
			});

			// submit button loading state while form is submitting 
			this.getDialog().on("click", "button", function() {
				$(this).addClass("loading ui-state-disabled");
			});

			// handle dialog form submission
			this.getDialog().on("submit", "form", function() {
				
				var dlg = self.getDialog().dialog(),
				options = {};

				options.success = function(response) {
					if($(response).is(".field")) {
						// get updated field
						var selectElement = self.siblings('select:first');
						var url = fa[0] + '/field/' + self.getFieldName() + '/AddNewFieldHolderHTML' + (fa[1] ? '?'+fa[1] : '');
						$.get(url, { "selected": selectElement.chosen().val() }, function(response, status) {
							self.getDialog().empty().dialog("close");
							// We need to find and select this new item, go through the existing list and record each item, then go through the new list and select anything that is new
							var responseObj = $(response);
							var optionValues = selectElement.data('option-values').split(',');
							selectElement.data('option-values', responseObj.find('select:first').data('option-values'));
							//alert(selectElement.data('option-values'));
							responseObj.find('select:first option').each(function(){ 
								if ($.inArray($(this).attr('value'), optionValues) == -1) {
									$(this).attr('selected', true);
									// move into live list
									if ($(this).prev().length) {
										$(this).insertAfter(selectElement.find('option[value="'+$(this).prev().attr('value')+'"]'));
									} else {
										selectElement.prepend($(this));
									}
								}
							});
							selectElement.trigger("liszt:updated");// Legacy, SS is still using an old version of chosen?
							selectElement.trigger("chosen:updated");// New chosen API ready for when they update
							selectElement.trigger("change");
							//self.parents('div.field:first').replaceWith(responseObj);
							//$('#'+id).find('select:first').addClass('changed');
							//$('#'+id).find('select:first').change();// trigger form changed to update save buttons etc
							
						});
					} else {
						self.getDialog().html(response);
					}
				}

				$(this).ajaxSubmit(options);

				return false;
			});
		},
		
		onclick: function() {
			this.showDialog();
			return false;
		},

		showDialog: function(url) {
			var dlg = this.getDialog();

			dlg.empty().dialog("open").parent().addClass("loading");

			dlg.load(this.getURL(), function(){
				dlg.parent().removeClass("loading");
			});
		}
	});

});


