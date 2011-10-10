<?php
	if ($cart->count_contents() > 0) {
		//generate shipping
		$final_amount = $shoppingCartCalculatedTotals['withCoupon'];
		if ($final_amount > 100) {
			$shipping_cost = 0;
			//generate correct pulldown for shipping
			$shipping_methods = array(
				array('id'=>'0', 'text'=>'Free shipping'),
				array('id'=>'1', 'text'=>'Signature confirmation (+ $2.00)'),
			);
		}
		else {
			$shipping_cost = 15;
			$shipping_methods = array(
				array('id'=>'2', 'text'=>'Flat Rate'),
				array('id'=>'3', 'text'=>'Signature confirmation (+ $2.00)'),
			);                                                    
		}
		$tax = 0;
?>
		<script type="text/javascript">
			var total = <?php echo number_format($shoppingCartCalculatedTotals['withoutCoupon'] + $shipping_cost, 2, '.', ''); ?>;
			var sess_id = '<?php echo session_id(); ?>'; //fixed
			var o_st = <?php echo number_format($shoppingCartCalculatedTotals['withoutCoupon'], 2, '.', ''); ?>; //fixed
			var o_sh = <?php echo number_format($shipping_cost, 2, '.', ''); ?>;
			var o_tax = 0.00;
			var discount_amount = <?php echo number_format($shoppingCartCalculatedTotals['discount'], 2, '.', ''); ?>;
			var cart_confirmed = false;
			var varCartID = '<?php echo $cart->cartID; ?>';
		</script>
		<div>
			<div style="float:left; width: 300px;"><img src="/templates/ed_new/img/shoppingcart-top.jpg" alt="" /></div>
		</div>
		<div id="saveShoppingCartDialog">
			<form action="shopping_cart.php" method="post" id="saveShoppingCart_form">
				<table>
					<tr>
						<td style="font-size: 14px;">Your Email:</td>
						<td><input type="text" name="email" value="" style="width: 200px;" id="sc_email" /></td>
						<td><?php echo tep_image_submit('button_send.gif', 'Save'); ?></td>
					</tr>
				</table>
			</form>
		</div>
		<div style="clear:both;font-size:1px;">&nbsp;</div>
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td class="main">
        <?php echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_SHOPPING_CART, 'action=update_product'), 'post', 'id="cart_quantity"') . tep_draw_hidden_field('targetAddress', '', 'id="targetAddressID"'); ?>
        <?php 
        if (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == 'exdecor') {
            echo tep_draw_hidden_field('discount1', '', 'id="discount1ID"'); 
            echo tep_draw_hidden_field('discount2', '', 'id="discount2ID"');        
        }
        ?>
        	<table cellpadding="4" cellspacing="0" style="width: 100%;">
        		<tr>
        			<td align="center" class="productListing-heading" style="width: 350px;">Product Description</td>
        			<td align="center" class="productListing-heading">Price</td>
        			<td align="center" class="productListing-heading">Price<br />with coupon</td>
        			<td align="center" class="productListing-heading">Quantity</td>
        			<td align="center" class="productListing-heading">Total</td>
        			<td align="center" class="productListing-heading">Total<br />with coupon</td>
        		</tr>
<?php
	  for ($i=0, $n=sizeof($products); $i<$n; $i++) {
	// Push all attributes information in an array
	    if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
	      while (list($option, $value) = each($products[$i]['attributes'])) {
	        if(!preg_match('/bundle_id/', $option)) {
	            echo tep_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
	            $attributes = tep_db_query("select popt.products_options_name, popt.products_options_track_stock, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
	                                        from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
	                                        where pa.products_id = '" . $products[$i]['id'] . "'
	                                         and pa.options_id = '" . $option . "'
	                                         and pa.options_id = popt.products_options_id
	                                         and pa.options_values_id = '" . $value . "'
	                                         and pa.options_values_id = poval.products_options_values_id
	                                         and popt.language_id = '" . $languages_id . "'
	                                         and poval.language_id = '" . $languages_id . "'");
	            $attributes_values = tep_db_fetch_array($attributes);
	
	            $products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
	            $products[$i][$option]['options_values_id'] = $value;
	            $products[$i][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
	            $products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];
	            $products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];
	            $products[$i][$option]['track_stock'] = $attributes_values['products_options_track_stock'];
	          }
	      }
	    }
	  }
    $any_out_of_stock = 0;
    $isInShowers = false;
    
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      $cur_row = sizeof($info_box_contents) - 1;
      
      $result = array_intersect($products[$i]['categories'], array(101, 110));
      if (count($result)) {
      	$isInShowers = true;
      }

			if(!isBundle($products[$i]['id'])) {
				$product_price = $currencies->format(tep_add_tax($products[$i]['final_price_wo_coupon'], tep_get_tax_rate($products[$i]['tax_class_id'])));
				$total_price = $currencies->format(tep_add_tax($products[$i]['final_price_wo_coupon'], tep_get_tax_rate($products[$i]['tax_class_id'])) * $products[$i]['quantity']);
			}
			else {
				$product_price = $total_price = calculateBundlePrice($products[$i]['attributes']['bundle_id']);
			}
			$product_price_discount = $currencies->format(tep_add_tax($products[$i]['final_price'], tep_get_tax_rate($products[$i]['tax_class_id'])));
			$total_price_discount = $currencies->format(tep_add_tax($products[$i]['final_price'], tep_get_tax_rate($products[$i]['tax_class_id'])) * $products[$i]['quantity']);
