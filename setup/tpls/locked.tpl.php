<h2><?php echo $l['head']; ?></h2>
<div class="post">

<?php echo wpautop(sprintf(
			$l['descr'], 
			LOCKFILE, 
			BLOG_BASEURL, 
			BLOG_BASEURL . 'setup.php'
		)); ?>

</div>