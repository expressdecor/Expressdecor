<?php echo tep_draw_form('cart_quantity_vd', tep_href_link(FILENAME_VALUEDEALS_PROCESS, tep_get_all_get_params(array('action')) . 'action=add_vd')); ?>
<table border="0" width="100%" cellspacing="0" cellpadding="0" style="padding-right:4px">

<?php
if (!isset($_POST['valuedeals_id'])) {
  echo "Error. No Valuedeals Selected";
} else {
  $valuedeals_query_raw = "select vd_id, vd_name, vd_price
  							from ".TABLE_VALUEDEALS."
							where vd_id = ".$_POST['valuedeals_id'];
  $valuedeals_query = tep_db_query($valuedeals_query_raw);
  $valuedeals = tep_db_fetch_array($valuedeals_query);
  echo '<div class="pageHeading" style="padding-left:5px">'.HEADING_TEXT.'</div>';
  foreach($_POST as $key => $value) {
    if (strpos($key, 'ProductID') !== false) {
	  
      $product_info_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, p.products_msrp, p.products_image_med, p.products_image_lrg, p.products_image_sm_1, p.products_image_xl_1, p.products_image_sm_2, p.products_image_xl_2, p.products_image_sm_3, p.products_image_xl_3, p.products_image_sm_4, p.products_image_xl_4, p.products_image_sm_5, p.products_image_xl_5, p.products_image_sm_6, p.products_image_xl_6, products_image_sm_7, products_image_xl_7, products_image_sm_8, products_image_xl_8, products_image_sm_9, products_image_xl_9, products_image_sm_10, products_image_xl_10, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . $value . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
      $product_info = tep_db_fetch_array($product_info_query);

      tep_db_query("update " . TABLE_PRODUCTS_DESCRIPTION . " set products_viewed = products_viewed+1 where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and language_id = '" . (int)$languages_id . "'");

      $products_price = '<table class="PriceList" border="0" width="100%" cellspacing="0" cellpadding="0">';
	  $new_price = tep_get_products_special_price($product_info['products_id']);
	  if ($product_info['products_msrp'] > $product_info['products_price'])
	    $products_price .= '<tr><td>' . TEXT_PRODUCTS_MSRP . '</td><td class="oldPrice" align=right>' . $currencies->display_price($product_info['products_msrp'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</td></tr>';
	  $products_price .= '<tr><td>' . TEXT_PRODUCTS_OUR_PRICE . '</td>';
	  if ($new_price != '')
	    {$products_price .= '<td class="oldPrice"';}
	  else
	    {$products_price .= '<td';}
	  $products_price .= ' align=right>' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</td></tr>';
	  if ($new_price != '')
	    {$products_price .= '<tr class="productSpecialPrice"><td>' . TEXT_PRODUCTS_SALE . '</td><td align=right>' . $currencies->display_price($new_price, tep_get_tax_rate($product_info['products_tax_class_id'])) . '</td></tr>';}
	  if ($product_info['products_msrp'] > $product_info['products_price'])
	    {if ($new_price != '')
	      {$products_price .= '<tr><td>' . TEXT_PRODUCTS_SAVINGS . '</td><td align=right>' . $currencies->display_price(($product_info['products_msrp'] -  $new_price), tep_get_tax_rate($product_info['products_tax_class_id'])) . '</td></tr>';}
	    else
	      {$products_price .= '<tr><td>' . TEXT_PRODUCTS_SAVINGS . '</td><td align=right>' . $currencies->display_price(($product_info['products_msrp'] -  $product_info['products_price']), tep_get_tax_rate($product_info['products_tax_class_id'])) . '</td></tr>';}}
	  else
	    {if ($new_price != '')
	      {$products_price .= '<tr><td>' . TEXT_PRODUCTS_SAVINGS . '</td><td align=right>' . $currencies->display_price(($product_info['products_price'] -  $new_price), tep_get_tax_rate($product_info['products_tax_class_id'])) . '</td></tr>';}}
	  $products_price .= '</table>';
      $products_name = $product_info['products_name'];
	  
	echo tep_draw_input_field("ProductID_".$value, $value, '', 'Hidden');
?>
    <table>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td valign="top" class="tableHeading" style="padding-left:5px"><?php echo $products_name; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="main" style="padding-left:5px">
          <table border="0" cellspacing="0" cellpadding="2" align="left">
            <tr>
              <td align="left" class="smallText" width="30%">
<?php
      if (tep_not_null($product_info['products_image'])) {
?>
          <table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td align="center" class="smallText">
<!-- // BOF MaxiDVD: Modified For Ultimate Images Pack! //-->
<?php
      $new_image = $product_info['products_image'];
?>
<script type="text/javascript"><!--
      document.write('<?php echo '<a href="javascript:popupWindow(\\\'' . tep_href_link(FILENAME_POPUP_IMAGE, 'pID=' . $product_info['products_id']) . '\\\')">' . tep_image1(DIR_WS_IMAGES . $new_image, addslashes($product_info['products_name']), 100) . '<br></a>'; ?>');
//--></script>
	<?php if (tep_not_null($product_info['products_model'])) {
		  echo '<span style="font-weight:bold;color:black;font-size:10px;font-family:Tahoma">&nbsp;Model Number: '.$product_info['products_model'].'</span><br><br>';
	  } ?>
<script type="text/javascript"><!--
      document.write('<?php// echo '<a href="javascript:popupWindow(\\\'' . tep_href_link(FILENAME_POPUP_IMAGE, 'pID=' . $product_info['products_id']) . '\\\')">' . tep_image_button('image_enlarge.gif', TEXT_CLICK_TO_ENLARGE) . '</a>'; ?>');
//--></script>
<noscript>
      <?php echo '<a href="' . tep_href_link(DIR_WS_IMAGES . $product_info['products_image_med']) . '">' . tep_image(DIR_WS_IMAGES . $new_image, addslashes($product_info['products_name']), $image_width, $image_height, 'hspace="5" vspace="5"') . '<br><br>' . tep_image_button('image_enlarge.gif', TEXT_CLICK_TO_ENLARGE) . '</a>'; ?>
</noscript>
<!-- // EOF MaxiDVD: Modified For Ultimate Images Pack! //-->
              </td>
            </tr>
          </table>
		</td>
		<td valign="top">
		<?php echo $products_price; ?>
<?php
    }
?>
<?php      if (STOCK_CHECK == 'true') {
        $stock_check = tep_check_stock($product_info['products_id'], 1);
        if (!tep_not_null($stock_check)) {
			echo '<div align="justify" style="padding-left:5px; font-size:11px; font-family:tahoma;font-color:">In stock. This product normally leaves our warehouse within 1-4 business days</div>';
        }
      }
	?>
<?php
    $products_attributes_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$HTTP_GET_VARS['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "'");
    $products_attributes = tep_db_fetch_array($products_attributes_query);
    if ($products_attributes['total'] > 0) {
?>
          <table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td class="main" colspan="2"><?php echo TEXT_PRODUCT_OPTIONS; ?></td>
            </tr>
<?php
			//clr 030714 update query to pull option_type
      $products_options_name_query = tep_db_query("select distinct popt.products_options_id, popt.products_options_name, popt.products_options_type, popt.products_options_length, popt.products_options_comment from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$HTTP_GET_VARS['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "' order by popt.products_options_name");
      while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
				//clr 030714 add case statement to check option type
        switch ($products_options_name['products_options_type']) {
          case PRODUCTS_OPTIONS_TYPE_TEXT:
            //CLR 030714 Add logic for text option
            $products_attribs_query = tep_db_query("select distinct patrib.options_values_price, patrib.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$HTTP_GET_VARS['products_id'] . "' and patrib.options_id = '" . $products_options_name['products_options_id'] . "'");
            $products_attribs_array = tep_db_fetch_array($products_attribs_query);
            $tmp_html = '<input type="text" name ="id[' . TEXT_PREFIX . $products_options_name['products_options_id'] . ']" size="' . $products_options_name['products_options_length'] .'" maxlength="' . $products_options_name['products_options_length'] . '" value="' . $cart->contents[$HTTP_GET_VARS['products_id']]['attributes_values'][$products_options_name['products_options_id']] .'">  ' . $products_options_name['products_options_comment'] ;
            if ($products_attribs_array['options_values_price'] != '0') {
              $tmp_html .= '(' . $products_attribs_array['price_prefix'] . $currencies->display_price($products_attribs_array['options_values_price'], $product_info_values['products_tax_class_id']) .')';
            }
?>
            <tr>
              <td class="main"><?php echo $products_options_name['products_options_name'] . ':'; ?></td>
              <td class="main"><?php echo $tmp_html;  ?></td>
            </tr>
<?php
            break;
          case PRODUCTS_OPTIONS_TYPE_RADIO:
            //CLR 030714 Add logic for radio buttons
            $tmp_html = '<table>';
            $products_options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pa.options_id = '" . $products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . $languages_id . "'");
            $checked = true;
            while ($products_options_array = tep_db_fetch_array($products_options_query)) {
              $tmp_html .= '<tr><td class="main">';
              $tmp_html .= tep_draw_radio_field('id[' . $products_options_name['products_options_id'] . ']', $products_options_array['products_options_values_id'], $checked);
              $checked = false;
              $tmp_html .= $products_options_array['products_options_values_name'] ;
              $tmp_html .=$products_options_name['products_options_comment'] ;
              if ($products_options_array['options_values_price'] != '0') {
                $tmp_html .= '(' . $products_options_array['price_prefix'] . $currencies->display_price($products_options_array['options_values_price'], $product_info_values['products_tax_class_id']) .')&nbsp';
              }
              $tmp_html .= '</tr></td>';
            }
            $tmp_html .= '</table>';
?>
            <tr>
              <td class="main"><?php echo $products_options_name['products_options_name'] . ':'; ?></td>
              <td class="main"><?php echo $tmp_html;  ?></td>
            </tr>
<?php
            break;
          case PRODUCTS_OPTIONS_TYPE_CHECKBOX:
            //CLR 030714 Add logic for checkboxes
            $products_attribs_query = tep_db_query("select distinct patrib.options_values_id, patrib.options_values_price, patrib.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$HTTP_GET_VARS['products_id'] . "' and patrib.options_id = '" . $products_options_name['products_options_id'] . "'");
            $products_attribs_array = tep_db_fetch_array($products_attribs_query);
            echo '<tr><td class="main">' . $products_options_name['products_options_name'] . ': </td><td class="main">';
            echo tep_draw_checkbox_field('id[' . $products_options_name['products_options_id'] . ']', $products_attribs_array['options_values_id']);
            echo $products_options_name['products_options_comment'] ;
            if ($products_attribs_array['options_values_price'] != '0') {
              echo '(' . $products_attribs_array['price_prefix'] . $currencies->display_price($products_attribs_array['options_values_price'], $product_info_values['products_tax_class_id']) .')&nbsp';
            }
            echo '</td></tr>';
            break;
          default:
            //clr 030714 default is select list
            //clr 030714 reset selected_attribute variable
            $selected_attribute = false;
        		$products_options_array = array();
// BOF: WebMakers.com Added: Attributes Copy and Sort
  if ( PRODUCTS_OPTIONS_SORT_BY_PRICE !='1' ) {
        $products_options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "'" . " order by pa.products_options_sort_order, pov.products_options_values_name");
  } else {
        $products_options_query = tep_db_query("select pa.products_options_sort_order, pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "'" . " order by pa.products_options_sort_order, pa.options_values_price");
  }
// EOF: WebMakers.com Added: Attributes Copy and Sort
        		while ($products_options = tep_db_fetch_array($products_options_query)) {
          		$products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name']);
          		if ($products_options['options_values_price'] != '0') {
            		$products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . $products_options['price_prefix'] . $currencies->display_price($products_options['options_values_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) .') ';
          		}
        		}

        		if (isset($cart->contents[$HTTP_GET_VARS['products_id']]['attributes'][$products_options_name['products_options_id']])) {
          		$selected_attribute = $cart->contents[$HTTP_GET_VARS['products_id']]['attributes'][$products_options_name['products_options_id']];
        		} else {
          		$selected_attribute = false;
        		}
?>
            <tr>
              <td class="main"><?php echo $products_options_name['products_options_name'] . ':'; ?></td>
              <td class="main"><?php echo tep_draw_pull_down_menu('id[' . $products_options_name['products_options_id'] . ']', $products_options_array, $selected_attribute) . $products_options_name['products_options_comment'];  ?></td>
            </tr>
<?php
        }  //clr 030714 end switch
      }
?>
          </table>
<?php
    }
?>
		  </tr>
		</table>
		</td>
      </tr>
	</table>
<?
	}
  }
  echo '<div align="right" class="PriceList" style="padding-right:5px">Value Deal Price: '.$currencies->format($valuedeals['vd_price']).'</div>';
  echo tep_draw_separator('pixel_trans.gif', '100%', '10');
  echo '<div align="right" style="padding-right:5px">'.TEXT_NOTE.'</div>';
  echo tep_draw_separator('pixel_trans.gif', '100%', '10');
  echo tep_draw_hidden_field('vd_id', $valuedeals['vd_id']);
  echo '<div align="right" style="padding-right:5px">'.tep_image_submit('button_checkout.gif').'</div>';
}
?>
</form>