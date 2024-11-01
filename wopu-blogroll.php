<?php

/*
  Plugin Name: Wopu Blogroll
  Plugin URI: http://webtrunghieu.info
  Description: Some usefull customized widget for links display
  Version: 1.0
  Author: Hieu Le Trung
  Author URI: http://webtrunghieu.info
  License: GPL2

  @since 2.2.0 2.8.0
 */

include_once dirname(__FILE__) . '/wopu_links_plus.php';
include_once dirname(__FILE__) . '/wopu_scrolling_links.php';

function wopu_widgets_init(){
	if(!version_compare("2.8", get_bloginfo('version'), "<=")){
		die ("Your Wordpress version is older than 2.8, please upgrade WP before using this plugin. ");
	}
	register_widget('wopu_links_plus');
	register_widget('wopu_scrolling_links');
	wp_enqueue_script('jscroller', plugins_url('/js/jquery.Scroller-1.0.min.js', __FILE__), array('jquery'));
	wp_enqueue_script('wopu_scroller', plugins_url('/js/wopu_scrolling_links.js', __FILE__), array('jquery'));
}
add_action('widgets_init', 'wopu_widgets_init');
/* End of file wopu-blogroll.php */