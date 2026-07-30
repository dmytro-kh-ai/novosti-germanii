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

    if ( empty( $attr['alt'] ) ) {
        $alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );

        if ( ! $alt && ! empty( $attachment->post_parent ) ) {
            $alt = get_the_title( $attachment->post_parent );
        }

        if ( ! $alt ) {
            $alt = $attachment->post_title;
        }

        if ( $alt ) {
            $attr['alt'] = wp_strip_all_tags( $alt );
        }
    }

    if ( empty( $attr['title'] ) && ! empty( $attr['alt'] ) ) {
        $attr['title'] = $attr['alt'];
    }

    if ( empty( $attr['sizes'] ) ) {
        if ( $size === 'news-card' ) {
            $attr['sizes'] = '(max-width: 600px) 100vw, (max-width: 900px) 50vw, 400px';
        } elseif ( $size === 'news-list' ) {
            $attr['sizes'] = '180px';
        } elseif ( $size === 'news-featured' ) {
            $attr['sizes'] = '(max-width: 1200px) 100vw, 1200px';
        }
    }

    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'novosti_add_lazy', 10, 3 );

function novosti_allow_modern_image_uploads( $mimes ) {
    $mimes['webp'] = 'image/webp';
    $mimes['avif'] = 'image/avif';

    return $mimes;
}
add_filter( 'upload_mimes', 'novosti_allow_modern_image_uploads' );

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

// ===== ИНДЕКСАЦИЯ =====
function novosti_get_noindex_category_slugs() {
    return array( 'reklama', 'partner' );
}

function novosti_is_noindex_category() {
    if ( ! is_category() ) return false;

    $term = get_queried_object();
    if ( ! $term || empty( $term->slug ) ) return false;

    return in_array( $term->slug, novosti_get_noindex_category_slugs(), true );
}

function novosti_has_noindex_query_params() {
    if ( empty( $_GET ) ) return false;

    $allowed = array( 's', 'paged', 'page' );

    foreach ( array_keys( $_GET ) as $key ) {
        if ( ! in_array( sanitize_key( $key ), $allowed, true ) ) {
            return true;
        }
    }

    return false;
}

function novosti_is_noindex_archive_or_filter() {
    return (
        is_search()
        || is_preview()
        || is_tag()
        || is_author()
        || is_date()
        || novosti_is_noindex_category()
        || novosti_has_noindex_query_params()
    );
}

function novosti_robots_txt( $output, $public ) {
    if ( '0' === (string) $public ) {
        return $output;
    }

    $lines = array(
        'User-agent: *',
        'Disallow: /wp-admin/',
        'Allow: /wp-admin/admin-ajax.php',
        'Disallow: /wp-login.php',
        'Disallow: /xmlrpc.php',
        'Disallow: /?s=',
        'Disallow: /*?s=',
        'Disallow: /*&s=',
        'Disallow: /*?replytocom=',
        'Disallow: /*&replytocom=',
        'Disallow: /*?preview=',
        'Disallow: /*&preview=',
        'Disallow: /*?attachment_id=',
        'Disallow: /*&attachment_id=',
        'Disallow: /topics/reklama/',
        'Disallow: /topics/partner/',
        '',
        'Sitemap: ' . home_url( '/sitemap_index.xml' ),
    );

    return implode( "\n", $lines ) . "\n";
}
add_filter( 'robots_txt', 'novosti_robots_txt', 999, 2 );

function novosti_wp_robots( $robots ) {
    $robots['max-image-preview'] = 'large';

    if ( novosti_is_noindex_archive_or_filter() ) {
        unset( $robots['index'] );
        $robots['noindex'] = true;
        $robots['follow']  = true;
    }

    return $robots;
}
add_filter( 'wp_robots', 'novosti_wp_robots' );

function novosti_yoast_robots( $robots ) {
    if ( novosti_is_noindex_archive_or_filter() ) {
        return 'noindex, follow, max-image-preview:large';
    }

    if ( is_string( $robots ) && $robots && strpos( $robots, 'max-image-preview' ) === false ) {
        $robots .= ', max-image-preview:large';
    }

    return $robots;
}
add_filter( 'wpseo_robots', 'novosti_yoast_robots' );

