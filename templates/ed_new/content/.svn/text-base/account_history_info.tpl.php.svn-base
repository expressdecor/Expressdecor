<?php if ($guest_history_show_form) { ?>
		<?php echo tep_draw_form('guest_order_info', 'guest_history_info.php', 'get'); ?>
		<table border="0" cellspacing="0" cellpadding="4" class="border" style="margin: 0 0 0 1px; width: 1079px">
			<tr>
				<td colspan="2">
					<img src="/templates/ed_new/img/order-status.jpg" alt="" />
					<?php if (isset($err) && $err != '') { ?>
						<div style="font-size: 14px; padding: 5px;" class="ed-red"><?php echo htmlspecialchars($err, ENT_QUOTES); ?></div>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td align="right" valign="top" class="main" width="10%"><span class="inputRequirement">*</span> Order Number:</td>
				<td align="left" valign="top" class="main"><?php echo tep_draw_input_field('order_id', (isset($_GET['order_id'])?$_GET['order_id']:''), 'style="width: 200px;"'); ?></td>
			</tr>
			<tr>
				<td align="right" valign="top" class="main"><span class="inputRequirement">*</span> Email address:</td>
				<td align="left" valign="top" class="main"><?php echo tep_draw_input_field('email', (isset($_GET['email'])?$_GET['email']:''), 'style="width: 200px;"'); ?></td>
			</tr>
			<tr>
				<td colspan="2" style="padding-left: 227px;"><?php echo tep_image_submit('button_continue.gif', 'Submit'); ?></td>
			</tr>
		</table>
		</form>
<?php } else { ?>
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td align="center" valign="top" class="main">
            <div class="success-title ed-red">Thank you for shopping with ExpressDecor.</div>
						<div class="success-products-container">
							<div class="header">Purchased Product<?php echo count($order->products)>1?'s':''; ?></div>
							<table border="0" cellspacing="2" cellpadding="4" style="width: 100%;">
<?php
	for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
		list($productid) = explode("{", $order->products[$i]['id']);
		$product_info_add = tep_db_fetch_array(tep_db_query("select p.additional_fields, products_image AS image FROM products p WHERE p.products_id = " . $productid));
		$additional_fields = (array)@unserialize($product_info_add['additional_fields']);
?>
								<tr>
									<td class="productListing-data" colspan="2">
										<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $order->products[$i]['id']); ?>" style="font-size:14px;"><strong><?php echo $order->products[$i]['name']; ?></strong></a><br />
                    <strong style="font-size:11px;">Model number: <?php echo $order->products[$i]['model'] ?></strong><br />
<?php
		$options_names = '';
    if (isset($order->products[$i]['attributes']) && is_array($order->products[$i]['attributes'])) {
      reset($order->products[$i]['attributes']);
      while (list($option, $value) = each($order->products[$i]['attributes'])) {
        $options_names .= $value['option'] . ': ' . $value['value'] . ', ';
      }
    }
?>
										<span style="font-size:11px;"><?php echo substr($options_names, 0, -2); ?></span>
									</td>
								</tr>
                <tr>
									<td class="productListing-data" valign="top" style="width:154px;border-bottom: 4px solid #ccc;">
										<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $order->products[$i]['id']); ?>">
											<?php echo tep_image(DIR_WS_IMAGES . $product_info_add['image'], $order->products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?>
										</a>
									</td>
									<td class="productListing-data" valign="top" style="border-bottom: 4px solid #ccc;">
<?php
		if (STOCK_CHECK == 'true') {
      if (isset($order->products[$i]['attributes']) && is_array($order->products[$i]['attributes'])) {
        $stock_check = tep_check_stock($order->products[$i]['id'], $order->products[$i]['quantity'], $order->products[$i]['attributes']); 
      }else{
      	$stock_check = tep_check_stock($order->products[$i]['id'], $order->products[$i]['quantity']);
      }
      if (tep_not_null($stock_check)) {
        $any_out_of_stock = 1;
        //echo $stock_check;
      }
    }
?>
										<ul class="normal">
											<?php if ($additional_fields['mounting_rings_incl']==1) { ?><li>Mounting Rings (Included)</li> <?php } ?>
											<?php if ($additional_fields['free_kraus_kitchen_towel']==1) { ?><li>Free Kraus Kitchen Towel w. Hook (Included)</li> <?php } ?>
											<?php if ($additional_fields['sink_strainer']==1) { ?><li>Free Sink Strainer (Included)</li> <?php } ?>
											<?php if ($additional_fields['sink_strainer_100']==1) { ?><li>Free 2 Sink Strainers (Included)</li> <?php } ?>
											<?php if ($additional_fields['bottom_grid_50']==1) { ?><li>Free Bottom Grid (Included)</li> <?php } ?>
											<?php if ($additional_fields['bottom_grid_100']==1) { ?><li>Free 2 Bottom Grids (Included)</li> <?php } ?>
											<?php if ($additional_fields['drain_mounting_ring']==1) { ?><li>Free Pop-Up Drain & Mounting Ring (Included)</li> <?php } ?>
											<?php if ($additional_fields['pop_up_drain']==1) { ?><li>Free Pop-Up Drain (Included)</li> <?php } ?>
											<?php /*if ($additional_fields['thirty_day_back_guarantee']==1) ?><li>30 Day Money Back Guarantee</li> <?php } */?>
										</ul>
									</td>
                </tr>
<?php } ?>
							</table>
						</div>
						<div class="success-orderdetails-container">
							<div class="header">Order Details</div>
							<div class="success-orderdetails-dates">
								<span class="success-subtitle2 ed-red">Order Number: </span>
								<span class="success-subtitle2"><?php echo $_GET['order_id']; ?></span>
								<br />
								<span class="success-subtitle2 ed-red">Order date: </span>
								<span><strong><?php echo tep_date_short($order->info['date_purchased']); ?></strong></span>
							</div>
							<div class="success-orderdetails-gift">
							<?php 
							if (strtotime($order->info['date_purchased']) > strtotime('2009-12-01') && strtotime($order->info['date_purchased']) < strtotime('2010-01-01')) {
								$giftAmount = 100;
							}
							elseif (strtotime($order->info['date_purchased']) > strtotime('2010-01-01')) {
								$giftAmount = 200;
							}
							else {
								$giftAmount = 10000000000;
							}
							if (str_replace('$', '', $order->info['total'])>$giftAmount) { ?>
								<img src="/templates/ed_new/img/freegift-big.png" alt="" />
							<?php } ?>
							</div>
							<div class="success-orderdetails-print">
								<a href="#" onclick=" window.print(); return false" rel="nofollow"><img src="/templates/ed_new/img/print-big.png" alt="Print version" /></a>
							</div>
							<div style="clear: both;"></div>
							
							<div class="success-orderdetails-shipping">
							<?php
							  if ($order->delivery != false) {
							  	echo '								<span class="success-subtitle2 ed-red">Shipping Address</span><br />';
							  	echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>');
							  }
							  
								echo '<br /><br />								<span class="success-subtitle2 ed-red">'. HEADING_PAYMENT_METHOD .'</span><br />';
	              echo '								<span>'. $order->info['payment_method'] .'</span><br /><br />';
							?>
							</div>
							
							<div class="success-orderdetails-totals">
							<?php
							  for ($i=0, $n=sizeof($order->totals); $i<$n; $i++) {
							    echo '<strong>'. $order->totals[$i]['title'] .'</strong> '. $order->totals[$i]['text'] .'<br />';
							  }
							?>
							</div>
							<div style="clear: both;"></div>
							<div style="padding: 5px;">
								<table cellspacing="1" cellpadding="4" class="table1">
									<tr>
										<th>Date</th>
										<th>Status</th>
										<th>Comments</th>
									</tr>
