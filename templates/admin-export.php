<?php echo '<?xml version="1.0" encoding="UTF-8" ?>' ?>

<catablog_items date="<?php echo date('Y-m-d') ?>" time="<?php echo date('H:i:s') ?>">

<?php foreach ($results as $result): ?>
	<item>
		<order><?php echo $result->getOrder() ?></order>
		<image><![CDATA[<?php echo $result->getImage() ?>]]></image>
		<subimages>
<?php foreach ($result->getSubImages() as $image): ?>
			<image><![CDATA[<?php echo $image ?>]]></image>
<?php endforeach ?>
		</subimages>
		<title><![CDATA[<?php echo $result->getTitle() ?>]]></title>
		<link><![CDATA[<?php echo $result->getLink() ?>]]></link>
		<description><![CDATA[<?php echo $result->getDescription() ?>]]></description>
		<categories>
<?php foreach ($result->getCategories() as $cat_id => $cat_name): ?>
			<category><![CDATA[<?php echo $cat_name ?>]]></category>
<?php endforeach ?>
		</categories>
		<price><![CDATA[<?php echo $result->getPrice() ?>]]></price>
		<product_code><![CDATA[<?php echo $result->getProductCode() ?>]]></product_code>
		<quantity><![CDATA[<?php echo $result->getQuantity() ?>]]></quantity>
		<size><![CDATA[<?php echo $result->getSize() ?>]]></size>
		<prices><![CDATA[<?php echo $result->getPrices() ?>]]></prices>
	</item>
<?php endforeach ?>

</catablog_items>