function novosti_exclude_service_terms_from_yoast_sitemap( $url, $type, $object ) {
    if ( $object instanceof WP_User ) {
        return false;
    }

    if (
        $object instanceof WP_Term
        && $object->taxonomy === 'post_tag'
    ) {
        return false;
    }

    if (
        $object instanceof WP_Term
        && $object->taxonomy === 'category'
        && in_array( $object->slug, novosti_get_noindex_category_slugs(), true )
    ) {
        return false;
    }

    return $url;
}
add_filter( 'wpseo_sitemap_entry', 'novosti_exclude_service_terms_from_yoast_sitemap', 10, 3 );
add_filter( 'wpseo_sitemap_exclude_author', '__return_true' );

function novosti_get_canonical_url() {
    if ( is_singular() ) {
        return get_permalink();
    }

    if ( is_home() || is_front_page() || is_archive() || is_search() ) {
        $url = get_pagenum_link( max( 1, get_query_var( 'paged' ) ) );

        return strtok( $url, '?' );
    }

    return '';
}

function novosti_yoast_canonical( $canonical ) {
    $url = novosti_get_canonical_url();

    return $url ? $url : $canonical;
}
add_filter( 'wpseo_canonical', 'novosti_yoast_canonical' );

// ===== SEO: URL HYGIENE =====
function novosti_transliterate_slug_text( $text ) {
    $map = array(
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'j',
        'к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f',
        'х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shh','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        'ä'=>'ae','ö'=>'oe','ü'=>'ue','ß'=>'ss',
    );

    $text = function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
    $text = strtr( $text, $map );
    $text = remove_accents( $text );

    return $text;
}

function novosti_make_clean_slug( $text, $max_words = 8, $max_length = 78 ) {
    $text = novosti_transliterate_slug_text( wp_strip_all_tags( $text ) );
    $text = preg_replace( '/\bseo[-\s_]*zagolovok\b/i', '', $text );
    $text = preg_replace( '/[^a-z0-9]+/i', '-', $text );
    $text = trim( strtolower( $text ), '-' );

    if ( ! $text ) return '';

    $words = array_values( array_filter( explode( '-', $text ) ) );
    $words = array_slice( $words, 0, $max_words );
    $slug  = implode( '-', $words );

    if ( strlen( $slug ) > $max_length ) {
        $slug = substr( $slug, 0, $max_length );
        $slug = preg_replace( '/-[^-]*$/', '', $slug );
        $slug = trim( $slug, '-' );
    }

    return $slug;
}

function novosti_sanitize_title_for_slug( $title, $raw_title = '', $context = 'display' ) {
    if ( $context !== 'save' ) return $title;

    $source = $raw_title ? $raw_title : $title;
    $slug   = novosti_make_clean_slug( $source );

    return $slug ? $slug : $title;
}
add_filter( 'sanitize_title', 'novosti_sanitize_title_for_slug', 9, 3 );

function novosti_clean_public_url_redirect() {
    if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) return;
    if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || $_SERVER['REQUEST_METHOD'] !== 'GET' ) return;
    if ( empty( $_SERVER['REQUEST_URI'] ) ) return;

    $request_uri = wp_unslash( $_SERVER['REQUEST_URI'] );
    $parts       = wp_parse_url( $request_uri );
    $path        = isset( $parts['path'] ) ? $parts['path'] : '/';
    $query       = array();

    if ( ! empty( $parts['query'] ) ) {
        wp_parse_str( $parts['query'], $query );
    }

    $tracking_params = array(
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'fbclid', 'gclid', 'gbraid', 'wbraid', 'yclid', 'mc_cid', 'mc_eid',
    );

    $allowed_query_params = array( 's', 'paged', 'page' );
    $clean_query          = $query;

    foreach ( array_keys( $clean_query ) as $param ) {
        if ( ! in_array( sanitize_key( $param ), $allowed_query_params, true ) ) {
            unset( $clean_query[ $param ] );
        }
    }

    foreach ( $tracking_params as $param ) {
        unset( $clean_query[ $param ] );
    }

    $clean_path = strtolower( $path );

    if ( $clean_path === $path && $clean_query === $query ) return;

    $target = home_url( $clean_path );
    if ( ! empty( $clean_query ) ) {
        $target = add_query_arg( $clean_query, $target );
    }

    wp_safe_redirect( $target, 301 );
    exit;
}
add_action( 'template_redirect', 'novosti_clean_public_url_redirect', 1 );

