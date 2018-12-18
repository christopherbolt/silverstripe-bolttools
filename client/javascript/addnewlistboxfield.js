 // JavaScript Document

jQuery.entwine("addnewlistboxfield", function($) {

	var _match = function () {
		var self = this;
		
		if (self.hasClass('addnewlistboxfield-button-edit')) {
			this.setEdit(true);
			var selectElement = self.siblings('select:first');
			var val = selectElement.chosen().val();
			if (val && /^[0-9]+$/.test(val)) {
				this.show();
			} else {
				this.hide();
			}
			selectElement.chosen().change(function() {
				var val = $(this).val();
				if (val && /^[0-9]+$/.test(val)) {
					self.show();
				} else {
					self.hide();
				}
			});
		}
		
		if (this.getEdit()) {
			this.setDialog(self.siblings('.addnewlistboxfield-edit-dialog:first'));
		} else {
			this.setDialog(self.siblings('.addnewlistboxfield-dialog:first'));
		}
		this.setFieldName(self.parents('div.field:first').find('select').attr('name').replace(/\[\]/,''));
	
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
					var fa = self.parents('form').attr('action').split('?');
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
	}
		
	var _click = function() {
		var fa = this.parents('form').attr('action').split('?');
		var url = fa[0] + '/field/' + this.getFieldName() + '/AddNewListboxFormHTML';	
		if (this.getEdit()) {
			var id = this.siblings('select:first').chosen().val();
			if (id && /^[0-9]+$/.test(id)) {
				url += (fa[1] ? '?' + fa[1] + '&' : '?') + 'ItemID=' + id;
			}
		} else {
			url += (fa[1] ? '?'+fa[1] : '');
		}
		
		this.showDialog(url);
		return false;
	}
	
  	var _showDialog = function(url) {
		var dlg = this.getDialog();

		dlg.empty().dialog("open").parent().addClass("loading");

		dlg.load(url, function(){
			dlg.parent().removeClass("loading");
		});
	}
	
	$(".addnewlistboxfield-button-edit").entwine({
		Loading: null,
		Dialog:  null,
		FieldName: null,
		Edit: false,
		onmatch: _match,
		onclick: _click,
		showDialog: _showDialog
	});

	
	$(".addnewlistboxfield-button-new").entwine({
		Loading: null,
		Dialog:  null,
		FieldName: null,
		Edit: false,
		onmatch: _match,
		onclick: _click,
		showDialog: _showDialog
	});
	
	
});


