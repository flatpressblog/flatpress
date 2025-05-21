<?php

	$o = new fs_filelister('./setup/lang/');
	$languages = $o->getList();
	
?>
<h2><?php echo $l['head']; ?></h2>

<div class="post">

<input type="hidden" name="setupid" value="<?php echo $setupid; ?>" />

<?php 
	echo wpautop(sprintf($l['descr'], FP_CONTENT));
	

	if ($err)
		 echo wpautop(sprintf($l['descrw'], FP_CONTENT));
		 
?>
<div id="buttonbar">
	<input type="submit" name="start" id="start" 
	value="<?php echo $lang['buttonbar']['next']; ?>" />
</div>

</div>