function novosti_trim_meta_text( $text, $max = 155 ) {
    $text = wp_strip_all_tags( $text );
    $text = preg_replace( '/\s+/u', ' ', $text );
    $text = trim( $text );

    if ( function_exists( 'mb_strimwidth' ) ) {
        return mb_strimwidth( $text, 0, $max, '...' );
    }

    return strlen( $text ) > $max ? substr( $text, 0, $max - 3 ) . '...' : $text;
}

function novosti_get_meta_description() {
    $site_name = get_bloginfo( 'name' );
    $site_desc = get_bloginfo( 'description' );
    $paged     = max( 1, (int) get_query_var( 'paged' ) );

    if ( is_singular() ) {
        global $post;

        if ( ! empty( $post->post_excerpt ) ) {
            return novosti_trim_meta_text( $post->post_excerpt );
        }

        if ( ! empty( $post->post_content ) ) {
            return novosti_trim_meta_text( wp_trim_words( $post->post_content, 28, '' ) );
        }

        return novosti_trim_meta_text( get_the_title() . ' — ' . $site_name );
    }

    if ( is_category() ) {
        $term = get_queried_object();
        if ( $term instanceof WP_Term ) {
            $description = term_description( $term, 'category' );
            if ( $description ) {
                return novosti_trim_meta_text( $description );
            }

            $text = novosti_get_category_seo_text( $term );
            if ( $paged > 1 ) {
                $text .= ' Страница ' . $paged . '.';
            }

            return novosti_trim_meta_text( $text );
        }
    }

    if ( is_author() ) {
        $author_name = get_the_author_meta( 'display_name', get_query_var( 'author' ) );
        $text = 'Публикации автора ' . $author_name . ' на сайте ' . $site_name . ': свежие новости Германии, аналитика и полезные материалы.';

        return novosti_trim_meta_text( $text );
    }

    if ( is_search() ) {
        return novosti_trim_meta_text( 'Результаты поиска по сайту ' . $site_name . ': новости Германии, темы, города и полезные материалы.' );
    }

    if ( is_home() || is_front_page() ) {
        $text = 'Свежие новости Германии на русском языке: политика, экономика, миграция, города, транспорт, недвижимость и важные события Европы каждый день.';
        if ( $paged > 1 ) {
            $text .= ' Страница ' . $paged . '.';
        }

        return novosti_trim_meta_text( $text );
    }

    if ( is_archive() ) {
        $title = get_the_archive_title();
        $text = $title . ': последние новости Германии, свежие публикации, важные события и обновления.';
        if ( $paged > 1 ) {
            $text .= ' Страница ' . $paged . '.';
        }

        return novosti_trim_meta_text( $text );
    }

    return novosti_trim_meta_text( $site_desc ? $site_desc : $site_name );
}

function novosti_yoast_metadesc( $description ) {
    $generated = novosti_get_meta_description();

    return $generated ? $generated : $description;
}
add_filter( 'wpseo_metadesc', 'novosti_yoast_metadesc' );

