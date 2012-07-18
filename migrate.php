<?php  
ini_set("memory_limit", "1024M");
set_time_limit(0);

function get_tag_id($tag,$storeId){
	$tagModel = Mage::getModel('tag/tag');
	$tagModel->unsetData();
	$tagModel->loadByName($tag);
	$tag_id=$tagModel->getTagId();	
	
	if (empty($tag_id)) {   // IF TAG DOESNT EXIST => make new
		$tag_name=$tag;
		//$tag_name=str_replace(' ','_',$tag_name);
		$tagModel->setName($tag_name);
		$tagModel->setStoreId($storeId);
		$tagModel->setStoreId($storeId);
		$tagModel->setStatus(1);
		$tagModel->save();
		$tagModel->unsetData()->loadByName($tag_name);
		$tag_id=$tagModel->getTagId();
	}
		
	return $tag_id;	
}

function add_relation($tag_name,$product_id){
	
	//1. check if relation exist
	$tagModel = Mage::getModel('tag/tag');
	$tagModel->unsetData()->loadByName($tag_name);
	$rel_exist=0;
	$tag_id=$tagModel->getTagId();
	
	foreach ($tagModel->getRelatedProductIds() as $pr_id) {
		if ($product_id==$pr_id) {
			$rel_exist=1;
		}		
	}
	//2. add if not exist
	$tagRelationModel = Mage::getModel('tag/tag_relation');
	if ($rel_exist==0){
		$tagRelationModel->unsetData()
						 ->setStoreId($storeId)
						 ->setProductId($product_id)
						 ->setActive(1)
	 					 ->setCreatedAt( $tagRelationModel->getResource()->formatDate(time()) )
		 				 ->setTagId($tag_id)
						 ->save();
	}
	
}

function get_name_cat($collection) {

	foreach ($collection as $cat) {
		$cur_category = Mage::getModel('catalog/category')->load($cat->getId());
		$name=$cur_category->getName();
		$storeId = Mage::app()->getStore()->getId();	
		// check category name and explode into tags
		$name=str_replace(' Collection', '', $name);
		$name=str_replace('Faucet', 'Faucets', $name);
		$name=str_replace('Faucetss', 'Faucets', $name);
		$name=str_replace('Sink', 'Sinks', $name);
		$name=str_replace('Sinkss', 'Sinks', $name);
		$name=str_replace(' and', '', $name);
		$name=str_replace(',', '', $name);
		$name=str_replace('bathroom ', 'Bathroom ', $name);
		$name=trim($name);
		// With space
		/*
		 * Shower Sets
		 * Shower Heads
		 * Tile Redi
		 * Allied Brass 
		 * Elizabethan Classics 
		 * Belle Foret
		 * New Arrivals
		*/		
		$name=str_replace('Shower Sets', 'Shower_Sets', $name);
		$name=str_replace('Shower Heads', 'Shower_Heads', $name);
		$name=str_replace('Tile Redi', 'Tile_Redi', $name);
		$name=str_replace('Allied Brass', 'Allied_Brass', $name);
		$name=str_replace('Elizabethan Classics', 'Elizabethan_Classics', $name);
		$name=str_replace('Belle Foret', 'Belle_Foret', $name);
		$name=str_replace('New Arrivals', 'New_Arrivals', $name);
		
		$tags_names=explode(' ', $name);		
		$used_tag_ids_array=array();
		
		foreach ($tags_names as $tag_name){
			$tag_name=str_replace('Shower_Sets', 'Shower Sets', $tag_name);
			$tag_name=str_replace('Shower_Heads', 'Shower Heads', $tag_name);
			$tag_name=str_replace('Tile_Redi', 'Tile Redi', $tag_name);
			$tag_name=str_replace('Allied_Brass', 'Allied Brass', $tag_name);
			$tag_name=str_replace('Elizabethan_Classics', 'Elizabethan Classics', $tag_name);
			$tag_name=str_replace('Belle_Foret', 'Belle Foret', $tag_name);
			$tag_name=str_replace('New_Arrivals', 'New Arrivals', $tag_name);
			
			echo $tag_name.'<br/>';
			 
			//tag
			$tag_id=get_tag_id($tag_name,$storeId);
			array_push($used_tag_ids_array, $tag_id);
			
			// tag relation
			$products=$cat->getProductCollection();
			foreach ($products as $product) {
				$product_id=$product->getId();			
				add_relation($tag_name,$product_id);
			}
		}
			 	
		//cms page
		$PageModel=Mage::getModel('cms/page');
		$PageModel->unsetData();
		$content="";

		//check if exist with same name -- doesn't matter
		// get info about static block
		$cms_block=Mage::getModel('cms/block')->unsetData();
		$static_id=$cur_category->getLandingPage();
		if (!empty($static_id)) {
			$cms_block->load($static_id);			
			$content=$cms_block->getContent();
			if (!empty($content)) {
				$content='<div class="category-view">'.$content."</div>";
			}
			$cms_block->delete();
		//	echo "__BLOCK #".$static_id."_WAS_DELETED_";
		}
		
		//get tag ids
		
		$tag_ids=implode(',',$used_tag_ids_array);
		$tag_content='{{widget type="widgettagproducts/list" product_tag_ids="'.$tag_ids.'" option_column_count="4" option_page_size="20" option_sort_by="position" option_sort_direction="desc"}}';
		$content.=$tag_content.$cur_category->getDescription();
		$layout_update_xml= $cur_category->getCustomLayoutUpdate().'<reference name="left">
<block type="catalog/layer_view" name="catalog.leftnav" before="-" template="catalog/layer/view.phtml"/>
</reference>';

		$data=array(
		'title' => $cur_category->getMetaTitle(),
		'identifier' => $cur_category->getUrlKey().'.html',
		'content' => $content,
		'content_heading' => $cur_category->getName(),
		'is_active' => 1,
		'under_version_control' => 'No',
		'stores' => array(0),
		'root_template' => 'two_columns_left',
		'layout_update_xml'=>$layout_update_xml,
		'meta_keywords'=> $cur_category->getMetaKeywords(),
		'meta_description'=> $cur_category->getMetaDescription()
		);

		$PageModel->setData($data)->save();
		echo "<br/>".$cur_category->getUrlKey().'.html'."<br/>";
		//end cms

		// delete from url rewrite ??
		$url_rewrite_Model=Mage::getModel('core/url_rewrite')->unsetData();
		$url_rewrites=$url_rewrite_Model->getCollection()
		->addFieldToFilter('category_id', array(
		'eq' => $cur_category->getId()
		));
		if (count($url_rewrites) > 0) {
			foreach ($url_rewrites as $url_rewrite) {
				$url_rewrite->delete();
			}
		}
		//end url_rewrites
		$url_key=$cur_category->getUrlKey();
		$cur_category->setUrlKey($url_key.'_old');
		$cur_category->save();
		//end script

		$childs=$cat->getChildrenCategories();
		if (!empty($childs)) {
			//die(); // comment for all (if not commented will change only Kraus)
			get_name_cat($childs);
		}
	}

}

require_once 'app/Mage.php';
Mage::app('default');

//78 brands category id

$main_cat =  Mage::getModel('catalog/category')->load(78);
$collection0 = $main_cat->getChildrenCategories();

if (!empty($collection0) ) {
	get_name_cat($collection0);
}

?>