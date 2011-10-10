     <table border="0" width="100%" cellspacing="0" cellpadding="<?php echo CELLPADDING_SUB; ?>"> 
      <tr> 
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_specials.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
          <tr>
            <td class="main" colspan=2><?php echo TEXT_INFORMATION; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="infoBoxHeading" align="center"><?php echo TEXT_AFFILIATE_INDIVIDUAL_BANNER . ' ' . $affiliate_banners['affiliate_banners_title']; ?></td>
          </tr>
          <tr>
            <td class="smallText" align="center"><?php echo TEXT_AFFILIATE_INDIVIDUAL_BANNER_INFO . tep_draw_form('individual_banner', tep_href_link(FILENAME_AFFILIATE_BANNERS) ) . "\n" . tep_draw_input_field('individual_banner_id', '', 'size="5"') . "&nbsp;&nbsp;" . tep_image_submit('button_affiliate_build_a_link.gif', IMAGE_BUTTON_BUILD_A_LINK); ?></form></td>
          </tr>
<?php
  if (tep_not_null($HTTP_POST_VARS['individual_banner_id']) || tep_not_null($HTTP_GET_VARS['individual_banner_id'])) {

    if (tep_not_null($HTTP_POST_VARS['individual_banner_id'])) $individual_banner_id = $HTTP_POST_VARS['individual_banner_id'];
    if ($HTTP_GET_VARS['individual_banner_id']) $individual_banner_id = $HTTP_GET_VARS['individual_banner_id'];
    $affiliate_pbanners_values = tep_db_query("select p.products_image, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . $individual_banner_id . "' and pd.products_id = '" . $individual_banner_id . "' and p.products_status = '1' and pd.language_id = '" . $languages_id . "'");
    if ($affiliate_pbanners = tep_db_fetch_array($affiliate_pbanners_values)) {
      switch (AFFILIATE_KIND_OF_BANNERS) {
        case 1:
          $link = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . FILENAME_PRODUCT_INFO . '?ref=' . $affiliate_id . '&products_id=' . $individual_banner_id . '&affiliate_banner_id=1" class="target-blank"><img src="' . HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES . $affiliate_pbanners['affiliate_banners_image'] . '" border="0" alt="' . $affiliate_pbanners['products_name'] . '"></a>';
          break;
        case 2: // Link to Products
          $link = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . FILENAME_PRODUCT_INFO . '?ref=' . $affiliate_id . '&products_id=' . $individual_banner_id . '&affiliate_banner_id=1" class="target-blank"><img src="' . HTTP_SERVER . DIR_WS_CATALOG . FILENAME_AFFILIATE_SHOW_BANNER . '?ref=' . $affiliate_id . '&affiliate_pbanner_id=' . $individual_banner_id . '" border="0" alt="' . $affiliate_pbanners['products_name'] . '"></a>';
          break;
      }
    }
?>
          <tr>
            <td class="smallText" align="center"><br><?php echo $link; ?></td>
          </tr>
          <tr>
            <td class="smallText" align="center"><?php echo TEXT_AFFILIATE_INFO; ?></td>
          </tr>
          <tr>
            <td align="center">
            	<?php 
//            	   $link = htmlspecialchars($link, ENT_QUOTES);
                 echo tep_draw_textarea_field('affiliate_banner', 'soft', '60', '6', $link); 
              ?>
            </td>
          </tr>
<?php
  }
?>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '20'); ?></td>
      </tr>
<?php
  if (tep_db_num_rows($affiliate_banners_values)) {

    while ($affiliate_banners = tep_db_fetch_array($affiliate_banners_values)) {
      $affiliate_products_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . $affiliate_banners['affiliate_products_id'] . "' and language_id = '" . $languages_id . "'");
      $affiliate_products = tep_db_fetch_array($affiliate_products_query);
      $prod_id = $affiliate_banners['affiliate_products_id'];
      $ban_id = $affiliate_banners['affiliate_banners_id'];
      switch (AFFILIATE_KIND_OF_BANNERS) {
        case 1: // Link to Products
          if ($prod_id > 0) {
            $link = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . FILENAME_PRODUCT_INFO . '?ref=' . $affiliate_id . '&products_id=' . $prod_id . '&affiliate_banner_id=' . $ban_id . '" class="target-blank"><img src="' . HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES . $affiliate_banners['affiliate_banners_image'] . '" border="0" alt="' . $affiliate_products['products_name'] . '"></a>';
          } else { // generic_link
            $link = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . FILENAME_DEFAULT . '?ref=' . $affiliate_id . '&affiliate_banner_id=' . $ban_id . '" class="target-blank"><img src="' . HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES . $affiliate_banners['affiliate_banners_image'] . '" border="0" alt="' . $affiliate_banners['affiliate_banners_title'] . '"></a>';
          }
          break;
        case 2: // Link to Products
          if ($prod_id > 0) {
            $link = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . FILENAME_PRODUCT_INFO . '?ref=' . $affiliate_id . '&products_id=' . $prod_id . '&affiliate_banner_id=' . $ban_id . '" class="target-blank"><img src="' . HTTP_SERVER . DIR_WS_CATALOG . FILENAME_AFFILIATE_SHOW_BANNER . '?ref=' . $affiliate_id . '&affiliate_banner_id=' . $ban_id . '" border="0" alt="' . $affiliate_products['products_name'] . '"></a>';
          } else { // generic_link
            $link = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . FILENAME_DEFAULT . '?ref=' . $affiliate_id . '&affiliate_banner_id=' . $ban_id . '" class="target-blank"><img src="' . HTTP_SERVER . DIR_WS_CATALOG . FILENAME_AFFILIATE_SHOW_BANNER . '?ref=' . $affiliate_id . '&affiliate_banner_id=' . $ban_id . '" border="0" alt="' . $affiliate_banners['affiliate_banners_title'] . '"></a>';
          }
          break;
      }
?>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="infoBoxHeading" align="center"><?php echo TEXT_AFFILIATE_NAME . ' ' . $affiliate_banners['affiliate_banners_title']; ?></td>
          </tr>
          <tr>
            <td class="smallText" align="center"><br><?php echo $link; ?></td>
          </tr>
          <tr>
            <td class="smallText" align="center"><?php echo TEXT_AFFILIATE_INFO; ?></td>
          </tr>
          <tr>
            <td class="smallText" align="center">
              <?php 
//                $link = htmlspecialchars($link, ENT_QUOTES);
                echo tep_draw_textarea_field('affiliate_banner', 'soft', '60', '6', $link); 
              ?>
            </td>
          </tr>
        </table></td>
      </tr>	
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '20'); ?></td>
      </tr>
<?php
    }
  }
?>
    </table>