function novosti_yoast_title( $title ) {
    if ( is_singular() ) {
        $post_title = wp_strip_all_tags( get_the_title() );

        if ( function_exists( 'mb_strlen' ) && mb_strlen( $post_title ) < 25 ) {
            return $post_title . ' — новости Германии';
        }

        if ( function_exists( 'mb_strlen' ) && mb_strlen( $post_title ) > 65 ) {
            return novosti_trim_meta_text( $post_title, 62 );
        }

        return $post_title;
    }

    if ( is_category() ) {
        $term  = get_queried_object();
        $paged = max( 1, (int) get_query_var( 'paged' ) );

        if ( $term instanceof WP_Term ) {
            if ( novosti_is_city_category() ) {
                $title = 'Новости ' . novosti_city_genitive( $term->slug ) . ' сегодня';
            } else {
                $title = novosti_get_category_title( $term );
            }

            if ( $paged > 1 ) {
                $title .= ' — страница ' . $paged;
            }

            return novosti_trim_meta_text( $title, 65 );
        }
    }

    return $title;
}
add_filter( 'wpseo_title', 'novosti_yoast_title' );

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
function novosti_get_schema_logo_url() {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        $logo = wp_get_attachment_image_url( $custom_logo_id, 'full' );
        if ( $logo ) return $logo;
    }

    $site_icon = get_site_icon_url( 512 );
    if ( $site_icon ) return $site_icon;

    return '';
}

function novosti_get_social_image_url( $post_id = 0 ) {
    if ( $post_id && has_post_thumbnail( $post_id ) ) {
        $image = get_the_post_thumbnail_url( $post_id, 'news-featured' );
        if ( $image ) return $image;
    }

    $logo_url = novosti_get_schema_logo_url();
    if ( $logo_url ) return $logo_url;

    return get_template_directory_uri() . '/screenshot.png';
}

function novosti_seo_head() {
    $site_name = get_bloginfo('name');
    $site_url  = home_url('/');

    if ( is_singular() ) {
        global $post;
        $title       = get_the_title();
        $description = novosti_get_meta_description();
        $url         = get_permalink();
        $type        = 'article';
        $image       = novosti_get_social_image_url( $post->ID );
        $pub_date    = get_the_date( 'c' );
        $mod_date    = get_the_modified_date( 'c' );
        $cats        = get_the_category();
        $cat_name    = ! empty($cats) ? $cats[0]->name : '';
        $author_id   = (int) get_the_author_meta( 'ID' );
        $author_name = get_the_author_meta( 'display_name', $author_id );
    } else {
        $title       = is_category() ? single_cat_title('', false) . ' — ' . $site_name : $site_name;
        $description = novosti_get_meta_description();
        $url         = novosti_get_canonical_url();
        $type        = 'website';
        $image       = novosti_get_social_image_url();
        $pub_date    = '';
        $mod_date    = '';
        $cat_name    = '';
        $author_id   = 0;
        $author_name = '';
    }

    $description = novosti_trim_meta_text( $description );
    $logo_url    = novosti_get_schema_logo_url();
    ?>
<?php if ( ! defined( 'WPSEO_VERSION' ) ) : ?>
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
<?php endif; ?>
<?php if ( is_singular('post') && $pub_date ) : ?>
<meta property="article:published_time" content="<?php echo esc_attr($pub_date); ?>">
<meta property="article:modified_time"  content="<?php echo esc_attr($mod_date); ?>">
<?php
$publisher = array(
    '@type' => 'Organization',
    '@id'   => home_url( '/#organization' ),
    'name'  => $site_name,
    'url'   => $site_url,
);

if ( $logo_url ) {
    $publisher['logo'] = array(
        '@type' => 'ImageObject',
        '@id'   => home_url( '/#logo' ),
        'url'   => $logo_url,
    );
}

$article_schema = array(
    '@context'            => 'https://schema.org',
    '@type'               => array( 'NewsArticle', 'Article' ),
    '@id'                 => trailingslashit( $url ) . '#newsarticle',
    'headline'            => $title,
    'description'         => $description,
    'url'                 => $url,
    'mainEntityOfPage'    => array(
        '@type' => 'WebPage',
        '@id'   => $url,
    ),
    'datePublished'       => $pub_date,
    'dateModified'        => $mod_date,
    'image'               => array(
        '@type'  => 'ImageObject',
        '@id'    => trailingslashit( $url ) . '#primaryimage',
        'url'    => $image,
        'width'  => 1200,
        'height' => 675,
    ),
    'author'              => array(
        '@type' => 'Person',
        '@id'   => $author_id ? get_author_posts_url( $author_id ) . '#author' : home_url( '/#author' ),
        'name'  => $author_name ? $author_name : $site_name,
    ),
    'publisher'           => $publisher,
    'isAccessibleForFree' => true,
    'inLanguage'          => 'ru-RU',
);

if ( $cat_name ) {
    $article_schema['articleSection'] = $cat_name;
}
?>
<script type="application/ld+json"><?php echo wp_json_encode( $article_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>
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
    $items = novosti_get_breadcrumb_items();

    echo '<nav class="breadcrumbs" aria-label="Хлебные крошки"><div class="container"><span>';

    foreach ( $items as $index => $item ) {
        if ( $index > 0 ) echo ' → ';

        if ( ! empty( $item['url'] ) && $index < count( $items ) - 1 ) {
            echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['name'] ) . '</a>';
        } else {
            echo '<span>' . esc_html( $item['name'] ) . '</span>';
        }
    }

    echo '</span></div></nav>';
}

