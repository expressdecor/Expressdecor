<?php
				if ($product_info['reviews_count']) {
					$res = tep_db_query('select r.reviews_id, r.customers_name, rd.reviews_text from reviews r, reviews_description rd 
            where r.reviews_id=rd.reviews_id and 
            rd.languages_id='.(int)$languages_id.' and 
            r.products_id='. (int)$product_info['products_id'] .' and r.reviews_status=0 order by r.date_added DESC');
?>
				<div id="reviews">
					<div id="reviews_rating">
						<span style="float: left; padding-right: 15px;">Customer's Rating</span>
						<div id="rating_stars"><img alt="<?php echo ceil($product_info['reviews_rating']); ?> stars" src="/images/stars_<?php echo ceil($product_info['reviews_rating']); ?>.gif"/></div> 
						<a id="reviews_write" href="/product_reviews_write.php/products_id/<?php echo ((int)$HTTP_GET_VARS['products_id']);?>" rel="nofollow">Write Own Review</a>
					</div>
					<div style="clear:both;"></div>
					<div id="review_content">
						<table cellpadding="0" cellspacing="0" class="paginated" border="0">
						<?php while ($row = tep_db_fetch_array($res)) { ?>
							<tr>
								<td>
									<div class="reviews_name"><?php echo str_replace('&', '&amp;', $row['customers_name']); ?></div>
									<div class="reviews_content"><?php echo stripslashes(tep_output_string_protected($row['reviews_text'])); ?></div>
								</td>
							</tr>
						<?php } ?>
						</table>
					</div>
				</div>
<?php 
				} 
				else {
?>
        <div id="reviews">
        	<div id="reviews_rating">
        		<span style="float: left; padding-right: 15px; padding-left: 5px; font-style:italic; font-weight: normal;">No reviews yet</span>
        		<a id="reviews_write" href="/product_reviews_write.php/products_id/<?php echo ((int)$HTTP_GET_VARS['products_id']);?>" rel="nofollow">Write Own Review</a>
        	</div>
					<div style="clear:both;"></div>
        </div>
<?php 
				}
?>