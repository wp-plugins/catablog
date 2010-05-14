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
		
		<?php if (mb_strlen($this->options['paypal-email']) > 0 && $result->price > 0): ?>
			<form method='post' action='https://www.paypal.com/cgi-bin/webscr' target='paypal'>
				<input type='hidden' name='cmd' value='_cart'>
				<input type='hidden' name='business' value='<?php echo htmlspecialchars($this->options['paypal-email'], ENT_QUOTES, 'UTF-8') ?>'>
				<input type='hidden' name='item_name' value='<?php echo htmlspecialchars($result->title, ENT_QUOTES, 'UTF-8') ?>'>
				<input type='hidden' name='item_number' value='<?php echo htmlspecialchars($result->product_code, ENT_QUOTES, 'UTF-8') ?>'>
				<input type='hidden' name='amount' value='<?php echo htmlspecialchars($result->price, ENT_QUOTES, 'UTF-8') ?>'>

				<input type='hidden' name='shipping' value='0.00'>
				<input type='hidden' name='currency_code' value='USD'>
				<input type='hidden' name='return' value=''>
				<input type='hidden' name='quantity' value='1'>
				<input type='hidden' name='add' value='1'>
				<input type='image' src='http://images.paypal.com/en_US/i/btn/x-click-but22.gif' border='0' name='submit' width='87' height='23' alt='Add to Cart'>
			</form>
			
		<?php endif ?>
	</div>
<?php endforeach ?>