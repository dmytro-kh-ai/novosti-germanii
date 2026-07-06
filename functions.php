<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ===== ENQUEUE =====
function novosti_enqueue() {
    wp_enqueue_style(
        'google-fonts',
        'https://fonts.googleapis.com/css2?...',
        array(),
        null
    );

    wp_enqueue_style(
        'novosti-style',
        get_stylesheet_uri(),
        array('google-fonts'),
        '1.4'
    );

    wp_enqueue_script(
        'novosti-js',
        get_template_directory_uri() . '/js/main.js',
        array(),
        '1.5',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'novosti_enqueue' );

// ===== ТЕМА =====
function novosti_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', array('height'=>100,'width'=>300,'flex-height'=>true,'flex-width'=>true) );
    add_theme_support( 'html5', array('search-form','comment-form','comment-list','gallery','caption') );
    add_theme_support( 'automatic-feed-links' );
    add_image_size( 'news-card',     400, 260, true );
    add_image_size( 'news-list',     180, 120, true );
    add_image_size( 'news-featured', 1200, 675, true );
    register_nav_menus( array('primary'=>'Основное меню','footer'=>'Меню в футере') );
}
add_action( 'after_setup_theme', 'novosti_setup' );

// ===== LAZY LOADING =====
function novosti_add_lazy( $attr, $attachment, $size ) {
    $attr['loading']  = 'lazy';
    $attr['decoding'] = 'async';
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'novosti_add_lazy', 10, 3 );

// ===== ЧИСТИМ <head> =====
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
add_filter( 'the_generator', '__return_empty_string' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'global-styles' );
}, 100 );

// ===== DNS PREFETCH =====
function novosti_dns_prefetch() {
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";
}
add_action( 'wp_head', 'novosti_dns_prefetch', 1 );

// ===== DEFER JS =====
function novosti_defer_scripts( $tag, $handle, $src ) {
    if ( $handle === 'novosti-js' )
        return str_replace( ' src', ' defer src', $tag );
    return $tag;
}
add_filter( 'script_loader_tag', 'novosti_defer_scripts', 10, 3 );

// ===== РЕВИЗИИ =====
add_filter( 'wp_revisions_to_keep', function($n, $p) { return 3; }, 10, 2 );

