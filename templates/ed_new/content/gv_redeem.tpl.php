<table width="100%" border="0" cellspacing="2" cellpadding="1">
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr> 
          <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr> 
                <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                <td align="right">&nbsp;</td>
              </tr>
            </table></td>
        </tr>
        <tr> 
          <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
        </tr>
        <tr> 
          <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr> 
                <td class="main"><?php echo TEXT_INFORMATION; ?></td>
              </tr>
              <?php
// if we get here then either the url gv_no was not set or it was invalid
// so output a message.
  $message = sprintf(TEXT_VALID_GV, $currencies->format($coupon['coupon_amount']));
  if ($error) {
    $message = TEXT_INVALID_GV;
  }
?>
              <tr> 
                <td class="main"><?php echo $message; ?></td>
              </tr>
              <tr> 
                <td align="right"><br>
                  <?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></td>
              </tr>
            </table></td>
        </tr>
      </table></td>
  </tr>
</table>