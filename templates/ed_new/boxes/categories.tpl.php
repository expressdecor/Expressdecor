<!-- categories //-->
		<div class="main_menu_heading">Bathroom</div>
		<ul class="menu_left menu_left_for_ed">
			<?php echo $boxContent; ?>
		</ul>

		<div class="main_menu_heading">Kitchen</div>
		<ul class="menu_left menu_left_for_ed">
			<li>
				<div class="menu_left">
					<a href="<?php echo HTTP_SERVER; ?>/stainless-steel-kitchen-sinks.html" <?php echo ($cPath_array[0]==75? 'style="font-weight: bold;"':''); ?> rel="nofollow">Stainless Kitchen Sinks</a>
				</div>
				<div class="menu_left_bottom">&nbsp;</div>
				<?php
				if ($cPath == 75) {
					echo tep_show_subcategory(75);
				}
				?>
			</li>
			<li>
				<div class="menu_left">
					<a href="<?php echo HTTP_SERVER; ?>/copper-kitchen-sinks.html" <?php echo ($cPath_array[0]==267? 'style="font-weight: bold;"':''); ?> rel="nofollow">Copper Kitchen Sinks</a>
				</div>
				<div class="menu_left_bottom">&nbsp;</div>
				<?php
				if ($cPath == 267) {
					echo tep_show_subcategory(267);
				}
				?>
			</li>
			<li>
				<div class="menu_left">
					<a href="<?php echo HTTP_SERVER; ?>/kitchen-sink-accessories.html" <?php echo ($cPath_array[0]==268? 'style="font-weight: bold;"':''); ?> rel="nofollow">Kitchen Sink Accessories</a>
				</div>
				<div class="menu_left_bottom">&nbsp;</div>
				<?php
				if ($cPath == 268) {
					echo tep_show_subcategory(268);
				}
				?>
			</li>
			<li>
				<div class="menu_left">
					<a href="<?php echo HTTP_SERVER; ?>/kitchen-faucets.html" <?php echo ($cPath_array[0]==17? 'style="font-weight: bold;"':''); ?> rel="nofollow">Kitchen Faucets</a>
				</div>
				<div class="menu_left_bottom">&nbsp;</div>
				<?php
				if ($cPath == 17) {
					echo tep_show_subcategory(17);
				}
				?>
			</li>
		</ul>
<!-- categories_eof //-->

		<div class="main_menu_heading">Customer Support</div>
		<ul class="menu_left menu_left_for_ed">
			<li><div class="menu_left"><a href="<?php echo HTTP_SERVER; ?>/about.html" <?php echo (basename($page_args['page_name'])=='about.html'? 'style="font-weight: bold;"':''); ?> rel="nofollow">About Us</a></div><div class="menu_left_bottom"> </div></li>
			<li><div class="menu_left"><a href="<?php echo HTTP_SERVER; ?>/contact-us.html" <?php echo (basename($page_args['page_name'])=='contact-us.html'? 'style="font-weight: bold;"':''); ?> rel="nofollow">Contact Us</a></div><div class="menu_left_bottom"> </div></li>
			<li><div class="menu_left"><a href="<?php echo HTTP_SERVER; ?>/coupons.html" <?php echo (basename($page_args['page_name'])=='coupons.html'? 'style="font-weight: bold;"':''); ?> rel="nofollow">Coupons</a></div><div class="menu_left_bottom"> </div></li>
			<li><div class="menu_left"><a href="<?php echo HTTP_SERVER; ?>/returns.html" <?php echo (basename($page_args['page_name'])=='returns.html'? 'style="font-weight: bold;"':''); ?> rel="nofollow">Returns &amp; Exchange</a></div><div class="menu_left_bottom"> </div></li>
			<li><div class="menu_left"><a href="<?php echo HTTP_SERVER; ?>/most-faqs.html" <?php echo (basename($page_args['page_name'])=='most-faqs.html'? 'style="font-weight: bold;"':''); ?> rel="nofollow">Most FAQs</a></div><div class="menu_left_bottom"> </div></li>
			<li><div class="menu_left"><a href="<?php echo HTTP_SERVER; ?>/video-and-documentation-library.html" <?php echo (basename($page_args['page_name'])=='video-and-documentation-library.html'? 'style="font-weight: bold;"':''); ?> rel="nofollow">Installation Videos</a></div><div class="menu_left_bottom"> </div></li>
			<li><div class="menu_left"><a href="<?php echo HTTP_SERVER; ?>/privacy.html" <?php echo (basename($page_args['page_name'])=='privacy.html'? 'style="font-weight: bold;"':''); ?> rel="nofollow">Privacy Policy</a></div><div class="menu_left_bottom"> </div></li>
		</ul>