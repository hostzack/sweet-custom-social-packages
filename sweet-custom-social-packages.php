<?php
/*
Plugin Name: Sweet Custom Social Packages
Plugin URI: 
Description: Use WP to manage social packages like facebook likes, facebook followers, youtube likes, youtubes viewrs etc. It supports paypal.
Author: Vinod Kumar Dhundhara
Version: 1.0.0
Author URI: http://www.facebook.com/v9ddhundhara
Author Company : Hostzack Techno Solutions
Author Company URI : http://www.hostzack.com
*/

define('SWL_TABLE', $table_prefix . 'social_packages');

$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
$stockID = !empty($_REQUEST['stockID']) ? $_REQUEST['stockID'] : '';

//admin menu
function social_packages_admin() {
	if (function_exists('add_options_page')) {
		add_management_page('tk_social_packages', 'Social Packages', 1, basename(__FILE__), 'social_packages_admin_panel');
  }
}

function social_packages_admin_panel() {

	global $wpdb, $table_prefix;
	$alt = 'alternate';
	$buttontext = "Add Package &raquo;";

	//Get the action for the form
	if(!empty($_REQUEST['action'])) {
		$action = $_REQUEST['action'];
	}
	else {
		$action = "add";
	}

	//Get ID of Edit/Delete
	if(!empty($_REQUEST['stkID'])) { $stkid = $_REQUEST['stkID'];	}

	//First time run - allow to build new table
	$tableExists = false;

	$tables = $wpdb->get_results("show tables;");

	foreach ( $tables as $table )
	{
		foreach ( $table as $value )
		{
			if ( $value == SWL_TABLE )
			{
				$tableExists=true;
				break;
			}
		}
	}
	
	if ( !$tableExists )
	{
		$sql = "CREATE TABLE wp_scsp_paypal (
					scsp_paypal_ID INT(11) NOT NULL AUTO_INCREMENT,
					scsp_paypal_email VARCHAR(255),
					PRIMARY KEY ( scsp_paypal_ID )
				)";
		$wpdb->get_results($sql);
		
		$default = "INSERT INTO wp_scsp_paypal (scsp_paypal_email) VALUES('example@paypal.com')";
		$wpdb->get_results($default);
	}
	if ( !$tableExists )
	{
		$sql = "CREATE TABLE " . SWL_TABLE . " (
					scsp_ID INT(11) NOT NULL AUTO_INCREMENT,
					scsp_pkg_name VARCHAR(255),
					scsp_pkg_title VARCHAR(255),
					scsp_pkg_price VARCHAR(255),
					scsp_pkg_for ENUM('facebook','instagram','pinterest','soundcloud','twitter','youtube'),
					scsp_pkg_description TEXT,
					PRIMARY KEY ( scsp_ID )
				)";
		$wpdb->get_results($sql);
	}

	//perform Add/Edit/Delete
	switch ($action) {
		case 'add':
			//check that we have the necessary variables
			if(!empty($_REQUEST['pkgName'])) {
				$pkgname = $_REQUEST['pkgName'];
				$pkgtitle = $_REQUEST['pkgTitle'];
				$pkgdescription = $_REQUEST['pkgDescription'];
				$pkgprice = $_REQUEST['pkgPrice'];
				$pkgfor = $_REQUEST['pkgFor'];
				
				
				//echo $pkgname . $pkgdescription . $pkgprice . $pkgfor;
				$sql = "INSERT INTO " . SWL_TABLE . " (scsp_pkg_name, scsp_pkg_title, scsp_pkg_price, scsp_pkg_description, scsp_pkg_for)
								VALUES ('" . $pkgname . "','". $pkgtitle ."', '" . $pkgprice . "', '" . $pkgdescription . "', '" . $pkgfor . "')";
				$wpdb->get_results($sql);
			}
			break;
		case 'edit':
			if(empty($_REQUEST['save'])) {
				if(!empty($_REQUEST['stkID'])) {
					$sql = "SELECT scsp_ID, scsp_pkg_name, scsp_pkg_title, scsp_pkg_description, scsp_pkg_price, scsp_pkg_for FROM " . SWL_TABLE . " WHERE scsp_ID=" . $_REQUEST['stkID'];
					$stockedit = $wpdb->get_results($sql);
					$stockedit = $stockedit[0];
					$buttontext = "Save Package &raquo;";
					$save = "&amp;save=yes";
				}
			} else {
				//check that we have the necessary variables
				if(!empty($_REQUEST['pkgName'])) {
					$pkgname = $_REQUEST['pkgName'];
					$pkgtitle = $_REQUEST['pkgTitle'];
					$pkgdescription = $_REQUEST['pkgDescription'];
					$pkgfor = $_REQUEST['pkgFor'];
					$pkgprice = $_REQUEST['pkgPrice'];

					//echo $pkgname . $pkgfor . $pkgrice . $pkgdescription;
					$sql = "UPDATE " . SWL_TABLE . "
									SET
									scsp_pkg_name='" . $pkgname . "',
									scsp_pkg_title='". $pkgtitle ."',
									scsp_pkg_description='" . $pkgdescription . "',
									scsp_pkg_price='" . $pkgprice . "',
									scsp_pkg_for='" . $pkgfor . "'
									WHERE scsp_ID=" . $_REQUEST['pkgID'];
					$wpdb->get_results($sql);
					$action = "add";
				}
			}
			break;
		case 'delete':
			$sql = "DELETE FROM " . SWL_TABLE . " WHERE scsp_ID=" . $_REQUEST['stkID'];
			$wpdb->get_results($sql);

			break;
	}

	?>
	<div class="clear" style="height:30px">
		<h2>Paypal Email Address</h2>
	</div>
	<?php
		if($_REQUEST['submit_email'])
		{
			$email = $_REQUEST['email'];
			
			$email_sbmt = $wpdb->get_results("UPDATE wp_scsp_paypal SET scsp_paypal_email='$email'");
		}
		
		$emails = $wpdb->get_results("SELECT * FROM wp_scsp_paypal WHERE LIMIT 1");
		foreach ( $emails as $email ) 
		{
	?>
	<form name="addstock" id="addstock" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=sweet-custom-social-packages.php' . $save ?>">
		<input name="email" id="stkName" type="email" value="<?php $paypal_email = $email->scsp_paypal_email; echo $email->scsp_paypal_email; ?>" size="40" required/>
		<input type="submit" name="submit_email" class="button button-primary" value="Submit Email" />
	</form>
	<?php
		}
	?>
	
	<div class="wrap">

		<h2>Stock Watchlist <span style="font-size:14px; color:green"><b style="color:red">Use this shortcode :</b> [custom_social_package id='id_here']</span></h2>

		<table class="widefat">
			<thead>
				<tr>
					<th scope="col">ID</th>
					<th scope="col">Pkg Name</th>
					<th scope="col">Pkg Title</th>
					<th scope="col">Pkg Price</th>
					<th scope="col">Pkg Description</th>
					<th scope="col">Pkg For</th>
					<th colspan="3" style="text-align: center">Action</th>
				</tr>
			</thead>

			<tbody>

			<?php
			$stocks = $wpdb->get_results("SELECT scsp_ID, scsp_pkg_price, scsp_pkg_name, scsp_pkg_title, scsp_pkg_description, scsp_pkg_for FROM " . SWL_TABLE);

			foreach ( $stocks as $stock ) {
				$class = ('alternate' == $class) ? '' : 'alternate';
			?>

				<tr id='post-7' class='<?php echo $class; ?>'>
					<th scope="row"><?php echo $stock->scsp_ID; ?></th>
					<td><?php echo $stock->scsp_pkg_name; ?></td>
					<td><?php echo $stock->scsp_pkg_title; ?></td>
					<td><?php echo $stock->scsp_pkg_price; ?></td>
					<td><?php echo $stock->scsp_pkg_description; ?></td>
					<td><?php echo $stock->scsp_pkg_for; ?></td>
					<td><a href="edit.php?page=sweet-custom-social-packages&amp;action=edit&amp;stkID=<?php echo $stock->scsp_ID; ?>#addstock" class="delete"><?php echo __('Edit'); ?></a></td>
					<td><a href="edit.php?page=sweet-custom-social-packages&amp;action=delete&amp;stkID=<?php echo $stock->scsp_ID; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this stock?')"><?php echo __('Delete'); ?></a></td>
				</tr>

			<?php

				if ($alt = 'alternate') { $alt = ''; } elseif ($alt = '') { $alt = 'alternate'; }

			}
			?>

			</tbody>
		</table>

	</div>

	<div class="wrap">

		<h2>Add Package</h2>

		<form name="addstock" id="addstock" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=sweet-custom-social-packages.php' . $save ?>">
			<input type="hidden" name="action" value="<?php echo $action ?>" />
			<input type="hidden" name="pkgID" value="<?php $pkgID = $_REQUEST['stkID']; echo $pkgID; ?>" />

			<table class="editform" width="100%" cellspacing="2" cellpadding="5">
				<tr>
					<th width="33%" scope="row" valign="top"><label for="stkName"><?php _e('Package Name:') ?></label></th>
					<td width="67%">
					<input name="pkgName" id="stkName" type="text" value="<?php echo attribute_escape($stockedit->scsp_pkg_name); ?>" size="40" /></td>
				</tr>
				<tr>
					<th width="33%" scope="row" valign="top"><label for="stkName"><?php _e('Package Title:') ?></label></th>
					<td width="67%">
					<input name="pkgTitle" id="stkName" type="text" value="<?php echo attribute_escape($stockedit->scsp_pkg_title); ?>" size="40" /></td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="stkDescription"><?php _e('Package Price:') ?></label></th>
					<td><input name="pkgPrice" id="stkName" type="text" value="<?php echo attribute_escape($stockedit->scsp_pkg_price); ?>" size="40" /></td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="stkDescription"><?php _e('Package Description:') ?></label></th>
					<td>
					<textarea name="pkgDescription" id="stkDescription" cols="50"><?php echo attribute_escape($stockedit->scsp_pkg_description); ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="stkVisible"><?php _e('Package For:') ?></label></th>
					<td>
						<select name="pkgFor" required>
							<option selected disbaled>Select Social Network Name</option>
							<option value="<?php $facebook = "facebook"; echo $facebook; ?>" <?php if($stockedit->scsp_pkg_for == $facebook){ echo "selected";} ?>><?php echo $facebook;  ?></option>
							<option value="<?php $instagram = "instagram"; echo $instagram; ?>" <?php if($stockedit->scsp_pkg_for == $instagram){ echo "selected";} ?>><?php echo $instagram;  ?></option>
							<option value="<?php $pinterest = "pinterest"; echo $pinterest; ?>" <?php if($stockedit->scsp_pkg_for == $pinterest){ echo "selected";} ?>><?php echo $pinterest;  ?></option>
							<option value="<?php $soundcloud = "soundcloud"; echo $soundcloud; ?>" <?php if($stockedit->scsp_pkg_for == $soundcloud){ echo "selected";} ?>><?php echo $soundcloud;  ?></option>
							<option value="<?php $twitter = "twitter"; echo $twitter; ?>" <?php if($stockedit->scsp_pkg_for == $twitter){ echo "selected";} ?>><?php echo $twitter;  ?></option>
							<option value="<?php $youtube = "youtube"; echo $youtube; ?>" <?php if($stockedit->scsp_pkg_for == $youtube){ echo "selected";} ?>><?php echo $youtube;  ?></option>
						</select>
					</td>
				</tr>
			</table>

			<p class="submit"><input class="button button-primary" type="submit" name="submit" value="<?php echo $buttontext ?>" /></p>

		</form>

	</div>

	<?php
}


