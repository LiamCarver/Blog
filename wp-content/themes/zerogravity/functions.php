<?php
/**
 * ZeroGravity functions and definitions
 *
 * @package ZeroGravity
 */

// Set up the content width value based on the theme's design and stylesheet.
if ( ! isset( $content_width ) )
	$content_width = 625;

/**
 * ZeroGravity setup.
 *
 * Sets up theme defaults and registers the various WordPress features that
 * ZeroGravity supports.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_editor_style() To add a Visual Editor stylesheet.
 * @uses add_theme_support() To add support for post thumbnails, automatic feed links,
 * 	custom background, and post formats.
 * @uses register_nav_menu() To add support for navigation menus.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since ZeroGravity 1.0
 */
 
add_action( 'after_setup_theme', 'zerogravity_setup' );
function zerogravity_setup() {
	/*
	 * Makes ZeroGravity available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on ZeroGravity, use a find and replace
	 * to change 'zerogravity' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'zerogravity', get_template_directory() . '/languages' );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu( 'primary', __( 'Primary Menu', 'zerogravity' ) );

	/*
	 * This theme supports custom background color and image,
	 * and here we also set up the default background color.
	 */
	add_theme_support( 'custom-background', array(
		'default-color' => 'e6e6e6',
	) );
	
	add_theme_support( 'title-tag' );
	
	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 624, 9999 ); // Unlimited height, soft crop
}

// Images size
add_image_size('excerpt-thumbnail-zg-176', 176, 176, true);

/**
 * Add support for a custom header image.
 */
require( get_template_directory() . '/inc/custom-header.php' );

/**
 * Return the Google font stylesheet URL if available.
 *
 * The use of Open Sans by default is localized. For languages that use
 * characters not supported by the font, the font can be disabled.
 *
 * @since ZeroGravity 1.0
 *
 * @return string Font stylesheet or empty string if disabled.
 */
function zerogravity_get_font_url() {
	$font_url = '';

	/* translators: If there are characters in your language that are not supported
	 * by Open Sans, translate this to 'off'. Do not translate into your own language.
	 */
	if ( 'off' !== _x( 'on', 'Open Sans font: on or off', 'zerogravity' ) ) {
		$subsets = 'latin,latin-ext';

		/* translators: To add an additional Open Sans character subset specific to your language,
		 * translate this to 'greek', 'cyrillic' or 'vietnamese'. Do not translate into your own language.
		 */
		$subset = _x( 'no-subset', 'Open Sans font: add new subset (greek, cyrillic, vietnamese)', 'zerogravity' );

		if ( 'cyrillic' == $subset )
			$subsets .= ',cyrillic,cyrillic-ext';
		elseif ( 'greek' == $subset )
			$subsets .= ',greek,greek-ext';
		elseif ( 'vietnamese' == $subset )
			$subsets .= ',vietnamese';
		
		$fuente = str_replace(" ", "+", get_theme_mod('zerogravity_fonts', 'Open Sans')); 
		$protocol = is_ssl() ? 'https' : 'http';
		$query_args = array(
			'family' => $fuente.":400italic,700italic,400,700",
			'subset' => $subsets,
		);
		$font_url = add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" );
	}

	return $font_url;
}

/**
 * Enqueue scripts and styles for front-end.
 *
 * @since ZeroGravity 1.0
 */
 
add_action( 'wp_enqueue_scripts', 'zerogravity_scripts_styles' );
function zerogravity_scripts_styles() {
	global $wp_styles;

	/*
	 * Adds JavaScript to pages with the comment form to support
	 * sites with threaded comments (when in use).
	 */
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	// Adds JavaScript for handling the navigation menu hide-and-show behavior.
	wp_enqueue_script( 'zerogravity-navigation', get_template_directory_uri() . '/js/navigation.js', array( 'jquery' ), '20140711', true );

	$font_url = zerogravity_get_font_url();
	if ( ! empty( $font_url ) )
		wp_enqueue_style( 'zerogravity-fonts', esc_url_raw( $font_url ), array(), null );

	// Loads our main stylesheet.
	wp_enqueue_style( 'zerogravity-style', get_stylesheet_uri() );
	
	// Custom style
	wp_enqueue_style('custom-style', get_template_directory_uri()."/custom-style.css");

	// Loads the Internet Explorer specific stylesheet IE 9.
	wp_enqueue_style( 'zerogravity-ie', get_template_directory_uri() . '/css/ie.css', array( 'zerogravity-style' ), '20121010' );
	$wp_styles->add_data( 'zerogravity-ie', 'conditional', 'lt IE 9' );
	
	// Loads the Internet Explorer specific stylesheet IE.
	wp_enqueue_style( 'zerogravity-ie', get_template_directory_uri() . '/css/ie.css', array( 'zerogravity-style' ), '20152311' );
	$wp_styles->add_data( 'zerogravity-ie', 'conditional', 'lt IE' );
	
	// Dashicons
	wp_enqueue_style( 'dashicons' );
	
	// Font Awesome
	wp_enqueue_style('font-awesome', get_template_directory_uri() .'/css/font-awesome-4.3.0/css/font-awesome.min.css');
	
	// Toggle search
	wp_enqueue_script('buscar', get_template_directory_uri() .'/js/zg-toggle-search.js', array('jquery'), false, true);
}


