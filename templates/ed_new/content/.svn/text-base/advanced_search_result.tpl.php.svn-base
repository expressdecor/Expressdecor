    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE_2; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_browse.gif', HEADING_TITLE_2, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
<?php
// create column list
$define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW);

asort($define_list);

$column_list = array();
reset($define_list);
while (list($key, $value) = each($define_list)) {
	if ($value > 0) $column_list[] = $key;
}

// BOF Separate Pricing Per Customer
if(!tep_session_is_registered('sppc_customer_group_id')) {
	$customer_group_id = '0';
} else {
	$customer_group_id = $sppc_customer_group_id;
}
// EOF Separate Pricing Per Customer
$select_column_list = 'p.products_model, p.products_image, pg.page_name, ';

// BOF Separate Pricing Per Customer
$status_tmp_product_prices_table = false;
$status_need_to_get_prices = true;

if ( (!isset($_GET['sort'])) || (!ereg('[1-8][ad]', $_GET['sort']))) {
	$_GET['sort'] = '2a';
}

if ((tep_not_null($pfrom) || tep_not_null($pto) || $status_need_to_get_prices == true) && $customer_group_id != '0') {
	$product_prices_table = TABLE_PRODUCTS_GROUP_PRICES.$customer_group_id;
	// the table with product prices for a particular customer group is re-built only a number of times per hour
	// (setting in /includes/database_tables.php called MAXIMUM_DELAY_UPDATE_PG_PRICES_TABLE, in minutes)
	// to trigger the update the next function is called (new function that should have been
	// added to includes/functions/database.php)
	tep_db_check_age_products_group_prices_cg_table($customer_group_id);
	$status_tmp_product_prices_table = true;
} elseif ((tep_not_null($pfrom) || tep_not_null($pto) || $status_need_to_get_prices == true) && $customer_group_id == '0') {
	// to be able to sort on retail prices we *need* to get the special prices instead of leaving them
	// NULL and do product_listing the job of getting the special price
	// first make sure that table exists and needs no updating
	tep_db_check_age_specials_retail_table();
	$status_tmp_special_prices_table = true;
} // end elseif ((tep_not_null($pfrom) || (tep_not_null($pfrom)) && ....

if ($status_tmp_product_prices_table == true) {
	$select_str = "select distinct " . $select_column_list . " m.manufacturers_id, p.products_id, pd.products_name, tmp_pp.products_price, p.products_tax_class_id, if(tmp_pp.status, tmp_pp.specials_new_products_price, NULL) as specials_new_products_price, IF(tmp_pp.status, tmp_pp.specials_new_products_price, tmp_pp.products_price) as final_price ";
} elseif ($status_tmp_special_prices_table == true) {
	$select_str = "select distinct " . $select_column_list . " m.manufacturers_id, p.products_id, pd.products_name, IFNULL(s.specials_new_products_price, p.products_price) AS products_price, p.products_tax_class_id, if(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, if(s.status, s.specials_new_products_price, p.products_price) as final_price ";
} else {
	$select_str = "select distinct " . $select_column_list . " m.manufacturers_id, p.products_id, pd.products_name, IFNULL(s.specials_new_products_price, p.products_price) AS products_price, p.products_tax_class_id ";
}
// next line original select query
// $select_str = "select distinct " . $select_column_list . " m.manufacturers_id, p.products_id, pd.products_name, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price ";

if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
	$select_str .= ", SUM(tr.tax_rate) as tax_rate ";
}

$select_str .= ", GROUP_CONCAT(p2c.categories_id SEPARATOR ',') as categoriesList ";

