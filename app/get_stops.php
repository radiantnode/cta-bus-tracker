<?php

require("cta.class.php");
new CTA;

$stops = json_decode(CTA::get_stops());

?><ul id="stops_" title="Stops">
<?php if(is_array($stops->items)) { foreach($stops->items as $item) { ?>
		<li><a href="/app/get_eta.php?route=<?=$item->route_id ?>&direction=<?=$item->direction ?>&stop=<?=$item->stop?>&id=<?=$item->id ?>"><?=$item->name; ?></a></li>
<?php } } ?>

</ul>