function novosti_get_breadcrumb_items() {
    $items = array(
        array(
            'name' => 'Главная',
            'url'  => home_url( '/' ),
        ),
    );

    if ( is_category() ) {
        $items[] = array(
            'name' => single_cat_title( '', false ),
            'url'  => '',
        );
    } elseif ( is_single() ) {
        $cats = get_the_category();
        if ( $cats ) {
            $items[] = array(
                'name' => $cats[0]->name,
                'url'  => get_category_link( $cats[0]->term_id ),
            );
        }

        $items[] = array(
            'name' => get_the_title(),
            'url'  => '',
        );
    } elseif ( is_page() ) {
        $items[] = array(
            'name' => get_the_title(),
            'url'  => '',
        );
    } elseif ( is_search() ) {
        $items[] = array(
            'name' => 'Поиск: ' . get_search_query(),
            'url'  => '',
        );
    } elseif ( is_archive() ) {
        $items[] = array(
            'name' => get_the_archive_title(),
            'url'  => '',
        );
    }

    return $items;
}

function novosti_breadcrumb_schema() {
    if ( is_front_page() ) return;

    $items = array();
    foreach ( novosti_get_breadcrumb_items() as $index => $item ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $index + 1,
            'name'     => $item['name'],
            'item'     => ! empty( $item['url'] ) ? $item['url'] : novosti_get_canonical_url(),
        );
    }

    if ( count( $items ) < 2 ) return;

    echo '<script type="application/ld+json">' . wp_json_encode( array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'novosti_breadcrumb_schema', 20 );

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
        'nuremberg',
        'nuernberg',
        'nurnberg',
        'hannover',
        'hanover',
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
            'Нюрнберг',
            'Ганновер',
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

function novosti_get_category_title( $term ) {
    if ( ! ( $term instanceof WP_Term ) ) return '';

    $map = array(
        'politika'       => 'Политика Германии',
        'ekonomika'      => 'Экономика Германии',
        'energetika'     => 'Энергетика Германии',
        'nedvizhimost'   => 'Недвижимость в Германии',
        'immigratsiya'   => 'Иммиграция в Германию',
        'deutsche-bahn'  => 'Новости Deutsche Bahn',
        'burgergeld'     => 'Bürgergeld в Германии',
        'rabota'         => 'Работа в Германии',
        'obrazovanie'    => 'Образование в Германии',
        'avtomobili'     => 'Автомобили в Германии',
        'biznes'         => 'Бизнес в Германии',
        'tehnologii'     => 'Технологии в Германии',
        'obshhestvo'     => 'Общество Германии',
        'proisshestviya' => 'Происшествия в Германии',
    );

    if ( isset( $map[ $term->slug ] ) ) {
        return $map[ $term->slug ];
    }

    return $term->name . ' — новости Германии';
}