// LINES CHANGED: MS2 update 501112
// Moved to below: " . TABLE_PRODUCTS_DESCRIPTION . " pd
// and : TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
// $from_str = "from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c";
if ($status_tmp_product_prices_table == true) {
	$from_str = "from " . TABLE_PRODUCTS . " p inner join page pg on p.page_id=pg.page_id left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id) left join " . $product_prices_table . " as tmp_pp using(products_id)";
} elseif ($status_tmp_special_prices_table == true) {
	$from_str = "from " . TABLE_PRODUCTS . " p inner join page pg on p.page_id=pg.page_id left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id) left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id";
} else {
	$from_str = "from " . TABLE_PRODUCTS . " p inner join page pg on p.page_id=pg.page_id left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id) left join specials s on p.products_id = s.products_id";
}
// EOF Separate Pricing Per Customer
if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
	if (!tep_session_is_registered('customer_country_id')) {
		$customer_country_id = STORE_COUNTRY;
		$customer_zone_id = STORE_ZONE;
	}
	$from_str .= " left join " . TABLE_TAX_RATES . " tr on p.products_tax_class_id = tr.tax_class_id left join " . TABLE_ZONES_TO_GEO_ZONES . " gz on tr.tax_zone_id = gz.geo_zone_id and (gz.zone_country_id is null or gz.zone_country_id = '0' or gz.zone_country_id = '" . (int)$customer_country_id . "') and (gz.zone_id is null or gz.zone_id = '0' or gz.zone_id = '" . (int)$customer_zone_id . "')";
}
// LINE ADDED: MS2 update 501112
$from_str .= ", " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c";

$where_str = " where p.products_status = '1' and c.categories_status = 1 and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id ";

if (isset($HTTP_GET_VARS['categories_id']) && tep_not_null($HTTP_GET_VARS['categories_id'])) {
	if (isset($HTTP_GET_VARS['inc_subcat']) && ($HTTP_GET_VARS['inc_subcat'] == '1')) {
		$subcategories_array = array();
		tep_get_subcategories($subcategories_array, $HTTP_GET_VARS['categories_id']);

		$where_str .= " and p2c.products_id = p.products_id and p2c.products_id = pd.products_id and (p2c.categories_id = '" . (int)$HTTP_GET_VARS['categories_id'] . "'";

		for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
			$where_str .= " or p2c.categories_id = '" . (int)$subcategories_array[$i] . "'";
		}

		$where_str .= ")";
	} else {
		$where_str .= " and p2c.products_id = p.products_id and p2c.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$HTTP_GET_VARS['categories_id'] . "'";
	}
}

if (isset($HTTP_GET_VARS['manufacturers_id']) && tep_not_null($HTTP_GET_VARS['manufacturers_id'])) {
	$where_str .= " and m.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "'";
}

if (isset($search_keywords) && (sizeof($search_keywords) > 0)) {
	$where_str .= " and (";
	for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
		switch ($search_keywords[$i]) {
			case '(':
			case ')':
			case 'and':
			case 'or':
				$where_str .= " " . $search_keywords[$i] . " ";
				break;
			default:
				$keyword = tep_db_prepare_input($search_keywords[$i]);
				$where_str .= "(pd.products_name like '%" . tep_db_input($keyword) . "%' or p.products_model like '%" . tep_db_input($keyword) . "%' or m.manufacturers_name like '%" . tep_db_input($keyword) . "%'";
				if (isset($HTTP_GET_VARS['search_in_description']) && ($HTTP_GET_VARS['search_in_description'] == '1')) $where_str .= " or pd.products_description like '%" . tep_db_input($keyword) . "%'";
				$where_str .= ')';
				break;
		}
	}
	$where_str .= " )";
}

if (tep_not_null($dfrom)) {
	$where_str .= " and p.products_date_added >= '" . tep_date_raw($dfrom) . "'";
}

if (tep_not_null($dto)) {
	$where_str .= " and p.products_date_added <= '" . tep_date_raw($dto) . "'";
}

if (tep_not_null($pfrom)) {
	if ($currencies->is_set($currency)) {
		$rate = $currencies->get_value($currency);

		$pfrom = $pfrom / $rate;
	}
}

