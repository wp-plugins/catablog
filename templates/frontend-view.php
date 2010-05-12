<?php foreach ($results as $result): ?>
	<div class='catablog_row'>
		<img src="<?php echo $this->urls['thumbnails'].'/'.$result->image ?>" class="catablog_image" width="<?php echo $size ?>" height="<?php echo $size ?>" title="<?php echo $result->title ?>" alt="" />
		<h4 class='catablog_title' style='margin-left:<?php echo $ml ?>'>
			<?php if (mb_strlen($result->link) > 0): ?>
				<a href="<?php echo $result->link ?>"><?php echo htmlspecialchars($result->title, ENT_QUOTES, 'UTF-8') ?></a>
			<?php else: ?>
				<?php echo htmlspecialchars($result->title, ENT_QUOTES, 'UTF-8') ?>
			<?php endif ?>
		</h4>
		
		<p class='catablog_description' style='margin-left:<?php echo $ml ?>'>
			<?php echo nl2br(htmlspecialchars($result->description, ENT_QUOTES, 'UTF-8')) ?>
		</p>
	</div>
<?php endforeach ?>