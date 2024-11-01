<?php
/**
 * Multicolumn links display widget
 * - Display links in multi columns
 * 
 * @since 1.0
 */
/**
 * Define direction constants for scroller
 */
define('WOPU_HORZ_LR', 'left');
define('WOPU_HORZ_RL', 'right');
define('WOPU_VERT_TD', 'top');
define('WOPU_VERT_DT', 'bottom');

class wopu_scrolling_links extends WP_Widget {

    public static $num_i;
    public $global_id;

    function __construct($options = array()) {
        $options['description'] = "Make your link list scroll";
        $control = array('width' => 400, 'height' => 500);
        parent::__construct('wopu-scroll-links', __('Wopu Scroll Links'), $options, $control);
        $this->global_id = self::$num_i;
        self::$num_i++;
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract($args, EXTR_SKIP);

        $show_title = isset($instance['show_title']) ? $instance['show_title'] : false;
        $show_description = isset($instance['description']) ? $instance['description'] : false;
        $show_name = isset($instance['name']) ? $instance['name'] : false;
        $show_rating = isset($instance['rating']) ? $instance['rating'] : false;
        $show_images = isset($instance['images']) ? $instance['images'] : true;
        $category = isset($instance['category']) ? $instance['category'] : false;
        $direction = ($instance['direction'] == 'left'|| $instance['direction'] == 'right') ? 'horizontal' : 'vertical';
        if ($instance['loop'] < 0)
            $instance['loop'] = 0;
        if ($instance['loop'] > 99)
            $instance['loop'] = 99;
        $loop = ($instance['loop'] == 0) ? 'infinite' : $instance['loop'];
        if ($instance['velocity'] < 1)
            $instance['velocity'] = 1;
        if ($instance['velocity'] > 99)
            $instance['velocity'] = 99;

        if (is_admin() && !$category) {
            // Display All Links widget as such in the widgets screen
            echo $before_widget . $before_title . __('All Links') . $after_title . $after_widget;
            return;
        }

        #$before_widget = preg_replace('/id="[^"]*"/', 'id="%id"', $before_widget);
        echo $before_widget;
        if ($show_title) {
            $cats = get_terms('link_category', array('include' => $category));
            echo $before_title;
            echo isset($cats[0]->name) ? $cats[0]->name : __('Bookmarks');
            echo $after_title;
        }
        $bm_filter = array(
            'orderby' => $instance['orderby'],
            'order' => $instance['order'],
            'category' => $category
        );
        $bookmarks = get_bookmarks($bm_filter);
        if (count($bookmarks) > 0) {
            if ($direction == 'horizontal')
                $between = 'span';
            else
                $between = 'div';
            ?>
            <div class="wopu_<?php echo $direction == 'horizontal' ? 'horz' : 'vert'; ?>_scroller" style="position:relative;display:block;overflow:hidden;height:<?php echo $instance['height']; ?>;width:<?php echo $instance['width']; ?>;" id="wopu-scroll-<?php echo $instance['wopu_id']; ?>">
                <div style="position:absolute;white-space:nowrap;">
                    <?php
                    foreach ($bookmarks as $bookmark) {
                        echo "<$between class='wopu_scrolling_item'>";
                        echo "<a href='" . esc_url($bookmark->link_url) . "' target='" . $bookmark->link_taget . "'>";
                        $output = '';
                        if ($bookmark->link_image != null && $show_images) {
                            if (strpos($bookmark->link_image, 'http') === 0) {
                                $output = $bookmark->link_image;
                            } else { // If it's a relative path
                                $output = get_option('siteurl') . $bookmark->link_image;
                            }
                            // display link image
                            echo "<$between class='wopu-link-img'><img src='" . $output . "'/></$between>";
                        }
                        // display link name
                        if ($show_name) {
                            echo "<$between class='wopu-link-name'>".$bookmark->link_name."</$between>";
                        }
                        echo "</a>";
                        // display link description
                        if ($show_description && $bookmark->link_description != null) {
                            echo "<$between class='wopu-link-desc'>" . esc_attr($bookmarks[$pos]->link_description) . "</$between>";
                        }

                        // display link rating
                        if ($show_rating) {
                            echo "<$between class='wopu-link-rate'> Rating: " . $bookmarks[$pos]->link_rating . "</$between>";
                        }
                        echo "</$between>&nbsp;";
                    }
                    ?>
                </div>
            </div>
            <script type="text/javascript">
                //$wopu = jQuery.noConflict();
                $wopu(document).ready(function(){
                    $wopu('#wopu-scroll-<?php echo $instance['wopu_id']; ?>').ResetScroller({
                        velocity: <?php echo $instance['velocity'];?>,
                        direction: '<?php echo $direction;?>',
                        startfrom: '<?php echo $instance['direction']?>',
                        loop: <?php echo $loop=='infinite'?'\''.$loop.'\'':$loop; ?>,
                        movetype: '<?php echo $instance['movetype'];?>'
                    });
                })
            </script>
            <?php
        }
        echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $new_instance = (array) $new_instance;
        $instance = array('images' => 0, 'name' => 0, 'description' => 0, 'rating' => 0, 'show_title' => 0,
            'orderby' => 'name', 'order' => 'ASC', 'wopu_id' => $this->global_id, 'direction' => WOPU_HORZ_RL,
            'movetype' => 'linear', 'loop' => 0, 'velocity' => 50, 'width' => '100%', 'height' => '2em');
        foreach ($instance as $field => $val) {
            if (isset($new_instance[$field]) && $new_instance[$field] !== "") {
                $instance[$field] = 1;
                if ($field == 'orderby' || $field == 'order' || $field == 'direction' || $field == 'wopu_id'
                        || $field == 'movetype' || $field == 'loop' || $field == 'velocity'
                        || $field == 'width' || $field == 'height') {
                    $instance[$field] = $new_instance[$field];
                }
            }
        }
        $instance['category'] = intval($new_instance['category']);

        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        //Defaults
        $instance = wp_parse_args((array) $instance, array('images' => true, 'name' => true, 'description' => false, 'rating' => false, 'category' => false, 'show_title' => false));
        $link_cats = get_terms('link_category');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('category'); ?>" class="screen-reader-text"><?php _e('Select Link Category'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
                <?php
                foreach ($link_cats as $link_cat) {
                    echo '<option value="' . intval($link_cat->term_id) . '"'
                    . ( $link_cat->term_id == $instance['category'] ? ' selected="selected"' : '' )
                    . '>' . $link_cat->name . "</option>\n";
                }
                ?>
            </select></p>
        <p>
        <table>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order by'); ?></label>
                    <select class="widefat" id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
                        <option value="" <?php echo $instance['orderby'] == '' ? 'selected="selected"' : ""; ?>>Link name</option>
                        <option value="link_id" <?php echo $instance['orderby'] == 'link_id' ? 'selected="selected"' : ""; ?>>Link ID</option>
                        <option value="url" <?php echo $instance['orderby'] == 'url' ? 'selected="selected"' : ""; ?>>Link URL</option>
                        <option value="updated" <?php echo $instance['orderby'] == 'updated' ? 'selected="selected"' : ""; ?>>Link update time</option>
                        <option value="length" <?php echo $instance['orderby'] == 'length' ? 'selected="selected"' : ""; ?>>Link name length</option>
                        <option value="rand" <?php echo $instance['orderby'] == 'rand' ? 'selected="selected"' : ""; ?>>Random</option>
                    </select>
                </td><td style="padding: 1em 0 0 1em;">
                    <input class="radio" type="radio" value="ASC" <?php checked($instance['order'], 'ASC') ?> name="<?php echo $this->get_field_name('order') ?>" >Ascending</input>
                    <input class="radio" type="radio" value="DESC" <?php checked($instance['order'], 'DESC') ?> name="<?php echo $this->get_field_name('order') ?>" >Descending</input>
                </td>
            </tr>
        </table>
        </p>
        <p>
            <label><?php _e('Dimension (width x height: px, %, em)'); ?></label><br />
            <input class="text" type="text"  id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" value="<?php echo $instance['width'] ?>"/>
            &nbsp; x &nbsp;
            <input class="text" type="text"  id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" value="<?php echo $instance['height'] ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wopu_id'); ?>"><?php _e('Unique ID (to identify each instant of this widget)'); ?></label><br />
            <input class="text" type="text"  id="<?php echo $this->get_field_id('wopu_id'); ?>" name="<?php echo $this->get_field_name('wopu_id'); ?>" value="<?php echo $instance['wopu_id'] ?>"/>
        </p>
        <p>
        <table>
            <tr> 
                <td>
                    <input class="checkbox" type="checkbox" <?php checked($instance['show_title'], true) ?> id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" />
                    <label for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e('Show Widget Title'); ?></label><br />
                    <input class="checkbox" type="checkbox" <?php checked($instance['images'], true) ?> id="<?php echo $this->get_field_id('images'); ?>" name="<?php echo $this->get_field_name('images'); ?>" />
                    <label for="<?php echo $this->get_field_id('images'); ?>"><?php _e('Show Link Image'); ?></label><br />
                    <input class="checkbox" type="checkbox" <?php checked($instance['name'], true) ?> id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" />
                    <label for="<?php echo $this->get_field_id('name'); ?>"><?php _e('Show Link Name'); ?></label><br />
                    <input class="checkbox" type="checkbox" <?php checked($instance['description'], true) ?> id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" />
                    <label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Show Link Description'); ?></label><br />
                    <input class="checkbox" type="checkbox" <?php checked($instance['rating'], true) ?> id="<?php echo $this->get_field_id('rating'); ?>" name="<?php echo $this->get_field_name('rating'); ?>" />
                    <label for="<?php echo $this->get_field_id('rating'); ?>"><?php _e('Show Link Rating'); ?></label>
                </td><td style="padding: 1em 0 0 1em;">
                    <label for="<?php echo $this->get_field_id('velocity'); ?>"><?php _e('Scroll velocity (1-99; default is 50)'); ?></label><br />
                    <input class="text" type="text"  id="<?php echo $this->get_field_id('velocity'); ?>" name="<?php echo $this->get_field_name('velocity'); ?>" value="<?php echo $instance['velocity'] ?>"/><br />
                    <label for="<?php echo $this->get_field_id('loop'); ?>"><?php _e('Loop time (1-99; 0 to infinite - default)'); ?></label><br />
                    <input class="text" type="text"  id="<?php echo $this->get_field_id('loop'); ?>" name="<?php echo $this->get_field_name('loop'); ?>" value="<?php echo $instance['loop'] ?>"/>
                </td>
            </tr>
        </table>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('direction'); ?>"><?php _e('Direction'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('direction'); ?>" name="<?php echo $this->get_field_name('direction'); ?>">
                <option value="<?php echo WOPU_HORZ_LR; ?>" <?php echo $instance['direction'] == WOPU_HORZ_LR ? 'selected="selected"' : ""; ?>>Horizontal; From left to right</option>
                <option value="<?php echo WOPU_HORZ_RL; ?>" <?php echo $instance['direction'] == WOPU_HORZ_RL ? 'selected="selected"' : ""; ?>>Horizontal; From right to left</option>
                <option value="<?php echo WOPU_VERT_TD; ?>" <?php echo $instance['direction'] == WOPU_VERT_TD ? 'selected="selected"' : ""; ?>>Vertical; From top to bottom</option>
                <option value="<?php echo WOPU_VERT_DT; ?>" <?php echo $instance['direction'] == WOPU_VERT_DT ? 'selected="selected"' : ""; ?>>Vertical; From bottom to top</option>
            </select>
            <input class="radio" type="radio" value="linear" <?php checked($instance['movetype'], 'linear') ?> name="<?php echo $this->get_field_name('movetype') ?>" >Linear</input>
            <input class="radio" type="radio" value="pingpong" <?php checked($instance['movetype'], 'pingpong') ?> name="<?php echo $this->get_field_name('movetype') ?>" >Pingpong</input>
        </p>
        <?php
    }

}

/* End of file wopu_multicol_blogroll.php */