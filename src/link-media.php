<?php
// Runs last in blueprint-default.json (base64-encoded into a wp eval step).
// Points content at the imported Media Library copies and sets featured images.

$raw = "https://raw.githubusercontent.com/jamiemarsland/studio-blueprint/main/assets/";

// Imported attachment IDs, keyed by file name.
$ids = array();
foreach ( get_posts( array( "post_type" => "attachment", "post_status" => "inherit", "numberposts" => -1 ) ) as $a ) {
	$ids[ wp_basename( get_attached_file( $a->ID ) ) ] = $a->ID;
}

// Featured images for the three portfolio posts.
$featured = array( "Website Build" => "shop.png", "Portfolio Website" => "room.png", "E-Commerce Store" => "socks.png" );
foreach ( $featured as $title => $file ) {
	$found = get_posts( array( "post_type" => "post", "title" => $title, "post_status" => "all", "numberposts" => 1 ) );
	if ( $found && isset( $ids[ $file ] ) ) {
		set_post_thumbnail( $found[0]->ID, $ids[ $file ] );
		echo "Featured image set for $title\n";
	}
}

// Asset URL => local URL, and the source site's attachment IDs => new IDs.
$urls = array();
foreach ( $ids as $file => $id ) {
	$urls[ $raw . $file ] = wp_get_attachment_url( $id );
}
if ( isset( $ids["the-team.png"] ) ) {
	$urls[ $raw . "the-team-1024x574.png" ] = wp_get_attachment_image_url( $ids["the-team.png"], "large" );
}
$old_ids = array( 128 => "the-team.png", 134 => "team-1.png", 135 => "team-2.png", 136 => "team-3.png", 137 => "team-4.png", 138 => "new-york-2.jpeg" );
$from = array_keys( $urls );
$to   = array_values( $urls );
foreach ( $old_ids as $old => $file ) {
	if ( ! isset( $ids[ $file ] ) ) {
		continue;
	}
	array_push( $from, "\"id\":$old,", "\"mediaId\":$old,", "wp-image-$old\"", "wp-image-$old " );
	array_push( $to, "\"id\":{$ids[$file]},", "\"mediaId\":{$ids[$file]},", "wp-image-{$ids[$file]}\"", "wp-image-{$ids[$file]} " );
}

foreach ( get_posts( array( "post_type" => array( "post", "page" ), "post_status" => "any", "numberposts" => -1 ) ) as $p ) {
	$content = str_replace( $from, $to, $p->post_content );
	if ( $content !== $p->post_content ) {
		wp_update_post( wp_slash( array( "ID" => $p->ID, "post_content" => $content ) ) );
		echo "Linked media in {$p->post_title}\n";
	}
}