?>
						<tr>
							<td class="productListing-data" style="border-bottom: 4px solid #d5d5d5;">
								<table border="0" cellspacing="2" cellpadding="2">
									<tr>
										<td class="productListing-data">
                                        <?php //echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']); ?>
											<a href="<?php echo HTTP_SERVER.'/'.$products[$i]['page_name']?>" style="font-size:14px;"><strong><?php echo $products[$i]['name']; ?></strong></a><br />
                      <strong style="font-size:11px;">Model number: <?php echo $products[$i]['model'] ?></strong><br />
<?php
			$options_names = '';
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        reset($products[$i]['attributes']);
        while (list($option, $value) = each($products[$i]['attributes'])) {
          if($option == 'bundle_id') {
            foreach($value as $bundle_id => $bundle_option_id) {
                if(is_array($bundle_option_id)) {
                    foreach($bundle_option_id as $bundle_option_id_key => $bundle_option_id_qty) {
                        $product_bundle = getBundleProduct(tep_get_prid($products[$i]['id']), $bundle_id, $bundle_option_id_key);
                        $bundle_option_id_qty = checkBundleOptionQty($bundle_id, $bundle_option_id_key, $bundle_option_id_qty);
                        $options_names .= $product_bundle['bundle_name'] . ': '. $bundle_option_id_qty .' x ' . $product_bundle['products_name'] . ', ';
                    }
                }
            }
          }
          else {
            $options_names .= $products[$i][$option]['products_options_name'] . ': ' . $products[$i][$option]['products_options_values_name'] . ', ';
          }
        }
      }
?>
											<span style="font-size:11px;"><?php echo substr($options_names, 0, -2); ?></span>
										</td>
									</tr>
								</table>
								<table border="0" cellspacing="2" cellpadding="2">
                  <tr>
										<td class="productListing-data" valign="top" style="width:154px;" width="154">
                                            <?php //echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']); ?>
											<a href="<?php echo HTTP_SERVER.'/'.$products[$i]['page_name']?>">
												<?php echo tep_image(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'class="border"'); ?>
											</a>
										</td>
										<td class="productListing-data" valign="top">
<?php
			if (STOCK_CHECK == 'true') {
        if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
          $stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity'], $products[$i]['attributes']); 
        }else{
        	$stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity']);
        }
        if (tep_not_null($stock_check)) {
          $any_out_of_stock = 1;
          //echo $stock_check;
        }
      }
			list($productid) = explode("{",$products[$i][id]);
			
			$product_info = tep_db_query("select p.products_id, p.additional_fields, p.products_status, pd.products_name, pd.products_description, pd.products_please_note, p.products_model, p.products_quantity, p.products_image, p.products_retail_price, p.products_image_med, p.products_image_lrg, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id, p.products_family, s.specials_new_products_price AS special_price, count(r.reviews_id) as reviews_count, AVG(r.reviews_rating) as reviews_rating FROM products p LEFT JOIN products_description pd ON pd.products_id = p.products_id LEFT JOIN specials s ON p.products_id = s.products_id LEFT JOIN reviews r ON p.products_id = r.products_id WHERE p.products_id = " . $productid . " and pd.language_id = " . (int)$languages_id . " group by p.products_id");
			$product_info = tep_db_fetch_array($product_info);
			$additional_fields = (array)@unserialize($product_info['additional_fields']);
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
                </table>
							</td>
							<td align="center" class="productListing-data" valign="top" style="color:#786b66;border-bottom: 4px solid #d5d5d5;"><strong><?php echo $product_price; ?></strong></td>
							<td align="center" class="productListing-data" valign="top" style="border-bottom: 4px solid #d5d5d5;"><strong><?php echo $product_price_discount; ?></strong></td>
							<td align="center" class="productListing-data" valign="top" style="border-bottom: 4px solid #d5d5d5;">
								<div><?php echo tep_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'size="3" style="border:1px solid black; font-size: 14px; font-weight: bold; width: 30px; text-align:center;"') . tep_draw_hidden_field('products_id[]', $products[$i]['id']); ?></div>
								<div>&nbsp;</div>
								<div><a href="javascript:updateCart('cart');"><?php echo tep_image_button('button_update_cart.gif','update', ' style="margin-bottom:5px;"'); ?></a></div>
								<div><a href="shopping_cart.php?action=cart_remove&products_id=<?php echo $products[$i]['id'];?>"><?php echo tep_image_button('button_remove_cart.gif','update'); ?></a></div>
							</td>
							<td align="center" class="productListing-data" valign="top" style="color:#786b66;border-bottom: 4px solid #d5d5d5;"><strong><?php echo $total_price; ?></strong></td>
							<td align="center" class="productListing-data" valign="top" style="border-bottom: 4px solid #d5d5d5;"><strong><?php echo $total_price_discount; ?></strong></td>
						</tr>
