<?php

require("cta.class.php");
new CTA;

$_ = CTA::get_eta();

//print_r($_);

?><div id="eta_<?=sha1($_GET['route'].$_GET['direction'].$_GET['stop'].$_GET['id'])?>" title="<?=$_['title']?>" class="panel">

	<h2>Currently</h2>
	
	<fieldset>
	
		<div class="row">
			<label><?=str_replace('°', "&deg;", $_['currently'])?></label>
		</div>
	
	</fieldset>

<?php if(is_array($_['etas'])) { ?>

	<h2>Bus Arrival Times</h2>
	
	<fieldset>
		
<?php foreach ($_['etas'] as $item) { ?>

		<div class="row" clickable="yes"><label><?=$item['bus']?> <?=$item['to']?></label> <label style="position:absolute;right:15px;font-weight:normal;"><?=$item['time']?></label></div>

<?php } ?>
		
	</fieldset>

<?php } else { ?>
	
	<p>No arrival times are available at this time. Check back soon.</p>
	
<?php } ?>

<?php if($_['closest']) { ?>
	<a class="whiteButton" href="/app/show_map.php?route=<?=$_['route']?>&bus=<?=$_['closest']?>">View Closest Bus</a>
<?php } ?>	
	
	<br /><br /><br /><br />
	
</div>