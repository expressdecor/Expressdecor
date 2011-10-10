<?php
  $location = ' : <a href="' . tep_href_link('pollbooth.php', 'op=results', 'NONSSL') . '" class="headerNavigation"> ' . NAVBAR_TITLE_1 . '</a>';
  DEFINE('MAX_DISPLAY_NEW_COMMENTS', '5');
if ($HTTP_GET_VARS['action']=='do_comment') {
   $comment_query_raw = "insert into phesis_comments (pollid, customer_id, name, date, host_name, comment,language_id) values ('" . $HTTP_GET_VARS['pollid'] . "', '" . $customer_id . "', '" . addslashes($HTTP_POST_VARS['comment_name']) . "', now(),'" . $REMOTE_ADDR . "','" . addslashes($HTTP_POST_VARS['comment']) . "','" . $languages_id . "')";
   $comment_query = tep_db_query($comment_query_raw);
  $HTTP_GET_VARS['action'] = '';
   $HTTP_GET_VARS['op'] = 'results';
}
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
         <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
           <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_specials.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php 
if (!isset($HTTP_GET_VARS['op'])) {
        $HTTP_GET_VARS['op']="list";
}
switch ($HTTP_GET_VARS['op']) {
     case "results":
        include("poll_results.php");
        break;

     case 'comment':
        include("poll_comment.php");
        break;

     case 'list':
        include("poll_list.php");
        break;

     case "vote":
        include("poll_vote.php");
        break;
}
?>
</table>
<?php
if (!$nolink) {
?>
<br><center><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>' . "</center>"; ?>
<?php
}
?>