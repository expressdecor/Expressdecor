<?php

//header("Content-type: text/csv");
//header("Cache-Control: no-store, no-cache");
//header('Content-Disposition: attachment; filename="filename.csv"');


ini_set ( "memory_limit", "2048M" );
set_time_limit ( 0 );

require_once 'app/Mage.php';
Mage::app ( 'default' );

//$ManufacturerId=150;
//$ManufacturerName="Kraus";


$products = Mage::getModel ( 'catalog/product' )->getCollection();
$products->addAttributeToFilter ( 'status', 1 ); // enabled
//$products->addAttributeToFilter('visibility', 4); // catalog, search
//$products->addAttributeToFilter('manufacturer', $ManufacturerId); // Kraus products only
//$products->addAttributeToFilter ( 'sku', 'GV-101-19mm_Base' ); // Kraus products only


$products->addAttributeToSelect ( '*' );
//$prodIds = $products->getAllIds();

/* for products prices*/

$websiteId = Mage::app()->getStore( $storeId )->getWebsiteId();
$dateTs = Mage::app()->getLocale()->storeTimeStamp ( $storeId );

/*columns*/
$arr_columns = array ('id', 'title', 'description', 'google product category', 'product type', 'link', 'image link', 'additional image link', 'condition', 'availability', 'price', 'brand', 'gtin', 'mpn', 'color', 'material', 'shipping' );
$arr_data = array();

foreach ( $products as $prod ) {
	
	/*Base fields*/
	$storeId = $prod->getStoreId();
	$productId=$prod->getId();
	$manufacturerName = $prod->getResource()->getAttribute ( 'manufacturer' )->getFrontend()->getValue ( $prod );
	$productname=$prod->getName();
	$prod_mod=Mage::getModel('catalog/product')->load($productId);
	$description=$prod_mod->getDescription();
	$newurl=$prod->getProductUrl();	 
	$sku=$prod->getSku();
	$upc_code=$prod_mod->getUpcCode();
	
	/*Prices block*/
	$price = $prod->getPrice();
	$_finalPrice = 0;
	$resource = Mage::getResourceModel ( 'catalogrule/rule' );

	$rules = $resource->getRulesForProduct ( $dateTs, $websiteId, $prod->getId() );
	if (count( $rules ) > 0) {
		$_finalPrice = $rules [0] ['rule_price'];
	}

	if ($_finalPrice > 0 && $price > $_finalPrice)
		$price = $_finalPrice;
	
	/* Custom shipping price*/
	$custom_ship_price = $prod->getResource()->getAttribute ( 'custom_ship_price' )->getFrontend()->getValue ( $prod );
	if (! $custom_ship_price) {
		if ($price > 100) {
			$bullet2 = ' Free Shipping in Continental US.';
			$shipPrice = 0;
		} else {
			$bullet2 = ' Free Shipping On Orders Over $100.';
			$shipPrice = 15;
		}
	} else {
		$bullet2 = '';
		$shipPrice = $custom_ship_price;
	}
	/*Product images*/
	$i = 0;
	foreach ( $prod_mod->getMediaGalleryImages() as $image ) {
		if ($i == 0)
			$image1 = $image->getData ( 'url' );
		if ($i == 1) {
			$image2 = $image->getData ( 'url' );
			break;
		}
		$i ++;
	}
		
	/*For simple products get main product id*/
	$configurable_product_model = Mage::getModel( 'catalog/product_type_configurable' );
	$parentId = $configurable_product_model->getParentIdsByChild( $productId );
	
	if (isset ( $parentId [0] )) {
		//if this product has a parent
		$parent = Mage::getModel ( 'catalog/product' )->load ( $parentId [0] );
		$newurl = $parent->getProductUrl();
	}
	/* end section for conf products*/
	$stockDescr = '30-Day Money Back Guarantee';
	
	$cat_type = getMainCat ( $prod );
	
	$arr_data_row = array (
	 $productId,
	 $productname,
	 $description,
	 $cat_type [1],
	 $cat_type [0],
	 $newurl,
	 $image1,
	 $image2,
	 "New",
	 "In Stock",
	 $price,
	 $manufacturerName,
	 $upc_code,
	 $sku,
	 getColor ( $prod_mod ), 
	 getMaterial ( $prod_mod ),
	 $shipPrice );
	/*push data to  array */
	array_push ( $arr_data, $arr_data_row );	
	 
}

exportCSV ( $arr_data, $arr_columns );

echo "Finished.";
function exportCSV($data, $col_headers = array(), $return_string = false) {
//	$stream = ($return_string) ? fopen ( 'php://temp/maxmemory', 'w+' ) : fopen ( 'php://output', 'w' );
	  /*to file*/
	  $myFile = "g.tsv";
	  $fh = fopen($myFile, 'w+') or die("can't open file");
	  
	if (! empty ( $col_headers )) {
//		fputcsv ( $stream, $col_headers );
	    /*to file*/
		fputcsv($fh, $col_headers);
	}
	foreach ( $data as $record ) {
//		fputcsv ( $stream, $record );
		/*to file*/
		 fputcsv($fh, $record);
	}
	if ($return_string) {
//		rewind ( $stream );
//		$retVal = stream_get_contents ( $stream );
		/*to file*/
		fclose($fh);
		
//		fclose ( $stream );
		return $retVal;
	} else {
		/*to file*/
		fclose($fh);
//		fclose ( $stream );
	}
}
function getMaterial($product) {
	$attributeSetModel = Mage::getModel ( "eav/entity_attribute_set" );
	$attributeSetModel->load ( $product->getAttributeSetId() );
	$att_set = $attributeSetModel->getAttributeSetName();
	$material = $product->getAttributeText ( 'material_product' );
	return $material;
}

