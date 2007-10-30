<?php

	$o =& new fs_filelister('./setup/lang/');
	$languages = $o->getList();
	
?>
<h2><?php echo $l['head']; ?></h2>

<div class="post">

<input type="hidden" name="setupid" value="<?php echo $setupid; ?>" />

<?php 
	echo wpautop(sprintf($l['descr'], FP_CONTENT));
	
	/*
	if ($languages){
	echo '<p><label> ' . $l['descrl1'] .' <select name="language">';
		foreach($languages as $lg) {
			$match = array();
			
			if (preg_match('|lang\.(.*)\.php|', $lg, $match)) {
				$lid = $match[1];
				$fc  = io_load_file("./setup/lang/$lg");
				
				$lv = $lid;
				if (preg_match('|LangId:(.*)|i', $fc, $match))
					$lv = $match[1];
				
				echo "<option value=\"$lid\"" . 
					((isset($_POST['language']) && $_POST['language'] == $lg)?
						' selected="selected"' : '')
					 . ">$lv</option>";
			}
		}
	echo '</select> </label></p>';
	
	echo '<p>' .$l['descrl2'] . '</p>';
	
	}
	
	
	echo '<div id="more-lang-info" class="info-notice">';
	echo wpautop(sprintf($l['descrlang'], FP_CONTENT));
	echo '</div>';
	
	*/
	
	if ($err)
		 echo wpautop(sprintf($l['descrw'], FP_CONTENT));
		 
?>
<!-- script type="text/javascript">
function toggleinfo() {
	var disp = document.getElementById('more-lang-info').style;
	if (disp.display.indexOf('block') != -1)
		disp.display = 'none';
	else
		disp.display = 'block';
}

document.getElementById('more-lang-info').style.display = 'none';

</script -->
<div id="buttonbar">
	<input type="submit" name="start" id="start" 
	value="<?php echo $lang['buttonbar']['next']; ?>" />
</div>

</div>