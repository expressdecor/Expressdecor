<table width="100%" border="0" cellspacing="2" cellpadding="1">
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr> 
          <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr> 
                <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                <td class="pageHeading" align="right">&nbsp;</td>
              </tr>
            </table></td>
        </tr>
        <tr> 
          <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
        </tr>
        <tr> 
          <td><br>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr> 
                <td class="main"><?php echo DOWN_FOR_MAINTENANCE_TEXT_INFORMATION; ?></td>
              </tr>
              <?php if (DISPLAY_MAINTENANCE_TIME == 'true') { ?>
              <tr> 
                <td class="main"><?php echo TEXT_MAINTENANCE_ON_AT_TIME . TEXT_DATE_TIME; ?></td>
              </tr>
              <?php
		  } 
		  if (DISPLAY_MAINTENANCE_PERIOD == 'true') { ?>
              <tr> 
                <td class="main"><?php echo TEXT_MAINTENANCE_PERIOD . TEXT_MAINTENANCE_PERIOD_TIME; ?></td>
              </tr>
              <?php } ?>
            </table></td>
        </tr>
        <tr> 
          <td align="right" class="main"><br>
            <?php echo DOWN_FOR_MAINTENANCE_STATUS_TEXT . '<br><br>' . '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></td>
        </tr>
      </table></td>
  </tr>
</table>

