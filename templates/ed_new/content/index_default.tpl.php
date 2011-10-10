		<?php
        if (tep_session_is_registered('userBannersArr')) {
            if (count ($userBannersArr['imageArray1']) != 1) {
           		tep_session_unregister('userBannersArr');
	            tep_session_register('userBannersArr');
	            $userBannersArr = array("imageArray1" => array(), "imageArray2" => array(), "imageArray3" => array());
            }
        } else {
            tep_session_register('userBannersArr');
            $userBannersArr = array("imageArray1" => array(), "imageArray2" => array(), "imageArray3" => array());
        }
        
        //$imageArray1 = array("/images/homepage1.jpg", "/images/homepage12.jpg", "/images/homepage13.jpg", "/images/homepage14.jpg");
        $imageArray1 = array("/images/homepage1.jpg");
        $imageArray2 = array("/images/homepage2.jpg", "/images/homepage22.jpg", "/images/homepage23.jpg", "/images/homepage24.jpg");
        $imageArray3 = array("/images/homepage3.jpg", "/images/homepage31.jpg");
        
        if(sizeof($userBannersArr['imageArray1']) == sizeof($imageArray1))  $userBannersArr['imageArray1'] = array();
        if(sizeof($userBannersArr['imageArray2']) == sizeof($imageArray2))  $userBannersArr['imageArray2'] = array();       
        if(sizeof($userBannersArr['imageArray3']) == sizeof($imageArray3))  $userBannersArr['imageArray3'] = array();       
        
        $selectionArray = array();
        foreach($imageArray1 as $path) {
            if(!in_array($path, $userBannersArr['imageArray1'])) array_push($selectionArray, $path);           
        }
        $imagePath1 = $selectionArray[(rand(0,sizeof($selectionArray)-1))];        
        array_push($userBannersArr['imageArray1'], $imagePath1);
        
        $selectionArray = array();
        foreach($imageArray2 as $path) {
            if(!in_array($path, $userBannersArr['imageArray2'])) array_push($selectionArray, $path);           
        }
        $imagePath2 = $selectionArray[(rand(0,sizeof($selectionArray)-1))];        
        array_push($userBannersArr['imageArray2'], $imagePath2);
        
        $selectionArray = array();
        foreach($imageArray3 as $path) {
            if(!in_array($path, $userBannersArr['imageArray3'])) array_push($selectionArray, $path);           
        }
        $imagePath3 = $selectionArray[(rand(0,sizeof($selectionArray)-1))];        
        array_push($userBannersArr['imageArray3'], $imagePath3);
        ?>
        <div class="desk_the_saving slideShow clearfix">
			<ul id="categories_slideshow" class="slides">
				<li class="slide"><a href="<?php echo HTTP_SERVER.'/coupons.html'?>"><img src="<?php echo $imagePath1; ?>" alt="Discount Coupons" /></a></li>
				<li class="slide"><a href="<?php echo HTTP_SERVER.'/coupons.html'?>"><img src="<?php echo $imagePath2; ?>" alt="Discount Coupons" /></a></li>
				<li class="slide"><a href="<?php echo HTTP_SERVER.'/coupons.html'?>"><img src="<?php echo $imagePath3; ?>" alt="Discount Coupons" /></a></li>
				<!--li class="slide"><a href="<?php echo HTTP_SERVER.'/coupons.html'?>"><img src="/images/homepage4.jpg" alt="Discount Coupons" /></a></li-->
			</ul>
			<ul class="navigation">
				<li><a href="javascript:void(0);" class="prev">&lt; prev</a></li>
				<li><a href="javascript:void(0);" class="page">1</a></li>
				<li><a href="javascript:void(0);" class="page">2</a></li>
				<li><a href="javascript:void(0);" class="page">3</a></li>
				<!--li><a href="javascript:void(0);" class="page">4</a></li-->
				<li><a href="javascript:void(0);" class="next">next &gt;</a></li>
			</ul>
		</div>
		
		<div class="right_column_home">
			<?php /*<div><a href="about.php" rel="nofollow"><img src="/templates/ed_new/img/box/box_right/why_choose.png" alt="Why choose ExpressDecor" /></a></div>*/ ?>
			<?php if (time() < strtotime($target_date)) { ?>
			<div style="border: 2px solid #d4d4d4;">
				<img src="/templates/ed_new/img/box/box_right/memorial_sale_small.png" alt="" /><br />
				<div id="countdown_dashboard">
					<div class="dash weeks_dash" style="display:none;">
						<span class="dash_title">weeks</span>
						<div class="digit">0</div>
						<div class="digit">0</div>
					</div>
				
					<div class="dash days_dash">
						<span class="dash_title">days</span>
						<div class="digit">0</div>
						<div class="digit">0</div>
					</div>
				
					<div class="dash hours_dash">
						<span class="dash_title">hours</span>
						<div class="digit">0</div>
						<div class="digit">0</div>
					</div>
				
					<div class="dash minutes_dash">
						<span class="dash_title">minutes</span>
						<div class="digit">0</div>
						<div class="digit">0</div>
					</div>
				
					<div class="dash seconds_dash">
						<span class="dash_title">seconds</span>
						<div class="digit">0</div>
						<div class="digit">0</div>
					</div>
				
				</div>
			</div>
			<?php } else { ?>
			<div><a href="<?php echo HTTP_SERVER?>/coupons.html" rel="nofollow"><img src="/templates/ed_new/img/box/box_right/discount_coupons.png" alt="Discount Coupons" /></a></div>
			<?php } ?>
			<br />
			<div><a href="<?php echo HTTP_SERVER?>/returns.html" rel="nofollow"><img src="/templates/ed_new/img/box/box_right/30-day-money-back.jpg" alt="30 Day money back guarantee" /></a></div>
		</div>
		<div style="clear:both;font-size:1px; height:1px;">&nbsp;</div>
		
		<?php if (count($_SESSION['testimonial_banners'])) { ?>
		<div class="homepage-testimonials-banner"><img src="/images/testimonials/<?php echo $_SESSION['testimonial_banners'][$_SESSION['testimonial_banners_cnt']]; ?>" alt="" /></div>
		<?php } ?>
		
		<div class="browse_categories">
			<div class="products_list">
			  <?php include(DIR_WS_MODULES . 'browse_categories.php'); ?>
			</div>
		</div>
		<div class="clearfix" style="height:1px;overflow:hidden;"><img src="/images/x.gif" alt="" /></div>
		
		<?php echo bestsellersGetProducts((int)$current_category_id); ?>
		<script type="text/javascript">
			 $(document).ready( function(){
					$('.desk_the_saving').slideShow({
						interval: 18,
						slideSize: false,
						start: 0
					});
					
					<?php if (time() < strtotime($target_date)) { ?>
					$('#countdown_dashboard').countDown({
						targetDate: {
							'day': 		<?php echo (int)date('j', strtotime($target_date)); ?>,
							'month': 	<?php echo (int)date('n', strtotime($target_date)); ?>,
							'year': 	<?php echo (int)date('Y', strtotime($target_date)); ?>,
							'hour': 	<?php echo (int)date('G', strtotime($target_date)); ?>,
							'min': 		<?php echo (int)date('i', strtotime($target_date)); ?>,
							'sec': 		<?php echo (int)date('s', strtotime($target_date)); ?>
						}
					});
					<?php } ?>
			 });
			 
			$(document).everyTime(9500, function(i) {
				$.post("/", { action: 'testimonial_banner', sender: 'ajax' }, function(data){
					if (data.img) {
						$("div.homepage-testimonials-banner img").attr("src", "/images/testimonials/"+data.img);
					}
				}, 'json');
			});
		</script>
		
		<div class="clearfix">&nbsp;</div>