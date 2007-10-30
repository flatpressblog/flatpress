<h2><?php echo $l['head']; ?></h2>
<div class="errors">

<input type="hidden" name="setupid" value="<?php echo $setupid; ?>" />
<input type="hidden" name="language" value="<?php echo $language; ?>" />

<?php 
	if ($err) {
?>
<ul>
<?php
		foreach($err as $e) { 
?>
	<li> <?php echo $e; ?> </li>
<?php
	 	}
?>
</ul>
<?php
	}
?>

</div>


<div class="post">

<?php echo wpautop(sprintf($l['descr'], FP_CONTENT)); ?>

	<dl>
		
		<dt>
			<label for="username">	<?php echo $l['fpuser']; ?> 	</label>
		</dt>
		<dd>	
			<input type="text" id="username" name="fpuser" />
		</dd>
		
		<dt>
			<label for="password">	<?php echo $l['fppwd']; ?>	</label>
		</dt>
		<dd>
			<input type="password" id="password" name="fppwd" />	
		</dd>
		
		<dt>
			<label for="password2">	<?php echo $l['fppwd2']; ?>	</label>
		</dt>
	
		<dd>
			<input type="password" id="password2" name="fppwd2" />	
		</dd>
		
		<dt>
			<label for="www">	<?php echo $l['www']; ?>	</label>
		</dt>
		<dd>
			<input type="text" id="www" name="www" value="<?php echo BLOG_BASEURL ; ?>" />	
		</dd>
		
		<dt>
			<label for="email">	<?php echo $l['email']; ?>	</label>
		</dt>
		<dd>
			<input type="text" id="email" name="email" />	
		</dd>
		
	</dl>

<div id="buttonbar">
	<input type="submit" name="start" id="start" 
	value="<?php echo $lang['buttonbar']['next']; ?>" />
</div>

</div>