// ===== SEO: OPEN GRAPH + META =====
function novosti_seo_head() {
    $site_name = get_bloginfo('name');
    $site_url  = home_url('/');

    if ( is_singular() ) {
        global $post;
        $title       = get_the_title();
        // Безопасно получаем описание
        $description = '';
        if ( ! empty($post->post_excerpt) ) {
            $description = $post->post_excerpt;
        } elseif ( ! empty($post->post_content) ) {
            $description = wp_trim_words( $post->post_content, 30, '' );
        }
        $description = wp_strip_all_tags( $description );
        $url         = get_permalink();
        $type        = 'article';
        $image       = has_post_thumbnail()
            ? get_the_post_thumbnail_url( $post->ID, 'news-featured' )
            : get_template_directory_uri() . '/img/og-default.jpg';
        $pub_date    = get_the_date( 'c' );
        $mod_date    = get_the_modified_date( 'c' );
        $cats        = get_the_category();
        $cat_name    = ! empty($cats) ? $cats[0]->name : '';
    } else {
        $title       = is_category() ? single_cat_title('', false) . ' — ' . $site_name : $site_name;
        $description = get_bloginfo('description');
        $url         = get_pagenum_link();
        $type        = 'website';
        $image       = get_template_directory_uri() . '/img/og-default.jpg';
        $pub_date    = '';
        $mod_date    = '';
        $cat_name    = '';
    }

    $description = mb_strimwidth( $description, 0, 160, '...' );
    ?>
<meta name="description" content="<?php echo esc_attr($description); ?>">
<link rel="canonical" href="<?php echo esc_url($url); ?>">
<meta property="og:type"        content="<?php echo esc_attr($type); ?>">
<meta property="og:title"       content="<?php echo esc_attr($title); ?>">
<meta property="og:description" content="<?php echo esc_attr($description); ?>">
<meta property="og:url"         content="<?php echo esc_url($url); ?>">
<meta property="og:site_name"   content="<?php echo esc_attr($site_name); ?>">
<meta property="og:image"       content="<?php echo esc_url($image); ?>">
<meta property="og:locale"      content="ru_RU">
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?php echo esc_attr($title); ?>">
<meta name="twitter:description" content="<?php echo esc_attr($description); ?>">
<meta name="twitter:image"       content="<?php echo esc_url($image); ?>">
<?php if ( is_singular('post') && $pub_date ) : ?>
<meta property="article:published_time" content="<?php echo esc_attr($pub_date); ?>">
<meta property="article:modified_time"  content="<?php echo esc_attr($mod_date); ?>">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": <?php echo json_encode($title); ?>,
  "description": <?php echo json_encode($description); ?>,
  "url": <?php echo json_encode($url); ?>,
  "datePublished": <?php echo json_encode($pub_date); ?>,
  "dateModified": <?php echo json_encode($mod_date); ?>,
  "image": {"@type":"ImageObject","url":<?php echo json_encode($image); ?>,"width":1200,"height":630},
  "publisher": {"@type":"Organization","name":<?php echo json_encode($site_name); ?>,"url":<?php echo json_encode($site_url); ?>},
  "author": {"@type":"Organization","name":<?php echo json_encode($site_name); ?>},
  "inLanguage": "ru"
}
</script>
<?php endif; ?>
<?php if ( is_home() || is_front_page() ) : ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": <?php echo json_encode($site_name); ?>,
  "url": <?php echo json_encode($site_url); ?>,
  "potentialAction": {"@type":"SearchAction","target":<?php echo json_encode(home_url('/?s={search_term_string}')); ?>,"query-input":"required name=search_term_string"}
}
</script>
<?php endif; ?>
    <?php
}
add_action( 'wp_head', 'novosti_seo_head', 5 );

