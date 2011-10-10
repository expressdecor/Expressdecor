<script type="text/javascript"><!--
function rowOverEffect(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow';
}
//--></script>
<table cellpadding="0" cellspacing="0" bgcolor="White" width="800px" style="height:200px;">
	<tr>
		<td width="17" height="17"><img src="/images/lt.gif"></td>
		<td width="100%"><img src="/images/x.gif"></td>
		<td width="17" height="17"><img src="/images/rt.gif"></td>
	</tr>
	<tr>
		<td width="17"><img src="/images/x.gif"></td>
		<td class="main"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php // echo tep_image(DIR_WS_IMAGES . 'money.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>
          <table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
            </tr>
<?php
  $points_query = tep_db_query("SELECT customers_shopping_points, customers_points_expires FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '" . (int)$customer_id . "' AND customers_points_expires > CURDATE()");
  $points = tep_db_fetch_array($points_query);
    if (tep_db_num_rows($points_query)) {
?>
              <td class="main"><?php echo sprintf(MY_POINTS_CURRENT_BALANCE, number_format($points['customers_shopping_points'],POINTS_DECIMAL_PLACES),$currencies->format(tep_calc_shopping_pvalue($points['customers_shopping_points']))); ?></td>
              <td class="main" align="right"><?php echo '<b>' . MY_POINTS_EXPIRE . '</b> ' . tep_date_short($points['customers_points_expires']); ?></td>
<?php
  } else {
         echo'<td class="main"><b>' . TEXT_NO_POINTS . '</b></td>';
  }
?>
            </tr>
          </table>