function getColor($product) {
	$attributeSetModel = Mage::getModel ( "eav/entity_attribute_set" );
	$attributeSetModel->load ( $product->getAttributeSetId() );
	$att_set = $attributeSetModel->getAttributeSetName();
	
	$color = $material = $product->getAttributeText ( 'color_product' );
	return $color;
}

function getMainCat($product) {
	$attributeSetModel = Mage::getModel ( "eav/entity_attribute_set" );
	$attributeSetModel->load ( $product->getAttributeSetId() );
	$catname = $attributeSetModel->getAttributeSetName();
	$prod_type = $catname;
	if (($catname == 'Bathroom Sinks') or ($catname == 'Bathroom Combos')) {
		if ($catname == 'Bathroom Combos') {
			$prod_type = 'Bathroom Sink and Faucet Combo';
		}
		if ($catname == 'Bathroom Sinks') {
			$prod_type = 'Bathroom Sink';
		}
		$catname = '"Hardware > Plumbing > Plumbing Fixtures > Sinks >Bathroom Sinks"';
	
	}
	
	if (($catname == 'Kitchen Sinks') or ($catname == 'Kitchen Combos')) {
		if ($catname == 'Kitchen Combos') {
			$prod_type = 'Kitchen Sink and Faucet Combo';
		}
		if ($catname == 'Kitchen Sinks') {
			$prod_type = 'Kitchen Sink';
		}
		$catname = '"Hardware > Plumbing > Plumbing Fixtures > Sinks >Kitchen Sinks"';
	}
	
	if (($catname == 'Bathroom Faucets') or ($catname == 'shower_and_bathtub_faucets') or ($catname == 'Kitchen Faucets')) {
		if ($catname == 'shower_and_bathtub_faucets') {
			$prod_type = 'Shower Bathtub Faucet';
		}
		if ($catname == 'Kitchen Faucets') {
			$prod_type = 'Kitchen Faucet';
		}
		
		if ($catname == 'Bathroom Faucets') {
			$prod_type = 'Bathroom Faucet';
		}
		
		$catname = '"Hardware > Plumbing > Plumbing Fixtures > Faucets"';
	}
	
	if (($catname == 'Towel Warmers') or ($catname == 'Shelves') or ($catname == 'Mirrors') or ($catname == 'Bathroom Accessory')) {
		if ($catname == 'Shelves') {
			$prod_type = 'Bathroom Shelf';
		}
		if ($catname == 'Mirrors') {
			$prod_type = 'Mirror';
		}
		if ($catname == 'Towel Warmers') {
			$prod_type = 'Towel Warmer';
		}
		$catname = '"Home & Garden > Bathroom Accessories"';
	}
	
	if (($catname == 'Sinks Racks and Grids') or ($catname == 'Kitchen Accessory')) {
		$catname = '"Hardware > Plumbing > Plumbing Fixtures > Sink Accessories"';
	}
	
	if ($catname == 'Default') {
		$prod_type = 'Sink Accessories';
		//		$prod_type=$category->getName().' asd';
		$catname = '"Home & Garden > Bathroom Accessories"';
	}
	
	if ($catname == 'Toilets') {
		$prod_type = 'Toilet';
		$catname = '"Hardware > Plumbing > Plumbing Fixtures > Toilets & Bidets > Toilets"';
	}
	if ($catname == 'Vanities') {
		$prod_type = 'Vanity';
		$catname = '"Furniture > Vanities > Bathroom Vanities"';
	}
	if ($catname == 'Bath Tubs') {
		$prod_type = 'Bathtub';
		$catname = '"Hardware > Plumbing > Plumbing Fixtures > Bathtubs"';
	}
	if ($catname == 'Showers') {
		$catname = '"Hardware > Plumbing > Plumbing Fixtures > Shower"';
	}
	
	if ($catname == 'Toilet paper/ tissue holders') {
		$prod_type = 'Toilet Paper Holder';
		$catname = '"Home & Garden > Bathroom Accessories > Toilet Paper Holders"';
	}
	
	if ($catname == 'Towel bars, Rings, Hooks and Racks') {
		$prod_type = 'Towel Holder';
		$catname = '"Home & Garden > Bathroom Accessories > Towel Racks & Holders"';
	}
	
	if ($catname == 'soap dishes') {
		$prod_type = 'Soap Dish';
		$catname = '"Home & Garden > Bathroom Accessories > Soap Dishes"';
	}
	
	if ($catname == 'Showerheads, Arms and Flangers') {
		$prod_type = 'Shower Head';
		$catname = '"Hardware > Plumbing > Plumbing Fixtures > Shower > Shower Heads"';
	}
	if ($catname == 'Soap Dispensers') {
		$prod_type = 'Soap Dispenser';
		$catname = '"Home & Garden > Bathroom Accessories > Soap & Lotion Dispensers"';
	}
	
	if ($catname == 'Toothbrush holders') {
		$prod_type = 'Toothbrush Holder';
		$catname = '"Home & Garden > Bathroom Accessories > Toothbrush Holders"';
	}
	
	$data = array ($prod_type, $catname );
	
	return $data;
}