<?php
  $statuses_query = tep_db_query("select os.orders_status_name, os.orders_status_id, osh.date_added, osh.comments from " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh where osh.orders_id = '" . (int)$HTTP_GET_VARS['order_id'] . "' and osh.orders_status_id = os.orders_status_id and os.language_id = " . (int)$languages_id . " and osh.customer_notified = 1 order by osh.date_added");
  $cnt = 0;
  $isShipped = false;
  while ($statuses = tep_db_fetch_array($statuses_query)) {
  	if ($cnt%2) {
  		$class = 'odd';
  	}
  	else {
  		$class = 'even';
  	}
  	$cnt++;
  	if (in_array($statuses['orders_status_id'], array(3, 12, 20, 24, 102, 105, 107))) {
  		$isShipped = true;
  	}
?>
									<tr class="<?php echo $class; ?>">
										<td><?php echo tep_date_short($statuses['date_added']); ?></td>
										<td><?php echo $statuses['orders_status_name']; ?></td>
										<td><?php echo (empty($statuses['comments']) ? '&nbsp;' : nl2br(tep_output_string_protected($statuses['comments']))); ?></td>
									</tr>
<?php
  }
?>
								</table>
							</div>
							<?php if ($isShipped) { ?>
							<div style="padding: 5px;text-align: right;">
								<a href="/track_fedex.php" target="_blank"><strong>Track this shipment</strong></a>
							</div>
							<?php } ?>
							<div class="success-orderdetails-explain">
								<p>Your order is currently being processed and will be promptly shipped. If you have any questions about your order, please contact our customer service department at <strong class="ed-red">866-5072725</strong> or Email us at <strong class="ed-red">info@expressdecor.com</strong>. Our knowledgeable representatives are available Monday-Friday 9am-6pm Eastern Standard Time to assist with any general questions, order inquiries, and any other questions you may have about your order or any of the products we carry.</p>
								<p>We appreciate your business; don't forget to checkout our website for all your future remodeling projects.</p>
							</div>
						</div>
						

						<?php
						echo '<img src="https://shareasale.com/sale.cfm?amount='.$orders_total.'&tracking='.$orders_id.'&transtype=sale&merchantID=17021" width="1" height="1">'; 
						
						if ($_SERVER['HTTP_HOST']=='www.expressdecor.com') {
							//comment to be able to see checkout success page after refresh
							tep_session_unregister('orders_total'); 
							tep_session_unregister('orders_id'); 
						}
						?>
				</td>
      </tr>
<?php //require('add_checkout_success.php'); //ICW CREDIT CLASS/GV SYSTEM ?>
    </table>
<?php } ?>