// ===== КАСТОМНЫЕ ПОЛЯ ДЛЯ АФИШИ =====
function novosti_afisha_meta_box() {
    add_meta_box( 'novosti_afisha', '📅 Данные события (Афиша)', 'novosti_afisha_meta_box_html', 'post', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'novosti_afisha_meta_box' );

function novosti_afisha_meta_box_html( $post ) {
    wp_nonce_field( 'novosti_afisha_save', 'novosti_afisha_nonce' );
    $date = get_post_meta( $post->ID, '_event_date', true );
    $time = get_post_meta( $post->ID, '_event_time', true );
    $city = get_post_meta( $post->ID, '_event_city', true );
    $addr = get_post_meta( $post->ID, '_event_address', true );
    echo '<p><label><strong>Дата события</strong><br><input type="date" name="_event_date" value="' . esc_attr($date) . '" style="width:100%"></label></p>';
    echo '<p><label><strong>Время</strong><br><input type="time" name="_event_time" value="' . esc_attr($time) . '" style="width:100%"></label></p>';
    echo '<p><label><strong>Город</strong><br><input type="text" name="_event_city" value="' . esc_attr($city) . '" placeholder="Берлин" style="width:100%"></label></p>';
    echo '<p><label><strong>Адрес</strong><br><input type="text" name="_event_address" value="' . esc_attr($addr) . '" style="width:100%"></label></p>';
}

function novosti_afisha_meta_save( $post_id ) {
    if ( ! isset($_POST['novosti_afisha_nonce']) ) return;
    if ( ! wp_verify_nonce($_POST['novosti_afisha_nonce'], 'novosti_afisha_save') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    foreach ( array('_event_date','_event_time','_event_city','_event_address') as $field )
        if ( isset($_POST[$field]) ) update_post_meta( $post_id, $field, sanitize_text_field($_POST[$field]) );
}
add_action( 'save_post', 'novosti_afisha_meta_save' );

// ===== ПОЛЕ "ИСТОЧНИК" =====
function novosti_source_meta_box() {
    add_meta_box( 'novosti_source', '🔗 Источник новости', 'novosti_source_meta_box_html', 'post', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'novosti_source_meta_box' );

function novosti_source_meta_box_html( $post ) {
    wp_nonce_field('novosti_source_save','novosti_source_nonce');
    $src  = get_post_meta($post->ID,'_source_name',true);
    $link = get_post_meta($post->ID,'_source_url',true);
    echo '<p><label><strong>Название источника</strong><br><input type="text" name="_source_name" value="' . esc_attr($src) . '" placeholder="ТАСС, DW..." style="width:100%"></label></p>';
    echo '<p><label><strong>Ссылка</strong><br><input type="url" name="_source_url" value="' . esc_attr($link) . '" placeholder="https://..." style="width:100%"></label></p>';
}

function novosti_source_meta_save( $post_id ) {
    if ( ! isset($_POST['novosti_source_nonce']) ) return;
    if ( ! wp_verify_nonce($_POST['novosti_source_nonce'],'novosti_source_save') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    foreach ( array('_source_name','_source_url') as $f )
        if ( isset($_POST[$f]) ) update_post_meta($post_id, $f, sanitize_text_field($_POST[$f]));
}
add_action( 'save_post', 'novosti_source_meta_save' );

// ===== ПОЛЕ "ССЫЛКА БАННЕРА" (для записей в рубрике reklama) =====
function novosti_banner_meta_box() {
    add_meta_box( 'novosti_banner', '🔗 Ссылка баннера (Реклама)', 'novosti_banner_meta_box_html', 'post', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'novosti_banner_meta_box' );

function novosti_banner_meta_box_html( $post ) {
    wp_nonce_field('novosti_banner_save','novosti_banner_nonce');
    $link = get_post_meta($post->ID,'_banner_url',true);
    echo '<p><label><strong>Куда ведёт баннер</strong><br><input type="url" name="_banner_url" value="' . esc_attr($link) . '" placeholder="https://gadanie.in.ua/" style="width:100%"></label></p>';
    echo '<p style="color:#888;font-size:11px;margin:0;">Работает для записей в рубрике «reklama». Если поле пустое — баннер ведёт на khursenko.agency.</p>';
}

function novosti_banner_meta_save( $post_id ) {
    if ( ! isset($_POST['novosti_banner_nonce']) ) return;
    if ( ! wp_verify_nonce($_POST['novosti_banner_nonce'],'novosti_banner_save') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( isset($_POST['_banner_url']) )
        update_post_meta($post_id, '_banner_url', esc_url_raw($_POST['_banner_url']));
}
add_action( 'save_post', 'novosti_banner_meta_save' );

// ===== РЕКЛАМА ВНУТРИ ТЕКСТА (после 3-го абзаца) =====
function novosti_inject_ad_in_content( $content ) {
    if ( ! is_single() ) return $content;
    $ad_cat = get_category_by_slug('reklama');
    if ( ! $ad_cat ) return $content;
    $ads = get_posts( array('post_type'=>'post','posts_per_page'=>1,'category__in'=>array($ad_cat->term_id),'orderby'=>'rand') );
    if ( ! $ads ) return $content;
    $ad   = $ads[0];
    $img  = has_post_thumbnail($ad->ID) ? get_the_post_thumbnail($ad->ID,'news-card') : '';
    $link = 'https://khursenko.agency/';
    $ad_html = '<div class="in-content-ad">'
        . '<span class="in-content-ad__label">Реклама</span>'
        . '<a href="' . esc_url($link) . '" class="in-content-ad__inner">'
        . ( $img ? '<div class="in-content-ad__img">' . $img . '</div>' : '' )
        . '<div class="in-content-ad__text"><strong>' . esc_html($ad->post_title) . '</strong></div>'
        . '</a></div>';
    $paragraphs = explode('</p>', $content);
    if ( count($paragraphs) > 4 ) {
        $paragraphs[3] .= '</p>' . $ad_html;
        return implode('</p>', $paragraphs);
    }
    return $content . $ad_html;
}
add_filter( 'the_content', 'novosti_inject_ad_in_content' );

// ===== ХЛЕБНЫЕ КРОШКИ =====
function novosti_breadcrumbs() {
    if ( is_front_page() ) return;
    echo '<nav class="breadcrumbs" aria-label="Хлебные крошки"><div class="container"><span>';
    echo '<a href="' . esc_url(home_url('/')) . '">Главная</a>';
    if ( is_category() ) {
        echo ' → <span>' . single_cat_title('', false) . '</span>';
    } elseif ( is_single() ) {
        $cats = get_the_category();
        if ( $cats ) echo ' → <a href="' . esc_url(get_category_link($cats[0]->term_id)) . '">' . esc_html($cats[0]->name) . '</a>';
        echo ' → <span>' . get_the_title() . '</span>';
    } elseif ( is_page() ) {
        echo ' → <span>' . get_the_title() . '</span>';
    } elseif ( is_search() ) {
        echo ' → <span>Поиск: ' . get_search_query() . '</span>';
    }
    echo '</span></div></nav>';
}

// ===== ХЕЛПЕРЫ =====
function novosti_get_ad_banner() {
    return get_posts( array('post_type'=>'post','posts_per_page'=>10,'category_name'=>'reklama') );
}
function novosti_get_ad_articles( $count = 2 ) {
    return get_posts( array('post_type'=>'post','posts_per_page'=>$count,'category_name'=>'reklama') );
}
function novosti_get_partner_posts( $count = 3 ) {
    return get_posts( array('post_type'=>'post','posts_per_page'=>$count,'category_name'=>'partner') );
}

function novosti_get_special_category_ids() {
    $ex = array();
    foreach ( array('reklama','partner','afisha') as $s ) {
        $c = get_category_by_slug($s);
        if ( $c ) $ex[] = $c->term_id;
    }
    return $ex;
}

function novosti_get_city_category_ids() {
    $ids = array();
    $city_slug_aliases = array(
        'berlin',
        'hamburg',
        'munich',
        'muenchen',
        'cologne',
        'koeln',
        'koln',
        'keln',
        'frankfurt',
        'frankfurt-am-main',
        'duesseldorf',
        'dusseldorf',
        'leipzig',
        'dortmund',
        'essen',
        'dresden',
        'stuttgart',
    );

    foreach ( array_unique( array_merge( array_keys( novosti_get_cities() ), $city_slug_aliases ) ) as $slug ) {
        $c = get_category_by_slug( $slug );
        if ( $c ) $ids[] = $c->term_id;
    }

    $city_names = array_merge(
        array_values( novosti_get_cities() ),
        array(
            'Берлин',
            'Гамбург',
            'Мюнхен',
            'Кельн',
            'Кёльн',
            'Франкфурт',
            'Франкфурт-на-Майне',
            'Дюссельдорф',
            'Лейпциг',
            'Дортмунд',
            'Эссен',
            'Дрезден',
            'Штутгарт',
        )
    );
    foreach ( $city_names as $name ) {
        $c = get_term_by( 'name', $name, 'category' );
        if ( $c && ! is_wp_error( $c ) ) $ids[] = (int) $c->term_id;
    }

    $city_name_keys = array();
    foreach ( $city_names as $name ) {
        $city_name_keys[] = str_replace( 'ё', 'е', mb_strtolower( $name ) );
    }

    $all_categories = get_terms( array(
        'taxonomy'   => 'category',
        'hide_empty' => false,
    ) );
    if ( ! is_wp_error( $all_categories ) ) {
        foreach ( $all_categories as $category ) {
            $name_key = str_replace( 'ё', 'е', mb_strtolower( $category->name ) );
            if ( in_array( $name_key, $city_name_keys, true ) ) {
                $ids[] = (int) $category->term_id;
            }
        }
    }

    return array_values( array_unique( array_filter( $ids ) ) );
}

function novosti_get_latest_news( $count = 6 ) {
    $ex = novosti_get_special_category_ids();
    return get_posts( array('post_type'=>'post','posts_per_page'=>$count,'category__not_in'=>$ex) );
}

function novosti_get_common_latest_news( $count = 6 ) {
    $ex = array_merge( novosti_get_special_category_ids(), novosti_get_city_category_ids() );
    return get_posts( array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $count,
        'category__not_in'    => $ex,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ) );
}

function novosti_get_all_city_latest_news( $count = 6 ) {
    $city_ids = novosti_get_city_category_ids();
    if ( ! $city_ids ) return array();
    return get_posts( array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $count,
        'category__in'        => $city_ids,
        'category__not_in'    => novosti_get_special_category_ids(),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ) );
}

function novosti_get_yesterday_news( $count = 3 ) {
    $ex = array_merge( novosti_get_special_category_ids(), novosti_get_city_category_ids() );
    return get_posts( array(
        'post_type'        => 'post',
        'post_status'      => 'publish',
        'posts_per_page'   => $count,
        'date_query'       => array( array(
            'year'  => date('Y', strtotime('-1 day')),
            'month' => date('m', strtotime('-1 day')),
            'day'   => date('d', strtotime('-1 day')),
        )),
        'category__not_in' => $ex,
        'ignore_sticky_posts' => true,
        'no_found_rows'    => true,
    ));
}
function novosti_get_afisha( $count = 3 ) {
    return get_posts( array('post_type'=>'post','posts_per_page'=>$count,'category_name'=>'afisha','orderby'=>'meta_value','meta_key'=>'_event_date','order'=>'ASC') );
}
function novosti_time_ago( $post_id = null ) {
    $time = get_post_time( 'U', false, $post_id );
    if ( ! $time ) return '';
    $diff = time() - $time;
    if ( $diff < 3600 )   return round($diff/60) . ' мин назад';
    if ( $diff < 86400 )  return round($diff/3600) . ' ч назад';
    if ( $diff < 172800 ) return 'вчера в ' . get_post_time('H:i', false, $post_id);
    return get_post_time('d M', false, $post_id);
}
function novosti_widgets_init() {
    register_sidebar( array('name'=>'Сайдбар','id'=>'sidebar-1','before_widget'=>'<div class="widget">','after_widget'=>'</div>','before_title'=>'<h3 class="widget-title">','after_title'=>'</h3>') );
}
add_action( 'widgets_init', 'novosti_widgets_init' );

// ===== СКРЫВАТЬ БИТЫЕ КАРТИНКИ В ТЕКСТЕ =====
add_filter('the_content', function($content){
    return preg_replace(
        '/<img(?![^>]*onerror)([^>]*)>/i',
        '<img$1 onerror="this.closest(\'.single-post__thumb\') ? this.closest(\'.single-post__thumb\').style.display=\'none\' : this.style.display=\'none\';">',
        $content
    );
});

// ===== ГОРОДА =====
function novosti_get_cities() {
    return array(
        'berlin'      => 'Берлин',
        'hamburg'     => 'Гамбург',
        'munich'      => 'Мюнхен',
        'cologne'     => 'Кёльн',
        'frankfurt'   => 'Франкфурт',
        'stuttgart'   => 'Штутгарт',
        'duesseldorf' => 'Дюссельдорф',
        'leipzig'     => 'Лейпциг',
        'dortmund'    => 'Дортмунд',
        'essen'       => 'Эссен',
        'dresden'     => 'Дрезден',
    );
}

function novosti_is_city_category() {
    if ( ! is_category() ) return false;
    $obj = get_queried_object();
    if ( ! $obj || ! isset( $obj->slug ) ) return false;
    return isset( novosti_get_cities()[ $obj->slug ] );
}

function novosti_get_city_name( $slug = '' ) {
    if ( ! $slug ) {
        $obj = get_queried_object();
        $slug = $obj ? $obj->slug : '';
    }
    $cities = novosti_get_cities();
    return isset( $cities[ $slug ] ) ? $cities[ $slug ] : '';
}

function novosti_get_excluded_cats() {
    $ex = array();
    foreach ( array( 'reklama', 'partner', 'afisha' ) as $s ) {
        $c = get_category_by_slug( $s );
        if ( $c ) $ex[] = $c->term_id;
    }
    return $ex;
}

function novosti_get_city_latest_news( $city_slug, $count = 6 ) {
    $city_cat = get_category_by_slug( $city_slug );
    if ( ! $city_cat ) return array();
    return get_posts( array(
        'post_type'      => 'post',
        'posts_per_page' => $count,
        'category__in'   => array( $city_cat->term_id ),
        'category__not_in' => novosti_get_excluded_cats(),
    ) );
}

function novosti_get_city_yesterday_news( $city_slug, $count = 3 ) {
    $city_cat = get_category_by_slug( $city_slug );
    if ( ! $city_cat ) return array();
    return get_posts( array(
        'post_type'        => 'post',
        'posts_per_page'   => $count,
        'category__in'     => array( $city_cat->term_id ),
        'category__not_in' => novosti_get_excluded_cats(),
        'date_query'       => array( array(
            'year'  => date( 'Y', strtotime( '-1 day' ) ),
            'month' => date( 'm', strtotime( '-1 day' ) ),
            'day'   => date( 'd', strtotime( '-1 day' ) ),
        ) ),
    ) );
}

function novosti_get_city_afisha( $city_slug, $count = 3 ) {
    $city_cat   = get_category_by_slug( $city_slug );
    $afisha_cat = get_category_by_slug( 'afisha' );
    if ( ! $city_cat || ! $afisha_cat ) return array();
    return get_posts( array(
        'post_type'      => 'post',
        'posts_per_page' => $count,
        'category__and'  => array( $city_cat->term_id, $afisha_cat->term_id ),
        'orderby'        => 'meta_value',
        'meta_key'       => '_event_date',
        'order'          => 'ASC',
    ) );
}

// Заголовок вкладки для городских страниц: «Новости Берлина — Новости Германии»
add_filter( 'document_title_parts', function( $title ) {
    if ( ! novosti_is_city_category() ) return $title;
    $obj = get_queried_object();
    if ( ! $obj ) return $title;
    $title['title'] = 'Новости ' . novosti_city_genitive( $obj->slug );
    return $title;
} );

// Фильтр главного запроса для городских страниц: убирает reklama/partner/afisha
add_action( 'pre_get_posts', 'novosti_city_query_filter' );
function novosti_city_query_filter( $query ) {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_category() ) return;

    $cat_name = $query->get( 'category_name' );
    // Обрабатываем вложенные пути типа 'germany/berlin'
    $slug = $cat_name ? basename( $cat_name ) : '';
    if ( ! $slug || ! isset( novosti_get_cities()[ $slug ] ) ) return;

    $ex = array();
    foreach ( array( 'reklama', 'partner', 'afisha' ) as $s ) {
        $c = get_category_by_slug( $s );
        if ( $c ) $ex[] = $c->term_id;
    }
    if ( $ex ) {
        $query->set( 'category__not_in', $ex );
    }
}

// Родительный падеж для заголовка H1 «Новости <города>»
function novosti_city_genitive( $slug ) {
    $map = array(
        'berlin'      => 'Берлина',
        'hamburg'     => 'Гамбурга',
        'munich'      => 'Мюнхена',
        'cologne'     => 'Кёльна',
        'frankfurt'   => 'Франкфурта',
        'stuttgart'   => 'Штутгарта',
        'duesseldorf' => 'Дюссельдорфа',
        'leipzig'     => 'Лейпцига',
        'dortmund'    => 'Дортмунда',
        'essen'       => 'Эссена',
        'dresden'     => 'Дрездена',
    );
    return isset( $map[ $slug ] ) ? $map[ $slug ] : novosti_get_city_name( $slug );
}
