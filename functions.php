<?php
add_action( 'admin_menu', 'amb_remove_menus');
function amb_remove_menus() {
  remove_menu_page( 'edit.php' );
}

add_action( 'init', 'amb_book_init' );
/**
 * Register a book post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function amb_book_init() {
	$labels = array(
		'name'               => _x( 'Books', 'post type general name', 'your-plugin-textdomain' ),
		'singular_name'      => _x( 'Book', 'post type singular name', 'your-plugin-textdomain' ),
		'menu_name'          => _x( 'Books', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Book', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add New', 'book', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Book', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Book', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Book', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Book', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Books', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Books', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Books:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No books found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No books found in Trash.', 'your-plugin-textdomain' )
	);

	$args = array(
		'labels'             => $labels,
                'description'        => __( 'Description.', 'your-plugin-textdomain' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'book' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
    'taxonomies'         => array('category', 'post_tag')
	);

	register_post_type( 'book', $args );
}

add_filter( 'post_updated_messages', 'amb_book_updated_messages' );
/**
 * Book update messages.
 *
 * See /wp-admin/edit-form-advanced.php
 *
 * @param array $messages Existing post update messages.
 *
 * @return array Amended post update messages with new CPT update messages.
 */
function amb_book_updated_messages( $messages ) {
	$post             = get_post();
	$post_type        = get_post_type( $post );
	$post_type_object = get_post_type_object( $post_type );

	$messages['book'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Book updated.', 'your-plugin-textdomain' ),
		2  => __( 'Custom field updated.', 'your-plugin-textdomain' ),
		3  => __( 'Custom field deleted.', 'your-plugin-textdomain' ),
		4  => __( 'Book updated.', 'your-plugin-textdomain' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Book restored to revision from %s', 'your-plugin-textdomain' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Book published.', 'your-plugin-textdomain' ),
		7  => __( 'Book saved.', 'your-plugin-textdomain' ),
		8  => __( 'Book submitted.', 'your-plugin-textdomain' ),
		9  => sprintf(
			__( 'Book scheduled for: <strong>%1$s</strong>.', 'your-plugin-textdomain' ),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i', 'your-plugin-textdomain' ), strtotime( $post->post_date ) )
		),
		10 => __( 'Book draft updated.', 'your-plugin-textdomain' )
	);

	if ( $post_type_object->publicly_queryable && 'book' === $post_type ) {
		$permalink = get_permalink( $post->ID );

		$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View book', 'your-plugin-textdomain' ) );
		$messages[ $post_type ][1] .= $view_link;
		$messages[ $post_type ][6] .= $view_link;
		$messages[ $post_type ][9] .= $view_link;

		$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
		$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview book', 'your-plugin-textdomain' ) );
		$messages[ $post_type ][8]  .= $preview_link;
		$messages[ $post_type ][10] .= $preview_link;
	}

	return $messages;
}

// Determine if login page
function is_login_page() {
  return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
}

if (!is_admin() && !is_login_page()) {
  // Enqueue Styles
  wp_register_style( 'fonts', 'https://fonts.googleapis.com/css?family=Bree+Serif|Source+Sans+Pro' );
  wp_enqueue_style( 'style', get_stylesheet_uri() );
  wp_enqueue_style( 'fonts', get_stylesheet_uri() );
}

// Book card html
function amb_book_card($book) { ?>
  <li class="book book-full">
    <h3 class="book--title"><a href="<?php echo $book[url]; ?>">
      <?php if (isset($book[title])) :
        echo $book[title];
      else:
          the_title();
      endif; ?>
    </a></h3>
    <span class="book--isbn" style="display: none;"><?php the_field('isbn'); ?></span>
    <?php if (isset($book[cover][medium])) : ?>
      <img alt="book cover" class="book--cover" src="<?php echo $book[cover][medium]; ?>">
    <?php endif; ?>
    <div class="book--details">
      <?php if (isset($book[authors])) : ?>
        <ul class="book--author-info">
          <?php for ($i = 0; $i < count($book[authors]); $i++) : ?>
            <li>
              <div class="author-cropper">
                <img alt="author portrait" src="http://covers.openlibrary.org/a/olid/<?php echo explode('/', trim(parse_url($book[authors][$i][url])[path]))[2]; ?>-S.jpg">
              </div>
              <h4 class="book--author-name">
                <a href="<?php echo $book[authors][$i][url]; ?>">
                  <?php echo $book[authors][$i][name]; ?>
                </a>
              </h4>
            </li>
          <?php endfor; ?>
        </ul>
      <?php endif; ?>
      <dl class="book--info">
        <?php if (isset($book[publish_date])) : ?>
          <dt>published</dt>
          <dd><?php echo $book[publish_date]; ?></dd>
        <?php endif; ?>
        <?php if (isset($book[number_of_pages])) : ?>
          <dt>pages</dt>
          <dd><?php echo $book[number_of_pages]; ?></dd>
        <?php endif; ?>
      </dl>
      <?php if (isset($book[subjects])) : ?>
        <ul class="book--tags">
          <?php for ($i = 0; $i < count($book[subjects]); $i++) : ?>
            <li>
              <a href="<?php echo $book[subjects][$i][url]; ?>" target="_blank">
                <?php echo $book[subjects][$i][name]; ?>
              </a>
            </li>
          <?php endfor; ?>
        </ul>
      <?php endif;
      if (isset($book[links])) : ?>
        <ul class="book--links">
          <?php for ($i = 0; $i < count($book[links]); $i++) : ?>
            <li>
              <a href="<?php echo $book[links][$i][url]; ?>" target="_blank">
                <?php echo $book[links][$i][title]; ?>
              </a>
            </li>
          <?php endfor; ?>
        </ul>
      <?php endif; ?>
    </div>
    <div class="book--controls">
      <button class="stop-reading">
        <svg class="icon">
          <use xlink:href="#clock" />
        </svg>
      </button>
      <button class="done-reading">
        <svg class="icon">
          <use xlink:href="#check" />
        </svg>
      </button>
    </div>
  </li>
<?php }
