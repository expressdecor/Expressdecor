      <div class="simple_block clearfix">
				<h1 class="special_title"></h1>
				<a class="simple_block_img_box" href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $random_product["products_id"]); ?>">
          <?php echo tep_image(DIR_WS_IMAGES . $random_product['products_image'], $random_product['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?>
        </a>
				<div class="brief_info">
					<span class="vanity_plus"><a class="vanity_plus" href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $random_product["products_id"]); ?>">
            <?php echo $random_product['products_name']; ?></a> - <span class="vanity_cost"><?php echo $currencies->display_price($random_product['specials_new_products_price'], tep_get_tax_rate($random_product['products_tax_class_id'])) ?></span>
          </span>
					<a class="read_more" href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $random_product["products_id"]); ?>">Read more</a>
				</div>
			</div>