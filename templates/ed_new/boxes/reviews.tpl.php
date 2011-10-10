			<div class="border latest-testimonials">
				<h2>Latest Testimonials</h2>
				<div style="float:right; margin-top:-26px"><a href="/rss.php?page=reviews"><img src="/templates/ed_new/img/rss.png" alt="" /></a></div>
				<?php foreach($random_products as $random_product) {?>
				<div class="brief_info">
					<span class=""><a class="" href="<?php echo HTTP_SERVER.'/'.$random_product['page_name']?>"><?php echo $random_product['products_name']; ?></a>&nbsp;&nbsp;<?php echo tep_image(DIR_WS_IMAGES . 'stars_' . $random_product['reviews_rating'] . '.gif' , sprintf(BOX_REVIEWS_TEXT_OF_5_STARS, $random_product['reviews_rating'])); ?></span><br />
					<span class="product_info"><?php echo $random_product['reviews_text']; ?></span>
				</div>
				<?php } ?>
				<div class="more-reviews"><a href="<?php echo tep_href_link(FILENAME_REVIEWS); ?>" rel="nofollow">More testimonials</a></div>
			</div>