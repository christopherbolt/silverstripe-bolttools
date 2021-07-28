<!DOCTYPE html>
<html lang="$ContentLocale">
<head>
	<% base_tag %>
	<title><% if $MetaTitle %>$MetaTitle<% else %><% if $URLSegment = "home" || $ClassName = "HomePage" %>$SiteConfig.Title<% else %>$Title<% end_if %><% if $URLSegment != "home" && $ClassName != "HomePage" %> - $SiteConfig.Title<% end_if %><% end_if %></title>
	<meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="icon" href="{$BaseHref}favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="{$BaseHref}favicon.ico" type="image/x-icon">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
	<link rel="canonical" href="$AbsoluteLink"/>
	$MetaTags(false)
</head>

<body class="$SanitizedClassName url{$SanitizedURLSegment} id{$ID}">

	<header id="header">
		<div class="logo">
			<a href="$BaseHref" title="Return to $SiteConfig.Title home">
				$SiteConfig.Title
			</a>
		</div>
		<nav class="nav">
			<ul>
				<% loop $Menu(1) %>
				<li class="$LinkingMode"><a class="$LinkingMode" href="$Link" title="$Title.XML">$MenuTitle.XML</a></li>
				<% end_loop %>
			</ul>
		</nav>
	</header>

	<main class="layout">
		$Layout
	</main>

	<footer class="footer">

	</footer>

</body>
</html>
