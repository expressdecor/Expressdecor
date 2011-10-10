<?php 
$options = array(
	'$1 Bill',
	'$5 Bill',
	'$10 Bill',
	'$20 Bill',
	'$50 Bill',
	'$100 Bill',
); 
?>
<div>
	<h3 style="color: #FE6612; text-align:center; font-size: 18px;">Take Part in this Fun Contest brought to you by ExpressDecor - Get a Chance to Win $100</h3>
	<div style="margin-bottom: 10px;margin-top: 10px;">
		<div style="float:left; width: 723px; padding: 6px;">All you need to do is answer one question. You can participate in the contest only once, so please be careful in choosing the answer.<br /><strong>Please Note:</strong> No purchase necessary to participate in the contest.</div>
		<div style="float: right; width: 115px; border: 2px solid #00AA00; padding: 6px; background-color: #AFA;"><strong>Previous Winner</strong><br />Carol N., CA</div>
		<div style="clear: both;"></div>
	</div>
	<div>
	<?php if (!$surveyTaken) { ?>
	<form method="post" action="survey.php">
	<input type="hidden" name="action" value="send" />
	<?php } ?>
	<table cellpadding="4" cellspacing="0" class="table1">
		<tr>
			<td class="border" valign="top">
				<div style="margin-bottom:5px;">Here is the question: <br /><strong style="font-size: 14px;">Which United States paper currency lists all 50 states?</strong></div>
				<?php if ($surveyTaken) { ?>
				You have chosen: <strong><?php echo $options[$survey_result['q1']]; ?></strong>
				<?php } else { 
								foreach($options as $key=>$val) {
					?>
				<input type="radio" name="q1" value="<?php echo $key; ?>" /><?php echo $val; ?><br />
				<?php 
								}
							}
				?>
				<?php if (!$surveyTaken) { ?>
				<p style="margin-top:5px;">Your name/nickname: <input type="text" name="name" value="" maxlength="64" class="survey_name" /></p>
				<p>
					Optional: If you're a winner, how can we contact you? (Please provide either your e-mail address, your phone number, or your mailing address)<br />
					<textarea cols="42" rows="3" name="contacts"></textarea>
				</p>
			
				<input type="image" src="/includes/languages/english/images/buttons/button_send.gif" name="send" />
				<?php } ?>
			</td>
			<td class="border" valign="top" width="310">
				<img src="/templates/ed_new/img/box/box_content/survey_image_july.jpg" alt="" />
			</td>
		</tr>
	</table>
	</form>
	</div>
	<br />
	<p><strong>Please Note:</strong> The winner will be chosen randomly amongst the people who gave the correct answer. If our program will choose the winner with no contact information, it will continue looking for the winner with contact info. Results will be announced on July 31th 2010 at 3pm ET. The name of the winner and the answer will be displayed here on this page. Come back and check them out.</p>

	<p>Any information gathered from you will not be distributed to third parties and will not be used for any other purpose.</p>
	
	<div>
		<h2>Previous Contests</h2>
		
		<table cellpadding="4" cellspacing="0" class="table1">
			<tr>
				<th colspan="2"><span style="font-size:18px; font-weight: bold; color:#FFF">June</span></th>
			</tr>
			<tr>
				<td class="border"><img src="/templates/ed_new/img/box/box_content/survey_image_june.jpg" alt="" /></td>
				<td class="border" valign="top">
					<p>Here are the results from June contest.</p>
					<p>The question was - <strong>There is a statement that almost all babies are born with blue eyes. True or False?</strong></p>
					<p>The correct answer is - <strong>True</strong>. 70% of people gave the right answer. </p>
					<p>Almost all babies are born with blue eyes.  This is due to the fact that at birth the baby's iris has not yet made any brown pigment that colors the iris. Once the child's eyes are exposed to light, the iris will begin producing melanin, and the baby's eye color will change. This process typically takes several months, that is the reason why so many babies are born with blue eyes and have blue eyes for the first few months after birth.</p>
					<p><strong>Our June Fun Contest Winner is Carol N., from California. Congratulations, Carol!</strong></p>
				</td>
			</tr>
		</table>
		<br />
		<table cellpadding="4" cellspacing="0" class="table1">
			<tr>
				<th colspan="3"><span style="font-size:18px; font-weight: bold; color:#FFF">May</span></th>
			</tr>
			<tr>
				<td class="border"><img src="/templates/ed_new/img/box/box_content/survey_image2.jpg" alt="" /></td>
				<td class="border"><img src="/templates/ed_new/img/box/box_content/survey_image2answer.jpg" alt="" /></td>
				<td class="border" valign="top">
					<p>Here are the results from May contest.</p>
					<p>The question was - <strong>What animal does this ear belong to</strong>?</p>
					<p>The correct answer is - <strong>the Dog</strong>. 35% of people gave the right answer. </p>
					<p><strong>Our May Fun Contest Winner is Catherine A. from Arizona. Congratulations, Catherine!</strong></p>
				</td>
			</tr>
		</table>
		
	</div>
	<br />
	<p>Have fun!</p>
	
	<p><strong>ExpressDecor Team</strong></p>
	
	<script type="text/javascript">
	$(document).ready(function() {
		$("form").submit(function() {
			if (
				undefined === $("input[name='q1']:checked").val()
			) {
				alert('You didn\'t answer a question.');
				return false;
			}
			if ($('.survey_name').val() == '') {
				alert('Please add your name');
				$('.survey_name').focus();
				return false;
			}
		});
	});
	</script>
</div>