<?php
    $pending_points_query = "SELECT unique_id, orders_id, points_pending, points_comment, date_added, points_status, points_type from " . TABLE_CUSTOMERS_POINTS_PENDING . " WHERE customer_id = '" . (int)$customer_id . "' ORDER BY unique_id DESC";
    $pending_points_split = new splitPageResults($pending_points_query, MAX_DISPLAY_POINTS_RECORD);
    $pending_points_query = tep_db_query($pending_points_split->sql_query);

    if (tep_db_num_rows($pending_points_query)) {
?>
          <table border="0" width="100%" cellspacing="1" cellpadding="2" class="productListing-heading">
            <tr class="productListing-heading">
              <td class="productListing-heading"width="13%"><?php echo HEADING_ORDER_DATE; ?></td>
              <td class="productListing-heading"width="25%"><?php echo HEADING_ORDERS_NUMBER; ?></td>
              <td class="productListing-heading" width="35%"><?php echo HEADING_POINTS_COMMENT; ?></td>
              <td class="productListing-heading"><?php echo HEADING_POINTS_STATUS; ?></td>
              <td class="productListing-heading" align="right"><?php echo HEADING_POINTS_TOTAL; ?></td>
            </tr>
          </table>
          <table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
            <tr class="infoBoxContents">
              <td><table border="0" width="100%" cellspacing="1" cellpadding="2">
                <tr>
<?php
    while ($pending_points = tep_db_fetch_array($pending_points_query)) {
      $orders_status_query = tep_db_query("SELECT o.orders_id, o.orders_status, s.orders_status_name FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATUS . " s WHERE o.customers_id = '" . (int)$customer_id . "' AND o.orders_id = '" . $pending_points['orders_id'] . "' AND o.orders_status = s.orders_status_id AND s.language_id = '" . (int)$languages_id . "'");
      $orders_status = tep_db_fetch_array($orders_status_query);

	  if ($pending_points['points_status'] == 1) $points_status_name = TEXT_POINTS_PENDING;
	  if ($pending_points['points_status'] == 2) $points_status_name = TEXT_POINTS_CONFIRMED;
	  if ($pending_points['points_status'] == 3) $points_status_name = '<font color="FF0000">' . TEXT_POINTS_CANCELLED . '</font>';
	  if ($pending_points['points_status'] == 4) $points_status_name = '<font color="0000FF">' . TEXT_POINTS_REDEEMED . '</font>';
		  
	  if ($orders_status['orders_status'] == 2 && $pending_points['points_status'] == 1 || $orders_status['orders_status'] == 3 && $pending_points['points_status'] == 1) {
		$points_status_name = TEXT_POINTS_PROCESSING;
	  }
		
	  if (($pending_points['points_type'] == SP) && ($pending_points['points_comment'] == 'TEXT_DEFAULT_COMMENT')) {
		$pending_points['points_comment'] = TEXT_DEFAULT_COMMENT;
	  }
		if($pending_points['points_comment'] == 'TEXT_DEFAULT_REDEEMED') {
		   $pending_points['points_comment'] = TEXT_DEFAULT_REDEEMED;
	  }
	  if ($pending_points['points_type'] == RF) {
        $referred_name_query = tep_db_query("SELECT customers_name FROM " . TABLE_ORDERS . " WHERE orders_id = '" . $pending_points['orders_id'] . "' LIMIT 1");
        $referred_name = tep_db_fetch_array($referred_name_query);
		if ($pending_points['points_comment'] == 'TEXT_DEFAULT_REFERRAL') {
		  $pending_points['points_comment'] = TEXT_DEFAULT_REFERRAL;
	    }
	  }
	  if (($pending_points['points_type'] == RV) && ($pending_points['points_comment'] == 'TEXT_DEFAULT_REVIEWS')) {
		$pending_points['points_comment'] = TEXT_DEFAULT_REVIEWS;
	  }
	  if (($pending_points['orders_id'] > 0) && (($pending_points['points_type'] == SP)||($pending_points['points_type'] == RD))) {
?>
        <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?php echo tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $pending_points['orders_id'], 'SSL'); ?>'" title="<?php echo TEXT_ORDER_HISTORY .'&nbsp;' . $pending_points['orders_id']; ?>">
<?php
	  }
	  if ($pending_points['points_type'] == RV) {
?>
        <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?php echo tep_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $pending_points['orders_id'], 'NONSSL'); ?>'" title="<?php echo TEXT_REVIEW_HISTORY; ?>">
<?php
	  }
	  if (($pending_points['orders_id'] == 0) || ($pending_points['points_type'] == RF) || ($pending_points['points_type'] == RV)) {
		$orders_status['orders_status_name'] = '<font color="ff0000">' . TEXT_STATUS_ADMINISTATION . '</font>';
		$pending_points['orders_id'] = '<font color="ff0000">' . TEXT_ORDER_ADMINISTATION . '</font>';
	  }
?>
                  <td class="productListing-data"width="13%"><?php echo tep_date_short($pending_points['date_added']); ?></td>
                  <td class="productListing-data"width="25%"><?php echo '#' . $pending_points['orders_id'] . '&nbsp;&nbsp;' . $orders_status['orders_status_name']; ?></td>                    
                  <td class="productListing-data" width="35%"><?php echo  $pending_points['points_comment'] .'&nbsp;' . $referred_name['customers_name']; ?></td>                    
                  <td class="productListing-data"><?php echo  $points_status_name; ?></td>                    
                  <td class="productListing-data" align="right"><?php echo number_format($pending_points['points_pending'],POINTS_DECIMAL_PLACES); ?></td>                    
                </tr>
<?php
   }
?>
              </table></td>
            </tr>
          </table>
          <table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="smallText" valign="top"><?php echo $pending_points_split->display_count(TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
            <td class="smallText" align="right"><?php echo TEXT_RESULT_PAGE . ' ' . $pending_points_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
		        <td class="main"><a href="javascript:history.go(-1)"><?php echo tep_image_button('button_back.gif', IMAGE_BUTTON_BACK); ?></a></td>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
	</tr>
	<tr><td height="100%"><img src="/images/x.gif"></td></tr>
	<tr>
		<td width="17" height="17"><img src="/images/lb.gif"></td>
		<td width="100%"><img src="/images/x.gif"></td>
		<td width="17" height="17"><img src="/images/rb.gif"></td>
	</tr>
</table>