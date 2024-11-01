<?php
/**
 * Multicolumn links display widget
 * - Display links in multi columns
 * 
 * @since 1.0
 */
/**
 * Define direction constants
 * - Top->Down Left->Right
 * - Top->Down Right->Left
 * - Left->Right Top->Down
 * - Left->Right Down->Top
 */
define('WOPU_DIRECT_LRTD', 0);
define('WOPU_DIRECT_LRDT', 1);
define('WOPU_DIRECT_TDLR', 2);
define('WOPU_DIRECT_TDRL', 3);

class wopu_links_plus extends WP_Widget {

    function __construct($options = array()) {
        $options['description'] = "Display your links with advanced options";
        $control = array('height' => 450);
        parent::__construct('wopu-link-plus', __('Wopu Links+'), $options, $control);
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

        if (is_admin() && !$category) {
            // Display All Links widget as such in the widgets screen
            echo $before_widget . $before_title . __('All Links') . $after_title . $after_widget;
            return;
        }

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
        if ($instance['limit'] > 0) {
            $bm_filter['limit'] = $instance['limit'];
        }
        $bookmarks = get_bookmarks($bm_filter);
        if (count($bookmarks) > 0) {
            $numrows = floor((count($bookmarks) - 1)/ $instance['numcols']) + 1;
            echo "<table class='wopu-links' style='display:table;'>";
            for ($row = 0; $row < $numrows; $row++) {
                echo "<tr>";
                for ($col = 0; $col < $instance['numcols']; $col++) {
                    echo "<td><div class='wopu-links-item'>";
                    // arragement algorithm
                    switch ($instance['direction']) {
                        case WOPU_DIRECT_LRTD:
                            $pos = $row * $instance['numcols'] + $col;
                            break;
                        case WOPU_DIRECT_LRDT:
                            $pos = ($numrows - 1 - $row) * $instance['numcols'] + $col;
                            break;
                        case WOPU_DIRECT_TDLR:
                            $pos = $col * $numrows + $row;
                            break;
                        case WOPU_DIRECT_TDRL:
                            $pos = ($instance['numcols'] - 1 - $col) * $numrows + $row;
                            break;
                    }

                    if (isset($bookmarks[$pos])) {
                        echo "<a href='" . esc_url($bookmarks[$pos]->link_url) . "' alt='" . $bookmarks[$pos]->link_description . "'>";
                        $output = '';
                        if ($bookmarks[$pos]->link_image != null && $show_images) {
                            if (strpos($bookmarks[$pos]->link_image, 'http') === 0) {
                                $output = $bookmarks[$pos]->link_image;
                            } else { // If it's a relative path
                                $output = get_option('siteurl') . $bookmarks[$pos]->link_image;
                            }
                            // display link image
                            echo "<p class='wopu-link-img'><img src='" . $output . "'/></p>";
                        }
                        // display link name
                        if ($show_name) {
                            echo "<p  class='wopu-link-name'>".$bookmarks[$pos]->link_name."</p>";
                        }
                        echo "</a>";
                        // display link description
                        if ($show_description && $bookmarks[$pos]->link_description != null) {
                            echo "<p class='wopu-link-desc'>" . esc_attr($bookmarks[$pos]->link_description) . "</p>";
                        }

                        // display link rating
                        if ($show_rating) {
                            echo "<p class='wopu-link-rate'> Rating: " . $bookmarks[$pos]->link_rating . "</p>";
                        }
                    }
                    echo "</div></td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
        echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $new_instance = (array) $new_instance;
        $instance = array('images' => 0, 'name' => 0, 'description' => 0, 'rating' => 0, 'show_title' => 0,
            'orderby' => 'name', 'order' => 'ASC', 'numcols' => 1, 'limit' => 0, 'direction' => WOPU_DIRECT_LRTD);
        foreach ($instance as $field => $val) {
            if (isset($new_instance[$field])) {
                $instance[$field] = 1;
                if ($field == 'orderby' || $field == 'order' || $field == 'direction' || $field == 'limit' || $field == 'numcols') {
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
            <label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order by'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
                <option value="" <?php echo $instance['orderby'] == '' ? 'selected="selected"' : ""; ?>>Link name</option>
                <?php if(version_compare("3.2", get_bloginfo('version'), "<=")):?>
                <option value="link_id" <?php echo $instance['orderby'] == 'link_id' ? 'selected="selected"' : ""; ?>>Link ID</option>
                <?php else: ?>
                 <option value="id" <?php echo $instance['orderby'] == 'id' ? 'selected="selected"' : ""; ?>>Link ID</option>
                <?php endif; ?>
?>
                <option value="url" <?php echo $instance['orderby'] == 'url' ? 'selected="selected"' : ""; ?>>Link URL</option>
                <option value="updated" <?php echo $instance['orderby'] == 'updated' ? 'selected="selected"' : ""; ?>>Link update time</option>
                <option value="length" <?php echo $instance['orderby'] == 'length' ? 'selected="selected"' : ""; ?>>Link name length</option>
                <option value="rand" <?php echo $instance['orderby'] == 'rand' ? 'selected="selected"' : ""; ?>>Random</option>
            </select>
            <input class="radio" type="radio" value="ASC" <?php checked($instance['order'], 'ASC') ?> name="<?php echo $this->get_field_name('order') ?>" >Ascending</input>
            <input class="radio" type="radio" value="DESC" <?php checked($instance['order'], 'DESC') ?> name="<?php echo $this->get_field_name('order') ?>" >Descending</input>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Limit (0: unlimited)'); ?></label><br />
            <input class="text" type="text"  id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $instance['limit'] ?>"/>
        </p>
        <p>
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
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('numcols'); ?>"><?php _e('Number of columns'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('numcols'); ?>" name="<?php echo $this->get_field_name('numcols'); ?>">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    ?>
                    <option value="<?php echo $i; ?>" <?php echo $instance['numcols'] == $i ? 'selected="selected"' : ""; ?>><?php echo $i; ?></option>
                    <?php
                }
                ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('direction'); ?>"><?php _e('Direction'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('direction'); ?>" name="<?php echo $this->get_field_name('direction'); ?>">
                <option value="<?php echo WOPU_DIRECT_LRTD; ?>" <?php echo $instance['direction'] == WOPU_DIRECT_LRTD ? 'selected="selected"' : ""; ?>>Left->right; Top->Bottom</option>
                <option value="<?php echo WOPU_DIRECT_LRDT; ?>" <?php echo $instance['direction'] == WOPU_DIRECT_LRDT ? 'selected="selected"' : ""; ?>>Left->right; Bottom->top</option>
                <option value="<?php echo WOPU_DIRECT_TDLR; ?>" <?php echo $instance['direction'] == WOPU_DIRECT_TDLR ? 'selected="selected"' : ""; ?>>Top->Bottom; Left->right</option>
                <option value="<?php echo WOPU_DIRECT_TDRL; ?>" <?php echo $instance['direction'] == WOPU_DIRECT_TDRL ? 'selected="selected"' : ""; ?>>Top->Bottom; Right->left</option>
            </select>
        </p>
        <?php
    }

}

/* End of file wopu_multicol_blogroll.php */