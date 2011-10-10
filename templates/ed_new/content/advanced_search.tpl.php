    <?php echo tep_draw_form('advanced_search', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get', 'onSubmit="return check_form(this);"') . tep_hide_session_id(); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE_1; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_browse.gif', HEADING_TITLE_1, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  if ($messageStack->size('search') > 0) {
?>
      <tr>
        <td><?php echo $messageStack->output('search'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  }
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td height="14" class="infoBoxHeading"><img src="/images/infobox/corner_left.gif" border="0" alt="" width="11" height="14"></td>
            <td width="100%" height="14" class="infoBoxHeading"><?php echo HEADING_SEARCH_CRITERIA; ?></td>
            <td height="14" class="infoBoxHeading" nowrap><img src="/images/infobox/corner_right.gif" border="0" alt="" width="11" height="14"></td>
          </tr>
        </table>
        <table border="0" width="100%" cellspacing="0" cellpadding="1" class="infoBox">
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="3" class="infoBoxContents">
              <tr>
                <td><img src="/images/pixel_trans.gif" border="0" alt="" width="100%" height="1"></td>
              </tr>
              <tr>
                <td class="boxText"><?php echo tep_draw_input_field('keywords', '', 'style="width: 100%"'); ?></td>
              </tr>
              <tr>
                <td align="right" class="boxText"><?php echo tep_draw_checkbox_field('search_in_description', '1') . ' ' . TEXT_SEARCH_IN_DESCRIPTION; ?></td>
              </tr>
              <tr>
                <td><img src="/images/pixel_trans.gif" border="0" alt="" width="100%" height="1"></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="smallText"><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_POPUP_SEARCH_HELP) . '\')">' . TEXT_SEARCH_HELP_LINK . '</a>'; ?></td>
            <td class="smallText" align="right"><?php echo tep_image_submit('button_search.gif', IMAGE_BUTTON_SEARCH); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="fieldKey"><?php echo ENTRY_CATEGORIES; ?></td>
                <td class="fieldValue"><?php echo tep_draw_pull_down_menu('categories_id', tep_get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES)))); ?></td>
              </tr>
              <tr>
                <td class="fieldKey">&nbsp;</td>
                <td class="smallText"><?php echo tep_draw_checkbox_field('inc_subcat', '1', true) . ' ' . ENTRY_INCLUDE_SUBCATEGORIES; ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
              </tr>
              <tr>
                <td class="fieldKey"><?php echo ENTRY_MANUFACTURERS; ?></td>
                <td class="fieldValue"><?php echo tep_draw_pull_down_menu('manufacturers_id', tep_get_manufacturers(array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS)))); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
              </tr>
              <tr>
                <td class="fieldKey"><?php echo ENTRY_PRICE_FROM; ?></td>
                <td class="fieldValue"><?php echo tep_draw_input_field('pfrom'); ?></td>
              </tr>
              <tr>
                <td class="fieldKey"><?php echo ENTRY_PRICE_TO; ?></td>
                <td class="fieldValue"><?php echo tep_draw_input_field('pto'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
              </tr>
              <tr>
                <td class="fieldKey"><?php echo ENTRY_DATE_FROM; ?></td>
                <td class="fieldValue"><?php echo tep_draw_input_field('dfrom', DOB_FORMAT_STRING, 'onFocus="RemoveFormatString(this, \'' . DOB_FORMAT_STRING . '\')"'); ?></td>
              </tr>
              <tr>
                <td class="fieldKey"><?php echo ENTRY_DATE_TO; ?></td>
                <td class="fieldValue"><?php echo tep_draw_input_field('dto', DOB_FORMAT_STRING, 'onFocus="RemoveFormatString(this, \'' . DOB_FORMAT_STRING . '\')"'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></form>
