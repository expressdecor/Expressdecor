    <table border="0" width="100%" cellspacing="0" cellpadding="<?php echo CELLPADDING_SUB; ?>"> 
      <tr> 
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0"> 
          <tr> 
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td> 
            <td rowspan="2" class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_login.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td> 
          </tr> 
        </table></td> 
      </tr> 
      <tr> 
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td> 
      </tr> 
<?php 
  if (isset($HTTP_GET_VARS['login']) && ($HTTP_GET_VARS['login'] == 'fail')) { 
    $info_message = TEXT_LOGIN_ERROR; 
  } 

  if (isset($info_message)) { 
?> 

      <tr> 
        <td class="smallText"><?php echo $info_message; ?></td> 
      </tr> 
      <tr> 
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td> 
      </tr> 
<?php 
  } 
?> 
      <tr> 
        <td><?php echo tep_draw_form('login', tep_href_link(FILENAME_AFFILIATE, 'action=process', 'SSL')); ?><table border="0" width="100%" cellspacing="0" cellpadding="2"> 
          <tr> 
            <td class="main" width="50%" valign="top"><b><?php echo HEADING_NEW_AFFILIATE; ?></b></td> 
            <td class="main" width="50%" valign="top"><b><?php echo HEADING_RETURNING_AFFILIATE; ?></b></td> 
          </tr> 
          <tr> 
            <td width="50%" height="100%" valign="top"><table border="0" width="100%" height="100%" cellspacing="0" cellpadding="1" class="infoBox"> 
              <tr> 
                <td><table border="0" width="100%" height="100%" cellspacing="0" cellpadding="2" class="infoBoxContents"> 
                  <tr> 
                    <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td> 
                  </tr> 
                  <tr> 
                    <td class="main" valign="top"><?php echo TEXT_NEW_AFFILIATE . '<br><br>' . TEXT_NEW_AFFILIATE_INTRODUCTION; ?></td> 
                  </tr> 
                  <tr> 
                    <td class="smallText" colspan="2"><?php echo '<a  href="' . tep_href_link(FILENAME_AFFILIATE_TERMS, '', 'SSL') . '">' . TEXT_NEW_AFFILIATE_TERMS . '</a>'; ?></td> 
                  </tr> 
                  <tr> 
                    <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td> 
                  </tr> 
                </table></td> 
              </tr> 
            </table></td> 
            <td width="50%" height="100%" valign="top"><table border="0" width="100%" height="100%" cellspacing="0" cellpadding="1" class="infoBox"> 
              <tr> 
                <td><table border="0" width="100%" height="100%" cellspacing="0" cellpadding="2" class="infoBoxContents"> 
                  <tr> 
                    <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td> 
                  </tr> 
                  <tr> 
                    <td class="main" colspan="2"><?php echo TEXT_RETURNING_AFFILIATE; ?></td> 
                  </tr> 
                  <tr> 
                    <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td> 
                  </tr> 
                  <tr> 
                    <td class="main"><b><?php echo TEXT_AFFILIATE_ID; ?></b></td> 
                    <td class="main"><?php echo tep_draw_input_field('affiliate_username'); ?></td> 
                  </tr> 
                  <tr> 
                    <td class="main"><b><?php echo TEXT_AFFILIATE_PASSWORD; ?></b></td> 
                    <td class="main"><?php echo tep_draw_password_field('affiliate_password'); ?></td> 
                  </tr> 
                  <tr> 
                    <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td> 
                  </tr> 
                  <tr> 
                    <td class="smallText" colspan="2"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE_PASSWORD_FORGOTTEN, '', 'SSL') . '">' . TEXT_AFFILIATE_PASSWORD_FORGOTTEN . '</a>'; ?></td> 
                  </tr> 
                  <tr> 
                    <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td> 
                  </tr> 
                </table></td> 
              </tr> 
            </table></td> 
          </tr> 
              <tr> 
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2" class="infoBoxContents"> 
          <tr> 
            <td width="50%" align="left" valign="top"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE_SIGNUP, '', 'SSL') . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></td> 
            <td width="50%" align="right" valign="top"><?php echo tep_image_submit('button_login.gif', IMAGE_BUTTON_LOGIN); ?></td> 
          </tr> 
                </table></td> 
              </tr> 
        </table></form></td> 
      </tr> 
    </table>