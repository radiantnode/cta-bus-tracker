<?php

require("cta.class.php");
new CTA;

$_ = CTA::build_map_for_route($_GET['route'], $_GET['bus']);

?><div id="map_<?=sha1($_GET['route'].$_GET['bus'])?>" title="Map" class="panel">
	
<?php if($_['map_uri']) { ?>
	<div style="background: #f60; background: url(/iui/map_load.gif) no-repeat 50% 50%; width: 302px; height: 352px;"><div style="-webkit-border-radius: 10px; border: 1px solid #b4b4b4; background: url('<?=$_['map_uri']?>'); width: 300px; height: 350px;"></div></div>

<?php if(is_array($_['plot'])) { ?>
	<br />
	<a class="whiteButton" href="http://maps.google.com/maps?f=q&hl=en&geocode=&q=<?=$_['plot']['latitude'].",".$_['plot']['longitude']?>">Plot on Maps</a>
	<br /><br />
<?php } ?>

<?php } else { ?>
	
	No map available.
	
<?php } ?>
	
</div>