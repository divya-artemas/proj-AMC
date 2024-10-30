<?php
add_action('wp_enqueue_scripts', 'keny_child_css', 1001);
// Load CSS
function keny_child_css() {
    wp_deregister_style( 'styles-child' );
    wp_register_style( 'styles-child', get_stylesheet_directory_uri() . '/style.css' );
    wp_enqueue_style( 'styles-child' );
}

// Redirect WooCommerce Shop URL
function wpc_shop_url_redirect() {
    if( is_shop() ){
        wp_redirect( home_url() ); // Assign custom internal page here
        exit();
    }
}
add_action( 'template_redirect', 'wpc_shop_url_redirect' );


/**
 * @snippet       Redirect To Shop @ WooCommerce Category Pages
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 7
 * @community     https://businessbloomer.com/club/
 */
 
add_action( 'wp', 'bbloomer_redirect_cats_to_shop' );
 
function bbloomer_redirect_cats_to_shop() {
   if ( is_product_category() ) {
      //wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
	    wp_redirect( home_url()."#products" );
      exit;
   }
}

/**
* @snippet       Print Script @ Checkout Footer - WooCommerce
* @how-to        Get CustomizeWoo.com FREE
* @author        Rodolfo Melogli
* @testedwith    WooCommerce 5
* @community     https://businessbloomer.com/club/
*/
 
add_action( 'wp_head', 'bbloomer_add_jscript_checkout', 9999 );
 
function bbloomer_add_jscript_checkout() {
   global $wp;
   if ( is_checkout() && empty( $wp->query_vars['order-pay'] ) && ! isset( $wp->query_vars['order-received'] ) ) {
      echo "<script>
  gtag('event', 'conversion', {
      'send_to': 'AW-933759614/GK8ECJDM59MZEP6UoL0D',
      'value': 1.0,
      'currency': 'INR',
      'transaction_id': ''
  });
</script>";
   }
}

//redirect to myaccount
add_filter( 'woocommerce_registration_redirect', 'custom_redirection_after_registration', 10, 1 );
function custom_redirection_after_registration( $redirection_url ){
    // Change the redirection Url
    $redirection_url = get_permalink( wc_get_page_id( 'myaccount' ) ); // My Account
    return $redirection_url; // Always return something
}

/*
//add link in myaccount
    add_action( 'woocommerce_account_content', 'action_woocommerce_account_content' );
    function action_woocommerce_account_content() {
        global $current_user; // The WP_User Object
        ?>
        <p><a href="https://wa.me/7625088742"><?php _e( 'Return/Exchange', 'woocommerce' ); ?></a></p>
        <?php
};
*/

// Add a custom menu item
add_filter('woocommerce_account_menu_items', 'add_my_account_custom_menu_item');
function add_my_account_custom_menu_item( $menu_items ) {
    $menu_item_key = 'custom_link';
    $menu_items[$menu_item_key] = __('Return/Exchange', 'woocommerce');
    return $menu_items;
}
   
// Replace the custom menu item Link
add_action('template_redirect', 'change_my_account_custom_menu_item_link', 10);
function change_my_account_custom_menu_item_link() {
    if ( is_user_logged_in() && is_account_page() ) {
        $menu_item_key = 'custom_link'; // HERE set the custom menu item key you are using
        $custom_link   = esc_url('https://wa.me/7625088742'); // HERE set your custom link
        // jQuery code
        wc_enqueue_js("$('li.woocommerce-MyAccount-navigation-link--{$menu_item_key} a').attr('href','{$custom_link}');");
		//wc_enqueue_js("$('li.woocommerce-MyAccount-navigation-link--{$menu_item_key} a').attr('href','{$custom_link}').attr('target','_blank');");
    }
}


//order editor
//
function hide_admin_menu_items() {
 
    $current_user = wp_get_current_user();
 
    if (in_array('author', $current_user->roles)) {
 
        remove_menu_page('edit.php'); // Posts
		remove_menu_page( 'edit.php?post_type=page' );    //Pages
 
        remove_menu_page('upload.php'); // Media
 
        remove_menu_page('link-manager.php'); // Links
 
        remove_menu_page('edit-comments.php'); // Comments
 
        remove_menu_page('themes.php'); // Appearance
 
        remove_menu_page('plugins.php'); // Plugins
 
        remove_menu_page('users.php'); // Users
 
        remove_menu_page('tools.php'); // Tools
 
        remove_menu_page('options-general.php'); // Settings
		remove_menu_page('wpseo_dashboard');
	
 
    }
 
}
 
add_action('admin_menu', 'hide_admin_menu_items');




/**
 * @snippet       Bulk (Dynamic) Pricing - WooCommerce
 * @how-to        businessbloomer.com/woocommerce-customization
 * @author        Rodolfo Melogli, Business Bloomer
 * @compatible    WooCommerce 3.8
 * @community     https://businessbloomer.com/club/
 * modified version with product ID en fixed price discount 
 */
  
add_action( 'woocommerce_before_calculate_totals', 'bbloomer_quantity_based_pricing', 9999 );
  
function bbloomer_quantity_based_pricing( $cart ) {
   global $product;
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) return;
  
    // Define discount rules and thresholds and product ID
    $threshold1 = 2; // Change price if items > 2
    $discount1 = 1245; // Reduce unit price by 1245 rs
    $threshold2 = 200; // Change price if items > 199
    $discount2 = 1245; // Reduce unit price by 0.20 euro
    $product_id = 53174; //zwollenaartje
 
    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
      $product_in_cart = $cart_item['product_id'];
        if ( $product_in_cart === $product_id && $cart_item['quantity'] >= $threshold1 && $cart_item['quantity'] < $threshold2 ) {
           $price = round( $cart_item['data']->get_price() - $discount1, 2 );
           $cart_item['data']->set_price( $price );
        } elseif ( $product_in_cart === $product_id && $cart_item['quantity'] >= $threshold2 ) {
           $price = round( $cart_item['data']->get_price() - $discount2, 2 );
           $cart_item['data']->set_price( $price );
        }    
      }
}

