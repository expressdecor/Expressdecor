<table border=1>
<?
set_time_limit(0);

require_once 'app/Mage.php';
Mage::app('default'); 


$categories = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addAttributeToSelect('*');
                    
foreach($categories as $category) {
 	if($category->getLevel()<4)
    echo "<tr><td>".$category->getId()."</td><td>".$category->getName()."</td></tr>";
    
}

?>
</table>