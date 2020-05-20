<?php
/**
 * shortcode
 */


 /**
  * Add new shortcode [luogo luogo_id="x" cat_id="y" ]
  */
 function wpc_elementor_shortcode2( $atts ) {
   $taxonomy = 'luogo';
   $term = get_term($atts['luogo_id']);
   $shop_luogo_ids = get_objects_in_term($atts['luogo_id'],'luogo');
   $shop_category = get_objects_in_term($atts['cat_id'],'product_cat');
   echo '<div class="woo-listing-top">';
   echo '<div data-alignment="left" data-products="type-1">';
   echo '<ul class="products columns-4">';
   foreach($shop_category as $shop) {
     if(in_array($shop,$shop_luogo_ids)) {
       echo  '<li class="product-category product first">';
         $page = get_page($shop);
         $link = get_page_link($shop);
         echo '<a href="'.$link.'">'. $page->post_title . '</a>';
         echo get_the_post_thumbnail($shop, 'thumbnail');
       echo "</li>";
     }
   }
   echo "</ul></div></div>";
   echo "</ul></div>";
 }


 /* Add new shortcode [main_select] */
 function wpc_select_shortcode( $atts ) {
   $terms = get_terms( 'luogo', array(
     'hide_empty' => false,
   ));
   echo '<input id="main-select-country" type="text" list="cars" /><datalist id="cars">';
   foreach($terms as $t) {
     echo "<option data-post=\"33\" value=\"".$t->name."\">".$t->name."</option>";
   }
   echo '</datalist></input>';
   echo '<a id="main-link-country" class="hfe-menu-item elementor-button" aria-haspopup="true" aria-expanded="false">SCEGLI LA TUA CITTA’</a>';
 }


 /**
  * Add new shortcode [checkout_negozio]
  */
 function wpc_elementor_shortcode( $atts ) {
   $shop_tax = 'negozio-list';
   $prod_tax = 'product_cat';
   $post = _getNegozioByPath();
   $n_term = wp_get_post_terms($post->ID,$shop_tax);
   $p_term = wp_get_post_terms($post->ID,$prod_tax);
   if(empty($n_term)) { return false;}

   $n_term=reset($n_term);
   $product_ids = get_objects_in_term($n_term->term_id, $shop_tax);
   foreach($p_term as $pt) {
     if($pt->parent == 0) { // [cibo asporto]
       $child = get_term_children($pt->term_id, $prod_tax);
       if(!empty($child)) {
         $opt = array('link' => false,'separator' => '', 'inclusive' => true);
         $parent = get_term_parents_list( $pt->term_id, $prod_tax ,$opt);
         echo '<h3>' . $parent . '</h3>';
         foreach ($child as $ids) {
           $tmp_ids = [];
           foreach($product_ids  as $p) {
             if(has_term($ids,$prod_tax,$p)) {
               $tmp_ids[$p] = $p;
             }
           }
           if(!empty($tmp_ids)) {
               echo '<h4>' . get_term($ids)->name . '</h4>';
               echo implode(",",$tmp_ids);
               $shortcode = do_shortcode( shortcode_unautop('[products ids="'.implode(",",$tmp_ids).'" ]'));
               echo $shortcode;
           }
         }
       }
      }
   }
 }

 add_shortcode( 'product_negozio', 'wpc_elementor_shortcode');
 add_shortcode( 'luogo', 'wpc_elementor_shortcode2');
 add_shortcode( 'main_select', 'wpc_select_shortcode');

 ?>
