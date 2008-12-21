<?php

require("cta.class.php");
new CTA;

$_ = CTA::get_direction();

?><ul id="route_<?=sha1($_['route'])?>" title="Route <?=$_['route']?>">

<?php if(is_array($_['directions'])) { foreach($_['directions'] as $item) { ?>
		<li><a href="/app/get_stops.php?route=<?=$item['route']?>&direction=<?=$item['slug']?>"><?=$item['title']?></a></li>
<?php } } ?>

</ul>