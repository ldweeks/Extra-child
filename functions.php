<?php
	add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
	function theme_enqueue_styles() {
	    wp_enqueue_style( 'extra', get_template_directory_uri() . '/style.css' );

	}
	
	
// Remove unneccesary feeds from head
remove_action('wp_head', 'feed_links_extra', 3 ); // removes individual post comment feeds
function return_false() {
  return false;
}
add_filter('feed_links_show_comments_feed', 'return_false'); // removes site comment feed


// excluding bands, books, & magazines from sidebar widget
  function exclude_widget_categories($args)
  {
    $exclude = "43,44,45"; // The IDs of the excluding categories
    $args["exclude"] = $exclude;
    return $args;
  }
  add_filter("widget_categories_args","exclude_widget_categories");

// Removing "Category:" etc from title on category pages
add_filter( 'get_the_archive_title', function ($title) {

    if ( is_category() ) {

            $title = single_cat_title( '', false );

        } elseif ( is_tag() ) {

            $title = single_tag_title( '', false );

        } elseif ( is_author() ) {

            $title = '<span class="vcard">' . get_the_author() . '</span>' ;

        }

    return $title;

});

// Remove Jetpack's related posts from their default position in order to put them lower down (ie below footnotes and tags)
function jetpackme_remove_rp() {
    if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
        $jprp = Jetpack_RelatedPosts::init();
        $callback = array( $jprp, 'filter_add_target_to_dom' );
        remove_filter( 'the_content', $callback, 40 );
    }
}
add_filter( 'wp', 'jetpackme_remove_rp', 20 );

// Add custom header widget area
function wpb_widgets_init() {
 
    register_sidebar( array(
        'name'          => 'Custom Header Widget Area',
        'id'            => 'custom-header-widget',
        'before_widget' => '<div class="chw-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="chw-title">',
        'after_title'   => '</h2>',
    ) );
 
}
add_action( 'widgets_init', 'wpb_widgets_init' );


// The following three functions add support for the coauthors plus plugin

function whm_extra_display_archive_post_meta() {
	$post_meta_options = et_get_option( 'extra_postinfo1', array(
		'author',
		'date',
		'categories',
		'comments',
		'rating_stars',
	) );

	$meta_args = array(
		'author_link'    => in_array( 'author', $post_meta_options ),
		'author_link_by' => et_get_safe_localization( __( 'Posted by %s', 'extra' ) ),
		'post_date'      => in_array( 'date', $post_meta_options ),
		'categories'     => in_array( 'categories', $post_meta_options ),
		'comment_count'  => in_array( 'comments', $post_meta_options ),
		'rating_stars'   => in_array( 'rating_stars', $post_meta_options ),
	);

	return whm_et_extra_display_post_meta( $meta_args );
}

function whm_extra_display_single_post_meta() {
	$post_meta_options = et_get_option( 'extra_postinfo2', array(
		'author',
		'date',
		'categories',
		'comments',
		'rating_stars',
	) );

	$meta_args = array(
		'author_link'    => in_array( 'author', $post_meta_options ),
		'author_link_by' => et_get_safe_localization( __( 'Posted by %s', 'extra' ) ),
		'post_date'      => in_array( 'date', $post_meta_options ),
		'categories'     => in_array( 'categories', $post_meta_options ),
		'comment_count'  => in_array( 'comments', $post_meta_options ),
		'rating_stars'   => in_array( 'rating_stars', $post_meta_options ),
	);

	return whm_et_extra_display_post_meta( $meta_args );
}

function whm_et_extra_display_post_meta( $args = array() ) {
	$default_args = array(
		'post_id'        => get_the_ID(),
		'author_link'    => true,
		'author_link_by' => et_get_safe_localization( __( 'by %s', 'extra' ) ),
		'post_date'      => true,
		'date_format'    => et_get_option( 'extra_date_format', '' ),
		'categories'     => true,
		'comment_count'  => true,
		'rating_stars'   => true,
	);

	$args = wp_parse_args( $args, $default_args );

	$meta_pieces = array();

	if ( $args['author_link'] ) {
		// $meta_pieces[] = sprintf( $args['author_link_by'], coauthors_posts_links(', ','','','',false) );
		if ( function_exists( 'coauthors_posts_links' ) ) {
			$meta_pieces[] = coauthors_posts_links(', ', ' & ', 'by ', '', false ); }
		else {
			$meta_pieces[] = sprintf( $args['author_link_by'], extra_get_post_author_link( $args['post_id'] ) ); }
	}

	if ( $args['post_date'] ) {
		$meta_pieces[] = extra_get_the_post_date( $args['post_id'], $args['date_format'] );
	}

	if ( $args['categories'] ) {
		$meta_piece_categories = extra_get_the_post_categories( $args['post_id'] );
		if ( !empty( $meta_piece_categories ) ) {
			$meta_pieces[] = $meta_piece_categories;
		}
	}

	if ( $args['comment_count'] ) {
		$meta_piece_comments = extra_get_the_post_comments_link( $args['post_id'] );
		if ( !empty( $meta_piece_comments ) ) {
			$meta_pieces[] = $meta_piece_comments;
		}
	}

	if ( $args['rating_stars'] && extra_is_post_rating_enabled( $args['post_id'] ) ) {
		$meta_piece_rating_stars = extra_get_post_rating_stars( $args['post_id'] );
		if ( !empty( $meta_piece_rating_stars ) ) {
			$meta_pieces[] = $meta_piece_rating_stars;
		}
	}

	$output = implode( ' | ', $meta_pieces );

	return $output;
}
// end support for multiple authors

