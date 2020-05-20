<?php
/**
 *
 * @package           Pringoo
 * @author            Ale
 * @link              http://www.siteground.com/
 *
 * @wordpress-plugin
 * Plugin Name:       Pringoo Carts
 * Plugin URI:        https://siteground.com
 * Description:       Carts alters
 * Version:           5.5.4
 * Author:            Ale
 */

function ___logs($txt) {
  file_put_contents('/logs/errors.log', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
}

/**
 * Utility
 */
 function _getNegozioByPath() {
   if($_SERVER['REQUEST_METHOD'] != "GET") {}
   $explode_url = explode( '/', $_SERVER["REQUEST_URI"] );
   if(in_array('negozio',$explode_url)) {
     $index = array_search('negozio', $explode_url);

     if(isset($explode_url[$index+1])) {
       $replace   = array($explode_url[$index+1]);
       $slug = str_replace(array("@a"), $replace, "@a");

       $negozio = get_page_by_path($slug, OBJECT, 'negozio');
       if(!empty(wp_get_post_terms($negozio->ID,'negozio-list'))) {
         return $negozio;
       }
     }
   }
   return false;
 }

/* Include */
include( plugin_dir_path( __FILE__ ) . 'widget.php');
include( plugin_dir_path( __FILE__ ) . 'shortcode.php');
include( plugin_dir_path( __FILE__ ) . 'negozi_post_taxonomy.php');


/*
* carts utils
*/
function pringoo_post_is_shop($ret = "shop") {
  $shop_ID = _getNegozioByPath();
  //var_dump($shop_ID);
  if($shop_ID == false) { return false;}
  $shop_term_ID = wp_get_post_terms($shop_ID->ID,  'negozio-list');
  if(empty($shop_term_ID)) { return false; }
  if($ret == "term") { return reset($shop_term_ID)->term_id; }
//  var_dump($shop_ID->ID);
  return $shop_ID->ID;
}


// producst is_in shop
function __pringoo_prod_in_shop($shop_id, $pid) {
  $term_shop_id = wp_get_post_terms($shop_id,'negozio-list');
  $product_shop_id = wp_get_post_terms($pid,'negozio-list');
  if(reset($term_shop_id)->term_id == reset($product_shop_id)->term_id) {
    return false;
  }
  return true;
}

// shop_id by shop producs_id
function pringoo_get_shop_by_producs($pid) {
  $shop_term_id = wp_get_post_terms($pid,'negozio-list');
  if(empty($shop_term_id)) { return null;}
  return reset($shop_term_id)->term_id;
}

// all_products by shop post_id
function pringoo_get_products_by_shop($shop_id) {
  $shop_term_id = wp_get_post_terms($shop_id,'negozio-list');
  if(empty($shop_term_id)) {
    return array();
  }
  $shop_term_id = reset($shop_term_id)->term_id;
  return get_objects_in_term($shop_term_id, 'negozio-list');
}

// set current shop in session
function pringoo_set_current_shop($stoken, $shop_ID) {
  if(!isset($_SESSION[$stoken]["current_shop"]) || $_SESSION[$stoken]["current_shop"] != $shop_ID ) {
    $_SESSION[$stoken]["current_shop"] = $shop_ID;
    return true;
  }
  return false;
}

// re add products to carts
function __pringoo_add_to_carts_temps($stoken, $shop_term_ID) {
  if(!isset($_SESSION[$stoken]["products"])
    || !isset($_SESSION[$stoken]["products"][$shop_term_ID])
    || empty($_SESSION[$stoken]["products"][$shop_term_ID])
  ) {
    return false;
  }
  $producs = $_SESSION[$stoken]["products"][$shop_term_ID];
  $items = wc()->cart->get_cart();
  $all = [];
  foreach($items as $item => $values) {
    $all[] = $values['product_id'];
  }
  foreach($producs as $pid) {
    if(!in_array($pid,$all)) {
      wc()->cart->add_to_cart($pid);
    }
  }
}

// add or remove products to tmp session
function __pringoo_add_to_cart_session($pid , $flag = true) {
  if(session_status() == PHP_SESSION_NONE) {session_start();}
  $stoken = wp_get_session_token();
  if(!isset($_SESSION[$stoken]["products"])) {
    $_SESSION[$stoken]["products"] = [];
  }
  $shop_term_id = pringoo_get_shop_by_producs($pid);
  if($shop_term_id == null) {return false;}
  if($flag  == true) {
    $_SESSION[$stoken]["products_all"][$pid] = $pid;
    $_SESSION[$stoken]["products"][$shop_term_id][$pid] = $pid;
  }
  else {
    unset($_SESSION[$stoken]["products_all"][$pid]);
    unset($_SESSION[$stoken]["products"][$shop_term_id][$pid]);
  }
}
/*end pringoo_get_products_by_sho*/


/**
 * Pringoo start session
 * @hook init
 */
function pringoo_startSession() {
  ___logs("pringoo_startSession");
  ___logs($_SERVER['REQUEST_METHOD']);
  if(session_status() == PHP_SESSION_NONE) {session_start();}
  if (!method_exists( $GLOBALS['woocommerce']->cart, 'add_to_cart' ) ) {
    return false;
  }
  if($_SERVER['REQUEST_METHOD'] == "GET" && !isset($_SERVER['HTTP_REFERER'])) {
  //  return false;
  }
  add_action( 'woocommerce_cart_loaded_from_session', 'pringoo_cart_loaded_from_session' );
  add_action( 'woocommerce_cart_remove_cart_item_from_session', 'pringoo_remove_cart_item_from_session',111, 11  );
  add_action( 'woocommerce_pre_remove_cart_item_from_session', 'pringoo_pre_remove_cart_item_from_session', 111, 11 );

  //var_dump("ss");
  $shop_ID = pringoo_post_is_shop("shop");
  //$A = pringoo_post_is_shop("shop");
  //var_dump($A);
  //var_dump(is_numeric($shop_ID));
  if(!is_numeric($shop_ID)) { return false;}
  $stoken = wp_get_session_token();
  $new_shop = pringoo_set_current_shop($stoken, $shop_ID);


}
add_action('wp_enqueue_scripts', 'collectiveray_load_js_script');
add_action('woocommerce_remove_cart_item', 'pringoo_remove_cart_item',11, 98);
add_action('woocommerce_add_to_cart', "printgoo_add_to_cart", 11, 99 );
add_action('init', 'pringoo_startSession', 1);

/**
 * @hoock woocommerce_pre_remove_cart_item_from_session
 */
function pringoo_pre_remove_cart_item_from_session($key, $session_data, $values)  {
  if(session_status() == PHP_SESSION_NONE) {session_start();}
  $pid = $values['product_id'];
  $stoken = wp_get_session_token();
  $shop_id = $_SESSION[$stoken]["current_shop"];
  //  var_dump("test");
  //  var_dump( __pringoo_prod_in_shop($shop_id, $pid));
  return __pringoo_prod_in_shop($shop_id, $pid);
}
/**
 * @hoock woocommerce_cart_loaded_from_session
 */
function pringoo_cart_loaded_from_session($cart) {
  ___logs("my cart_loaded_from_session");
  if(session_status() == PHP_SESSION_NONE) {session_start();}
  $stoken = wp_get_session_token();
  $shop_ID = $_SESSION[$stoken]["current_shop"];
  $shop_term_ID = wp_get_post_terms($shop_ID,  'negozio-list');
  if(empty($shop_term_ID)) { return false; }
  $shop_term_ID = reset($shop_term_ID)->term_id;
  __pringoo_add_to_carts_temps($stoken, $shop_term_ID);
}


/**
 * @hoock woocommerce_remove_cart_item
 */
function pringoo_remove_cart_item($cart_item_key , $carts) {
  $id =  $carts->cart_contents[$cart_item_key]['product_id'];
  __pringoo_add_to_cart_session($id, false);
  return true;
}

/**
 * @hoock woocommerce_add_cart_item
 */
function printgoo_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
  __pringoo_add_to_cart_session($product_id);
}