/**
 * Filter TinyMCE CSS path to include Google Fonts.
 *
 * Adds additional stylesheets to the TinyMCE editor if needed.
 *
 * @uses zerogravity_get_font_url() To get the Google Font stylesheet URL.
 *
 * @since ZeroGravity 1.0
 *
 * @param string $mce_css CSS path to load in TinyMCE.
 * @return string Filtered CSS path.
 */
function zerogravity_mce_css( $mce_css ) {
	$font_url = zerogravity_get_font_url();

	if ( empty( $font_url ) )
		return $mce_css;

	if ( ! empty( $mce_css ) )
		$mce_css .= ',';

	$mce_css .= esc_url_raw( str_replace( ',', '%2C', $font_url ) );

	return $mce_css;
}
add_filter( 'mce_css', 'zerogravity_mce_css' );

/**
 * Filter the page menu arguments.
 *
 * Makes our wp_nav_menu() fallback -- wp_page_menu() -- show a home link.
 *
 * @since ZeroGravity 1.0
 */
function zerogravity_page_menu_args( $args ) {
	if ( ! isset( $args['show_home'] ) )
		$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'zerogravity_page_menu_args' );

/**
 * Register sidebars.
 *
 * Registers our main widget area and the front page widget areas.
 *
 * @since ZeroGravity 1.0
 */
function zerogravity_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Main Sidebar', 'zerogravity' ),
		'id' => 'sidebar-1',
		'description' => __( 'Appears on posts and pages except Full-width Page Template', 'zerogravity' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title"><span class="prefix-widget-title"><i class="fa fa-th-large"></i></span> ',
		'after_title' => '</h3>',
	) );

	register_sidebar(array(
    	'name' => __('ZeroGravity: Below entries title', 'zerogravity'),
        'description' => __('Appears below entries title', 'zerogravity'),
        'id' => 'wt-sub-title',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<p class="widget-title">',
        'after_title' => '</p>'
    ));
	
	register_sidebar(array(
        'name' => __('ZeroGravity: End of entries', 'zerogravity'),
        'description' => __('Appears at the end of entries content', 'zerogravity'),
        'id' => 'wt-post-end',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<p class="widget-title">',
        'after_title' => '</p>'
    ));
}
add_action( 'widgets_init', 'zerogravity_widgets_init' );

function fix_privAndCurrPost()
{
    // Register the script like this for a plugin:
    //wp_register_script( 'customPostFixes', plugins_url( '/custome_js/customPostFixes.js'), array( 'jquery'), '20150911', true );
    // or
    // Register the script like this for a theme:
    //wp_register_script( 'customPostFixes', get_template_directory_uri() . '/js/customPostFixes.js', array( 'jquery'), '20150911', true );
	
	//wp_localize_script('customPostFixes','PR',$args);
 
    // For either a plugin or a theme, you can then enqueue the script:
    //wp_enqueue_script( 'customPostFixes' );
}
//add_action( 'wp_enqueue_scripts', 'fix_privAndCurrPost' );

if ( ! function_exists( 'zerogravity_content_nav' ) ) :
/**
 * Displays navigation to next/previous pages when applicable.
 *
 * @since ZeroGravity 1.0
 */
function zerogravity_content_nav( $html_id ) {
	global $wp_query;

	$html_id = esc_attr( $html_id );

	if ( $wp_query->max_num_pages > 1 ) : ?>
		<nav id="<?php echo $html_id; ?>" class="navigation" role="navigation">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'zerogravity' ); ?></h3>
			<div class="wrapper-navigation-below">
			<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'zerogravity' ) ); ?></div>
			<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'zerogravity' ) ); ?></div>
			</div>
		</nav><!-- #<?php echo $html_id; ?> .navigation -->
	<?php endif;
}
endif;