if (tep_not_null($pto)) {
	if (isset($rate)) {
		$pto = $pto / $rate;
	}
}
// BOF Separate Pricing Per Customer
if ($status_tmp_product_prices_table == true) {
	if (DISPLAY_PRICE_WITH_TAX == 'true') {
		if ($pfrom > 0) $where_str .= " and (IF(tmp_pp.status, tmp_pp.specials_new_products_price, tmp_pp.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) >= " . (double)$pfrom . ")";
		if ($pto > 0) $where_str .= " and (IF(tmp_pp.status, tmp_pp.specials_new_products_price, tmp_pp.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) <= " . (double)$pto . ")";
	} else {
		if ($pfrom > 0) $where_str .= " and (IF(tmp_pp.status, tmp_pp.specials_new_products_price, tmp_pp.products_price) >= " . (double)$pfrom . ")";
		if ($pto > 0) $where_str .= " and (IF(tmp_pp.status, tmp_pp.specials_new_products_price, tmp_pp.products_price) <= " . (double)$pto . ")";
	}
} else { // $status_tmp_product_prices_table is not true: uses p.products_price instead of cg_products_price
	// because in the where clause for the case $status_tmp_special_prices is true, the table
	// specials_retail_prices is abbreviated with "s" also we can use the same code for "true" and for "false"
	if (DISPLAY_PRICE_WITH_TAX == 'true') {
		if ($pfrom > 0) $where_str .= " and (IF(s.status AND s.customers_group_id = '" . $customer_group_id . "', s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) >= " . (double)$pfrom . ")";
		if ($pto > 0) $where_str .= " and (IF(s.status AND s.customers_group_id = '" . $customer_group_id . "', s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) <= " . (double)$pto . ")";
	} else {
		if ($pfrom > 0) $where_str .= " and (IF(s.status AND s.customers_group_id = '" . $customer_group_id . "', s.specials_new_products_price, p.products_price) >= " . (double)$pfrom . ")";
		if ($pto > 0) $where_str .= " and (IF(s.status AND s.customers_group_id = '" . $customer_group_id . "', s.specials_new_products_price, p.products_price) <= " . (double)$pto . ")";
	}
}
// EOF Separate Pricing Per Customer

if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
	$where_str .= " group by p.products_id, tr.tax_priority";
}
else {
	$where_str .= " group by p.products_id";
}

if ( (!isset($_GET['sort'])) || (!ereg('[1-8][ad]', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > sizeof($column_list)) ) {
	for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
		if ($column_list[$i] == 'PRODUCT_LIST_NAME') {
			$_GET['sort'] = $i+1 . 'a';
			$order_str = ' order by pd.products_name';
			break;
		}
	}
} else {
	$sort_col = substr($_GET['sort'], 0 , 1);
	$sort_order = substr($_GET['sort'], 1);
	$order_str = ' order by ';
	switch ($column_list[$sort_col-1]) {
		case 'PRODUCT_LIST_MODEL':
			$order_str .= "p.products_model " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
			break;
		case 'PRODUCT_LIST_NAME':
			$order_str .= "pd.products_name " . ($sort_order == 'd' ? "desc" : "");
			break;
		case 'PRODUCT_LIST_MANUFACTURER':
			$order_str .= "m.manufacturers_name " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
			break;
		case 'PRODUCT_LIST_QUANTITY':
			$order_str .= "p.products_quantity " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
			break;
		case 'PRODUCT_LIST_IMAGE':
			$order_str .= "pd.products_name";
			break;
		case 'PRODUCT_LIST_WEIGHT':
			$order_str .= "p.products_weight " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
			break;
		case 'PRODUCT_LIST_PRICE':
			$order_str .= "final_price " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
			break;
	}
}

$listing_sql = $select_str . $from_str . $where_str . $order_str;

require(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING_COL);

// stats_keywords.php_bof
$keyword_lookup = tep_db_query("select search_text, search_date from " . TABLE_SEARCH_QUERIES . " where search_text = '" . tep_db_input($_GET['keywords']) . "'");

if (tep_db_num_rows($keyword_lookup) > 0) {
	tep_db_query("update " . TABLE_SEARCH_QUERIES . " set search_count = search_count+1, search_date = now() where search_text = '" . tep_db_input($_GET['keywords']) . "'");
} else {
	tep_db_query("insert into " . TABLE_SEARCH_QUERIES . " (search_text, search_date) values ('" . tep_db_input($_GET['keywords']) . "', now())");
}

// stats_keywords.php_eof
?>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_ADVANCED_SEARCH, tep_get_all_get_params(array('sort', 'page')), 'NONSSL', true, false) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
      </tr>
    </table>