/*add js carts*/
function collectiveray_load_js_script() {
  $shop_ID = pringoo_post_is_shop();
  if($shop_ID == false) { return false;}
  ___logs("collectiveray_load_js_script");
  wp_enqueue_script('js-file', '/wp-content/plugins/pringoo/cart.js', array('jquery'), '', true);
  $empty = $_SESSION["current_cart_empty"];
  //var_dump($shop_ID);
  $params = array(
      'ajax_url'                     => WC()->ajax_url(),
      'wc_ajax_url'                  => WC_AJAX::get_endpoint( '%%endpoint%%' ),
      'update_shipping_method_nonce' => wp_create_nonce( 'update-shipping-method' ),
      'apply_coupon_nonce'           => wp_create_nonce( 'apply-coupon' ),
      'remove_coupon_nonce'          => wp_create_nonce( 'remove-coupon' ),
      'cart_empty' => $empty,
      'shop_id' => $shop_ID,
  );
  wp_localize_script( 'js-file', 'wc_cart_params', $params);
}




function pringoo_remove_cart_item_from_session($key, $values) {
//  var_dump("pringoo_remove_cart_item_from_session");
}

/*
// Mini Cart update with AJAX
add_filter( 'woocommerce_add_to_cart_fragments', 'custom_cart_count_fragments', 10, 1 );
function custom_cart_count_fragments( $fragments ) {
    $t = $fragments;
    foreach ($t  as $key => $value) {
      ___logs($key);
    }
    //$fragments['div.cart-totals'] = '<div class="cart-totals">' . WC()->cart->get_cart_contents_count() . '</div>';
    return $fragments;
}*/
/*

function filter_woocommerce_get_cart_item_from_session( $session_data, $values, $key ) {
  return $session_data;
};
add_filter( 'woocommerce_get_cart_item_from_session', 'filter_woocommerce_get_cart_item_from_session', 11, 99 );
*/
