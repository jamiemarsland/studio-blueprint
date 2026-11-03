<?php
// Runs last in blueprint-default.json (base64-encoded into a wp eval step).
// Sets the featured images on the three portfolio posts.

// Imported attachment IDs, keyed by file name.
$ids = array();
foreach ( get_posts( array( "post_type" => "attachment", "post_status" => "inherit", "numberposts" => -1 ) ) as $a ) {
	$ids[ wp_basename( get_attached_file( $a->ID ) ) ] = $a->ID;
}

$featured = array( "Website Build" => "shop.png", "Portfolio Website" => "room.png", "E-Commerce Store" => "socks.png" );
foreach ( $featured as $title => $file ) {
	$found = get_posts( array( "post_type" => "post", "title" => $title, "post_status" => "all", "numberposts" => 1 ) );
	if ( $found && isset( $ids[ $file ] ) ) {
		set_post_thumbnail( $found[0]->ID, $ids[ $file ] );
		echo "Featured image set for $title\n";
	}
}
