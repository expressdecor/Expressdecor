<?php /* infobox template  */ ?>
          <tr>
            <td>
		      <table width="100%"  border="0" cellspacing="0" cellpadding="0" style="margin-top: 1px; border: 1px solid #E2E2E2; background-color: #F8F8F8;">
		        <tr>
		          <td valign="top" style="padding: 8px 11px 10px 11px;"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
		            <tr>
		              <td height="12"><?php echo $boxHeading; ?></td>
					  <td width="60" align="right"></td>
					</tr>
				</table>
				</td>
                </tr>
		        <tr>
		          <td align="center" valign="top" <?php echo $boxContent_attributes; ?>><?php echo $boxContent; ?></td>
		        </tr>
				<tr>
				  <td height="10"></td>
				</tr>
              </table>
            </td>
          </tr>