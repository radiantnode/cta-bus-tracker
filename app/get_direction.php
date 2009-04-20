<?php

require("cta.class.php");
new CTA;
$directions = json_decode(CTA::get_direction());
print_r($directions);
?><ul id="route_<?= sha1($directions->route_id); ?>" title="Route <?= $directions->route_id ?>">

<?php if(is_array($directions->items)) { foreach($directions->items as $item) { ?>
		<li><a href="/app/get_stops.php?route=<?= $item->route_id ?>&direction=<?=$item->slug?>"><?=$item->name ?></a></li>
<?php } } ?>

</ul>