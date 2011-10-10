<table width="100%" border="0" cellspacing="2" cellpadding="1">
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr> 
          <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr> 
                <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                <td align="right"></td>
              </tr>
            </table></td>
        </tr>
        <tr> 
          <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
        </tr>
        <tr> 
          <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <?php	
			 $languages_query = tep_db_query("select languages_id, name, code, image, directory from " . TABLE_LANGUAGES . " order by sort_order");
			 while ($languages = tep_db_fetch_array($languages_query)) {
				$languages_array[] = array('id'			=> $languages['languages_id'],
										   'name'		=> $languages['name'],
                                 		   'code'		=> $languages['code'],
                                 		   'image'		=> $languages['image'],
                                           'directory'	=> $languages['directory']);
			 }
          	 for ($i=0; $i<sizeof($languages_array); $i++) {			 
             	$this_language_id = $languages_array[$i]['id'];
				$this_language_name = $languages_array[$i]['name'];
				$this_language_code = $languages_array[$i]['code'];
				$this_language_image = $languages_array[$i]['image'];
				$this_language_directory = $languages_array[$i]['directory'];
				echo " <tr>\n";
             	$products_query = tep_db_query("SELECT p.products_id, pd.products_name FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd WHERE p.products_id = pd.products_id AND p.products_status = 1 AND pd.language_id = $this_language_id ORDER BY pd.products_name");
				$products_array = array();
				while($products = tep_db_fetch_array($products_query)) {
				   $products_array[] = array('id'   => $products['products_id'],
				   							 'name' => $products['products_name']);
				}
				for ($j=0; $j<NR_COLUMNS; $j++) {
					echo "   <td class=main valign=\"top\">\n";
					for ($k=$j; $k<sizeof($products_array); $k+=NR_COLUMNS) {
						$this_products_id   = $products_array[$k]['id'];
						$this_products_name = $products_array[$k]['name'];
						echo "     <a href=\"" . tep_href_link(FILENAME_PRODUCT_INFO, 'name=' .str_replace("/", "&#47;", rawurlencode($this_products_name)). '&products_id=' . $this_products_id . (($this_language_code == DEFAULT_LANGUAGE) ? '' : ('&language=' . $this_language_code)), 'NONSSL', false) . "\">" . $this_products_name . "</a><br>\n";
					}
					echo "   </td>\n";
				}
			    echo " </tr>\n";
			}
?>
            </table></td>
        </tr>
      </table>
  
      
      
    </td>
  </tr>
</table>