function novosti_get_category_seo_text( $term ) {
    if ( ! ( $term instanceof WP_Term ) ) return '';

    $cities = novosti_get_cities();
    if ( isset( $cities[ $term->slug ] ) ) {
        $city = novosti_get_city_name( $term->slug );
        return 'На странице собраны последние новости города ' . $city . ': транспорт, жильё, городская политика, происшествия, события, работа служб и изменения, которые важны для жителей и гостей. Раздел помогает быстро следить за локальной повесткой и находить материалы по теме в одном месте.';
    }

    $map = array(
        'politika'       => 'В рубрике «Политика» публикуются ключевые события и решения, которые формируют внутреннюю и внешнюю повестку Германии. Здесь собраны новости о правительстве, партиях, выборах, законах, заявлениях политиков и решениях, влияющих на жителей страны.',
        'ekonomika'      => 'Рубрика «Экономика» посвящена финансовой и деловой повестке Германии: рынку труда, инфляции, налогам, промышленности, бизнесу, банкам и решениям правительства. Материалы помогают следить за изменениями, которые влияют на доходы, цены и компании.',
        'energetika'     => 'В разделе «Энергетика» собраны новости о ценах на электричество и газ, переходе на возобновляемые источники, сетях, отоплении, субсидиях и решениях властей. Здесь удобно отслеживать всё, что влияет на счета домохозяйств и бизнес.',
        'nedvizhimost'   => 'Раздел «Недвижимость» рассказывает о рынке жилья в Германии: аренде, покупке квартир и домов, ипотеке, строительстве, коммунальных расходах и правах жильцов. Здесь публикуются новости, полезные арендаторам, владельцам и тем, кто планирует переезд.',
        'immigratsiya'   => 'В рубрике «Иммиграция» собраны новости о правилах въезда, ВНЖ, гражданстве, интеграции, визах, работе ведомств и изменениях миграционной политики Германии. Раздел помогает отслеживать решения, важные для иностранцев и новых жителей страны.',
        'deutsche-bahn'  => 'Новости Deutsche Bahn: изменения расписания, задержки поездов, забастовки, ремонтные работы, новые маршруты, билеты, S-Bahn, региональный транспорт и инфраструктура. Раздел полезен всем, кто регулярно пользуется поездками по Германии.',
        'burgergeld'     => 'Раздел Bürgergeld посвящён социальным выплатам в Германии: решениям Jobcenter, правилам получения помощи, санкциям, реформам, размерам выплат и практическим изменениям для получателей пособий.',
        'rabota'         => 'В рубрике «Работа» публикуются новости о рынке труда Германии, зарплатах, вакансиях, условиях занятости, правах работников, дефиците кадров и изменениях для работодателей и сотрудников.',
        'obrazovanie'    => 'Раздел «Образование» собирает новости о школах, университетах, детских садах, профессиональном обучении, экзаменах, цифровизации и реформах образовательной системы Германии.',
        'avtomobili'     => 'В рубрике «Автомобили» собраны новости о правилах дорожного движения, штрафах, техосмотре, электромобилях, налогах, страховании, парковке и изменениях для водителей в Германии.',
        'biznes'         => 'Раздел «Бизнес» освещает новости компаний, предпринимательства, инвестиций, банкротств, регулирования, торговли и решений, которые влияют на деловую среду Германии.',
        'tehnologii'     => 'В рубрике «Технологии» публикуются новости о цифровизации, искусственном интеллекте, стартапах, IT-компаниях, кибербезопасности, связи и технологической политике Германии.',
        'obshhestvo'     => 'Раздел «Общество» собирает материалы о повседневной жизни в Германии: социальных изменениях, семье, здоровье, потребителях, городских инициативах, культуре и важных общественных дискуссиях.',
        'proisshestviya' => 'В рубрике «Происшествия» публикуются новости о чрезвычайных ситуациях, расследованиях, работе полиции и спасательных служб, авариях, пожарах и других событиях в Германии.',
    );

    if ( isset( $map[ $term->slug ] ) ) {
        return $map[ $term->slug ];
    }

    return 'Последние новости по теме «' . $term->name . '»: актуальные события Германии, важные изменения, свежие публикации и полезная информация на русском языке. Раздел регулярно обновляется и помогает быстро найти материалы по выбранной теме.';
}

