		<?php

		    $info_box_contents = array();
		    $info_box_contents[] = array('text' => TEXT_ADMIN_CREATE_ACCOUNT);

		    new infoBoxHeading($info_box_contents, true, false);
		    unset($info_box_contents);
		    $info_box_contents[] = array('align' => 'center',
						 'text'  => '<a href="'.tep_href_link('admin_create_account.php','','SSL').'">'.TEXT_ADMIN_CREATE_ACCOUNT.'</a>');

		    new infoBox($info_box_contents);

		?>

		<br>

		<?php

		    $info_box_contents = array();
		    $info_box_contents[] = array('text' => TEXT_ADMIN_SEARCH_ACCOUNT);

		    new infoBoxHeading($info_box_contents, true, false);

		    unset($info_box_contents);

		    $info_box_contents[] = array('align' => 'center',
						 'text'  => tep_draw_form('search_customers', tep_href_link('admin.php','','SSL'), 'get').TEXT_ADMIN_SEARCH_EMAIL.'<br>'.tep_draw_input_field('search_email').'<br>'.TEXT_ADMIN_SEARCH_LASTNAME.'<br>'.tep_draw_input_field('search_lastname').'<br>'.TEXT_ADMIN_SEARCH_PHONE.'<br>'.tep_draw_input_field('search_phone'));
		    $info_box_contents[] = array('align' => 'center',
						 'text'  => tep_image_submit('button_search.gif', IMAGE_BUTTON_SEARCH).tep_hide_session_id().'</form>');

		    if ($HTTP_GET_VARS['search_email']) {
		    	$search_email = tep_db_prepare_input($HTTP_GET_VARS['search_email']);
		    	$where_clause = "customers_email_address RLIKE '".tep_db_input($search_email)."'";
		    }

		    if ($HTTP_GET_VARS['search_phone']) {
		    	$search_phone = tep_db_prepare_input($HTTP_GET_VARS['search_phone']);
		    	$where_clause .= ($where_clause ? ' or ' : '')."customers_telephone RLIKE '".tep_db_input($search_phone)."'";
		    }

		    if ($HTTP_GET_VARS['search_lastname']) {
		    	$search_lastname = tep_db_prepare_input($HTTP_GET_VARS['search_lastname']);
		    	$where_clause .= ($where_clause ? ' or ' : '')." customers_lastname RLIKE '".tep_db_input($search_lastname)."'";
		    }

		    
		    if ($where_clause) {
		    
		    	$search_sql = "select * from ".TABLE_CUSTOMERS." where ".$where_clause;
		    	$search_query = tep_db_query($search_sql);
		    	
		    	if (tep_db_num_rows($search_query)) {
		    		
			    $info_box_contents[] = array('align' => 'center',
							 'text'  => TEXT_ADMIN_MATCHES);
		    	    
		    		
			    $search_display = '<table border="1" width="100%" cellspacing="0" cellpadding="2">';
			    $search_display .= '<tr><td class="tableHeading">'.TEXT_ADMIN_SEARCH_EMAIL.'</td><td class="tableHeading">'.TEXT_ADMIN_SEARCH_NAME.'</td><td class="tableHeading">'.TEXT_ADMIN_SEARCH_PHONE.'</td></tr>';	
			    while ($search_result = tep_db_fetch_array($search_query)) {
			    	//$search_display .= '<tr><td class="smallText"><a href="'.tep_href_link('admin_login.php','email_address='.$search_result['customers_email_address'],'SSL').'">'.$search_result['customers_email_address'].'</a></td><td class="smallText">'.$search_result['customers_firstname'].' '.$search_result['customers_lastname'].'</td><td class="smallText">'.$search_result['customers_telephone'].'</td></tr>';	
					$search_display .= '<tr><td class="smallText"><a href="/admin_login.php?email_address='.$search_result['customers_email_address'].'">'.$search_result['customers_email_address'].'</a></td><td class="smallText">'.$search_result['customers_firstname'].' '.$search_result['customers_lastname'].'</td><td class="smallText">'.$search_result['customers_telephone'].'</td></tr>';	
			    }
			    $search_display .= '</table>';
				
			    $info_box_contents[] = array('align' => 'left',
							 'text'  => $search_display);
				
			} else {
				    $info_box_contents[] = array('align' => 'center',
								 'text'  => TEXT_ADMIN_NO_MATCHES);
			}
		    }

		    new infoBox($info_box_contents);
?>
