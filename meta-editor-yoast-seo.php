<?php
/*
Plugin Name: Meta Editor for Yoast SEO
Plugin URI: https://www.renzramos.com/plugins/wordpress/meta-editor-yoast-seo
Description: Meta Editor for Yoast SEO
Version: 1.0
Author: Renz Ramos
Author URI: https://www.renzramos.com
*/

define('META_EDITOR_YOAST_SEO_PLUGIN_TITLE', 'Meta Editor for Yoast SEO' );
define('META_EDITOR_YOAST_SEO_PLUGIN_SLUG', 'meta-editor-yoast-seo' );
define('META_EDITOR_YOAST_SEO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define('META_EDITOR_YOAST_SEO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

class MetaEditorYoastSEO
{

    public function __construct(){

        add_action( 'admin_menu', array( $this, 'admin_meta_editor_yoast_seo_editor_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_meta_editor_yoast_seo_editor_scripts' ));

        add_action( 'wp_ajax_meta_editor_yoast_seo_editor_action', array ( $this, 'meta_editor_yoast_seo_editor_action') );
        add_action( 'wp_ajax_nopriv_meta_editor_yoast_seo_editor_action', array ( $this, 'meta_editor_yoast_seo_editor_action') );

    }

    function admin_meta_editor_yoast_seo_editor_scripts(){

        wp_enqueue_style( 'meta-editor-yoast-seo-tableexport-style', plugin_dir_url(__FILE__) . 'assets/css/tableexport.min.css', array(), null  );
        wp_enqueue_style( 'meta-editor-yoast-seo-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), null  );

        wp_enqueue_script('meta-editor-yoast-seo-xlsx-script', plugin_dir_url(__FILE__) . 'assets/js/xlsx.core.min.js', array(), null, true);
        wp_enqueue_script('meta-editor-yoast-seo-filesave-script', plugin_dir_url(__FILE__) . 'assets/js/FileSaver.min.js', array(), null, true);
        wp_enqueue_script('meta-editor-yoast-seo-tableexport-script', plugin_dir_url(__FILE__) . 'assets/js/tableexport.min.js', array(), null, true);
        wp_enqueue_script('meta-editor-yoast-seo-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array(), null, true);
        $url = array( 
            'ajaxURL' => admin_url( 'admin-ajax.php' ), 
            'siteNameSlug' => sanitize_title(get_bloginfo('name')), 
        );
        wp_localize_script( 'meta-editor-yoast-seo-script', 'yoastMetaEditor', $url );
    }

    public function admin_meta_editor_yoast_seo_editor_page()
    {
        // This page will be under "Settings"
        add_menu_page(
            META_EDITOR_YOAST_SEO_PLUGIN_TITLE, 
            META_EDITOR_YOAST_SEO_PLUGIN_TITLE, 
            'publish_posts', 
            META_EDITOR_YOAST_SEO_PLUGIN_SLUG, 
            array( $this, 'meta_editor_yoast_seo_editor_page' ),
                'dashicons-edit',
            9.5
        );
    }

    public function meta_editor_yoast_seo_editor_page()
    {
        
        $this->options = get_option( 'hosted_ga_data' );
        ?>
        <div id="meta-editor-yoast-seo-container" data-ajax-url="<?php echo admin_url( 'admin-ajax.php' ); ?>" class="wrap">
            <h1>Meta Editor for Yoast SEO (version 1.0.0)</h1>
            
            <div class="summary">
                <p>Page Without Meta Description: <span class="page-without-description">0</span>
            </div>

            <?php
            $args = array(
                'public'   => true,
            );

            $output = 'names'; // names or objects, note names is the default
            $operator = 'and'; // 'and' or 'or'
            $post_types = get_post_types( $args, $output, $operator ); 

            $count = 1;

            echo '<table class="meta-editor-yoast-seo-table" border="1">';
            echo '<thead>';
            echo '<th class="page">Page</th>';
            echo '<th class="title">Title</th>';
            echo '<th class="description">Description</th>';
            echo '<th class="hidden">URL</th>';
            echo '</thead>';
            echo '<tbody>';

            $titles = get_option( 'wpseo_titles' );

            foreach ( $post_types  as $key => $post_type ) {
                if ($post_type == 'attachment') continue;

                $args = array(
                   'post_type' => $post_type,
                   'posts_per_page' => -1,
                   'post_status' => 'publish'
                );
                $query = new WP_Query( $args );

                $title = $titles['title-' . $post_type];
                
                
                if ( $query->have_posts() ) : 

                    echo '<tr class="post-type tableexport-ignore">';
                    echo '<td colspan="3"><h2>' . strtoupper($post_type) .  '</h2></td>';
                    echo '</tr>';
                
                    while ( $query->have_posts() ) : $query->the_post(); 
                        if (get_the_title() == '' ) continue;

                            $meta_title = get_post_meta( get_the_ID() , '_yoast_wpseo_title', true); 
                            $title = apply_filters( 'the_title', $title );
                            $meta_description = get_post_meta( get_the_ID() , '_yoast_wpseo_metadesc', true); 
                        ?>

                        <tr>
                            <td>
                                <a target="_blank" href="<?php echo get_the_permalink(); ?>">
                                    <strong>
                                        <?php echo get_the_title(); ?>
                                    </strong>
                                </a>
                            </td>
                            <td>
                                <input placeholder="<?php echo $title; ?>" name="_yoast_wpseo_title" data-id="<?php echo get_the_ID(); ?>" class="field <?php echo ($meta_title == '') ? 'empty':'filled'; ?>" type="text" value="<?php echo $meta_title; ?>">
                                <span class="hidden"><?php echo $meta_title; ?></span>
                            </td>
                            <td>
                                <textarea name="_yoast_wpseo_metadesc" data-id="<?php echo get_the_ID(); ?>" class="field <?php echo ($meta_description == '') ? 'empty':'filled'; ?>" rows="1"><?php echo $meta_description; ?></textarea>
                                <span class="character-count">Characters count: <?php echo strlen($meta_description); ?></span>
                            </td>
                            <td class="hidden">
                                <?php echo get_the_permalink(); ?>
                            </td>
                        </tr>
                    
                    <?php 
                    endwhile; 
                    wp_reset_postdata(); 
                endif; 
                $count++;
            }
            echo '</tbody>';
            echo '</table>';
            ?>
            <small>Developed by Renz R. (06-13-2018)</small>
        </div>
        <?php
    }

    public function meta_editor_yoast_seo_editor_action() {

        if (isset($_POST)){
        
            $field = $_POST['field'];
            $value = $_POST['value'];
            $id = $_POST['id'];
            
            echo $field . '-' . $value . '-' . $id;
            update_post_meta( $id, $field, '' . $value . '');
            
        }
        wp_die();
    }

}
$meta_editor_yoast_seo_editor = new MetaEditorYoastSEO();