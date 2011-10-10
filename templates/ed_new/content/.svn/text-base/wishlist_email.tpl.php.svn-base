<!-- body_text //-->
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo sprintf(HEADING_TITLE, $product_info['products_name']); ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_contact_us.gif', sprintf(HEADING_TITLE, $product_info['products_name']), HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
    $error = false;

    if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'process') && !tep_validate_email(trim($HTTP_POST_VARS['friendemail']))) {
      $friendemail_error = true;
      $error = true;
    } else {
      $friendemail_error = false;
    }

    if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'process') && empty($HTTP_POST_VARS['friendname'])) {
      $friendname_error = true;
      $error = true;
    } else {
      $friendname_error = false;
    }

    if (tep_session_is_registered('customer_id')) {
      $from_name = $account_values['customers_firstname'] . ' ' . $account_values['customers_lastname'];
      $from_email_address = $account_values['customers_email_address'];
    } else {
      $from_name = $HTTP_POST_VARS['yourname'];
      $from_email_address = $HTTP_POST_VARS['from'];
    }

    if (!tep_session_is_registered('customer_id')) {
      if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'process') && !tep_validate_email(trim($from_email_address))) {
        $fromemail_error = true;
        $error = true;
      } else {
        $fromemail_error = false;
      }
    }

    if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'process') && empty($from_name)) {
      $fromname_error = true;
      $error = true;
    } else {
      $fromname_error = false;
    }

    if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'process') && ($error == false)) {
      $email_subject = sprintf(TEXT_EMAIL_SUBJECT, $from_name, STORE_NAME);
      $email_body = sprintf(TEXT_EMAIL_INTRO, $HTTP_POST_VARS['friendname'], $from_name, $HTTP_POST_VARS['products_name'], STORE_NAME) . "\n\n";

//      if (tep_not_null($HTTP_POST_VARS['yourmessage'])) {
        $email_body .= $wishliststring . "\n\n" . $HTTP_POST_VARS['yourmessage'] . "\n\n";
//      }

      $email_body .= sprintf(TEXT_EMAIL_SIGNATURE, STORE_NAME . "\n"). "<A HREF=\"". HTTP_SERVER . DIR_WS_CATALOG ."\"><b><i>".STORE_NAME."</i></b></A> "."\n";

                     "\n\n" . $mywishlist .= $wishlist_query_array[1] ."\n";


      tep_mail($HTTP_POST_VARS['friendname'], $HTTP_POST_VARS['friendemail'], $email_subject, stripslashes($email_body), '', $from_email_address);
?>
      <tr>
        <td><br><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo sprintf(TEXT_EMAIL_SUCCESSFUL_SENT, $HTTP_POST_VARS['friendemail']); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_WISHLIST) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
      </tr>
<?php
    } else {
      if (tep_session_is_registered('customer_id')) {
        $your_name_prompt = $account_values['customers_firstname'] . ' ' . $account_values['customers_lastname'];
        $your_email_address_prompt = $account_values['customers_email_address'];
      } else {
        $your_name_prompt = tep_draw_input_field('yourname', (($fromname_error == true) ? $HTTP_POST_VARS['yourname'] : $HTTP_GET_VARS['yourname']));
        if ($fromname_error == true) $your_name_prompt .= '&nbsp;' . TEXT_REQUIRED;
        $your_email_address_prompt = tep_draw_input_field('from', (($fromemail_error == true) ? $HTTP_POST_VARS['from'] : $HTTP_GET_VARS['from']));
        if ($fromemail_error == true) $your_email_address_prompt .= ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
      }
?>
      <tr>
        <td><?php echo tep_draw_form('email_friend', tep_href_link(FILENAME_WISHLIST_SEND, 'action=process&products_id=' . $HTTP_GET_VARS['products_id'])) . tep_draw_hidden_field('products_name', $product_info['products_name']); ?><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="formAreaTitle"><?php echo FORM_TITLE_CUSTOMER_DETAILS; ?></td>
          </tr>
          <tr>
            <td class="main"><table border="0" width="100%" cellspacing="0" cellpadding="2" class="formArea">
              <tr>
                <td class="main"><table border="0" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="main"><?php echo FORM_FIELD_CUSTOMER_NAME; ?></td>
                    <td class="main"><?php echo $your_name_prompt; ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo FORM_FIELD_CUSTOMER_EMAIL; ?></td>
                    <td class="main"><?php echo $your_email_address_prompt; ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td class="formAreaTitle"><br><?php echo FORM_TITLE_FRIEND_DETAILS; ?></td>
          </tr>
          <tr>
            <td class="main"><table border="0" width="100%" cellspacing="0" cellpadding="2" class="formArea">
              <tr>
                <td class="main"><table border="0" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="main"><?php echo FORM_FIELD_FRIEND_NAME; ?></td>
                    <td class="main"><?php echo tep_draw_input_field('friendname', (($friendname_error == true) ? $HTTP_POST_VARS['friendname'] : $HTTP_GET_VARS['friendname'])); if ($friendname_error == true) echo '&nbsp;' . TEXT_REQUIRED;?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo FORM_FIELD_FRIEND_EMAIL; ?></td>
                    <td class="main"><?php echo tep_draw_input_field('friendemail', (($friendemail_error == true) ? $HTTP_POST_VARS['friendemail'] : $HTTP_GET_VARS['send_to'])); if ($friendemail_error == true) echo ENTRY_EMAIL_ADDRESS_CHECK_ERROR; ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td class="formAreaTitle"><br><?php echo FORM_TITLE_FRIEND_MESSAGE; ?></td>
          </tr>
          <tr>
            <td class="main"><table border="0" width="100%" cellspacing="0" cellpadding="2" class="formArea">
              <tr>
                <td><textarea name="yourmessage" cols=40 rows=4></textarea></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><br><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_WISHLIST) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
                <td align="right" class="main"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></form></td>
      </tr>
<?php
    }

?>
    </table>
<!-- body_text_eof //-->