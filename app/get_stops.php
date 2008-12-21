<?php

require("cta.class.php");
new CTA;

$_ = CTA::get_stops();

?><ul id="stops_<?=sha1($_['route'])?>" title="Stops">

<?php if(is_array($_['stops'])) { foreach($_['stops'] as $item) { ?>
		<li><a href="/app/get_eta.php?route=<?=$item['uri']['route']?>&direction=<?=$item['uri']['direction']?>&stop=<?=$item['uri']['stop']?>&id=<?=$item['uri']['id']?>"><?=$item['title']?></a></li>
<?php } } ?>

</ul>