//hooks
add_action('admin_menu', 'social_packages_admin');

function myShortCode( $atts ) {
	global $stocks,$wpdb;

	ob_start();
	
	extract(shortcode_atts(array(
			"id" => 'id'
		), $atts));
		
	$stocks = $wpdb->get_results("SELECT * FROM wp_social_packages WHERE scsp_ID='$id'");

	foreach ( $stocks as $stock ) {
	?> 
		
		<div class="pkg_wrapper<?php echo $id; ?>">
	<div class="pkg_header<?php echo $id; ?>">
		<div class="pkg_top<?php echo $id; ?>">
			$<?php echo $stock->scsp_pkg_price; ?>
		</div>
	</div>
	<div class="pkg_middle<?php echo $id; ?>">
		<div class="pkg_description<?php echo $id; ?>">
			<h2 style="color:red"><?php echo $stock->scsp_pkg_title; ?></h2>
		</div>
		<div class="pkg_price_wrapper<?php echo $id; ?>">
			<div class="pkg_image<?php echo $id; ?>">
			</div>
			<div class="pkg_price<?php echo $id; ?>">
				<span class="pkg_title<?php echo $id; ?>"><?php echo $stock->scsp_pkg_name; ?></span><br/>
				<span class="price<?php echo $id; ?>">$<?php echo $stock->scsp_pkg_price; ?> (USD)</span>
			</div>
		</div>
		<div class="clear"></div>
		<div class="pkg_description<?php echo $id; ?>">
			<?php echo $stock->scsp_pkg_description; ?>
		</div>
	</div>
	<div class="pkg_footer<?php echo $id; ?>" align="center">
		<p>Enter your <?php echo $stock->scsp_pkg_for; ?> url</p>
		<div class="paypal_button"><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_cart">
			<input type="hidden" name="business" value="<?php $emails = $wpdb->get_results("SELECT * FROM wp_scsp_paypal");foreach ( $emails as $email ) { echo $email->scsp_paypal_email; }?>">
			<input type="hidden" name="lc" value="US">
			<input type="hidden" name="item_name" value="<?php echo site_url(); ?>">
			<input type="hidden" name="item_number" value="Product ID : <?php echo $id.", "; echo $stock->scsp_pkg_description; ?>">
			<input type="hidden" name="amount" value="<?php echo $stock->scsp_pkg_price; ?>">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="button_subtype" value="products">
			<input type="hidden" name="no_note" value="0">
			<input type="hidden" name="add" value="1">
			<input type="hidden" name="bn" value="PP-ShopCartBF:btn_cart_LG.gif:NonHostedGuest">
			<input type="hidden" name="on0" value="url">
			<table width="304" style="margin:0; padding:0; border:none">
				<tbody><tr><td class="paypal-input<?php echo $id; ?>"><input class="url2" type="text" name="os0" maxlength="200"></td></tr>
				</tbody>
			</table>
			<div class="paypal-bttn<?php echo $id; ?>"><input type="image" src="http://buyyoutubesubscribers.com/wp-content/themes/featurepitch/images/btn_cart_SK.gif.png" name="submit" alt="PayPal - The safer, easier way to pay online!"></div>
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
	</div>
	<div class="pkg_end<?php echo $id; ?>"></div>
</div>
<style type="text/css">
.pkg_wrapper<?php echo $id; ?>{
	float: left;
	height: auto;
	width: 291px;
	margin-top: 20px;
	margin-left: 15px;
	margin-right: 7px;
	z-index:1000;
	
    -moz-transition: all 0.5s ease;
    -ms-transition: all 0.5s ease;
    -o-transition: all 0.5s ease;
    transition: all 0.5s ease;
	}
.pkg_wrapper<?php echo $id; ?>:hover{
	/* Un-prefixed. Prefix `transform` for all target browsers. */
    transform: scale(1.1);
	}
.pkg_header<?php echo $id; ?>{
	background-image:url(<?php echo site_url(); ?>/wp-content/plugins/sweet-custom-social-packages/<?php echo $stock->scsp_pkg_for; ?>/packages-top.jpg);
	padding:37px;
	height:18px;
	}
.pkg_top<?php echo $id; ?>{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 30px;
	font-weight: bold;
	color: #FFFFFF;
	height: auto;
	margin-right:5px;
	margin-top:-15px;
	text-align:right;
	background:none;
	}
.pkg_middle<?php echo $id; ?>{
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/sweet-custom-social-packages/<?php echo $stock->scsp_pkg_for; ?>/packages-mid.jpg);
	background-repeat: repeat-y;
	float: left;
	height: auto;
	width: 291px;
	}
.pkg_title<?php echo $id; ?>{
	font-family: Arial, Helvetica, sans-serif;
	color: #41588C;
	text-align: center;
	font-size: 25px;
	font-weight: bold;
	text-decoration: none;
	margin-top: 10px;
	color:#7c2200;
	}
.pkg_image<?php echo $id; ?>{
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/sweet-custom-social-packages/<?php echo $stock->scsp_pkg_for; ?>/fb-icon.jpg);
	background-repeat: no-repeat;
	float: left;
	height: 96px;
	width: 67px;
	margin-top: 10px;
	margin-left: 10px;
	}
