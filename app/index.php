<?php

require("cta.class.php");
new CTA;

$routes = CTA::index();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
         "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>CTA Bus Tracker</title>

<link rel="apple-touch-icon" href="/icon.png" /> 


<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<style type="text/css" media="screen">@import "/app/iui/iui.css";</style>
<script type="application/x-javascript" src="/app/iui/iui.js"></script>
</head>

<body>
    <div class="toolbar">
        <h1 id="pageTitle"></h1>
        <a id="backButton" class="button" href="#"></a>
    </div>

    <ul id="routes" title="CTA Routes" selected="true">
	
<?php if(is_array($routes)) { foreach($routes as $item) { ?>
		<li><a href="/app/get_direction.php?route=<?=$item['id']?>"><?=$item['title']?></a></li>
<?php } } ?>	

    </ul>

</body>
</html>