function novosti_render_category_seo_intro( $term ) {
    if ( ! ( $term instanceof WP_Term ) ) return;
    if ( max( 1, (int) get_query_var( 'paged' ) ) > 1 ) return;

    $text = novosti_get_category_seo_text( $term );
    if ( ! $text ) return;
    ?>
    <div class="category-seo">
      <p><?php echo esc_html( $text ); ?></p>
    </div>
    <?php
}

function novosti_render_category_links( $current_term = null ) {
    $slugs = array(
        'politika',
        'ekonomika',
        'deutsche-bahn',
        'nedvizhimost',
        'immigratsiya',
        'burgergeld',
        'berlin',
        'hamburg',
        'munich',
        'cologne',
        'frankfurt',
        'duesseldorf',
    );

    $links = array();
    foreach ( $slugs as $slug ) {
        $term = get_category_by_slug( $slug );
        if ( ! $term ) continue;
        if ( $current_term instanceof WP_Term && (int) $current_term->term_id === (int) $term->term_id ) continue;

        $links[] = $term;
    }

    if ( ! $links ) return;
    ?>
    <nav class="category-links" aria-label="Связанные разделы">
      <span class="category-links__label">Связанные разделы:</span>
      <?php foreach ( $links as $term ) : ?>
        <a href="<?php echo esc_url( get_category_link( $term->term_id ) ); ?>">
          <?php echo esc_html( $term->name ); ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <?php
}

function novosti_get_related_posts( $post_id, $count = 6 ) {
    $cat_ids = wp_get_post_categories( $post_id );
    if ( ! $cat_ids ) return array();

    return get_posts( array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $count,
        'category__in'        => $cat_ids,
        'category__not_in'    => novosti_get_special_category_ids(),
        'post__not_in'        => array( $post_id ),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ) );
}

function novosti_get_more_from_primary_category( $post_id, $count = 4, $exclude_ids = array() ) {
    $cats = get_the_category( $post_id );
    if ( ! $cats ) return array();

    $exclude_ids[] = $post_id;

    return get_posts( array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $count,
        'category__in'        => array( $cats[0]->term_id ),
        'category__not_in'    => novosti_get_special_category_ids(),
        'post__not_in'        => array_values( array_unique( array_map( 'intval', $exclude_ids ) ) ),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ) );
}

function novosti_render_link_list( $title, $posts, $class = '' ) {
    if ( ! $posts ) return;
    ?>
    <section class="internal-links <?php echo esc_attr( $class ); ?>">
      <h2 class="internal-links__title"><?php echo esc_html( $title ); ?></h2>
      <ul class="internal-links__list">
        <?php foreach ( $posts as $item ) : ?>
          <li>
            <a href="<?php echo esc_url( get_permalink( $item->ID ) ); ?>">
              <?php echo esc_html( get_the_title( $item->ID ) ); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php
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
add_filter( 'the_content', function( $content ) {
    if ( ! is_singular( 'post' ) ) return $content;

    return preg_replace( '#<script[^>]+type=["\']application/ld\+json["\'][^>]*>.*?</script>#is', '', $content );
}, 6 );

add_filter('the_content', function($content){
    return preg_replace(
        '/<img(?![^>]*onerror)([^>]*)>/i',
        '<img$1 onerror="this.closest(\'.single-post__thumb\') ? this.closest(\'.single-post__thumb\').style.display=\'none\' : this.style.display=\'none\';">',
        $content
    );
});

add_filter( 'the_content', function( $content ) {
    if ( ! is_singular( 'post' ) ) return $content;

    return preg_replace(
        array( '/<h3(\s[^>]*)?>/i', '/<\/h3>/i' ),
        array( '<h2$1>', '</h2>' ),
        $content
    );
}, 8 );

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

function novosti_home_pagination_query_filter( $query ) {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) return;
    if ( (int) $query->get( 'paged' ) < 2 ) return;

    $exclude = novosti_get_special_category_ids();
    if ( $exclude ) {
        $query->set( 'category__not_in', $exclude );
    }
}
add_action( 'pre_get_posts', 'novosti_home_pagination_query_filter' );

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
