<?php
	function renderCategoryData($arr) {
		$str = '';
	  foreach ($arr as $cID =>$cData) {
    	//STUB Giagni removed from the listing
    	if ($cID == 236) continue;
    	
    	if (in_array($cID, array('bath', 'kitchen', 'support'))) {
    		echo '<li>'. str_replace('&', '&amp;', $cData['info']['categories_name']);
    	}
    	elseif (isset($cData['info']['link']) && $cData['info']['link']) {
    		echo '<li><a href="'. $cData['info']['link'] .'" rel="nofollow">'. str_replace('&', '&amp;', $cData['info']['categories_name']) .'</a>'; 
    	}
    	else {
    		//echo '<li><a href="'. tep_href_link('index.php', 'cPath='. $cData['info']['cPath']) .'" rel="nofollow">'. str_replace('&', '&amp;', $cData['info']['categories_name']) .'</a>';
            echo '<li><a href="'. urlencode($cData['info']['page_name']) .'" rel="nofollow">'. str_replace('&', '&amp;', $cData['info']['categories_name']) .'</a>';
    	}
	  	
	  	if (isset($cData['childs']) && count($cData['childs'])) {
	  		echo '&nbsp;&raquo;<ul>';
	  		renderCategoryData($cData['childs']);
	  		echo '</ul>';
	  	}
	  	echo '</li>';
	  }
	}
	
	$categories_array['bath'] = array(
		'info' => array(
			'categories_id' => 0,
			'categories_name' => 'Bathroom Products',
			'parent_id' => 0,
			'sort_order' => 0,
			'categories_status' => 1,
			'cPath' => 0
		)
	);
	$categories_array['kitchen'] = array(
		'info' => array(
			'categories_id' => 0,
			'categories_name' => 'Kitchen Products',
			'parent_id' => 0,
			'sort_order' => 0,
			'categories_status' => 1,
			'cPath' => 0
		)
	);
	$categories_array['support'] = array(
		'info' => array(
			'categories_name' => 'Customer Support',
		),
		'childs' => array(
			1 => array('info'=>array('link'=>'/about.html', 'categories_name'=>'About Us')),
			2 => array('info'=>array('link'=>'/contact-us.html', 'categories_name'=>'Contact Us')),
			3 => array('info'=>array('link'=>'/coupons.html', 'categories_name'=>'Coupons')),
			4 => array('info'=>array('link'=>'/returns.html', 'categories_name'=>'Returns & Exchange')),
			5 => array('info'=>array('link'=>'/most-faqs.html', 'categories_name'=>'Most FAQs')),
			6 => array('info'=>array('link'=>'/video-and-documentation-library.html', 'categories_name'=>'Installation Videos')),
			7 => array('info'=>array('link'=>'/privacy.html', 'categories_name'=>'Privacy Policy')),
		)
	);
	
	$tmpArr = tep_get_category_tree_my();
	foreach ($tmpArr as $key => $val) {
		switch ($key) {
			case in_array($key, array(28, 3, 4, 21, 2, 259, 1, 274)): //bathroom
				$categories_array['bath']['childs'][$key] = $val;
				break;
			case in_array($key, array(75, 267, 17, 268)): //kitchen
				$categories_array['kitchen']['childs'][$key] = $val;
				break;
		}
	}
	//_preD($categories_array);
?>
<script type="text/javascript">
	$(function(){
		$('ul.ed_menu').jdMenu();
		
	});
</script>
<ul class="jd_menu">
<?php
  echo renderCategoryData($categories_array);
?>
</ul>