.pkg_price<?php echo $id; ?>{
	float: right;
	height: auto;
	width: 200px;
	margin-top: 10px;
	}
.clear{
	clear:both;
	width:100%;
	}
.pkg_description<?php echo $id; ?>{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	font-weight: normal;
	color: #333333;
	text-decoration: none;
	text-align: left;
	height: auto;
	width: 250px;
	margin-top: 10px;
	margin-right: auto;
	margin-bottom: 10px;
	margin-left: auto;
	text-align:center;
	}
.pkg_footer<?php echo $id; ?>{
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/sweet-custom-social-packages/<?php echo $stock->scsp_pkg_for; ?>/packages-mid.jpg);
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #000000;
	text-decoration: none;
	height: auto;
	width: 100%;
	padding-top: 10px;
	text-align: center;
	}
.pkg_footer<?php echo $id; ?> p{margin:0px}
.price<?php echo $id; ?>{
	color:#0a4281;
	margin-left:25px;
	font-size:20px;
	}
.pkg_end<?php echo $id; ?>{
	width:100%;
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/sweet-custom-social-packages/<?php echo $stock->scsp_pkg_for; ?>/packages-bottom.jpg);
	display:block;
	height:17px;
	}
.paypal-input<?php echo $id; ?>{text-align:center; border:none}
.paypal-input<?php echo $id; ?> input{width:200px}
</style>
	
	<?PHP
	}
	return ob_get_clean();
}

add_shortcode('custom_social_package', 'myShortCode');

?>
