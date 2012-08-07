<?phpini_set("memory_limit", "1024M");
set_time_limit(0);

$filename="tags.csv";
$filedata=array();
$row=1;
require_once 'app/Mage.php';
Mage::app('default');
$storeId = Mage::app()->getStore()->getId();
if (($handle = fopen($filename, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data); 
		array_push($filedata, $data);
    }
    fclose($handle);
}
//print_r($filedata);
$model=Mage::getModel("tag/tag");
foreach ($filedata as $tag) { // for each row		$position=$tag[2];
	// add tags if they don't exists
	$model->loadByName($tag[1]);
	if(count($model->getData())==0) {
		$model->setStatus(1);
		$model->setName($tag[1]);
		$model->save();
	}
	$prod_id=Mage::getModel('catalog/product')->getIdBySku($tag[0]);
 	$tag_id=$model->loadByName($tag[1])->getId();
//	print_r($model->getRelatedProductIds());
	$exists=0;
	if (count($model->getRelatedProductIds())>0) {
		foreach ($model->getRelatedProductIds() as $pr_id) {
			 if ($prod_id==$pr_id) {
			 	$exists=1;			 	 
			 }
		}
	}
	// if relation didn't exists
		if ($exists==0) {					if (!empty($prod_id)){
		$tag_col=Mage::getModel('tag/tag_relation')
		->setTagId($tag_id) 
		->setProductId($prod_id)
		->setStoreId($storeId)
		->setCustomerId(null)		->setPosition($position)
		->setActive(1)
		->save();			} else {				echo $tag[0]."  Product doesn't exist in magento ! <br/>"; 			}
		}		
}
echo "Finished";
?>