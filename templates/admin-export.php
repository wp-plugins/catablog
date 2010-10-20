<?php echo '<?xml version="1.0" encoding="UTF-8" ?>' ?>

<catablog_items date="<?php echo date('Y-m-d') ?>" time="<?php echo date('H:i:s') ?>">
	<?php foreach ($results as $result): ?>
	
	<item>
		<id><?php echo $result->id ?></id>
		<ordinal><?php echo $result->ordinal ?></ordinal>
		<image><![CDATA[<?php echo $result->image ?>]]></image>
		<title><![CDATA[<?php echo $result->title ?>]]></title>
		<link><![CDATA[<?php echo $result->link ?>]]></link>
		<description><![CDATA[<?php echo $result->description ?>]]></description>
		<tags><![CDATA[<?php echo $result->tags ?>]]></tags>
		<price><![CDATA[<?php echo $result->price ?>]]></price>
		<product_code><![CDATA[<?php echo $result->product_code ?>]]></product_code>
	</item>
	<?php endforeach ?>

</catablog_items>
