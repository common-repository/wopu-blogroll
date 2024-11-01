/**
 * Initiation for wopu scrolling widgets
 * 
 * @package: Wordpress
 * @subpackage: Plugins/Wopu Blogroll
 * @author: Hieu Le Trung (http://webtrunghieu,info)
 */
$wopu = jQuery.noConflict();
$wopu(document).ready(function(){
    // create a default scroller for each widget
    $wopu('.wopu_horz_scroller').SetScroller({
        velocity:       50,
        direction:      'horizontal',
        startfrom:      'right',
        loop:           'infinite',
        movetype:       'linear',
        onmouseover:    'pause',
        onmouseout:     'play',
        onstartup: 	'play',
        cursor: 	'pointer'
    });
    $wopu('.wopu_vert_scroller').SetScroller({
        velocity:       50,
        direction:      'vertical',
        startfrom:      'bottom',
        loop:           'infinite',
        movetype:       'linear',
        onmouseover:    'pause',
        onmouseout:     'play',
        onstartup: 	'play',
        cursor: 	'pointer'
    });
})

/* End of file: wopu_scrolling_links.js */