<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/yeongkongpoh
 * @since      1.0.0
 *
 * @package    Searchable_Archives
 * @subpackage Searchable_Archives/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Searchable_Archives
 * @subpackage Searchable_Archives/includes
 * @author     Gerald Yeong <gnyeong@gmail.com>
 */
class Searchable_Archives_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$args = array(
			'post_type'  => 'page',
			'posts_per_page'   => -1,
			// 'meta_key' => '_wpsa_page',
			'meta_query' => array(
				array(
					'key' => '_wpsa_page',
					'value' => 1,
					'compare' => '=',
				)
			)
		 );
		 $query = new WP_Query($args);

		//  var_dump($query);

		 if ($query->have_posts()) {
			while ( $query->have_posts() ) :
			  $query->the_post();
			  $term_id = get_post_meta(get_the_ID(), '_wpsa_page_term_id', true);
			  if(!empty($term_id)) {
				  delete_term_meta($term_id, '_wpsa_page_id');
			  }
				wp_delete_post(get_the_ID(), true);
			endwhile;
		  }
		  wp_reset_postdata();
	}

	public static function delete_all_wpsa_pages() {
		$args = array(
			'post_type'  => 'page',
			'posts_per_page'   => -1,
			// 'meta_key' => '_wpsa_page',
			'meta_query' => array(
				array(
					'key' => '_wpsa_page',
					'value' => 1,
					'compare' => '=',
				)
			)
		 );
		 $query = new WP_Query($args);

		//  var_dump($query);

		 if ($query->have_posts()) {
			while ( $query->have_posts() ) :
			  $query->the_post();
			  $term_id = get_post_meta(get_the_ID(), '_wpsa_page_term_id', true);
			  if(!empty($term_id)) {
				  delete_term_meta($term_id, '_wpsa_page_id');
			  }
				wp_delete_post(get_the_ID(), true);
			endwhile;
		  }
		  wp_reset_postdata();
	}

}
