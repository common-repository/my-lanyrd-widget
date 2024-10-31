<?php
/**
 * Plugin Name: My Lanyard
 * Plugin URI: http://john.foliot.ca/experiments/MyLanyrd.zip
 * Description: A widget that adds your Lanyrd conferences to your WordPress blog.
 * Version: 0.1
 * Author: John Foliot
 * Author URI: http://john.foliot.ca
 *
 * Portions of this Software Copyright (c) 2010 Lachlan Hardy
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
  * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

add_action( 'widgets_init', 'load_lanyrd' );

function wp_mylanyrd_css() {
?>
<style type="text/css" id="MyLanyard">.lanyrd-badge{margin:0 0 2em 0;}.lanyrd-badge div.type{margin:1em 0 1em 0;}.lanyrd-badge div.type h2{margin:0 0 .5em 0;font-size:85%!important}.lanyrd-badge div.type h4,.lanyrd-badge div.type p{margin:0 0 .5em 0;}.lanyrd-badge div.type ul,.lanyrd-badge div.type li{list-style-type:none;margin:0;padding:0;}.lanyrd-badge div.type li{margin:0 0 1em 0;}.lanyrd-badge div.type li p{margin-left:-5px; margin-top:15px!important;}.lanyrd-badge div.type li img{float:left;margin-top:-10px; margin-right:-5px;}.lanyrd-badge div.type p.date{font-size:.9em; margin-left:-10px!important; margin-top:-3px!important;}</style>
<?php
}

function load_lanyrd() {
	register_widget( 'MyLanyrd' );
}
/**
 * MyLanyrd Class
 */
class MyLanyrd extends WP_Widget {
    /** constructor */
    function MyLanyrd() {
        parent::WP_Widget(false, $name = 'My Lanyrd');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		$account = apply_filters('widget_account', $instance['account']);
?>
<?php echo $before_widget; ?>
<?php if ( $title )
          echo $before_title . $title . $after_title; ?>
<div class="lanyrd-badge" data-user="<?php if ( $account ) echo $before_account . $account . $after_account; ?>" data-type="speaking attending tracking"></div> 
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
  <script type="text/javascript">
  $(function() {
   $(".lanyrd-badge").each(function() {
        var $badge = $(this),
            type = $badge.attr("data-type").split(" "),
            xpath = "",
            content = "";

            $.each(type, function(index, value) {
              if (index !== 0){
                xpath = xpath + "|";
              }
              xpath = xpath + "%2F%2Fdiv%5B%40class%3D'split'%5D%2Fh2%5Bcontains(.%2C'" + value +"')%5D%2Ffollowing-sibling%3A%3Aul%5B1%5D";
            });

 $.getJSON("http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20html%20where%20url%3D%22http%3A%2F%2Flanyrd.com%2Fpeople%2F" + $badge.attr("data-user") +"%2F%22%20and%20xpath%3D%22" + xpath + "%22&format=xml&callback=?", function (data){

          for (i = 0, ii = type.length - 1; i <= ii; i++) {
            var thisType = type[i],
                heading = "<h2>" + thisType + "</h2>",
                results = data.results[i];

            if (results !== undefined) {
              content = content + '<div class="type '+ thisType +'">' + heading + results + "</div>";
            }
          }
          
          if (content === "") {
            content = "<p>I'm not currently listed for any conferences on Lanyrd.</p>"
          } else {
            content = content.replace(/="\//g, '="http://lanyrd.com/');
          }
          
          $badge.append(content).append('');
        });
    });
});
  </script>
<?php echo $after_widget; ?>
<?php
    }

    /** @see WP_Widget::update */
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['account'] = strip_tags($new_instance['account']);

		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'account' => '' ) );
		$title = strip_tags($instance['title']);
		$account = strip_tags($instance['account']);
?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <br /><input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('account'); ?>">Twitter account: <input id="<?php echo $this->get_field_id('account'); ?>" name="<?php echo $this->get_field_name('account'); ?>" type="text" value="<?php echo attribute_escape($account); ?>" /></label></p>

<?php
    }
} // class MyLanyrd
// register MyLanyrd widget
add_action('widgets_init', create_function('', 'return register_widget("MyLanyrd");'));
add_filter('wp_head', 'wp_mylanyrd_css');
