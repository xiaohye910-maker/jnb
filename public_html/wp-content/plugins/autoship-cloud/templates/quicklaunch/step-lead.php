<?php
/**
 * The lead capture step for the Autoship Quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

defined( 'ABSPATH' ) || exit;

$wizard_user = wp_get_current_user();
$username    = $wizard_user->user_login;
$email       = $wizard_user->user_email;
?>

<div class="autoship-quicklaunch-content-step">
	<h1 class="autoship-font-30"><span class="autoship-orange-text"><?php echo esc_html( __( 'Help us', 'autoship' ) ); ?></span> <?php echo esc_html( __( 'get to know your store', 'autoship' ) ); ?></h1>

	<p class="autoship-font-sm">
		<?php echo esc_html( __( "Tell us a little about your WooCommerce store — who you are, what you sell, and where you're headed — so we can better support your journey with Autoship.", 'autoship' ) ); ?>
	</p>

	<div class="autoship-quicklaunch-content-step-fields">
		<div class="autoship-mb-20">
			<label for="autoship-lead-email" class="autoship-quicklaunch-field-label"><?php echo esc_html( __( 'What is your primary contact email?', 'autoship' ) ); ?></label>
			<input type="email" required="required" name="autoship-lead-email" id="autoship-lead-email" class="autoship-quicklaunch-field-text" placeholder="oswald@beautyloop.com" value="<?php echo esc_attr( $email ); ?>" />
		</div>

		<div class="autoship-mb-20">
			<label for="autoship-lead-role" class="autoship-quicklaunch-field-label"><?php echo esc_html( __( 'What is your role?', 'autoship' ) ); ?></label>
			<select required="required" name="autoship-lead-role" id="autoship-lead-role" class="autoship-quicklaunch-field-select">
				<option value="" disabled="disabled" selected="selected"><?php echo esc_html( __( 'Please choose one', 'autoship' ) ); ?></option>
				<option value="agency"><?php echo esc_html( __( 'Agency', 'autoship' ) ); ?></option>
				<option value="owner"><?php echo esc_html( __( 'Business Owner', 'autoship' ) ); ?></option>
				<option value="support"><?php echo esc_html( __( 'Customer Support', 'autoship' ) ); ?></option>
				<option value="designer"><?php echo esc_html( __( 'Web Designer', 'autoship' ) ); ?></option>
				<option value="developer"><?php echo esc_html( __( 'Web Developer', 'autoship' ) ); ?></option>
				<option value="employee"><?php echo esc_html( __( 'Other - Employee', 'autoship' ) ); ?></option>
				<option value="client-work"><?php echo esc_html( __( 'Other - Client work', 'autoship' ) ); ?></option>
				<option value="other"><?php echo esc_html( __( 'Other', 'autoship' ) ); ?></option>
			</select>
		</div>

		<div class="autoship-mb-20">
			<label for="autoship-lead-revenue" class="autoship-quicklaunch-field-label">
				<?php echo esc_html( __( 'What is your expected revenue for the next 12 months?', 'autoship' ) ); ?>
			</label>
			<select required="required" name="autoship-lead-revenue" id="autoship-lead-revenue" class="autoship-quicklaunch-field-select">
				<option value="" disabled="disabled" selected="selected"><?php echo esc_html( __( 'Please choose one', 'autoship' ) ); ?></option>
				<option value="0-100K"><?php echo esc_html( __( '$0 - $100,000', 'autoship' ) ); ?></option>
				<option value="100K-500K"><?php echo esc_html( __( '$100,000 - $500,000', 'autoship' ) ); ?></option>
				<option value="500K-1M"><?php echo esc_html( __( '$500,00 - $1,000,0000', 'autoship' ) ); ?></option>
				<option value="1M-10M"><?php echo esc_html( __( '$1,000,000 - $10,000,0000', 'autoship' ) ); ?></option>
				<option value="10M-50M"><?php echo esc_html( __( '$10,000,000 - $50,000,000', 'autoship' ) ); ?></option>
				<option value="50M-100M"><?php echo esc_html( __( '$50,000,000 - $100,000,000', 'autoship' ) ); ?></option>
				<option value="100M+"><?php echo esc_html( __( 'Over $100 million', 'autoship' ) ); ?></option>
			</select>
		</div>

		<div class="autoship-mb-20">
			<label for="autoship-lead-category" class="autoship-quicklaunch-field-label">
				<?php echo esc_html( __( 'What is your primary e-commerce selling category?', 'autoship' ) ); ?>
			</label>
			<select required="required" name="autoship-lead-category" id="autoship-lead-category" class="autoship-quicklaunch-field-select">
				<option value="" disabled="disabled" selected="selected"><?php echo esc_html( __( 'Please choose one', 'autoship' ) ); ?></option>
				<option value="Apparel and Fashion"><?php echo esc_html( __( 'Apparel and Fashion', 'autoship' ) ); ?></option>
				<option value="Automotive Parts and Accessories"><?php echo esc_html( __( 'Automotive Parts and Accessories', 'autoship' ) ); ?></option>
				<option value="Automotive Services"><?php echo esc_html( __( 'Automotive Services', 'autoship' ) ); ?></option>
				<option value="Beauty and Personal Care Services"><?php echo esc_html( __( 'Beauty and Personal Care Services', 'autoship' ) ); ?></option>
				<option value="Books, Music, and Video"><?php echo esc_html( __( 'Books, Music, and Video', 'autoship' ) ); ?></option>
				<option value="CBD, Delta-9, and Hemp"><?php echo esc_html( __( 'CBD, Delta-9, and Hemp', 'autoship' ) ); ?></option>
				<option value="Child Care and Day Care Services"><?php echo esc_html( __( 'Child Care and Day Care Services', 'autoship' ) ); ?></option>
				<option value="Cleaning and Maintenance Services"><?php echo esc_html( __( 'Cleaning and Maintenance Services', 'autoship' ) ); ?></option>
				<option value="Digital Music Distribution / Music Streaming Services"><?php echo esc_html( __( 'Digital Music Distribution / Music Streaming Services', 'autoship' ) ); ?></option>
				<option value="Education and Training Services"><?php echo esc_html( __( 'Education and Training Services', 'autoship' ) ); ?></option>
				<option value="Electronics and Appliances"><?php echo esc_html( __( 'Electronics and Appliances', 'autoship' ) ); ?></option>
				<option value="Event Planning and Catering"><?php echo esc_html( __( 'Event Planning and Catering', 'autoship' ) ); ?></option>
				<option value="Financial Services"><?php echo esc_html( __( 'Financial Services', 'autoship' ) ); ?></option>
				<option value="Fitness and Recreational Sports Centers"><?php echo esc_html( __( 'Fitness and Recreational Sports Centers', 'autoship' ) ); ?></option>
				<option value="Florists/Floral Services"><?php echo esc_html( __( 'Florists/Floral Services', 'autoship' ) ); ?></option>
				<option value="Food and Beverage"><?php echo esc_html( __( 'Food and Beverage', 'autoship' ) ); ?></option>
				<option value="Furniture and Home Furnishings"><?php echo esc_html( __( 'Furniture and Home Furnishings', 'autoship' ) ); ?></option>
				<option value="Gardening and Horticulture"><?php echo esc_html( __( 'Gardening and Horticulture', 'autoship' ) ); ?></option>
				<option value="General Merchandise"><?php echo esc_html( __( 'General Merchandise', 'autoship' ) ); ?></option>
				<option value="Hardware and Garden Supplies"><?php echo esc_html( __( 'Hardware and Garden Supplies', 'autoship' ) ); ?></option>
				<option value="Health and Personal Care"><?php echo esc_html( __( 'Health and Personal Care', 'autoship' ) ); ?></option>
				<option value="Health and Wellness Supplements"><?php echo esc_html( __( 'Health and Wellness Supplements', 'autoship' ) ); ?></option>
				<option value="Healthcare Services"><?php echo esc_html( __( 'Healthcare Services', 'autoship' ) ); ?></option>
				<option value="Jewelry, Luggage, and Leather Goods"><?php echo esc_html( __( 'Jewelry, Luggage, and Leather Goods', 'autoship' ) ); ?></option>
				<option value="Kitchen and Dining Gadgets / Home and Kitchen Appliances"><?php echo esc_html( __( 'Kitchen and Dining Gadgets / Home and Kitchen Appliances', 'autoship' ) ); ?></option>
				<option value="Legal Services"><?php echo esc_html( __( 'Legal Services', 'autoship' ) ); ?></option>
				<option value="Office Supplies, Stationery, and Gifts"><?php echo esc_html( __( 'Office Supplies, Stationery, and Gifts', 'autoship' ) ); ?></option>
				<option value="Online Marketplace / Multi-Vendor E-Commerce"><?php echo esc_html( __( 'Online Marketplace / Multi-Vendor E-Commerce', 'autoship' ) ); ?></option>
				<option value="Personal Development and Lifestyle Services"><?php echo esc_html( __( 'Personal Development and Lifestyle Services', 'autoship' ) ); ?></option>
				<option value="Personalized Gifts and Custom Printing"><?php echo esc_html( __( 'Personalized Gifts and Custom Printing', 'autoship' ) ); ?></option>
				<option value="Pet and Pet Supplies"><?php echo esc_html( __( 'Pet and Pet Supplies', 'autoship' ) ); ?></option>
				<option value="Pet Care Services"><?php echo esc_html( __( 'Pet Care Services', 'autoship' ) ); ?></option>
				<option value="Real Estate"><?php echo esc_html( __( 'Real Estate', 'autoship' ) ); ?></option>
				<option value="Restaurants and Food Services"><?php echo esc_html( __( 'Restaurants and Food Services', 'autoship' ) ); ?></option>
				<option value="Specialty Retail"><?php echo esc_html( __( 'Specialty Retail', 'autoship' ) ); ?></option>
				<option value="Sporting Goods, Hobbies, and Musical Instruments"><?php echo esc_html( __( 'Sporting Goods, Hobbies, and Musical Instruments', 'autoship' ) ); ?></option>
				<option value="Thrift Stores and Consignment Shops"><?php echo esc_html( __( 'Thrift Stores and Consignment Shops', 'autoship' ) ); ?></option>
				<option value="Tobacco and Vape"><?php echo esc_html( __( 'Tobacco and Vape', 'autoship' ) ); ?></option>
				<option value="Travel and Tourism"><?php echo esc_html( __( 'Travel and Tourism', 'autoship' ) ); ?></option>
				<option value="Unknown and Other"><?php echo esc_html( __( 'Unknown and Other', 'autoship' ) ); ?></option>
			</select>
		</div>

		<div class="autoship-mb-20">
			<label for="autoship-lead-reason" class="autoship-quicklaunch-field-label">
				<?php echo esc_html( __( 'What are you looking to do today?', 'autoship' ) ); ?>
			</label>
			<select required="required" name="autoship-lead-reason" id="autoship-lead-reason" class="autoship-quicklaunch-field-select">
				<option value="" disabled="disabled" selected="selected"><?php echo esc_html( __( 'Please choose one', 'autoship' ) ); ?></option>
				<option value="new-store"><?php echo esc_html( __( 'I have a store that is new (or not live yet) and want to launch an Autoship or a Subscription program', 'autoship' ) ); ?></option>
				<option value="live-store"><?php echo esc_html( __( 'I have a store that is already live and want to launch an Autoship or Subscription option', 'autoship' ) ); ?></option>
				<option value="migrate-store"><?php echo esc_html( __( 'I am migrating a store and want to launch an Autoship or Subscription option', 'autoship' ) ); ?></option>
				<option value="migrate-platform"><?php echo esc_html( __( 'I am migrating existing subscriptions from another platform to Autoship', 'autoship' ) ); ?></option>
				<option value="tryout"><?php echo esc_html( __( 'I like the idea of Autoship and want to try it out for a project', 'autoship' ) ); ?></option>
			</select>
		</div>

		<div class="autoship-mb-20">
			<input required="required" type="checkbox" name="autoship-lead-terms" id="autoship-lead-terms" value="true"/>
			<label for="autoship-lead-terms">
				<small><?php echo esc_html( __( 'I accept the', 'autoship' ) ); ?> <a href="https://qpilot.com/terms-of-service" target="_blank" class="autoship-purple-text autoship-text-no-decoration"><?php echo esc_html( __( 'Terms of Service', 'autoship' ) ); ?></a> <?php echo esc_html( __( 'and', 'autoship' ) ); ?> <a href="https://qpilot.com/privacy-policy" target="_blank" class="autoship-purple-text autoship-text-no-decoration"><?php echo esc_html( __( 'Privacy Policy', 'autoship' ) ); ?></a></small>
			</label>
		</div>
		<div id="autoship-quicklaunch-form-errors" class="autoship-text-danger autoship-font-sm" style="text-align: center; min-height: 30px;"></div>
		<div class="autoship-quicklaunch-content-step-actions">
			<div class="autoship-mb-20">
				<button type="button" id="lead-next-button" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100"><?php echo esc_html( __( 'Continue', 'autoship' ) ); ?></button>
				<a href="#" id="welcome-dismiss-button" class="autoship-quicklaunch-content-steps-action-link"><?php echo esc_html( __( "I'll set up my store manually", 'autoship' ) ); ?></a>
			</div>
		</div>
	</div>
</div>
