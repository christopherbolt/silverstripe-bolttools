<select $AttributesHTML data-option-values="<% loop $Options %>$Value.XML,<% end_loop %>">
<% loop $Options %>
	<option value="$Value.XML"<% if $Selected %> selected="selected"<% end_if %><% if $Disabled %> disabled="disabled"<% end_if %>>$Title.XML</option>
<% end_loop %>
</select>
<% if $Model %>
<button href='#' class='addnewlistboxfield-button ss-ui-button ss-ui-button-small ss-ui-action-constructive' data-icon="add">&nbsp;&nbsp;New</button>
<% end_if %>
<div class='addnewlistboxfield-dialog'></div>