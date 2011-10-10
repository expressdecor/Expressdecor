<!-- ////////////////////////////////////////////START/////////////////////////////////////////////////////////////////////// -->

<table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td vAlign="top">
<!-- customer_orders //-->
<?php
    unset($info_box_contents);
    
    echo '  <form name="cart_quantity" method="post" action="'.tep_href_link(FILENAME_SHOPPING_CART, 'action=update_product', 'SSL').'">';
	
		$category_query = tep_db_query("select pcd.categories_name, c.parent_id from " . TABLE_CATEGORIES_DESCRIPTION . " pcd, " . TABLE_CATEGORIES . " c where pcd.categories_id = c.categories_id and pcd.language_id = '" . $languages_id . "' and c.categories_id = '" . (int)$current_category_id . "'" );
		$category_fetch = tep_db_fetch_array($category_query);
		$back_id = $category_fetch['parent_id'];
?>
				<table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo $category_fetch['categories_name']; ?></td>
                <td class="dataTableHeadingContent" align="center">&nbsp;</td>
              </tr>
<?php
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by c.sort_order, cd.categories_name");
    while ($categories = tep_db_fetch_array($categories_query)) {
      $categories_count++;
      $rows++;

// Get parent_id for subcategories if search
      if (isset($HTTP_GET_VARS['search'])) $cPath= $categories['parent_id'];

      if ((!isset($HTTP_GET_VARS['cID']) && !isset($HTTP_GET_VARS['pID']) || (isset($HTTP_GET_VARS['cID']) && ($HTTP_GET_VARS['cID'] == $categories['categories_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
//        $category_childs = array('childs_count' => tep_childs_in_category_count($categories['categories_id']));
//        $category_products = array('products_count' => tep_products_in_category_count($categories['categories_id']));

        //$cInfo_array = array_merge($categories, $category_childs, $category_products);
        $cInfo_array = $categories;
        $cInfo = new objectInfo($cInfo_array);
      }

      if (isset($cInfo) && is_object($cInfo) && ($categories['categories_id'] == $cInfo->categories_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('admin_list_products.php', tep_get_path($categories['categories_id']), 'SSL') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('admin_list_products.php', 'cPath=' . $cPath . '&cID=' . $categories['categories_id'], 'SSL') . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link('admin_list_products.php', tep_get_path($categories['categories_id']), 'SSL') . '">' . tep_image('expressadmin/'.DIR_WS_ICONS . 'folder.gif', ICON_FOLDER) . '</a>&nbsp;<b>' . $categories['categories_name'] . '</b>'; ?></td>
                <td class="dataTableContent" align="center">&nbsp;</td>
              </tr>
<?php
    }	  

   $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by pd.products_name");
    while ($products = tep_db_fetch_array($products_query)) {
      $products_count++;
      $rows++;

// Get categories_id for product if search

      if ( (!isset($HTTP_GET_VARS['pID']) && !isset($HTTP_GET_VARS['cID']) || (isset($HTTP_GET_VARS['pID']) && ($HTTP_GET_VARS['pID'] == $products['products_id']))) && !isset($pInfo) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
// find out the rating average from customer reviews
        //$pInfo_array = array_merge($products, $reviews);
        $pInfo_array = $products;
        $pInfo = new objectInfo($pInfo_array);
      }

      if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" >' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link('admin_list_products.php', 'cPath=' . $cPath . '&pID=' . $products['products_id'] . '&action=new_product_preview&read=only', 'SSL') . '">' . tep_image('expressadmin/'.DIR_WS_ICONS . 'preview.gif', 'preview') . '</a>&nbsp;' . $products['products_name']; ?></td>
                <td class="dataTableContent" align="center">
<?php				
 										echo '<input type="hidden" name="products_id[]" value="' . $products['products_id'] . '"><input type="text" name="cart_quantity[]" value="' . $products_quantity[$products['products_id']] . '" size="4">';
?>						 
								</td>
              </tr>
<?php
    }
?>			  
							<tr>
								<td colspan="3" align="left">
									<?php echo '<a href="' . tep_href_link('admin_list_products.php', $cPath_back . 'cID=' . $back_id, 'SSL') . '">' . tep_image('expressadmin/includes/languages/english/images/buttons/button_back.gif') . '</a>';?>
								</td>
							</tr>
							<tr>
								<td colspan="2" align="right"><?php echo tep_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART); ?></td>
							</tr>
						</table></td>
					</tr>
				</table>
			</form>
<!-- customer_orders_eof //-->
				</td>
      </tr>
    </table>
    
<!-- //////////////////////////////////////////////////////END///////////////////////////////////////////////////////////// -->