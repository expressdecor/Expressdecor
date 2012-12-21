 <?php
  
require_once 'app/Mage.php';
Mage::app ( 'default' );

$first = $_GET ['first'];
$last = $_GET ['last'];
$type = $_GET ['type'];

if ($type == "left") {
	$strings = array ();
	$links = array ();
	for($i = $first; $i <= $last; $i ++) {		
		array_push ( $strings, Mage::getStoreConfig ( 'expressdecor/slides/slide' . $i . '_url', Mage::app ()->getStore () ) );
		array_push ( $links, Mage::getStoreConfig ( 'expressdecor/slides/slide' . $i . '_link', Mage::app ()->getStore () ) );
	
	}
	
/*	
	$strings = array ('http://www.dev.expressdecor.com/media/all-sink-main-new.jpg', 
			'http://www.dev.expressdecor.com/media/all-sink-main-new.jpg', 
			'http://www.dev.expressdecor.com/media/all-sink-main-new.jpg', 
			'http://www.dev.expressdecor.com/media/all-sink-main-new.jpg', 
			'http://www.dev.expressdecor.com/media/all-sink-main-new.jpg' );	
	$links = array ('#1', '#2', '#3', '#4', '#5' );
*/	 	
	for($i = count ( $links ); $i >= 1 ; $i --) {
		print_r ( "<a class='slide-link' href='" . $links [$i - 1] . "' ><img id='slide-" . $i . "' src='" . $strings [$i - 1] . "'></a>" );
	}
	
	
} 
if ($type == "right") {
	$titles = array ();
	$descriptions = array ();
	for($i = $first; $i <= $last; $i ++) {
		array_push ( $titles, Mage::getStoreConfig ( 'expressdecor/slides/slide' . $i . '_title', Mage::app ()->getStore () ) );
		array_push ( $descriptions, Mage::getStoreConfig ( 'expressdecor/slides/slide' . $i . '_descr', Mage::app ()->getStore () ) );
	
	}  
	
	for($i=1; $i <= count ($titles) ; $i++) {	
		
		if ($i==1){
			$tr_active="triangle-active";
			$title_active="title-text-active";
			$descr_active="descr-text-active";
			$block_active=" block-active";
		} else {
			$tr_active="";
			$title_active="";
			$descr_active="";
			$block_active="";
		}
		if ($i==count($titles)){
			$last="block-last";			
		}else {
			$last="";
		}
		
		echo '<a onclick="slider_show('.$i.');" href="javascript:void(0)">
		<div id="triangle-'.$i.'" class="triangle '.$tr_active.'"></div>
		<div id="block-'.$i.'" class="block '.$block_active.' '.$last.' ">
		<div id="title-text-'.$i.'"  class="title-text '.$title_active.'">'.$titles[$i-1].'</div>
		<div id="descr-text-'.$i.'" class="descr-text '.$descr_active.'">'.$descriptions[$i - 1].'</div></div>
		</a>';
		
	}
	
}

?>

          

