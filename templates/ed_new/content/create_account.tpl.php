<?php
$zones_array = array();
$zones_query = tep_db_query("select zone_id, zone_name from " . TABLE_ZONES . " where zone_country_id = " . (int)$country . " order by zone_name");
$zones_array[] = array('id' => '', 'text' => 'Select state');
while ($zones_values = tep_db_fetch_array($zones_query)) {
	$zones_array[] = array('id' => $zones_values['zone_id'], 'text' => $zones_values['zone_name']);
}
?>
<script type="text/javascript">
function refresh_form() {
	document.getElementById('create_account').action.value = 'refresh';
	document.getElementById('create_account').submit();
	return true;
}
function CSTB(cb) {
	df = document.getElementById('create_account');
	if(cb.checked) 	{
<?php if (ACCOUNT_GENDER == 'true') { ?>
		df["sh_gender"].value = df["gender"].value;
<?php } ?>
		df["sh_firstname"].value = df["firstname"].value;
		df["sh_lastname"].value = df["lastname"].value;
<?php if (ACCOUNT_COMPANY == 'true') { ?>
		df["sh_company"].value = df["company"].value;
<?php } ?>
		df["sh_street_address"].value = df["street_address"].value;
		df["sh_suburb"].value = df["suburb"].value;
		df["sh_city"].value = df["city"].value;
		df["sh_postcode"].value = df["postcode"].value;
<?php if (count($zones_array) > 1) { ?>
		df["sh_zone_id"].selectedIndex = df["zone_id"].selectedIndex;
<?php } else { ?>
		df["sh_state"].value = df["state"].value;
<?php } ?>
		df["sh_country"].selectedIndex = df["country"].selectedIndex;
	}
}
</script>
	<?php echo tep_draw_form('create_account', tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), 'post', '_onSubmit="return check_form(create_account);" id="create_account"') . tep_draw_hidden_field('action', 'process') .tep_draw_hidden_field('company_tax_id', 0); ?>
	<img src="/templates/ed_new/img/create_account-top.png" style="margin-left: 25px;" alt="" />
	<?php
	if ($messageStack->size('create_account') > 0) {
		echo $messageStack->output('create_account');
	}
	?>
	<table border="0" width="100%" cellspacing="0" cellpadding="0" style="margin-left:5px;">
		<tr>
			<td style="width: 450px;" valign="top">
				<div class="create-account-block border">
					<div class="header">Billing Information</div>
					<div class="content">
						<div class="hint">
							<span>Billing address must match the address appears on your credit card statement.</span>
						</div>
						<table border="0" cellspacing="2" cellpadding="2">
              <tr>
                <td class="main" align="right" style="width:110px;"><?php echo (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>&nbsp;': '') . ENTRY_FIRST_NAME; ?></td>
                <td class="main"><?php echo tep_draw_input_field('firstname', '', 'style="width:250px"'); ?></td>
              </tr>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>&nbsp;': '') . ENTRY_LAST_NAME; ?></td>
                <td class="main"><?php echo tep_draw_input_field('lastname', '', 'style="width:250px"'); ?></td>
              </tr>
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>&nbsp;': '') . ENTRY_COMPANY; ?></td>
                <td class="main"><?php echo tep_draw_input_field('company', '', 'style="width:250px"'); ?></td>
              </tr>
<?php
  }
?>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</span>&nbsp;': '') . ENTRY_STREET_ADDRESS; ?></td>
                <td class="main"><?php echo tep_draw_input_field('street_address', '', 'style="width:250px"'); ?></td>
              </tr>
              <tr>
                <td class="main" align="right">Apartment/Suite:</td>
                <td class="main"><?php echo tep_draw_input_field('suburb', '', 'style="width:50px"'); ?></td>
              </tr>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">' . ENTRY_CITY_TEXT . '</span>&nbsp;': '') . ENTRY_CITY; ?></td>
                <td class="main"><?php echo tep_draw_input_field('city', '', 'style="width:250px"'); ?></td>
              </tr>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">' . ENTRY_POST_CODE_TEXT . '</span>&nbsp;': '') . ENTRY_POST_CODE; ?></td>
                <td class="main"><?php echo tep_draw_input_field('postcode', '', 'style="width:250px"'); ?></td>
              </tr>
<?php
  if (ACCOUNT_STATE == 'true') {
?>
              <tr>
                <td class="main" align="right"><?php echo ENTRY_STATE; ?></td>
                <td class="main">
								<?php
								    if (count($zones_array) > 1) {
								      echo tep_draw_pull_down_menu('zone_id', $zones_array, '', 'style="width:256px"');
								    } else {
								      echo tep_draw_input_field('state');
								    }
								?>
                </td>
              </tr>
<?php
  }