if ( ! function_exists( 'zerogravity_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own zerogravity_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since ZeroGravity 1.0
 */
function zerogravity_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php _e( 'Pingback:', 'zerogravity' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'zerogravity' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
			break;
		default :
		// Proceed with normal comments.
		global $post;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<header class="comment-meta comment-author vcard">
				<?php
					echo get_avatar( $comment, 44 );
					printf( '<cite><b class="fn">%1$s</b> %2$s</cite>',
						get_comment_author_link(),
						// If current post author is also comment author, make it known visually.
						( $comment->user_id === $post->post_author ) ? '<span>' . __( 'Post author', 'zerogravity' ) . '</span>' : ''
					);
					printf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						/* translators: 1: date, 2: time */
						sprintf( __( '%1$s at %2$s', 'zerogravity' ), get_comment_date(), get_comment_time() )
					);
				?>
			</header><!-- .comment-meta -->

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'zerogravity' ); ?></p>
			<?php endif; ?>

			<section class="comment-content comment">
				<?php comment_text(); ?>
				<?php edit_comment_link( __( 'Edit', 'zerogravity' ), '<p class="edit-link">', '</p>' ); ?>
			</section><!-- .comment-content -->

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'zerogravity' ), 'after' => ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->
	<?php
		break;
	endswitch; // end comment_type check
}
endif;

if ( ! function_exists( 'zerogravity_entry_meta' ) ) :
/**
 * Set up post entry meta.
 *
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own zerogravity_entry_meta() to override in a child theme.
 *
 * @since ZeroGravity 1.0
 */
function zerogravity_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'zerogravity' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'zerogravity' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'zerogravity' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'zerogravity' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'zerogravity' );
	} else {
		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'zerogravity' );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}
endif;

/**
 * Extend the default WordPress body classes.
 *
 * Extends the default WordPress body class to denote:
 * 1. Using a full-width layout, when no active widgets in the sidebar
 *    or full-width template.
 * 2. Front Page template: thumbnail in use and number of sidebars for
 *    widget areas.
 * 3. White or empty background color to change the layout and spacing.
 * 4. Custom fonts enabled.
 * 5. Single or multiple authors.
 *
 * @since ZeroGravity 1.0
 *
 * @param array $classes Existing class values.
 * @return array Filtered class values.
 */
function zerogravity_body_class( $classes ) {
	$background_color = get_background_color();
	$background_image = get_background_image();

	if ( ! is_active_sidebar( 'sidebar-1' ) || is_page_template( 'page-templates/full-width.php' ) )
		$classes[] = 'full-width';

	if ( is_page_template( 'page-templates/front-page.php' ) ) {
		$classes[] = 'template-front-page';
		if ( has_post_thumbnail() )
			$classes[] = 'has-post-thumbnail';
		if ( is_active_sidebar( 'sidebar-2' ) && is_active_sidebar( 'sidebar-3' ) )
			$classes[] = 'two-sidebars';
	}

	if ( empty( $background_image ) ) {
		if ( empty( $background_color ) )
			$classes[] = 'custom-background-empty';
		elseif ( in_array( $background_color, array( 'fff', 'ffffff' ) ) )
			$classes[] = 'custom-background-white';
	}

	// Enable custom font class only if the font CSS is queued to load.
	if ( wp_style_is( 'zerogravity-fonts', 'queue' ) )
		$classes[] = 'custom-font-enabled';

	if ( ! is_multi_author() )
		$classes[] = 'single-author';

	return $classes;
}
add_filter( 'body_class', 'zerogravity_body_class' );

/**
 * Adjust content width in certain contexts.
 *
 * Adjusts content_width value for full-width and single image attachment
 * templates, and when there are no active widgets in the sidebar.
 *
 * @since ZeroGravity 1.0
 */
function zerogravity_content_width() {
	if ( is_page_template( 'page-templates/full-width.php' ) || is_attachment() || ! is_active_sidebar( 'sidebar-1' ) ) {
		global $content_width;
		$content_width = 960;
	}
}
add_action( 'template_redirect', 'zerogravity_content_width' );

/**
 * Excerpt
 */

// Establecer la longitud del excerpt
add_filter( 'excerpt_length', 'zerogravity_excerpt_length', 999 );

function zerogravity_excerpt_length($length) {
	return 60;
}

// Cambiar [...] del excerpt por texto
add_filter('excerpt_more', 'zerogravity_excerpt_more');

function zerogravity_excerpt_more($more) {
   global $post;
   return '... <a href="'. get_permalink($post->ID) . '">' . __( 'Read more', 'zerogravity' ) . ' &raquo;</a>';
}

define ('ZEROGRAVITY_AUTHOR_URI', 'http://galussothemes.com');

// Includes
require_once( get_template_directory() . '/inc/zg-lite-customization.php' );
require_once( get_template_directory() . '/inc/zg-lite-guide.php' );
require_once( get_template_directory() . '/inc/zg-lite-customizer.php' );
