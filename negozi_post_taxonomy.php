<?php
/**
 * Add new taxonomy for page and products.
 */

function create_shop_hierarchical_taxonomy() {
  $labels = array(
    'name' => _x( 'Luogo', 'taxonomy general name' ),
    'singular_name' => _x( 'Luogo', 'taxonomy singular name' ),
    'search_items' => __( 'Search Luogo' ),
    'all_items' => __( 'All Luoghi' ),
    'parent_item' => __( 'Parent Luogo' ),
    'parent_item_colon' => __( 'Parent Luogo:' ),
    'edit_item' => __( 'Edit Luogo' ),
    'update_item' => __( 'Update Luogo' ),
    'add_new_item' => __( 'Add New Luogo' ),
    'new_item_name' => __( 'New Luogo' ),
    'menu_name' => __( 'Luogo' ),
  );
  // taxonomy register
  $args = array(
    'labels' => $labels,
    'hierarchical' => true,
    'public' => true,
    'show_ui' => true,
    'has_archive' => true,
    'show_admin_column' => true,
    'show_in_nav_menus' => false,
    'show_in_rest' => true,
    'show_tagcloud' => false,
  	'rewrite' => array( 'slug' => 'luogo', 'with_front' => false ),
  );
  register_taxonomy('luogo',array('negozio'), $args );
  /**/
  $labels = array(
    'name' => _x( 'list Negozi', 'taxonomy general name' ),
    'singular_name' => _x( 'list Negozo', 'taxonomy singular name' ),
    'search_items' => __( 'Search Negozio' ),
    'all_items' => __( 'All Negozi' ),
    'parent_item' => __( 'Parent Negozi' ),
    'parent_item_colon' => __( 'Parent Negozi:' ),
    'edit_item' => __( 'Edit Negozio' ),
    'update_item' => __( 'Update Negozio' ),
    'add_new_item' => __( 'Add New Negozio' ),
    'new_item_name' => __( 'New Negozio' ),
    'menu_name' => __( 'Lista Negozi' ),
  );

  $args = array(
    'labels' => $labels,
    'hierarchical' => false,
    'public' => false,
    'show_ui' => true,
    'has_archive' => false,
    'show_admin_column' => true,
    'show_in_nav_menus' => false,
    'show_in_rest' => true,
    'show_tagcloud' => false,
  	'rewrite' => array( 'slug' => 'negozio-shop', 'with_front' => false ),
  );
  register_taxonomy('negozio-list', array('product','negozio'), $args );
  /**/
  $args =	array(
    'hierarchical'          => true,
    'update_count_callback' => '_wc_term_recount',
    'label'                 => __( 'Categories', 'woocommerce' ),
    'labels'                => array(
      'name'              => __( 'Product categories', 'woocommerce' ),
      'singular_name'     => __( 'Category', 'woocommerce' ),
      'menu_name'         => _x( 'Categories', 'Admin menu name', 'woocommerce' ),
      'search_items'      => __( 'Search categories', 'woocommerce' ),
      'all_items'         => __( 'All categories', 'woocommerce' ),
      'parent_item'       => __( 'Parent category', 'woocommerce' ),
      'parent_item_colon' => __( 'Parent category:', 'woocommerce' ),
      'edit_item'         => __( 'Edit category', 'woocommerce' ),
      'update_item'       => __( 'Update category', 'woocommerce' ),
      'add_new_item'      => __( 'Add new category', 'woocommerce' ),
      'new_item_name'     => __( 'New category name', 'woocommerce' ),
      'not_found'         => __( 'No categories found', 'woocommerce' ),
    ),
    'show_in_rest' => true,
    'show_ui'               => true,
    'query_var'             => true,
    'rewrite'               => array(
      'slug'         => $permalinks['category_rewrite_slug'],
      'with_front'   => false,
      'hierarchical' => true,
    ),
  );
  register_taxonomy('product_cat',array('product','negozio'), $args );
}


// Our custom post type function
function create_posttype() {
  register_post_type( 'negozio',
    array(
        'labels' => array(
            'name' => __( 'Negozi' ),
            'singular_name' => __( 'Negozio' )
        ),
        'public' => true,
        'has_archive' => false,
        'rewrite' => array('slug' => 'negozio'),
        'show_in_rest' => true,
    )
  );
}



/*
* Creating a function to create our CPT
*/
function custom_post_type() {

// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Negozio', 'Post Type General Name', 'twentytwenty' ),
        'singular_name'       => _x( 'Negozio', 'Post Type Singular Name', 'twentytwenty' ),
        'menu_name'           => __( 'Negozio', 'twentytwenty' ),
        'parent_item_colon'   => __( 'Parent Negozio', 'twentytwenty' ),
        'all_items'           => __( 'All negozio', 'twentytwenty' ),
        'view_item'           => __( 'View Negozio', 'twentytwenty' ),
        'add_new_item'        => __( 'Add New Negozio', 'twentytwenty' ),
        'add_new'             => __( 'Add New', 'twentytwenty' ),
        'edit_item'           => __( 'Edit Negozio', 'twentytwenty' ),
        'update_item'         => __( 'Update Negozio', 'twentytwenty' ),
        'search_items'        => __( 'Search Negozio', 'twentytwenty' ),
        'not_found'           => __( 'Not Found', 'twentytwenty' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwenty' ),
    );

    $args = array(
        'label'               => __( 'Negozio', 'twentytwenty' ),
        'description'         => __( 'Negozio news and reviews', 'twentytwenty' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions'),
        // You can associate this CPT with a taxonomy or custom taxonomy.
        //'taxonomies'          => array( 'genres' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'page',
        'show_in_rest' => true,
    );
    register_post_type( 'negozio', $args );
}

add_action('init', 'create_shop_hierarchical_taxonomy',12);
add_action( 'init', 'create_posttype' );
add_action( 'init', 'custom_post_type', 0 );