<?php } ?>
        	</table>
					</form>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
      </tr>
      <tr>
        <td class="main">
					<table border="0" style="width: 100%">
						<tr>
							<td valign="top">
								<table border="0" style="width: 99%">
									<tr>
										<td class="border" style="width: 603px; height: 182px; padding-left: 5px;" valign="top">
											<div style="margin-bottom:5px;">
												<span style="color:#fe792f; font-size: 18px; font-weight:bold;">Discount Coupon(s)</span>
												<span style="color:#786b66; font-size: 14px; font-weight:bold;"> - will be deducted automatically at checkout.</span>
											</div>
											<?php if ($shoppingCartCalculatedTotals['withCoupon'] > 100) { ?>
											<div style="margin-bottom:5px;">
												<img src="/images/static/shopping_cart/truck.jpg" alt="" /><span style="color:#fe792f; font-size: 18px; font-weight:bold;">&nbsp;&nbsp;Free shipping</span>
												<span style="color:#786b66; font-size: 14px; font-weight:bold;"> - this order qualifies for FREE SHIPPING within the Continental U.S. If the order is being shipped outside the 48 Continental United States, please call us toll free at: 866-5072725 for our low shipping rates.</span>
												<?php if ($isInShowers) { ?>
												<br /><span style="color:#786b66; font-size: 12px; font-style: italic;">* Please note, the shower enclosures and shower trays do not qualify for free shipping. <strong>The shipping charges for these items are already included in the total price.</strong></span>
												<?php } ?>
											</div>
											<?php } ?>
											<?php if ($shoppingCartCalculatedTotals['withCoupon'] > 200) { ?>
											<div style="margin-bottom:5px;">
												<img src="/images/static/shopping_cart/gift.jpg" alt="" /><span style="color:#fe792f; font-size: 18px; font-weight:bold;">&nbsp;&nbsp;Free gift</span>
												<span style="color:#786b66; font-size: 14px; font-weight:bold;"> - Receive a FREE GIFT with this order!</span>
											</div>
											<?php } ?> 
										</td>
									</tr>
									<tr>
										<td style="font-size: 5px;">&nbsp;</td>
									</tr>
									<tr>
										<td class="border" style="padding: 5px;">
											<div style="float: left; width: 500px;">
												<div style="color:#fe792f; font-size: 16px; font-weight:bold;">Save Shopping Cart</div>
												<div style="color:#786b66; font-size: 14px; font-weight:bold;">This option allows you to save your chosen items and send them to your email just in case you can't do the shopping right now.</div>
											</div>
											<div style="float: right; width: 105px; padding-top:5px;" class="toggleShoppingCartContainer">
												<a href="#" class="toggleShoppingCart"><img src="/templates/ed_new/img/box/box_content/shooping_cart-button.jpg" alt="" /></a>
											</div>
										</td>
									</tr>
								</table>
							</td>
							<td valign="top">
								<table border="0" style="background-color:#d5d5d5; border: 5px solid #d5d5d5; width: 100%; height: 182px;">
									<tr>
										<td style="height:50px;">
											<table style="width:100%;">
												<tr>
													<td><strong style="font-size:14px; color:#786b66;">Total: </strong></td>
													<td align="right"><strong style="font-size:14px; color:#786b66;"><?php echo $currencies->format($cart->show_total()); ?></strong></td>
												</tr>
												<tr>
													<td><strong style="font-size:14px;">Total (with coupon): </strong></td>
													<td align="right"><strong style="font-size:14px;"><?php echo $currencies->format($shoppingCartCalculatedTotals['withCoupon']); ?></strong></td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style="background-color:#FFF; height:115px;" align="center">
											<?php 
											if (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == 'exdecor') {
												echo '<a href="javascript:updateCart_AUTH_USER(\'checkout\');">' . tep_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT) . '</a>'; 
											} else {
												echo '<a href="javascript:updateCart(\'checkout\');">' . tep_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT) . '</a>'; 
											}
                      $products1 = $products;
									    // ** GOOGLE CHECKOUT **
									    // Checks if the Google Checkout payment module has been enabled and if so 
									    // includes gcheckout.php to add the Checkout button to the page 
									    if (defined('MODULE_PAYMENT_GOOGLECHECKOUT_STATUS') &&  MODULE_PAYMENT_GOOGLECHECKOUT_STATUS == 'True') {
									      include_once('googlecheckout/gcheckout.php');
									    }
									    // ** END GOOGLE CHECKOUT **
									    ?>
											<div align="right"><div style="width: 210px; text-align: center;"><b>- Or use -</b></div></div>
											<div>
												<a href="#" class="toggleIPN"><img src="/ext/modules/payment/paypal/images/btn_express.gif" border="0" alt="" title="Checkout with PayPal" /></a>   
											</div>
											<div id="paypalIPNDialog">
												<div style="font-weight: bold; font-size: 16px;">Please clarify your shipping details:</div>
												<table cellpadding="4" cellspacing="1" class="table1" style="width: 395px;">
													<tr class="odd">
														<td>Shipping method:<span class="inputRequirement">*</span></td>
														<td><?php echo tep_draw_pull_down_menu('ipn_shipping1', $shipping_methods, 0, 'id="ipn_shipping" style="width: 220px;"'); ?></td>
													</tr>
												</table>
												
												<div class="prices_container">
													<div class="ipn_total">
														<div class="label">Subtotal: </div>
														<div class="data"><span id="ipn_amount"><?php echo $currencies->format($cart->show_total()); ?></span></div>
													</div>
													<div class="ipn_discount">
														<div class="label">Discount: <span id="ipn_discount_name"></span></div>
														<div class="data"><span id="ipn_discount_price">-<?php echo $currencies->format($shoppingCartCalculatedTotals['discount']); ?></span></div>
													</div>
													<div class="ipn_shipping_block">
														<div class="label">Shipping: <span id="ipn_shipping_name"></span></div>
														<div class="data"><span id="ipn_shipping_price"><?php echo $currencies->format($shipping_cost); ?></span></div>
													</div>
													<div class="ipn_total_final">
														<div class="label"><strong>Total (with coupon): </strong></div>
														<div class="data"><span id="ipn_total_amount" style="font-weight: bold;"><?php echo $currencies->format($shoppingCartCalculatedTotals['withCoupon'] + $shipping_cost); ?></span></div>
													</div>
													<?php if (($shoppingCartCalculatedTotals['withCoupon'] + $shipping_cost) > 200) { ?>
													<div>
														<span style="color: #999; font-style: italic; font-weight: bold;">(Free gift included)</span>
													</div>
													<?php } ?>
													<div style="clear:both;"></div>
												</div>
												<form method="post" name="paypal_form" action="https://<?php echo $_SERVER['HTTP_HOST']=='www.expressdecor.com'? 'www':'sandbox'; ?>.paypal.com/cgi-bin/webscr" id="paypal_form">
													<input type="hidden" name="cmd" value="_cart"/>
													<input type="hidden" name="upload" value="1">
													<input type="hidden" name="business" value="<?php echo ($_SERVER['HTTP_HOST']=='www.expressdecor.com'?'sales@expressdecor.com':'seller_1269376610_biz@gmail.com'); ?>"/>
													
													<input type="hidden" name="rm" value="2"/>
													<input type="hidden" name="return" value="<?php echo ($_SERVER['HTTP_HOST']=='www.expressdecor.com'?'http://www.expressdecor.com/paypal.php?action=success':'http://www.expressdecor.com/devos/paypal.php?action=success'); ?>"/>
													<input type="hidden" name="cancel_return" value="<?php echo ($_SERVER['HTTP_HOST']=='www.expressdecor.com'?'http://www.expressdecor.com/shopping_cart.php':'http://www.expressdecor.com/devos/shopping_cart.php'); ?>"/>
													<input type="hidden" name="notify_url" value="<?php echo ($_SERVER['HTTP_HOST']=='www.expressdecor.com'?'http://www.expressdecor.com/paypal.php?action=ipn':'http://www.expressdecor.com/devos/paypal.php?action=ipn'); ?>"/>
													<input type="hidden" name="custom" value="" id="ipn_form_custom"/>
													
													<?php
													$i=1;
													foreach ($products1 as $prod) {
													?>
													<input type="hidden" name="quantity_<?php echo $i; ?>" value="<?php echo $prod['quantity']; ?>"/>
													<input type="hidden" name="item_name_<?php echo $i; ?>" value="<?php echo $prod['name']; ?>"/>
													<input type="hidden" name="item_number_<?php echo $i; ?>" value="<?php echo $prod['model']; ?>"/>
													<input type="hidden" name="amount_<?php echo $i; ?>" value="<?php echo $prod['final_price_wo_coupon']; ?>" />
													<input type="hidden" name="discount_amount_<?php echo $i; ?>" value="<?php echo (($prod['final_price_wo_coupon'] - $prod['final_price'])*$prod['quantity']); ?>"/>
													<?php
														if (is_array($prod['attributes']) && count($prod['attributes'])) {
															$j = 0;
															foreach ($prod['attributes'] as $option => $option_value) {
													?>
													<input type="hidden" name="on<?php echo $j .'_'. $i; ?>" value="<?php echo $prod[$option]['products_options_name']; ?>" />
													<input type="hidden" name="os<?php echo $j .'_'. $i; ?>" value="<?php echo $prod[$option]['products_options_values_name'] . ($prod[$option]['options_values_price']>0? ' (+$'. number_format($prod[$option]['options_values_price'], 2, '.', '') .')':''); ?>" />
													<?php
																$j++;
															}
														}
														$i++;
													}
													if (($shoppingCartCalculatedTotals['withCoupon'] + $shipping_cost) > 200) {
													?>
													<input type="hidden" name="quantity_<?php echo $i; ?>" value="1"/>
													<input type="hidden" name="item_name_<?php echo $i; ?>" value="Free Gift (Included)"/>
													<input type="hidden" name="item_number_<?php echo $i; ?>" value="free-gift"/>
													<input type="hidden" name="amount_<?php echo $i; ?>" value="0" />
													<input type="hidden" name="discount_amount_<?php echo $i; ?>" value="0"/>
													<?php
													}
													?>
													<input type="hidden" name="shipping_1" value="<?php echo number_format($shipping_cost, 2, '.', ''); ?>" id="ipn_shipping_amount"/>
													<center><input type="image" title=" Checkout " alt="Checkout" src="/ext/modules/payment/paypal/images/btn_express.gif"></center>
												</form>
											</div>
											<?php
									    // ** GOOGLE CHECKOUT **
									    // Checks if the Google Checkout payment module has been enabled and if so 
									    // includes gcheckout.php to add the Checkout button to the page 
									    if (defined('MODULE_PAYMENT_GOOGLECHECKOUT_STATUS') &&  MODULE_PAYMENT_GOOGLECHECKOUT_STATUS == 'True') {
									      include_once('googlecheckout/gcheckout.php');
									    }
									    // ** END GOOGLE CHECKOUT **
									    ?>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
      </tr>
