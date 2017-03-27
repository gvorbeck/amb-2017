<?php get_header(); ?>
<h2>Reading</h2>
<ul id="book-list">
  <?php
  // Current Reading List
  $args = array(
    'post_type'  => 'book',
    'meta_key'   => 'reading_state',
    'meta_value' => 1
  );
  $query = new WP_Query( $args );
  if ( $query->have_posts() ) :
    // Start the loop
    while ( $query->have_posts() ) : $query->the_post();
      $data = json_decode(file_get_contents("https://openlibrary.org/api/books?bibkeys=ISBN:" . get_field('isbn') . "&jscmd=data&format=json"), TRUE);
      amb_book_card($data['ISBN:' . get_field('isbn')]);
    // End the loop
    endwhile;
  else:
    _e('Sorry. Nope.', 'textdomain');
  endif;
  wp_reset_postdata();

  // delete after dev
// $json_url = 'https://openlibrary.org/api/books?bibkeys=ISBN:9780345509680&jscmd=data&format=json';
//   //$json_url = "http://openlibrary.org/api/volumes/brief/isbn/0771008139.json";
//   $json = file_get_contents($json_url);
//   $data = json_decode($json, TRUE);
//   echo "<pre>";
//   print_r($data);
//   echo "</pre>";
  // delete after dev
  ?>
</ul>

<?php get_footer(); ?>
