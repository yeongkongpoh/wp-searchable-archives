<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/yeongkongpoh
 * @since      1.0.0
 *
 * @package    Searchable_Archives
 * @subpackage Searchable_Archives/admin/partials
 */
?>

<?php 
function get_taxonomy_hierarchy( $taxonomy, $parent = 0 ) {
	// only 1 taxonomy
	$taxonomy = is_array( $taxonomy ) ? array_shift( $taxonomy ) : $taxonomy;
	// get all direct decendants of the $parent
	$terms = get_terms( $taxonomy, array( 'parent' => $parent ) );
	// prepare a new array.  these are the children of $parent
	// we'll ultimately copy all the $terms into this new array, but only after they
	// find their own children
	$children = array();
	// go through all the direct decendants of $parent, and gather their children
	foreach ( $terms as $term ){
		// recurse to get the direct decendants of "this" term
		$term->children = get_taxonomy_hierarchy( $taxonomy, $term->term_id );
		// add the term to our new array
		$children[ $term->term_id ] = $term;
	}
	// send the results back to the caller
	return $children;
}



    $args = array(
        'post_type'  => 'page',
        // 'meta_key' => '_wpsa_page',
        'meta_query' => array(
            array(
                'key' => '_wpsa_page',
                'value' => 1,
                'compare' => '=',
            ),
            array(
                'key' => '_wpsa_page_post_type',
                'value' => 'job_opportunity',
                'compare' => '=',
            )
        )
     );
     $query = new WP_Query($args);

    //  var_dump($query->have_posts());
     function echo_term_name($term) {
         echo '<p>' . $term->name . '</p>';
        foreach($term->children as $child) {
            echo_term_name($child);
        }
    }
       /** The taxonomy we want to parse */
   $taxonomy = "product_categories";
   /** Get all taxonomy terms */
//    $terms = get_terms($taxonomy, array(
//            "hide_empty" => false
//        )
//    );

    $h = get_taxonomy_hierarchy($taxonomy);

    // var_dump($h);

    // foreach($h as $term) {
    //     echo_term_name($term);
    // }
  

       
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<form method="post" action="options.php" name="searchable-archives_options">
       <?php 
       //$options = get_option($this->plugin_name); var_dump($options); 
       settings_fields($this->plugin_name); ?> 
    <p>Selecting a post type will generate phantom page(s) for the selected post type and it's related taxonomy's terms.</p>
    <p>Phantom page(s) are created as part of the "page" post type.</p>
    <h2>Select Post Types</h2>
    <?php $this->get_post_types(); ?>

    <!-- <h2>Select Taxonomies</h2> -->
    <?php //$this->get_all_taxonomies(); ?>

    <?php submit_button( __( 'Save all changes', $this->plugin_name ), 'primary','submit', TRUE ); ?>
</form>
