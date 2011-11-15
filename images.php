 

<?php

require_once 'app/Mage.php';
Mage::app('default');

$first=$_GET['first'];
$last=$_GET['last'];
$strings=array();
$links=array();

for ($i=$first;$i<=$last;$i++) {

array_push($strings,Mage::getStoreConfig('expressdecor/slides/slide'.$i.'_url',Mage::app()->getStore()));
array_push($links,Mage::getStoreConfig('expressdecor/slides/slide'.$i.'_link',Mage::app()->getStore()));

}
/*
$strings=array('http://www.alex.expressdecor.com/media/new-products.jpg',
'http://www.alex.expressdecor.com/media/main-banner/home_page_b_6.jpg',
'http://www.alex.expressdecor.com/media/main-banner/home_page_b_3.jpg',
'http://www.alex.expressdecor.com/media/main-banner/shipping.jpg',
'http://www.alex.expressdecor.com/media/main-banner/home_page_b_7.jpg'
);

$links=array('#','#','#','#','#');
*/
//echo ('<total>'.count($strings).'</total>');

for ($i=$first;$i<=$last;$i++) {	 
	print_r("<a class='slide-link' href='".$links[$i-1]."' ><img id='slide-".$i."' src='".$strings[$i-1]."'></a>");
}

?>

          

