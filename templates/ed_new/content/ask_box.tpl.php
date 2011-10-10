<!-- body_text //-->
<div style="border-bottom: 1px solid #ccc;"><img src="/templates/ed_new/img/ask_a_question-top.jpg" alt="" /></div>
<?php echo tep_draw_form('email_friend', tep_href_link('ask_a_question.php', 'action=process&products_id=' . $HTTP_GET_VARS['products_id'])); ?>

	<table border="0" width="100%" cellspacing="0" cellpadding="5">
		<tr>
			<td width="150">
				<?php echo tep_image(DIR_WS_IMAGES . $product_info['products_image'], $product_info['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?>
			</td>
			<td class="pageHeading">
				<div class="text">
					<h1 class="product_name"><?php echo $product_info['products_name']; ?></h1>
					<div class="product_code">
						Model number: <?php echo $product_info['products_model']; ?>
					</div>
					<br />
					<div class="normal_price">Our price: <span class="price"><?php echo $currencies->display_price($final_price, tep_get_tax_rate($product_info['products_tax_class_id'])); ?></span></div>
					<br />
					<?php if ($coupons_result) { ?>
					<div class="product_infotext"><img src="/templates/ed_new/img/savemoney.png" alt="Free Shipping" />&nbsp;Extra <?php echo number_format($coupons_result['coupons_discount_amount']*100, 0); ?>% OFF  <span class="product_infotext2">- Coupon Code <?php echo $coupons_result['coupons_id']; ?></span></div>
					<?php } 
					$row = mysql_fetch_row(tep_db_query("SELECT * FROM  `products_attributes` WHERE  `products_id`=".$product_info['products_id']." and `options_id`=18"));
					if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true' && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER < $final_price && !$row) { 
					?>
					<div class="product_infotext"><img src="/templates/ed_new/img/savemoney.png" alt="Free Shipping" />&nbsp;Free Shipping <span class="product_infotext2"> in Continental US</span></div>
					<?php }
					if ($product_info['mounting_rings_incl']==1) { ?>
					<div class="product_infotext"><img src="/templates/ed_new/img/savemoney.png" alt="Mounting Rings Included" />&nbsp;Mounting Ring <span class="product_infotext2">Included</span></div>
					<?php }
					if ($product_info['free_kraus_kitchen_towel']==1) { ?>
					<div class="product_infotext"><img src="/templates/ed_new/img/savemoney.png" alt="Free Kraus Kitchen Towel w. Hook a $20 Value" />&nbsp;Free Kitchen Towel w. Hook <span class="product_infotext2">a $20 Value</span></div>
					<?php }
					if ($product_info['sink_strainer']==1) { ?>
					<div class="product_infotext"><img src="/templates/ed_new/img/savemoney.png" alt="Free Sink Strainer a $50 Value" />&nbsp;Free Sink Strainer <span class="product_infotext2">a $50 Value</span></div>
					<?php }
					if ($product_info['sink_strainer_100']==1) { ?>
					<div class="product_infotext"><img src="/templates/ed_new/img/savemoney.png" alt="Free 2 Sink Strainers a $100 Value" />&nbsp;Free 2 Sink Strainers <span class="product_infotext2">a $100 Value</span></div>
					<?php }
					if ($product_info['bottom_grid_50']==1) { ?>
					<div class="product_infotext"><img src="/templates/ed_new/img/savemoney.png" alt="Free Bottom Grid a $50 Value" />&nbsp;Free Bottom Grid <span class="product_infotext2">a $50 Value</span></div>
					<?php }
					if ($product_info['bottom_grid_100']==1) { ?>
					<div class="product_infotext"><img src="/templates/ed_new/img/savemoney.png" alt="Free 2 Bottom Grids a $100 Value" />&nbsp;Free 2 Bottom Grids <span class="product_infotext2">a $100 Value</span></div>
					<?php }
					if ($product_info['drain_mounting_ring']==1) { ?>
					<div class="product_infotext">
						<div style="float:left; width: 20px;"><img src="/templates/ed_new/img/savemoney.png" alt="Free Pop-Up Drain &amp; Mounting Ring a $70 Value" /></div>
						<div style="float:left;">Free Pop-Up Drain &amp; Mounting Ring <span class="product_infotext2">a $70 Value</span></div>
						<div style="clear:both;"></div>
					</div>
					<?php }
					if ($product_info['pop_up_drain']==1) { ?>
					<div class="product_infotext"><img src="/templates/ed_new/img/savemoney.png" alt="Free Pop-Up Drain a $50 Value" />&nbsp;Free Pop-Up Drain <span class="product_infotext2">a $50 Value</span></div>
					<?php } ?>
				</div>
			</td>
		</tr>
<?php
  if ($messageStack->size('friend') > 0) {
?>
		<tr>
			<td colspan="2"><?php echo $messageStack->output('friend'); ?></td>
		</tr>
<?php
  }
?>
		<tr>
			<td colspan="2" style="border-top: 1px solid #ccc">
				<table border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td class="main"><?php echo FORM_FIELD_CUSTOMER_NAME; ?><span class="inputRequirement">*</span></td>
						<td class="main"><?php echo tep_draw_input_field('from_name'); ?></td>
					</tr>
					<tr>
						<td class="main"><?php echo FORM_FIELD_CUSTOMER_EMAIL; ?><span class="inputRequirement">*</span></td>
						<td class="main"><?php echo tep_draw_input_field('from_email_address'); ?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td class="main"><b><?php echo FORM_TITLE_FRIEND_MESSAGE; ?></b></td>
					</tr>
					<tr>
						<td class="main"><?php echo tep_draw_textarea_field('message', 'soft', 80, 8); ?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table border="0" width="678" cellspacing="0" cellpadding="2">
					<tr>
						<td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
						<td><?php echo '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $HTTP_GET_VARS['products_id']) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
						<td align="right"><?php echo tep_image_submit('button_send.gif', IMAGE_BUTTON_CONTINUE); ?></td>
						<td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

</form>
<!-- body_text_eof //-->