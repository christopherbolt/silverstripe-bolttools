<% if $ShowInSiteMap = 1 %>
<li><a href="$Link">$MenuTitle</a>
		<% if SiteMapChildren %>
        <ul>
		<% loop SiteMapChildren %>
			<% include SiteMap_loop %>
		<% end_loop %>
        </ul>
        <% end_if %>
    </li>
    <% end_if %>