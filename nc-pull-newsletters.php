<?php

/*
Plugin Name: Nc Pull Newsletters
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: This plugin pulls newsletters from NewGen.
Version: 1.0
Author: sehui
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

add_action('init', 'nc_shortcodes_init');
function nc_shortcodes_init() {
    add_shortcode('nc_newsletter', 'nc_newsletter_shortcode');
}

// Load external js library for pagination
add_action( 'wp_enqueue_scripts', 'nc_enqueueAssets');
function nc_enqueueAssets() {
    wp_enqueue_script('simplePagination', plugin_dir_url(__FILE__) . '/js/jquery.simplePagination.js', array('jquery'), '1.0', false);
}

function nc_newsletter_shortcode($atts = [], $content = null, $tag = '') {
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $nc_atts = shortcode_atts([
        'url' => '',
        'itemsonpage' => "2"
    ], $atts, $tag);

    ?>

    <div class="newsletter-wrap">
        <ul class="newsletters"></ul>
        <div class="newsletter-pagination"></div>
    </div>

    <script>
        console.log("<?php echo $nc_atts['itemsonpage']; ?>");
        function loadPage(p, onPage) {
            var container = jQuery('ul.newsletters');

            jQuery.ajax({
                url: "<?php echo $nc_atts['url']; ?>" + p + "/" + onPage,
                dataType: 'json',
                success: function(data) {

                    jQuery('.newsletter-pagination').pagination( {
                        items: data.total,
                        itemsOnPage: <?php echo $nc_atts['itemsonpage']; ?>,
                        displayedPages : 6,
                        currentPage: p,
                        cssStyle: 'dark-theme',
                        onInit : function (){
                            container.empty();
                            jQuery.each( data.items, function(k, v) {
                                var $item = jQuery("<li><a target='_blank' href=" + v.link + "><span class='nc_subject'>" + v.subject + "</span><span class='nc_date'>" + v.date + "</span></a></li>");
                                container.append($item.clone());
                            });
                        },
                        onPageClick:  function (pagenumber , event ) {
                            loadPage(pagenumber, <?php echo $nc_atts['itemsonpage']; ?>);

                            jQuery.each( data.items, function(k, v) {
                                var $item = jQuery("<li><a target='_blank' href=" + v.link + "><span class='nc_subject'>" + v.subject + "</span><span class='nc_date'>" + v.date + "</span></a></li>");
                                container.append($item.clone());
                            });
                        }
                    });
                },
                error: function(xhr) {
                    console.log(xhr);
                }
            });
            container.empty();
        }

        jQuery(document).ready(function() {
            loadPage(1,<?php echo $nc_atts['itemsonpage']; ?>);
        });
    </script>

    <?php
}
