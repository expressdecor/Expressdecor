<table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
<?php
  session_start();
  $old_user = $affiliate_id;  // store  to test if they *were* logged in
  $result = session_unregister("affiliate_id");

//session_destroy();

  if (!empty($old_user)) {
    if ($result) { // if they were logged in and are not logged out 
      echo '            <td class="main">' . TEXT_INFORMATION . '</td>';
    } else { // they were logged in and could not be logged out
      echo '            <td class="main">' . TEXT_INFORMATION_ERROR_1 . '</td>';
    } 
  } else { // if they weren't logged in but came to this page somehow
    echo '            <td class="main">' . TEXT_INFORMATION_ERROR_2 . '</td>';
  }
?>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td align="right" class="main"><br><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></td>
      </tr>
    </table>