//  Cleanup Click-to-tweet text in RSS feeds
function customizeRSS($content) {
//    $content = preg_replace( '/<iframe(.*)\/iframe>/is', '', $content );
//    $content = preg_replace( '/<script(.*)\/script>/is', '', $content );
//    $content = strip_shortcodes( $post->post_content );
    $content = preg_replace( '/<div class="sw-tweet-clear"><\/div>/is', '<blockquote>', $content );
    $content = preg_replace( '/Click To Tweet<i class="sw swp_twitter_icon"><\/i><\/span><\/span>/is', ' (Click To Tweet)<i class="sw swp_twitter_icon"></i></span></span></blockquote>', $content );
    
    return $content;
}
add_filter('the_content_feed', 'customizeRSS');
// end RSS cleanup

// Add iOS smart banners & rss for autodiscovery for each podcast, but not on homepage
function add_podcast_info() {
	if ($_SERVER['REQUEST_URI'] != '/') {
		$appID = FALSE;
		$podcastFeed = FALSE;
		if (function_exists('wp_get_terms_meta')) { 
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				foreach( $categories as $category ) {
					$appID = wp_get_terms_meta($category->term_id, "apple-app-id" ,true);
					$podcastFeed = wp_get_terms_meta($category->term_id, "podcast-feed" ,true);
					$podcastTitle = $category->cat_name;
					if ($appID != FALSE) break;
				}
			}
		} 

		if ($appID != FALSE) {
		?>

<meta name="apple-itunes-app" content="app-id=<?php echo $appID; ?>, affiliate-data=1010lNPD">
		<?php
		}

		if ($podcastFeed != FALSE) {
		?>

<link rel="alternate" type="application/rss+xml" title="<?php echo $podcastTitle; ?>" href="<?php echo $podcastFeed; ?>" />

		<?php
		}
	}
}
add_action( 'wp_head', 'add_podcast_info' );
//end adding podcast info to head

//allow post preview if you logged in, whatever role you might have (e.g. contributor)
function login_allows_preview( $posts ) {
    if(is_preview() && !empty($posts)){
		if ( is_user_logged_in() ) {
			$posts[0]->post_status = 'publish';
		}
    }
    return $posts;
}
add_filter( 'posts_results', 'login_allows_preview', 10, 2 );
//end allow post preview

// format Discourse post to include author and onebox
function whm_sanityville_publish_format( $input, $post_id ) {
    ob_start();
    ?>
    New Warhorn Media post by {author}:<br>
<?php
the_permalink( $post_id ); // The post's WordPress permalink
$output = ob_get_clean();
    
    // Note: the call to apply_filters() that was in the original function has been removed.
    return $output; 
}

add_filter( 'discourse_publish_format_html', 'whm_sanityville_publish_format', 10, 2 );
//end Discourse modifications


/**
 * @snippet        Remove Order Notes (fully, including the title) on the WooCommerce Checkout
 * @how-to         Watch tutorial @ https://businessbloomer.com/?p=19055
 * @sourcecode     https://businessbloomer.com/?p=17432
 * @author Rodolfo Melogli
 * @compatible    WC 3.5.4
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
 
add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );


/** 
 * attempt to put conferences & some podcast category pages in chronological order
 * Works on staging, but not main site!
 */

add_filter( 'pre_get_posts', 'reverse_some_categories_pre_get_posts' );
function reverse_some_categories_pre_get_posts( $query ) {
	if ( ! is_admin() && $query->is_main_query() ) {
		if ($query->is_category( array(458,827,459,456,965,964) ) ) {
			$query->set( 'order', 'ASC' );
		}
	}
}

/**
 * Registers an editor stylesheet for the theme.
 */
function wpdocs_theme_add_editor_styles() {
    add_editor_style( 'custom-editor-style.css' );
}
add_action( 'admin_init', 'wpdocs_theme_add_editor_styles' );

?>