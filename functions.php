<?php

// Function responsible for data gathering in SQL database
function recently_visited_posts_live_add_hits($postID) {
	global $wpdb;
	$post_popularity_graph_table = $wpdb->prefix . 'recently_visited_posts_live';
	if (!preg_match('/bot|spider|crawler|slurp|curl|^$/i', $_SERVER['HTTP_USER_AGENT'])) { // If there is no hit_count with proper ID and visitor is not a bot proceed
		$result = $wpdb->query("INSERT INTO $post_popularity_graph_table (post_id, date) VALUES ($postID, NOW())"); // Adds to SQL table post ID, date and hit count
		$wpdb->query("DELETE FROM $post_popularity_graph_table WHERE date <= NOW() - INTERVAL 30 DAY"); // Removes database entry older than 30 days
	}
}

// Function responsible for displaying the chart  
function recently_visited_posts_live_show() {
?>
<script type="text/javascript">
	(function(jQuery)
{
    jQuery(document).ready(function()
    {
        jQuery.ajaxSetup(
        {
            cache: false,
            beforeSend: function() {
                jQuery('#content').hide();
                jQuery('#loading').show();
            },
            complete: function() {
                jQuery('#loading').hide();
                jQuery('#content').show();
            },
            success: function() {
                jQuery('#loading').hide();
                jQuery('#content').show();
            }
        });
        var $container = jQuery("#content");
        $container.load("functions.php");
        var refreshId = setInterval(function()
        {
            $container.load('functions.php');
        }, 2000);
    });
})(jQuery);
</script>

<?php

	$feed_url = 'http://blogoola.com/blog/feed/';
	$content = file_get_contents($feed_url);
	$x = new SimpleXmlElement($content);
	$feedData = '';
	$date = date("Y-m-d H:i:s");

	//output
	$feedData .=  "

	    ";
	    foreach($x->channel->item as $entry) {
	        $feedData .= "" . $entry->title . "";
	    }
	    $feedData .= "";
	    $feedData .= "Data current as at: ".$date."";

	    echo $feedData;
}

?>