?>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</span>&nbsp;': '') . ENTRY_COUNTRY; ?></td>
                <td class="main"><?php echo tep_get_country_list('country', $country, 'onChange="return refresh_form();" style="width:256px"'); ?></td>
              </tr>
            </table>
					</div>
				</div>
			</td>
			<td style="width: 450px;" valign="top">
				<div class="create-account-block border">
					<div class="header">Shipping Information</div>
					<div class="content">
						<div class="hint">
							<span style="font-size: 11px;">NO PO BOX, APO/FPO.</span><br />
							<span style="font-size: 14px;"><input type="Checkbox" name="sameShippingBilling" value="1" onclick="CSTB(this)"> Please check if Shipping is the same as Billing</span>
						</div>
						<table border="0" cellspacing="2" cellpadding="2">
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>&nbsp;': '') . ENTRY_FIRST_NAME; ?></td>
                <td class="main"><?php echo tep_draw_input_field('sh_firstname', '', 'style="width:250px"'); ?></td>
              </tr>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>&nbsp;': '') . ENTRY_LAST_NAME; ?></td>
                <td class="main"><?php echo tep_draw_input_field('sh_lastname', '', 'style="width:250px"'); ?></td>
              </tr>

<?php
  if (ACCOUNT_COMPANY == 'true') {
?>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>&nbsp;': '') . ENTRY_COMPANY; ?></td>
                <td class="main"><?php echo tep_draw_input_field('sh_company', '', 'style="width:250px"'); ?></td>
              </tr>
<?php
  }
?>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</span>&nbsp;': '') . ENTRY_STREET_ADDRESS; ?></td>
                <td class="main"><?php echo tep_draw_input_field('sh_street_address', '', 'style="width:250px"'); ?></td>
              </tr>
              <tr>
                <td class="main" align="right">Apartment/Suite:</td>
                <td class="main"><?php echo tep_draw_input_field('sh_suburb', '', 'style="width:50px"'); ?></td>
              </tr>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">' . ENTRY_CITY_TEXT . '</span>&nbsp;': '') . ENTRY_CITY; ?></td>
                <td class="main"><?php echo tep_draw_input_field('sh_city', '', 'style="width:250px"'); ?></td>
              </tr>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">' . ENTRY_POST_CODE_TEXT . '</span>&nbsp;': '') . ENTRY_POST_CODE; ?></td>
                <td class="main"><?php echo tep_draw_input_field('sh_postcode', '', 'style="width:250px"'); ?></td>
              </tr>
<?php
  if (ACCOUNT_STATE == 'true') {
?>
              <tr>
                <td class="main" align="right"><?php echo ENTRY_STATE; ?></td>
                <td class="main">
<?php
// +Country-State Selector
    $zones_array = array();
    $zones_query = tep_db_query("select zone_id, zone_name from " . TABLE_ZONES . " where zone_country_id = " . (!(int)$sh_country?(int)$country:(int)$sh_country) . " order by zone_name");
	$zones_array[] = array('id' => '', 'text' => 'Select state');
    while ($zones_values = tep_db_fetch_array($zones_query)) {
      $zones_array[] = array('id' => $zones_values['zone_id'], 'text' => $zones_values['zone_name']);
      }
    if (count($zones_array) > 1) {
      echo tep_draw_pull_down_menu('sh_zone_id', $zones_array, '', 'style="width:256px"');
    } else {
      echo tep_draw_input_field('sh_state');
    }
// -Country-State Selector

    if (tep_not_null(ENTRY_STATE_TEXT)) echo '&nbsp;<span class="inputRequirement">';
?>
                </td>
              </tr>
<?php
  }
?>
              <tr>
                <td class="main" align="right"><?php echo (tep_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</span>&nbsp;': '') . ENTRY_COUNTRY; ?></td>
                <td class="main"><?php echo tep_get_country_list('sh_country',(!(int)$sh_country?$country:$sh_country), 'onChange="return refresh_form();" style="width:256px"'); ?></td>
              </tr>
        		</table>
					</div>
				</div>
			</td>
		</tr>
		</table>
		
		<div class="create-account-information-block border">
			<div class="header">Account Information</div>
			<div class="content">
				<table border="0" cellspacing="2" cellpadding="2">
					<tr>
						<td class="main" align="right"><?php echo (tep_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>&nbsp;': '') . ENTRY_EMAIL_ADDRESS; ?></td>
						<td class="main"><?php echo tep_draw_input_field('email_address'); ?></td>
						<td class="main" align="right" style="width: 200px;"><?php echo (tep_not_null(ENTRY_PASSWORD_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_TEXT . '</span>&nbsp;': '') . ENTRY_PASSWORD; ?></td>
						<td class="main"><?php echo tep_draw_password_field('password'); ?></td>
						<td rowspan="2" style="width: 180px;" align="center"><?php echo tep_image_submit('button_create_account_big.gif', IMAGE_BUTTON_CONTINUE); ?></td>
					</tr>
					<tr>
						<td class="main" align="right"><?php echo (tep_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>&nbsp;': '') . ENTRY_TELEPHONE_NUMBER; ?></td>
						<td class="main"><?php echo tep_draw_input_field('telephone'); ?></td>
						<td class="main" align="right"><?php echo (tep_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>&nbsp;': '') . ENTRY_PASSWORD_CONFIRMATION; ?></td>
						<td class="main"><?php echo tep_draw_password_field('confirmation'); ?></td>
					</tr>
				</table>
			</div>
		</div>
</form>

<?php unset($_SESSION['error_fields']); ?>