   <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td class="pageHeading" valign="top"><?php echo HEADING_TITLE . '\'' . $articles_name . '\''; ?></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
          <table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td valign="top">
                <table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  $reviews_query_raw = "select r.reviews_id, left(rd.reviews_text, 100) as reviews_text, r.reviews_rating, r.reviews_read, r.date_added, r.customers_name from " . TABLE_ARTICLE_REVIEWS . " r, " . TABLE_ARTICLE_REVIEWS_DESCRIPTION . " rd where r.articles_id = '" . (int)$article_info['articles_id'] . "' and r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$languages_id . "' and r.approved = '1' order by r.reviews_id desc";
  $reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);

  if ($reviews_split->number_of_rows > 0) {
    if ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3')) {
?>
                  <tr>
                    <td>
                      <table border="0" width="100%" cellspacing="0" cellpadding="2">
                        <tr>
                          <td class="smallText"><?php echo $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></td>
                          <td align="right" class="smallText"><?php echo TEXT_RESULT_PAGE . ' ' . $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info'))); ?></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
                  </tr>
<?php
    }

    $reviews_query = tep_db_query($reviews_split->sql_query);
    while ($reviews = tep_db_fetch_array($reviews_query)) {
?>
                  <tr>
                    <td>
                      <table border="0" width="100%" cellspacing="0" cellpadding="2">
                        <tr>
                          <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_ARTICLE_REVIEWS_INFO, 'articles_id=' . $article_info['articles_id'] . '&reviews_id=' . $reviews['reviews_id']) . '"><u><b>' . sprintf(TEXT_REVIEW_BY, tep_output_string_protected($reviews['customers_name'])) . '</b></u></a> (' . TEXT_REVIEW_VIEWS . $reviews['reviews_read'] . ')'; ?></td>
                          <td class="smallText" align="right"><?php echo sprintf(TEXT_REVIEW_DATE_ADDED, tep_date_long($reviews['date_added'])); ?></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
                        <tr class="infoBoxContents">
                          <td>
                            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                              <tr>
                                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                                <td valign="top" class="main"><?php echo tep_break_string(tep_output_string_protected($reviews['reviews_text']), 60, '-<br>') . '<br>' . '<a href="' . tep_href_link(FILENAME_ARTICLE_REVIEWS_INFO, 'articles_id=' . $article_info['articles_id'] . '&reviews_id=' . $reviews['reviews_id']) . '">' . TEXT_READ_REVIEW . '</a><br><br><i>' . sprintf(TEXT_REVIEW_RATING, tep_image(DIR_WS_IMAGES . 'stars_' . $reviews['reviews_rating'] . '.gif', sprintf(TEXT_OF_5_STARS, $reviews['reviews_rating'])), sprintf(TEXT_OF_5_STARS, $reviews['reviews_rating'])) . '</i>'; ?></td>
                                <td width="10" align="right"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
                  </tr>
<?php
    }
  } else {
?>
                  <tr>
                    <td><?php new infoBox(array(array('text' => TEXT_NO_ARTICLE_REVIEWS))); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
                  </tr>
<?php
  }

  if (($reviews_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
                  <tr>
                    <td>
                      <table border="0" width="100%" cellspacing="0" cellpadding="2">
                        <tr>
                          <td class="smallText"><?php echo $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></td>
                          <td align="right" class="smallText"><?php echo TEXT_RESULT_PAGE . ' ' . $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info'))); ?></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
                  </tr>
<?php
  }
?>
                  <tr>
                    <td>
                      <table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
                        <tr class="infoBoxContents">
                          <td>
                            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                              <tr>
                                <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_ARTICLE_INFO, tep_get_all_get_params()) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
                                <td class="main" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ARTICLE_REVIEWS_WRITE, tep_get_all_get_params()) . '">' . tep_image_button('button_write_review.gif', IMAGE_BUTTON_WRITE_REVIEW) . '</a>'; ?></td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </table>
                    </td>
              </tr>
            </table></td>
        </table></td>
      </tr>
    </table>
