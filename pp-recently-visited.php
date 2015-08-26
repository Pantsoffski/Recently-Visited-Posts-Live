<?php
/*
Plugin Name: Recently Visited Posts Live
Plugin URI: http://smartfan.pl/
Description: Widget which displays popularity chart / graph for posts.
Author: Piotr Pesta
Version: 1.0.1
Author URI: http://smartfan.pl/
License: GPL12
*/
include 'functions.php';

register_activation_hook(__FILE__, 'recently_visited_posts_live_activate'); //akcja podczas aktywacji pluginu
register_uninstall_hook(__FILE__, 'recently_visited_posts_live_uninstall'); //akcja podczas deaktywacji pluginu

// Installation and SQL table creation
function recently_visited_posts_live_activate() {
	global $wpdb;
	$post_popularity_graph_table = $wpdb->prefix . 'recently_visited_posts_live';
		$wpdb->query("CREATE TABLE IF NOT EXISTS $post_popularity_graph_table (
		id TINYINT(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		post_id BIGINT(50) NOT NULL,
		date DATETIME
		);");
}

// If uninstall - remove SQL table
function recently_visited_posts_live_uninstall() {
	global $wpdb;
	$post_popularity_graph_table = $wpdb->prefix . 'recently_visited_posts_live';
	delete_option('recently_visited_posts_live');
	$wpdb->query( "DROP TABLE IF EXISTS $post_popularity_graph_table" );
}

class recently_visited_posts_live extends WP_Widget {

// Widget constructor
	function recently_visited_posts_live() {

		parent::__construct(false, $name = __('Recently Visited Posts Live', 'wp_widget_plugin'));

	}

	// Widget backend
	function form($instance) {

	// Default values
		$defaults = array('chartcolor' => '#0033CC', 'backgroundcolor' => '#FFFFFF', 'vaxistitle' => 'Visits', 'haxistitle' => 'Time', 'chartstyle' => 'LineChart', 'ignoredcategories' => '', 'ignoredpages' => '', 'numberoftitles' => '10', 'title' => 'Post Popularity Graph');
		$instance = wp_parse_args( (array) $instance, $defaults );
	?>

	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
		<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('numberoftitles'); ?>">Include data from how many last days (1-30)?</label>
		<input id="<?php echo $this->get_field_id('numberoftitles'); ?>" name="<?php echo $this->get_field_name('numberoftitles'); ?>" value="<?php echo $instance['numberofdays']; ?>" style="width:100%;"/>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('ignoredpages'); ?>">If you would like to exclude any pages from being displayed, you can enter the Page IDs (comma separated, e.g. 34, 25, 439):</label>
		<input id="<?php echo $this->get_field_id('ignoredpages'); ?>" name="<?php echo $this->get_field_name('ignoredpages'); ?>" value="<?php echo $instance['ignoredpages']; ?>" style="width:100%;"/>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('ignoredcategories'); ?>">If you would like to exclude any categories from being displayed, you can enter the Category IDs (comma separated, e.g. 3, 5, 10):</label>
		<input id="<?php echo $this->get_field_id('ignoredcategories'); ?>" name="<?php echo $this->get_field_name('ignoredcategories'); ?>" value="<?php echo $instance['ignoredcategories']; ?>" style="width:100%;" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('chartstyle'); ?>">Include posts that where visited in how many last days?</label>
	<select id="<?php echo $this->get_field_id('chartstyle'); ?>" name="<?php echo $this->get_field_name('chartstyle'); ?>" value="<?php echo $instance['chartstyle']; ?>" style="width:100%;">
		<option value="LineChart" <?php if ($instance['chartstyle']=='LineChart') {echo "selected";} ?>>Line Chart</option>
		<option value="ColumnChart" <?php if ($instance['chartstyle']=='ColumnChart') {echo "selected";} ?>>Column Chart</option>
		<option value="AreaChart" <?php if ($instance['chartstyle']=='AreaChart') {echo "selected";} ?>>Area Chart</option>
	</select>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('backgroundcolor'); ?>">Background Color (it must be a html hex color code e.g. #000000, you can find color picker <a href="http://www.w3schools.com/tags/ref_colorpicker.asp" target="_blank">HERE</a>):</label>
		<input id="<?php echo $this->get_field_id('backgroundcolor'); ?>" name="<?php echo $this->get_field_name('backgroundcolor'); ?>" value="<?php
			//check hex value
			if (preg_match('/^\#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $instance['backgroundcolor'])) {
				echo $instance['backgroundcolor'];
			}else {
				echo "Error: it must be a html hex color code e.g. #000000";
			}
		?>" style="width:100%;" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('chartcolor'); ?>">Chart Color (it must be a html hex color code e.g. #000000, you can find color picker <a href="http://www.w3schools.com/tags/ref_colorpicker.asp" target="_blank">HERE</a>):</label>
		<input id="<?php echo $this->get_field_id('chartcolor'); ?>" name="<?php echo $this->get_field_name('chartcolor'); ?>" value="<?php
			//check hex value
			if (preg_match('/^\#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $instance['chartcolor'])) {
				echo $instance['chartcolor'];
			}else {
				echo "Error: it must be a html hex color code e.g. #000000";
			}
		?>" style="width:100%;" />
	</p>

	<?php

	}

	function update($new_instance, $old_instance) {
	$instance = $old_instance;

	// Available fields
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['numberoftitles'] = strip_tags($new_instance['numberoftitles']);
	$instance['ignoredpages'] = strip_tags($new_instance['ignoredpages']);
	$instance['ignoredcategories'] = strip_tags($new_instance['ignoredcategories']);
	$instance['chartstyle'] = strip_tags($new_instance['chartstyle']);
	$instance['backgroundcolor'] = strip_tags($new_instance['backgroundcolor']);
	$instance['chartcolor'] = strip_tags($new_instance['chartcolor']);
	return $instance;
	}

	// Widget front end
	function widget($args, $instance) {
	extract($args);

	// Widget variables
	$title = apply_filters('widget_title', $instance['title']);
	$numberoftitles = $instance['numberoftitles'];
	$numberoftitles = trim(preg_replace('/\s+/', '', $numberoftitles));
	$numberoftitles = $numberoftitles - 1;
	$ignoredpages = $instance['ignoredpages'];
	$ignoredpages = trim(preg_replace('/\s+/', '', $ignoredpages));
	$ignoredpages = explode(",",$ignoredpages);
	$ignoredcategories = $instance['ignoredcategories'];
	$ignoredcategories = trim(preg_replace('/\s+/', '', $ignoredcategories));
	$ignoredcategories = explode(",",$ignoredcategories);
	$chartstyle = $instance['chartstyle'];
	$backgroundcolor = $instance['backgroundcolor'];
	$chartcolor = $instance['chartcolor'];
	$postID = get_the_ID();
	$catID = get_the_category($postID);
	$postCatID = $catID[0]->cat_ID;
	echo $before_widget;
		
		// Checking category ID or post ID is banned or not
		if(in_array($postCatID, $ignoredcategories) || in_array($postID, $ignoredpages)) {
//			recently_visited_posts_live_add_hits($postID);
		}else{
			// Check title availability
			if($title) {
				echo $before_title . $title . $after_title;
			}

			recently_visited_posts_live_show();

//			recently_visited_posts_live_add_hits($postID);

			echo $after_widget;
		}
	}
}

// Widget registration
add_action('widgets_init', create_function('', 'return register_widget("recently_visited_posts_live");'));

?>