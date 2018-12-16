<select $AttributesHTML data-option-values="<% loop $Options %>$Value.XML,<% end_loop %>">
<% loop $Options %>
	<option value="$Value.XML"<% if $Selected %> selected="selected"<% end_if %><% if $Disabled %> disabled="disabled"<% end_if %>>$Title.XML</option>
<% end_loop %>
</select>
<% if $Model %>
<button href='#' class='addnewlistboxfield-button-new action action-detail btn btn-primary font-icon-plus-circled new new-link'>New</button>
	<% if $ShowEditButton %>
    <button href='#' class='addnewlistboxfield-button-edit ss-ui-button ss-ui-button-small'>Edit Selected</button>
    <div class='addnewlistboxfield-edit-dialog'></div>
	<% end_if %>
<% end_if %>
<div class='addnewlistboxfield-dialog'></div>