<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if (is_plugin_active('social-articles/social-articles.php') && is_plugin_active( 'buddypress/bp-loader.php' )) {
    define( 'SOCIAL_ARTICLES_LATE_LOAD', 2);
    add_action('plugins_loaded', 'social_articles_premium', 1);
}else {
    add_action( 'admin_notices', 'bp_social_articles_premium_install_buddypress_notice');
}


function bp_social_articles_premium_install_buddypress_notice() {
    echo '<div id="message" class="error fade"><p style="line-height: 150%">';
    _e('<strong>Social Articles Premium add-on</strong></a> requires <strong>BuddyPress</strong> plugin and <strong>Social Articles</strong> plugin to work. ', 'social-articles');
    echo '</p></div>';
}

define('SAP_BASE_PATH', plugin_dir_path( __FILE__ ));
define('SAP_DIR_NAME', plugin_basename(dirname(__FILE__)));
define('SAP_BASE_NAME', plugin_basename(__FILE__));
define('SAP_BASE_URL', plugins_url() . '/' . SAP_DIR_NAME);
define('SAP_PLUGIN_VERSION', '0.7');
define('SA_PLUGIN_PREMIUM_VERSION', '0.7');

function social_articles_premium(){

    require_once plugin_dir_path( __FILE__ ) . 'includes/sa-form/fields/classes/sa-premium-title-class.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/sa-form/fields/classes/sa-premium-taxonomy-regular-class.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/sa-form/fields/classes/sa-premium-taxonomy-hierarchical-class.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/sa-form/fields/classes/sa-premium-content-class.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/sa-form/fields/classes/sa-premium-featured-image-class.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/sa-form/sa-premium-helper-class.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/sa-form/sa-premium-form-settings-class.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/social-article-functions.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/admin/tabs/social-articles-extra-settings.php';
    add_filter( 'ajax_query_attachments_args', 'show_current_user_attachments' );

    $premium_form_setting = new SA_Premium_Form_Settings();
}


add_action( 'wp_enqueue_scripts', 'add_sa_premium_scripts' );
function add_sa_premium_scripts(){
    wp_enqueue_script('social-articles', SAP_BASE_URL . '/assets/js/social-articles.js', array('jquery'), SA_PLUGIN_PREMIUM_VERSION);
    wp_enqueue_style( 'sa-toolbox-addon', SAP_BASE_URL.'/assets/css/sa-toolbox-addon.css', array(),SAP_PLUGIN_VERSION,'all' );
    wp_enqueue_script('parsley-js', SAP_BASE_URL . '/assets/js/parsley.js', array(), SAP_PLUGIN_VERSION, true);
}

add_action('admin_enqueue_scripts', 'add_sa_premium_admin_scripts');
function add_sa_premium_admin_scripts(){
    if (isset($_GET['page']) && $_GET['page'] == 'social-articles') {
        wp_enqueue_script('sa-premium-form', SAP_BASE_URL . '/includes/sa-form/assets/js/sa-premium-form.js', array( 'jquery' ),SA_PLUGIN_PREMIUM_VERSION);
        wp_enqueue_style('select2-css', SAP_BASE_URL. '/assets/css/select2.min.css', array(), SAP_PLUGIN_VERSION);
        wp_enqueue_script( 'select2-js', SAP_BASE_URL. '/assets/js/select2.min.js', array('jquery'), SAP_PLUGIN_VERSION, true );
        wp_enqueue_style('highchecktree-css', SAP_BASE_URL. '/assets/css/highCheckTree.css', array(), SAP_PLUGIN_VERSION);
        wp_enqueue_script( 'highchecktree-js', SAP_BASE_URL. '/assets/js/highchecktree.js', array(), SAP_PLUGIN_VERSION, true );
    }
    wp_enqueue_style( 'sa-toolbox-addon', SAP_BASE_URL.'/assets/css/sa-toolbox-addon-admin.css', array(),SAP_PLUGIN_VERSION,'all' );
}

add_filter('sa_more_fields', 'add_premium_fields', 100, 3);
function add_premium_fields($all_fields, $registered_fields, $post_type){
    $taxonomies = get_object_taxonomies( $post_type, 'objects' );
    foreach($taxonomies as $taxonomy){
        if($taxonomy->name != 'post_format' && $taxonomy->name != 'category' && $taxonomy->name != 'post_tags'){
            if($taxonomy->hierarchical){
                $className = $registered_fields[SA_Helper::TAX_HIERARCHICAL_FIELD];
            }else{
                $className = $registered_fields[SA_Helper::TAX_REGULAR_FIELD];
            }
            if(!empty($className) && class_exists($className)) {
                $all_fields[$taxonomy->name] = new $className($taxonomy->name, $taxonomy->labels->name);
            }
        }
    }
    return $all_fields;
}

function create_activity($article_id, $author_id){
    global $bp, $socialArticles;
    $author    = bp_core_get_userlink( $author_id );
    $article = get_post($article_id);

    $permalink = get_permalink( $article->ID );
    $title     = $article->post_title;

    $action = "%author% added a new article - %article_link%";
    $action = str_replace( '%author%', $author, $action );
    $action = str_replace( '%article_link%', '<a href="' . $permalink . '">'.$article->post_title.'</a>', $action);

    /* Grab the content and make it into an excerpt of 140 chars if we're allowed */
    $content  = "";
    $thumb_id = get_post_thumbnail_id( $article->ID );
    if ( ! empty( $thumb_id ) ) {

        $thumb   = wp_get_attachment_image_src( $thumb_id, 'medium' );
        $content = '<div class="content-shared">
                        <div class="col-lg-4 shared-img" style="width:100%; float:left;">
                            <a href="' . $permalink . '">
                                <img src="' . $thumb['0'] . '" class="shared-content-image"/>
                            </a>
                        </div>
                        <div class="no-padding col-lg-8 shared-content" style="width:100%; float:left;">
                            <div class="ac-activity-title" style="width:100%; float:left;">
                                <a href="' . $permalink . '">' . $title . '</a>
                            </div>
                            <span>' . $article->post_content.'</span>
                        </div>
                    </div>';
    }
    bp_activity_add(
        array(
            'action'       => $action,
            'content'      => $content,
            'component'    => 'social-articles',
            'type'         => 'new_content',
            'user_id'      => $article->post_author,
            'item_id'      => $article->ID,
            'primary_link' => bp_activity_get_permalink( $article->ID )
        )
    );
}

?>