<?php
	  if (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == 'exdecor') {
?>
	  <tr>
	  	<td align="right">Discount % <input type="text" name="discount1_input" id="discount1_inputID"  value="0" size="5"></td>
	  </tr>
	  <tr>
	  	<td align="right">Discount $ <input type="text" name="discount2_input" id="discount2_inputID" value="0.00" size="5"></td>
	  </tr>
<?php
	  }
?>
    </table>
    <?php
      } else {
    ?>
		<script type="text/javascript">
			var total = 0;
			var sess_id = '<?php echo session_id(); ?>'; //fixed
			var o_st = 0; //fixed
			var o_sh = 0;
			var o_tax = 0.00;
			var discount_amount = 0;
			var cart_confirmed = false;
			var varCartID = '<?php echo $cart->cartID; ?>';
		</script>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
      	<td width="390"><img src="/templates/ed_new/img/shoppingcart-top.jpg" alt="" /></td>
        <td align="center">
        	<table cellpadding="0" cellspacing="0">
        		<tr>
        			<td align="center" style="font-size: 18px; font-weight: bold; padding-bottom: 10px;"><?php echo TEXT_CART_EMPTY; ?></td>
        		</tr>
        		<tr>
        			<td align="center"><a href="<?php echo HTTP_SERVER; ?>"><img src="/templates/ed_new/img/continue_shopping_btn.png" alt="" /></a></td>
        		</tr>
        	</table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="border-bottom: 1px solid #d5d5d5;"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
    </table>
<?php
  }
//echo '<!--'. print_r($_SESSION, true) .'-->';
?>

