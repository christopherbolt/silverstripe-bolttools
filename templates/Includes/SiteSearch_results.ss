<% if Results %>
<p><%t Website.SearchedFound 'You searched for <strong>{Query}</strong>. {TotalItems} results where found. Showing page {CurrentPage} of {TotalPages}' Query=$Query TotalItems=$Results.getTotalItems CurrentPage=$Results.CurrentPage TotalPages=$Results.TotalPages %></p>
<% else %>
<p><%t Website.SearchedNoResults 'You searched for <strong>{Query}</strong>, no matching pages were found.'  Query=$Query %></p>
<% end_if %>

<% if Results %>
  <ul id="SearchResults" class="contentList">
	<% loop Results %>
	  <li class="<% if First %>first<% end_if %> <% if Last %>last<% end_if %>">
		<h2><a class="searchResultHeader" href="$Link">$Title</a></h2>
		<p><% if $Content %>$Content.LimitWordCountNoHTML(41, "&nbsp;...")<% else_if $MetaDescription %>$MetaDescription.LimitWordCount(41, "&nbsp;...")<% else_if $SearchSynopsis %>$SearchSynopsis.LimitWordCountNoHTML(41, "&nbsp;...")<% end_if %></p>
        <p class="more"><a href="$Link"><%t Website.More 'More' %></a></p>
	  </li>
	<% end_loop %>
  </ul>
<% end_if %>

<% if Results.MoreThanOnePage %>
  <div id="PageNumbers">
	<% if Results.NotFirstPage %>
	  <a class="prev" href="$Results.PrevLink"><%t Website.Prev 'Previous' %></a>
	<% end_if %>
	<span>
	  <% loop Results.PaginationSummary(10) %>
		<% if CurrentBool %>
		  <span class="current">$PageNum</span>
		<% else %>
          <% if PageNum %>
		  <a href="$Link" title="View page number $PageNum">$PageNum</a>
          <% else %>
           ... 
          <% end_if %>
		<% end_if %>
	  <% end_loop %>
	</span>
	<% if Results.NotLastPage %>
	  <a class="next" href="$Results.NextLink"><%t Website.Next 'Next' %></a>
	<% end_if %>
  </div>
<% end_if %>