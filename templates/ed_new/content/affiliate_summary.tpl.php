<table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_GREETING . $affiliate['affiliate_firstname'] . ' ' . $affiliate['affiliate_lastname'] . '<br>' . TEXT_AFFILIATE_ID . $affiliate_id; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="infoboxheading"><?php echo TEXT_SUMMARY_TITLE; ?></td>
              </tr>
            </table></td>
          </tr> 
          <tr>
            <td><table width="100%" border="0" cellpadding="4" cellspacing="2">
              <center>
                <tr>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_IMPRESSIONS; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP_1) . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $affiliate_impressions; ?></td>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_VISITS; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP_2) . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $affiliate_clickthroughs; ?></td>
                </tr>
                <tr>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_TRANSACTIONS; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP_3) . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $affiliate_transactions; ?></td>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_CONVERSION; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP_4) . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $affiliate_conversions;?></td>
                </tr>
                <tr>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_AMOUNT; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP_5) . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $currencies->display_price($affiliate_amount, ''); ?></td>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_AVERAGE; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP_6) . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $currencies->display_price($affiliate_average, ''); ?></td>
                </tr>
                <tr>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_COMMISSION_RATE; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP_7) . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo tep_round($affiliate_percent, 2). '%'; ?></td>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_COMMISSION; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP_8) . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $currencies->display_price($affiliate_commission, ''); ?></td>
                </tr>
                <tr>
                  <td colspan="4"><?php echo tep_draw_separator(); ?></td>
                </tr>
                 <tr>
                  <td align="center" class="boxtext" colspan="4"><b><?php echo TEXT_SUMMARY; ?><b></td>
                </tr>
                <tr>
                  <td colspan="4"><?php echo tep_draw_separator(); ?></td>
                </tr>
                <tr>
                  <td align="right" colspan="4"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE_BANNERS, '') . '">' . tep_image_button('button_affiliate_banners.gif', IMAGE_BANNERS) . '</a> <a href="' . tep_href_link(FILENAME_AFFILIATE_CLICKS, '') . '">' . tep_image_button('button_affiliate_clickthroughs.gif', IMAGE_CLICKTHROUGHS) . '</a> <a href="' . tep_href_link(FILENAME_AFFILIATE_SALES, '','SSL') . '">' . tep_image_button('button_affiliate_sales.gif', IMAGE_SALES) . '</a>'; ?></td>
                </tr>
              </center>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table>