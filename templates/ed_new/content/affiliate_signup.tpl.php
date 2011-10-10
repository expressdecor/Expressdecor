<?php echo tep_draw_form('affiliate_details',  tep_href_link(FILENAME_AFFILIATE_SIGNUP, '', 'SSL'), 'post') . tep_draw_hidden_field('action', 'process'); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_account.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
<?php
  if (isset($HTTP_GET_VARS['affiliate_email_address'])) $a_email_address = tep_db_prepare_input($HTTP_GET_VARS['affiliate_email_address']);
  $affiliate['affiliate_country_id'] = STORE_COUNTRY;

  require(DIR_WS_MODULES . 'affiliate_account_details.php');
?>
        </td>
      </tr>
      <tr>
        <td align="right" class="main"><br><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></td>
      </tr>
    </table></form>