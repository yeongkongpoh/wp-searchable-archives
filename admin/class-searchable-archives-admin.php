<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/yeongkongpoh
 * @since      1.0.0
 *
 * @package    Searchable_Archives
 * @subpackage Searchable_Archives/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Searchable_Archives
 * @subpackage Searchable_Archives/admin
 * @author     Gerald Yeong <gnyeong@gmail.com>
 */
class Searchable_Archives_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Searchable_Archives_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Searchable_Archives_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/searchable-archives-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Searchable_Archives_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Searchable_Archives_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/searchable-archives-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	

	public function options_update() {
		register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
	}

	public function validate($input) {
		// All checkboxes inputs        
		$valid = array();
	 
		//Quote title
		// $valid['chosen-post-types'] = (isset($input['chosen-post-types']) && !empty($input['chosen-post-types'])) ? 1 : 0;

		// var_dump($input);

		//return 1;
		return $input;
	 }
    
    public function plugin_page_init() {

        add_options_page(
            'Searchable Archives Plugin',
            'Searchable Archives Settings',
            'manage_options',
            $this->plugin_name,
            array($this, 'plugin_page_callback')
		);
		
		$this->insert_phantom_pages_init();


	}

	public function page_meta_updated_hook($id, $post, $updated) {
		$pt = get_post_meta($id, '_wpsa_page_post_type', true);
		// var_dump($pt);
		// var_dump($updated);
		if(!empty($pt)) {
			if($updated) {
				// $pt = get_post_meta($id, '_wpsa_page_post_type', true);
				$obj_tax = get_object_taxonomies( $pt ); 
				$term_id = get_post_meta((int) $id, '_wpsa_page_term_id', true);
				$term = get_term($term_id);
				// var_dump($obj_tax[0]);
				// var_dump($term_id);
				foreach($obj_tax as $ot) {
					if($term->description !== $post->post_excerpt) {
						wp_update_term($term_id, $ot, array(
							'description' => $post->post_excerpt
						));
					}
					
				}
	
			}
		}
		
	}

	public function term_meta_updated_hook($id, $tax) {
		$page_id = get_term_meta( (int) $id, '_wpsa_page_id', true );

		// var_dump($page_id);

		if(!empty($page_id)) {
			$term = get_term($id);
			$post = get_post(array(
				'ID' => (int) $page_id,
				'post_type' => 'page'
			));

			$my_post = array(
				'ID'           => (int) $page_id,
				'post_type'	   => 'page',
				'post_excerpt' => $term->description
			);
		  
		  // Update the post into the database
		  if($term->description !== $post->post_excerpt) {
			wp_update_post( $my_post );
			
			}

	
		}
	}

	public function get_taxonomy_hierarchy( $taxonomy, $parent = 0 ) {
		// only 1 taxonomy
		$taxonomy = is_array( $taxonomy ) ? array_shift( $taxonomy ) : $taxonomy;
		// get all direct decendants of the $parent
		$terms = get_terms( $taxonomy, array( 'parent' => $parent, 'hide_empty' => false ) );
		// prepare a new array.  these are the children of $parent
		// we'll ultimately copy all the $terms into this new array, but only after they
		// find their own children
		$children = array();
		// go through all the direct decendants of $parent, and gather their children
		foreach ( $terms as $term ){
			// recurse to get the direct decendants of "this" term
			$term->children = $this->get_taxonomy_hierarchy( $taxonomy, $term->term_id );
			// add the term to our new array
			$children[ $term->term_id ] = $term;
		}
		// send the results back to the caller
		return $children;
	}

	public function create_page_for_term($term, $slug, $chosen_post_type, $parent_id) {
		$queried_post_type_post = get_page_by_path($slug,OBJECT,'page');

		$queried_post = get_page_by_path($slug . '/' . $term->slug,OBJECT,'page');

		if(empty($queried_post)) {
			if(!empty($queried_post_type_post)) {

				// create
				$my_post = array(
					'post_title'    => $term->name,
					'post_name'		=> $term->slug,
					'post_excerpt'	=> $term->description,
					'post_type'		=> 'page',
					'post_status'   => 'publish',
					'post_author'   => 1
				);
				// // var_dump($parent);
				$my_post['post_parent'] = $parent_id;

				$page_id = wp_insert_post( $my_post );
				update_post_meta( (int) $page_id, '_wpsa_page', true );
				update_post_meta( (int) $page_id, '_wpsa_page_post_type', $chosen_post_type );
				update_post_meta( (int) $page_id, '_wpsa_page_term_id', $term->term_id );
				update_term_meta( (int) $term->term_id, '_wpsa_page_id', $page_id );

				foreach($term->children as $child) {
					$this->create_page_for_term($child,$slug . '/' . $term->slug, $chosen_post_type, $page_id);
				}

			}
		}
	}

	public function insert_phantom_pages_init() {
		$options = get_option($this->plugin_name);
		$options = !empty($options['chosen-post-types']) ? $options['chosen-post-types'] : array();
		
		// var_dump($options);
		// var_dump(get_post_type_object('products'));
		
		// Perform
		foreach($options as $chosen_post_type) {

			$pt_object = get_post_type_object($chosen_post_type);
			$formatted_slug = preg_replace('/\/(%)\w+(%)/i', '', $pt_object->rewrite['slug']);

			
			$slugs = array_filter(explode("/", $formatted_slug));

			if(count($slugs) > 0) {
				$parent = 0; // always root
				$final_slug = array();
				// if it's a multi part rewrite slug, check for all parts if it exists
				foreach($slugs as $slug) {
					array_push($final_slug, $slug);

					// check each slug for it's existance
					$queried_post = get_page_by_path(implode($final_slug, '/'),OBJECT,'page');
					if(!empty($queried_post)) {
						$parent = $queried_post->ID;
					}


					// var_dump($queried_post);
					if(empty($queried_post)) {
						// var_dump($slug);
						// create
						$my_post = array(
							'post_title'    => $pt_object->labels->name,
							'post_name'		=> $slug,
							'post_type'		=> 'page',
							'post_status'   => 'publish',
							'post_author'   => 1
						);
						if($parent !== 0) {
							// var_dump($parent);
							$my_post['post_parent'] = $parent;
						}
						$page_id = wp_insert_post( $my_post );
						update_post_meta( (int) $page_id, '_wpsa_page', true );
						update_post_meta( (int) $page_id, '_wpsa_page_post_type', $chosen_post_type );
					}
				}

			} else {
				$queried_post = get_page_by_path($formatted_slug,OBJECT,'page');
				if(empty($queried_post)) {

					// create
					$my_post = array(
						'post_title'    => $pt_object->labels->name ,
						'post_name'		=> $slug,
						'post_type'		=> 'page',
						'post_status'   => 'publish',
						'post_author'   => 1
					);
					$page_id = wp_insert_post( $my_post );
					update_post_meta( (int) $page_id, '_wpsa_page', true );
					update_post_meta( (int) $page_id, '_wpsa_page_post_type', $chosen_post_type );

				}
				
			}

			// Tax
			$obj_tax = get_object_taxonomies( $chosen_post_type ); 

			foreach ($obj_tax as $ot) {

				$obj_tax_terms = $this->get_taxonomy_hierarchy($ot);


				foreach($obj_tax_terms as $term) {
					$queried_post_type_post = get_page_by_path($formatted_slug,OBJECT,'page');

					$this->create_page_for_term($term, $formatted_slug, $chosen_post_type, $queried_post_type_post->ID);
				}

			}
		}
	}

	public function insert_phantom_pages($option_name, $old_value, $value) {
		// var_dump($option_name);
		// var_dump($old_value);
		// var_dump($value);

		$diff = array();
		// to delete pages when unselected pt
		// var_dump($old_value);
		if(!empty($old_value) && is_array($old_value)) {
			if(array_key_exists('chosen-post-types', $old_value)) {
				if(is_array($old_value['chosen-post-types'])) {
					if(is_array($value['chosen-post-types'])) {
						// updated with new but not empty selections
						$diff = array_diff($old_value['chosen-post-types'], $value['chosen-post-types']);
					} else {
						// Cleared all post type selections
						$diff = $old_value['chosen-post-types'];
		
					}
				}
			}
			
			
			
		}

		// Loop through diff post types and delete all pages

		// var_dump($diff);
		foreach($diff as $diff_pt) {
			// $pt_object = get_post_type_object($diff_pt);
			$args = array(
				'post_type'  => 'page',
				'posts_per_page'   => -1,
				// 'meta_key' => '_wpsa_page',
				'meta_query' => array(
					array(
						'key' => '_wpsa_page',
						'value' => 1,
						'compare' => '=',
					),
					array(
						'key' => '_wpsa_page_post_type',
						'value' => $diff_pt,
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
			// query all posts that is associated with this pt.
		}

		
		if(!is_array($value)) {
			return;
		}

		

		
		
		if($option_name === 'searchable-archives') {
		
			if(array_key_exists('chosen-post-types', $value)) {
				// Perform
				foreach($value['chosen-post-types'] as $chosen_post_type) {

					$pt_object = get_post_type_object($chosen_post_type);
					// var_dump($pt_object);
					$formatted_slug = preg_replace('/\/(%)\w+(%)/i', '', $pt_object->rewrite['slug']);

					
					$slugs = array_filter(explode("/", $formatted_slug));

					if(count($slugs) > 0) {
						$parent = 0; // always root
						$final_slug = array();
						// if it's a multi part rewrite slug, check for all parts if it exists
						foreach($slugs as $slug) {
							array_push($final_slug, $slug);

							// check each slug for it's existance
							$queried_post = get_page_by_path(implode($final_slug, '/'),OBJECT,'page');
							if(!empty($queried_post)) {
								$parent = $queried_post->ID;
							}


							// var_dump($queried_post);
							if(empty($queried_post)) {
								// var_dump($slug);
								// create
								$my_post = array(
									'post_title'    => $pt_object->labels->name,
									'post_name'		=> $slug,
									'post_type'		=> 'page',
									'post_status'   => 'publish',
									'post_author'   => 1
								);
								if($parent !== 0) {
									// var_dump($parent);
									$my_post['post_parent'] = $parent;
								}
								$page_id = wp_insert_post( $my_post );
								update_post_meta( (int) $page_id, '_wpsa_page', true );
								update_post_meta( (int) $page_id, '_wpsa_page_post_type', $chosen_post_type );
							}
						}

					} else {
						$queried_post = get_page_by_path($formatted_slug,OBJECT,'page');
						if(empty($queried_post)) {

							// create
							$my_post = array(
								'post_title'    => $pt_object->labels->name ,
								'post_name'		=> $slug,
								'post_type'		=> 'page',
								'post_status'   => 'publish',
								'post_author'   => 1
							);
							$page_id = wp_insert_post( $my_post );
							update_post_meta( (int) $page_id, '_wpsa_page', true );
							update_post_meta( (int) $page_id, '_wpsa_page_post_type', $chosen_post_type );

						}
						
					}

					// Tax
					$obj_tax = get_object_taxonomies( $chosen_post_type ); 

					foreach ($obj_tax as $ot) {

						

						// $obj_tax_terms = get_terms( array(
						// 	'taxonomy' => $ot,
						// 	'hide_empty' => false,
						// ) );

						$obj_tax_terms = $this->get_taxonomy_hierarchy($ot);


						foreach($obj_tax_terms as $term) {
							$queried_post_type_post = get_page_by_path($formatted_slug,OBJECT,'page');

							$this->create_page_for_term($term, $formatted_slug, $chosen_post_type, $queried_post_type_post->ID);
						}

					}
				}
			}
		}
	}

	
	
	public function get_post_types() {
		// $q = new WP_Query(array('post_type' => 'post'));
		$options = get_option($this->plugin_name);
		$options = !empty($options['chosen-post-types']) ? $options['chosen-post-types'] : array();
		$pts = get_post_types();
		
		if(!empty($pts) && count($pts) !== 0) :
	?>
		<div class="wpsa-cb-cont">
		<?php foreach ($pts as $pt) {  ?>
				<div  class="wpsa-cb-label"> 
					<label><input type="checkbox" name="<?php echo $this->plugin_name . '[chosen-post-types][]'; ?>" value="<?php echo $pt; ?>" <?php echo in_array($pt, $options) ? 'checked' : ''; ?>><?php echo $pt; ?></label>

					<?php 
						$obj_tax = get_object_taxonomies( $pt ); 
						foreach ($obj_tax as $ot) {
							$obj_tax_terms = get_terms( array(
								'taxonomy' => $ot,
								'hide_empty' => false,
							) );

							foreach ($obj_tax_terms as $ot_term) {

							}
						}

						
					?>

				</div>
			
		<?php } ?>   
		</div>
	<?php
		endif;
	}

	public function get_all_taxonomies() {
		// $q = new WP_Query(array('post_type' => 'post'));
		$taxs = get_taxonomies();
		
		if(!empty($taxs) && count($taxs) !== 0) :
	?>
		<div class="wpsa-cb-cont">
		<?php foreach ($taxs as $tax) {  ?>
			
				<label class="wpsa-cb-label"><input type="checkbox" name="<?php echo $this->plugin_name . '_chosen_taxonomies'; ?>"><?php echo $tax; ?></label>
			
		<?php } ?>   
		</div>
	<?php
		endif;
	}

    public function plugin_page_callback() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/searchable-archives-admin-display.php';
	}
	
	public function delete_all_wpsa_pages() {
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
