<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ===== ENQUEUE =====
function novosti_enqueue() {
    wp_enqueue_style(
        'novosti-style',
        get_stylesheet_uri(),
        array(),
        '1.6'
    );

    wp_enqueue_script(
        'novosti-js',
        get_template_directory_uri() . '/js/main.js',
        array(),
        '1.6',
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
    $attachment_title = get_the_title( $attachment->ID );
    if ( empty( $attr['alt'] ) && $attachment_title ) {
        $attr['alt'] = $attachment_title;
    }
    if ( empty( $attr['title'] ) && $attachment_title ) {
        $attr['title'] = $attachment_title;
    }

    $attr['loading']  = 'lazy';
    $attr['decoding'] = 'async';

    if ( is_singular( 'post' ) && $size === 'news-featured' ) {
        $attr['loading'] = 'eager';
        $attr['fetchpriority'] = 'high';
    }

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

// ===== SEO: META, OPEN GRAPH, SCHEMA =====
function novosti_json( $data ) {
    return wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}

function novosti_meta_description_from_text( $text, $fallback = '' ) {
    $text = wp_strip_all_tags( strip_shortcodes( (string) $text ) );
    $text = preg_replace( '/\s+/u', ' ', trim( $text ) );
    if ( ! $text ) $text = $fallback;
    return mb_strimwidth( $text, 0, 158, '...' );
}

function novosti_featured_image_data( $post_id = null ) {
    $post_id = $post_id ?: get_the_ID();
    if ( $post_id && has_post_thumbnail( $post_id ) ) {
        $thumb_id = get_post_thumbnail_id( $post_id );
        $image = wp_get_attachment_image_src( $thumb_id, 'news-featured' );
        if ( $image ) {
            return array(
                'url'    => $image[0],
                'width'  => (int) $image[1],
                'height' => (int) $image[2],
            );
        }
    }
    return array(
        'url'    => get_template_directory_uri() . '/screenshot.png',
        'width'  => 1200,
        'height' => 630,
    );
}

function novosti_current_seo_context() {
    $site_name = get_bloginfo( 'name' );
    $title = wp_get_document_title();
    $description = get_bloginfo( 'description' );
    $url = is_singular() ? get_permalink() : get_pagenum_link();
    $type = is_singular( 'post' ) ? 'article' : 'website';
    $image = novosti_featured_image_data();

    if ( is_singular() ) {
        global $post;
        $description = novosti_meta_description_from_text(
            $post->post_excerpt ?: $post->post_content,
            get_bloginfo( 'description' )
        );
    } elseif ( is_category() ) {
        $term = get_queried_object();
        $title = single_cat_title( '', false ) . ' — ' . $site_name;
        $description = novosti_meta_description_from_text(
            term_description( $term ),
            'Свежие новости Германии по теме: ' . single_cat_title( '', false ) . '.'
        );
    } elseif ( is_search() ) {
        $title = 'Поиск: ' . get_search_query() . ' — ' . $site_name;
        $description = 'Результаты поиска по сайту ' . $site_name . '.';
    } elseif ( is_404() ) {
        $title = 'Страница не найдена — ' . $site_name;
        $description = 'Страница не найдена. Перейдите на главную страницу новостей Германии.';
    }

    $virtual = get_query_var( 'novosti_virtual' );
    if ( $virtual ) {
        $slug = get_query_var( 'novosti_slug' );
        $url = $slug ? home_url( '/topics/' . $slug . '/' ) : home_url( '/' . str_replace( '_', '-', $virtual ) . '/' );
        if ( $virtual === 'news' ) {
            $title = 'Последние новости Германии — ' . $site_name;
            $description = 'Все свежие новости Германии, Европы и мира на русском языке.';
            $url = home_url( '/news/' );
        } elseif ( $virtual === 'topics' ) {
            $title = 'Темы и города Германии — ' . $site_name;
            $description = 'Новости Германии по темам и городам: политика, экономика, миграция, работа, Берлин, Гамбург, Мюнхен и другие разделы.';
            $url = home_url( '/topics/' );
        } elseif ( $virtual === 'categories' ) {
            $title = 'Категории новостей Германии — ' . $site_name;
            $description = 'Каталог новостных категорий сайта: политика, экономика, транспорт, недвижимость, миграция и городские новости Германии.';
            $url = home_url( '/categories/' );
        } elseif ( $virtual === 'guides' ) {
            $title = 'Полезные материалы о Германии — ' . $site_name;
            $description = 'Практические материалы о жизни в Германии: гражданство, ВНЖ, пособия, Kindergeld, работа, налоги, авто, страховки и недвижимость.';
            $url = home_url( '/guides/' );
        } elseif ( $virtual === 'about' ) {
            $title = 'О проекте — ' . $site_name;
            $description = 'Информация о проекте, редакции и принципах работы сайта Новости Германии.';
            $url = home_url( '/o-proekte/' );
        } elseif ( $virtual === 'editorial_policy' ) {
            $title = 'Редакционная политика — ' . $site_name;
            $description = 'Редакционные принципы, проверка информации, работа с источниками и исправления на сайте Новости Германии.';
            $url = home_url( '/redakcionnaya-politika/' );
        } elseif ( $virtual === 'authors' ) {
            $title = 'Авторы — ' . $site_name;
            $description = 'Авторы и редакция сайта Новости Германии.';
            $url = home_url( '/avtory/' );
        } elseif ( $virtual === 'contacts' ) {
            $title = 'Контакты — ' . $site_name;
            $description = 'Контакты редакции сайта Новости Германии.';
            $url = home_url( '/kontakty/' );
        } elseif ( $virtual === 'sources_page' ) {
            $title = 'Источники — ' . $site_name;
            $description = 'Как сайт Новости Германии использует источники, официальные данные и публикации СМИ.';
            $url = home_url( '/istochniki/' );
        } elseif ( $virtual === 'topic' ) {
            $topic = novosti_get_topic_config( $slug );
            if ( $topic ) {
                $title = $topic['seo_title'] . ' — ' . $site_name;
                $description = $topic['meta_description'];
            }
        }
    }

    return compact( 'site_name', 'title', 'description', 'url', 'type', 'image' );
}

function novosti_schema_breadcrumbs() {
    if ( is_front_page() ) return null;
    $items = array(
        array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => home_url( '/' ) ),
    );
    if ( is_category() ) {
        $items[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => single_cat_title( '', false ), 'item' => get_pagenum_link() );
    } elseif ( is_single() ) {
        $cats = get_the_category();
        if ( $cats ) {
            $items[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => $cats[0]->name, 'item' => get_category_link( $cats[0]->term_id ) );
        }
        $items[] = array( '@type' => 'ListItem', 'position' => count( $items ) + 1, 'name' => get_the_title(), 'item' => get_permalink() );
    } elseif ( is_page() ) {
        $items[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => get_the_title(), 'item' => get_permalink() );
    }
    return array( '@type' => 'BreadcrumbList', '@id' => home_url( '/#breadcrumbs' ), 'itemListElement' => $items );
}

function novosti_seo_head() {
    $ctx = novosti_current_seo_context();
    $site_url = home_url( '/' );
    $org_id = home_url( '/#organization' );
    $website_id = home_url( '/#website' );
    $image = $ctx['image'];
    $graph = array(
        array(
            '@type' => 'Organization',
            '@id'   => $org_id,
            'name'  => $ctx['site_name'],
            'url'   => $site_url,
        ),
        array(
            '@type' => 'WebSite',
            '@id'   => $website_id,
            'name'  => $ctx['site_name'],
            'url'   => $site_url,
            'publisher' => array( '@id' => $org_id ),
            'potentialAction' => array(
                '@type' => 'SearchAction',
                'target' => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ),
            'inLanguage' => 'ru',
        ),
    );
    $breadcrumbs = novosti_schema_breadcrumbs();
    if ( $breadcrumbs ) $graph[] = $breadcrumbs;

    if ( is_singular( 'post' ) ) {
        $author_id = get_the_author_meta( 'ID' );
        $author_name = get_the_author_meta( 'display_name' ) ?: $ctx['site_name'];
        $author_url = $author_id ? get_author_posts_url( $author_id ) : home_url( '/o-proekte/' );
        $article_id = get_permalink() . '#article';
        $graph[] = array(
            '@type' => 'Person',
            '@id'   => $author_url . '#person',
            'name'  => $author_name,
            'url'   => $author_url,
        );
        $graph[] = array(
            '@type' => array( 'NewsArticle', 'Article' ),
            '@id'   => $article_id,
            'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => get_permalink() ),
            'headline' => get_the_title(),
            'description' => $ctx['description'],
            'url' => get_permalink(),
            'datePublished' => get_the_date( 'c' ),
            'dateModified' => get_the_modified_date( 'c' ),
            'author' => array( '@id' => $author_url . '#person' ),
            'publisher' => array( '@id' => $org_id ),
            'image' => array(
                '@type' => 'ImageObject',
                'url' => $image['url'],
                'width' => $image['width'],
                'height' => $image['height'],
            ),
            'inLanguage' => 'ru',
        );
    }
    ?>
<meta name="description" content="<?php echo esc_attr( $ctx['description'] ); ?>">
<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
<link rel="canonical" href="<?php echo esc_url( $ctx['url'] ); ?>">
<link rel="alternate" hreflang="ru" href="<?php echo esc_url( $ctx['url'] ); ?>">
<link rel="alternate" hreflang="x-default" href="<?php echo esc_url( $ctx['url'] ); ?>">
<meta property="og:type" content="<?php echo esc_attr( $ctx['type'] ); ?>">
<meta property="og:title" content="<?php echo esc_attr( $ctx['title'] ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $ctx['description'] ); ?>">
<meta property="og:url" content="<?php echo esc_url( $ctx['url'] ); ?>">
<meta property="og:site_name" content="<?php echo esc_attr( $ctx['site_name'] ); ?>">
<meta property="og:image" content="<?php echo esc_url( $image['url'] ); ?>">
<meta property="og:image:width" content="<?php echo esc_attr( $image['width'] ); ?>">
<meta property="og:image:height" content="<?php echo esc_attr( $image['height'] ); ?>">
<meta property="og:locale" content="ru_RU">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr( $ctx['title'] ); ?>">
<meta name="twitter:description" content="<?php echo esc_attr( $ctx['description'] ); ?>">
<meta name="twitter:image" content="<?php echo esc_url( $image['url'] ); ?>">
<?php if ( is_singular( 'post' ) ) : ?>
<meta property="article:published_time" content="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
<meta property="article:modified_time" content="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">
<?php endif; ?>
<script type="application/ld+json"><?php echo novosti_json( array( '@context' => 'https://schema.org', '@graph' => $graph ) ); ?></script>
    <?php
}
add_action( 'wp_head', 'novosti_seo_head', 5 );

add_filter( 'wp_robots', function( $robots ) {
    $robots['max-image-preview'] = 'large';
    $robots['max-snippet'] = -1;
    $robots['max-video-preview'] = -1;
    return $robots;
} );

add_filter( 'pre_get_document_title', function( $title ) {
    $virtual = get_query_var( 'novosti_virtual' );
    if ( ! $virtual ) return $title;

    $site_name = get_bloginfo( 'name' );
    $slug = get_query_var( 'novosti_slug' );
    if ( $virtual === 'news' ) return 'Последние новости Германии — ' . $site_name;
    if ( $virtual === 'topics' ) return 'Темы и города Германии — ' . $site_name;
    if ( $virtual === 'categories' ) return 'Категории новостей Германии — ' . $site_name;
    if ( $virtual === 'guides' ) return 'Полезные материалы о Германии — ' . $site_name;
    if ( $virtual === 'about' ) return 'О проекте — ' . $site_name;
    if ( $virtual === 'editorial_policy' ) return 'Редакционная политика — ' . $site_name;
    if ( $virtual === 'authors' ) return 'Авторы — ' . $site_name;
    if ( $virtual === 'contacts' ) return 'Контакты — ' . $site_name;
    if ( $virtual === 'sources_page' ) return 'Источники — ' . $site_name;
    if ( $virtual === 'topic' ) {
        $topic = novosti_get_topic_config( $slug );
        if ( $topic ) return $topic['seo_title'] . ' — ' . $site_name;
    }
    return $title;
} );

// ===== ROBOTS, SITEMAPS, SEO ROUTES =====
function novosti_register_seo_routes() {
    add_rewrite_rule( '^sitemap\.xml$', 'index.php?novosti_virtual=sitemap', 'top' );
    add_rewrite_rule( '^static-sitemap\.xml$', 'index.php?novosti_virtual=static_sitemap', 'top' );
    add_rewrite_rule( '^news-sitemap\.xml$', 'index.php?novosti_virtual=news_sitemap', 'top' );
    add_rewrite_rule( '^image-sitemap\.xml$', 'index.php?novosti_virtual=image_sitemap', 'top' );
    add_rewrite_rule( '^news/?$', 'index.php?novosti_virtual=news', 'top' );
    add_rewrite_rule( '^topics/?$', 'index.php?novosti_virtual=topics', 'top' );
    add_rewrite_rule( '^topics/([^/]+)/?$', 'index.php?novosti_virtual=topic&novosti_slug=$matches[1]', 'top' );
    add_rewrite_rule( '^categories/?$', 'index.php?novosti_virtual=categories', 'top' );
    add_rewrite_rule( '^guides/?$', 'index.php?novosti_virtual=guides', 'top' );
    add_rewrite_rule( '^o-proekte/?$', 'index.php?novosti_virtual=about', 'top' );
    add_rewrite_rule( '^redakcionnaya-politika/?$', 'index.php?novosti_virtual=editorial_policy', 'top' );
    add_rewrite_rule( '^avtory/?$', 'index.php?novosti_virtual=authors', 'top' );
    add_rewrite_rule( '^kontakty/?$', 'index.php?novosti_virtual=contacts', 'top' );
    add_rewrite_rule( '^istochniki/?$', 'index.php?novosti_virtual=sources_page', 'top' );

    if ( get_option( 'novosti_rewrite_version' ) !== '2026-07-11-seo-2' ) {
        flush_rewrite_rules( false );
        update_option( 'novosti_rewrite_version', '2026-07-11-seo-2' );
    }
}
add_action( 'init', 'novosti_register_seo_routes' );

add_filter( 'query_vars', function( $vars ) {
    $vars[] = 'novosti_virtual';
    $vars[] = 'novosti_slug';
    return $vars;
} );

add_filter( 'robots_txt', function( $output, $public ) {
    $lines = array(
        'User-agent: *',
        'Disallow: /wp-admin/',
        'Allow: /wp-admin/admin-ajax.php',
        '',
        'Sitemap: ' . home_url( '/sitemap.xml' ),
        'Sitemap: ' . home_url( '/static-sitemap.xml' ),
        'Sitemap: ' . home_url( '/news-sitemap.xml' ),
        'Sitemap: ' . home_url( '/image-sitemap.xml' ),
    );
    return implode( "\n", $lines ) . "\n";
}, 10, 2 );

function novosti_xml_header() {
    status_header( 200 );
    header( 'Content-Type: application/xml; charset=' . get_option( 'blog_charset' ), true );
}

function novosti_xml_escape( $value ) {
    return esc_xml( wp_strip_all_tags( (string) $value ) );
}

function novosti_output_sitemap_index() {
    novosti_xml_header();
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ( array( '/wp-sitemap.xml', '/static-sitemap.xml', '/news-sitemap.xml', '/image-sitemap.xml' ) as $path ) {
        echo '<sitemap><loc>' . esc_url( home_url( $path ) ) . '</loc><lastmod>' . esc_html( gmdate( 'c' ) ) . '</lastmod></sitemap>' . "\n";
    }
    echo '</sitemapindex>';
    exit;
}

function novosti_output_static_sitemap() {
    novosti_xml_header();
    $urls = array(
        home_url( '/' ),
        home_url( '/news/' ),
        home_url( '/topics/' ),
        home_url( '/categories/' ),
        home_url( '/guides/' ),
        home_url( '/o-proekte/' ),
        home_url( '/redakcionnaya-politika/' ),
        home_url( '/avtory/' ),
        home_url( '/kontakty/' ),
        home_url( '/istochniki/' ),
    );
    foreach ( array_keys( novosti_get_topic_configs() ) as $slug ) {
        $urls[] = home_url( '/topics/' . $slug . '/' );
    }
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ( array_unique( $urls ) as $url ) {
        echo '<url><loc>' . esc_url( $url ) . '</loc><lastmod>' . esc_html( gmdate( 'Y-m-d' ) ) . '</lastmod><changefreq>daily</changefreq><priority>0.7</priority></url>' . "\n";
    }
    echo '</urlset>';
    exit;
}

function novosti_output_news_sitemap() {
    novosti_xml_header();
    $posts = get_posts( array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 1000,
        'date_query'          => array( array( 'after' => '2 days ago' ) ),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ) );
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";
    foreach ( $posts as $post ) {
        echo '<url>';
        echo '<loc>' . esc_url( get_permalink( $post->ID ) ) . '</loc>';
        echo '<news:news>';
        echo '<news:publication><news:name>' . novosti_xml_escape( get_bloginfo( 'name' ) ) . '</news:name><news:language>ru</news:language></news:publication>';
        echo '<news:publication_date>' . esc_html( get_the_date( 'c', $post->ID ) ) . '</news:publication_date>';
        echo '<news:title>' . novosti_xml_escape( get_the_title( $post->ID ) ) . '</news:title>';
        echo '</news:news>';
        echo '</url>' . "\n";
    }
    echo '</urlset>';
    exit;
}

function novosti_output_image_sitemap() {
    novosti_xml_header();
    $posts = get_posts( array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 1000,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'meta_query'          => array( array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ) ),
    ) );
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    foreach ( $posts as $post ) {
        $image = novosti_featured_image_data( $post->ID );
        if ( empty( $image['url'] ) ) continue;
        echo '<url><loc>' . esc_url( get_permalink( $post->ID ) ) . '</loc>';
        echo '<image:image><image:loc>' . esc_url( $image['url'] ) . '</image:loc>';
        echo '<image:title>' . novosti_xml_escape( get_the_title( $post->ID ) ) . '</image:title></image:image>';
        echo '</url>' . "\n";
    }
    echo '</urlset>';
    exit;
}

function novosti_get_topic_configs() {
    return array(
        'berlin'      => array( 'type' => 'city', 'category' => 'berlin', 'name' => 'Берлин', 'genitive' => 'Берлина', 'seo_title' => 'Новости Берлина', 'meta_description' => 'Свежие новости Берлина на русском: транспорт, жильё, работа, происшествия, документы, события и жизнь города.' ),
        'hamburg'     => array( 'type' => 'city', 'category' => 'hamburg', 'name' => 'Гамбург', 'genitive' => 'Гамбурга', 'seo_title' => 'Новости Гамбурга', 'meta_description' => 'Последние новости Гамбурга на русском: городская политика, транспорт, работа, жильё, события и важные изменения.' ),
        'muenchen'    => array( 'type' => 'city', 'category' => 'munich', 'name' => 'Мюнхен', 'genitive' => 'Мюнхена', 'seo_title' => 'Новости Мюнхена', 'meta_description' => 'Новости Мюнхена и Баварии на русском языке: работа, транспорт, события, жильё, образование и городская жизнь.' ),
        'koeln'       => array( 'type' => 'city', 'category' => 'cologne', 'name' => 'Кёльн', 'genitive' => 'Кёльна', 'seo_title' => 'Новости Кёльна', 'meta_description' => 'Свежие новости Кёльна на русском: транспорт, происшествия, городские решения, жильё, работа и события.' ),
        'frankfurt'   => array( 'type' => 'city', 'category' => 'frankfurt', 'name' => 'Франкфурт-на-Майне', 'genitive' => 'Франкфурта-на-Майне', 'seo_title' => 'Новости Франкфурта-на-Майне', 'meta_description' => 'Новости Франкфурта-на-Майне на русском: финансы, транспорт, недвижимость, работа, события и городская среда.' ),
        'duesseldorf' => array( 'type' => 'city', 'category' => 'duesseldorf', 'name' => 'Дюссельдорф', 'genitive' => 'Дюссельдорфа', 'seo_title' => 'Новости Дюссельдорфа', 'meta_description' => 'Последние новости Дюссельдорфа на русском: транспорт, происшествия, работа, жильё, культура и события.' ),
        'leipzig'     => array( 'type' => 'city', 'category' => 'leipzig', 'name' => 'Лейпциг', 'genitive' => 'Лейпцига', 'seo_title' => 'Новости Лейпцига', 'meta_description' => 'Новости Лейпцига на русском: транспорт, работа, городские решения, события, жильё и жизнь в Саксонии.' ),
        'dresden'     => array( 'type' => 'city', 'category' => 'dresden', 'name' => 'Дрезден', 'genitive' => 'Дрездена', 'seo_title' => 'Новости Дрездена', 'meta_description' => 'Свежие новости Дрездена на русском: экономика, транспорт, происшествия, образование, жильё и события.' ),
        'stuttgart'   => array( 'type' => 'city', 'category' => 'stuttgart', 'name' => 'Штутгарт', 'genitive' => 'Штутгарта', 'seo_title' => 'Новости Штутгарта', 'meta_description' => 'Новости Штутгарта на русском: автомобили, работа, транспорт, бизнес, жильё и городские изменения.' ),
        'hannover'    => array( 'type' => 'city', 'category' => 'hannover', 'name' => 'Ганновер', 'genitive' => 'Ганновера', 'seo_title' => 'Новости Ганновера', 'meta_description' => 'Новости Ганновера на русском: транспорт, работа, образование, недвижимость, события и жизнь города.' ),
        'politika'    => array( 'type' => 'topic', 'category' => 'politika', 'name' => 'Политика', 'seo_title' => 'Политика Германии', 'meta_description' => 'Новости политики Германии: решения правительства, Бундестаг, партии, законы и влияние на жителей страны.' ),
        'ekonomika'   => array( 'type' => 'topic', 'category' => 'ekonomika', 'name' => 'Экономика', 'seo_title' => 'Экономика Германии', 'meta_description' => 'Экономические новости Германии: рынок труда, цены, налоги, бизнес, промышленность и финансы.' ),
        'migratsiya'  => array( 'type' => 'topic', 'category' => 'migratsiya', 'name' => 'Миграция', 'seo_title' => 'Миграция в Германии', 'meta_description' => 'Новости миграции в Германии: ВНЖ, гражданство, интеграция, ведомства, документы и правила для иностранцев.' ),
        'rabota'      => array( 'type' => 'topic', 'category' => 'trash-47', 'name' => 'Работа', 'seo_title' => 'Работа в Германии', 'meta_description' => 'Новости о работе в Германии: рынок труда, вакансии, зарплаты, права работников и изменения для мигрантов.' ),
        'nedvizhimost'=> array( 'type' => 'topic', 'category' => 'nedvizhimost', 'name' => 'Недвижимость', 'seo_title' => 'Недвижимость в Германии', 'meta_description' => 'Новости недвижимости Германии: аренда, жильё, строительство, цены, ипотека и права арендаторов.' ),
        'posobiya'    => array( 'type' => 'topic', 'category' => 'burgergeld', 'name' => 'Пособия', 'seo_title' => 'Пособия в Германии', 'meta_description' => 'Новости о пособиях в Германии: Bürgergeld, Kindergeld, социальная помощь, выплаты и изменения правил.' ),
        'proisshestviya' => array( 'type' => 'topic', 'category' => 'trash-43', 'name' => 'Происшествия', 'seo_title' => 'Происшествия в Германии', 'meta_description' => 'Происшествия в Германии: полиция, суды, аварии, городская безопасность и важные инциденты.' ),
        'avtomobili'  => array( 'type' => 'topic', 'category' => 'avtomobili', 'name' => 'Автомобили', 'seo_title' => 'Автомобили в Германии', 'meta_description' => 'Автомобильные новости Германии: дороги, правила, штрафы, электромобили, страховки и регистрация.' ),
        'obrazovanie' => array( 'type' => 'topic', 'category' => 'obrazovanie', 'name' => 'Образование', 'seo_title' => 'Образование в Германии', 'meta_description' => 'Новости образования Германии: школы, детские сады, университеты, обучение, дети и семьи.' ),
        'biznes'      => array( 'type' => 'topic', 'category' => 'biznes', 'name' => 'Бизнес', 'seo_title' => 'Бизнес в Германии', 'meta_description' => 'Новости бизнеса Германии: компании, стартапы, налоги, инвестиции, рынок и предпринимательство.' ),
        'tehnologii'  => array( 'type' => 'topic', 'category' => 'it', 'name' => 'Технологии', 'seo_title' => 'Технологии в Германии', 'meta_description' => 'Технологические новости Германии: IT, цифровизация, индустрия, стартапы, связь и инновации.' ),
        'obshhestvo'  => array( 'type' => 'topic', 'category' => 'obshhestvo', 'name' => 'Общество', 'seo_title' => 'Общество Германии', 'meta_description' => 'Новости общества Германии: жизнь людей, социальные изменения, культура, городская среда и важные события.' ),
    );
}

function novosti_get_topic_config( $slug ) {
    $topics = novosti_get_topic_configs();
    return isset( $topics[ $slug ] ) ? $topics[ $slug ] : null;
}

function novosti_find_category_for_topic( $topic ) {
    if ( empty( $topic['category'] ) ) return null;
    $term = get_category_by_slug( $topic['category'] );
    if ( ! $term && ! empty( $topic['name'] ) ) {
        $term = get_term_by( 'name', $topic['name'], 'category' );
    }
    return $term && ! is_wp_error( $term ) ? $term : null;
}

function novosti_topic_posts( $topic, $count = 6, $args = array() ) {
    $term = novosti_find_category_for_topic( $topic );
    if ( ! $term ) return array();
    return get_posts( array_merge( array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $count,
        'category__in'        => array( (int) $term->term_id ),
        'category__not_in'    => novosti_get_special_category_ids(),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ), $args ) );
}

function novosti_render_post_grid( $posts ) {
    if ( ! $posts ) return;
    echo '<div class="news-grid">';
    foreach ( $posts as $post ) {
        setup_postdata( $post );
        $cats = get_the_category( $post->ID );
        $cat = $cats ? $cats[0] : null;
        echo '<article class="news-card"><div class="news-card__thumb"><a href="' . esc_url( get_permalink( $post->ID ) ) . '">';
        if ( has_post_thumbnail( $post->ID ) ) {
            echo get_the_post_thumbnail( $post->ID, 'news-card', array(
                'onerror' => "this.style.display='none';this.closest('.news-card__thumb').classList.add('is-empty');",
            ) );
        } else {
            echo '<div style="width:100%;height:100%;background:#e8e8e8;"></div>';
        }
        echo '</a></div><div class="news-card__body">';
        if ( $cat ) {
            echo '<div class="news-card__cat"><a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a></div>';
        }
        echo '<h2 class="news-card__title"><a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . esc_html( get_the_title( $post->ID ) ) . '</a></h2>';
        echo '<div class="news-card__time">' . esc_html( novosti_time_ago( $post->ID ) ) . '</div></div></article>';
    }
    wp_reset_postdata();
    echo '</div>';
}

function novosti_get_internal_link_suggestions( $post_id, $limit = 10 ) {
    $links = array();
    $seen = array( (int) $post_id => true );
    $cat_ids = wp_get_post_categories( $post_id );
    $queries = array();

    if ( $cat_ids ) {
        $queries[] = array(
            'category__in' => $cat_ids,
            'posts_per_page' => $limit,
        );
    }
    $queries[] = array( 'posts_per_page' => $limit );

    foreach ( $queries as $query_args ) {
        $posts = get_posts( array_merge( array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'post__not_in'        => array_keys( $seen ),
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ), $query_args ) );
        foreach ( $posts as $post ) {
            if ( isset( $seen[ $post->ID ] ) ) continue;
            $seen[ $post->ID ] = true;
            $links[] = $post;
            if ( count( $links ) >= $limit ) return $links;
        }
    }

    return $links;
}

function novosti_city_seo_text( $topic ) {
    if ( $topic['type'] !== 'city' ) {
        return 'В этом разделе собраны последние материалы по теме «' . esc_html( $topic['name'] ) . '»: свежие новости, объяснения, важные изменения и практическая информация для русскоязычных читателей в Германии.';
    }
    return 'Раздел «Новости ' . esc_html( $topic['genitive'] ) . '» собирает важные события города для русскоязычных жителей Германии: транспорт, работа, жильё, документы, образование, происшествия, экономика и городская повестка. Материалы обновляются регулярно и связаны с общими новостями Германии.';
}

function novosti_render_virtual_page() {
    $virtual = get_query_var( 'novosti_virtual' );
    if ( ! $virtual ) return;

    if ( $virtual === 'sitemap' ) novosti_output_sitemap_index();
    if ( $virtual === 'static_sitemap' ) novosti_output_static_sitemap();
    if ( $virtual === 'news_sitemap' ) novosti_output_news_sitemap();
    if ( $virtual === 'image_sitemap' ) novosti_output_image_sitemap();

    status_header( 200 );
    global $wp_query;
    if ( $wp_query ) {
        $wp_query->is_404 = false;
    }

    get_header();
    echo '<main class="site-main"><div class="container">';

    if ( $virtual === 'news' ) {
        echo '<div class="section-wrap"><div class="section-head"><h1 class="section-head__title">Последние новости Германии</h1></div><hr class="section-divider">';
        novosti_render_post_grid( novosti_get_latest_news( 24 ) );
        echo '</div>';
    } elseif ( $virtual === 'topics' ) {
        echo '<div class="section-wrap"><div class="section-head"><h1 class="section-head__title">Темы и города</h1></div><hr class="section-divider"><div class="topic-link-grid">';
        foreach ( novosti_get_topic_configs() as $slug => $topic ) {
            echo '<a class="topic-link" href="' . esc_url( home_url( '/topics/' . $slug . '/' ) ) . '">' . esc_html( $topic['name'] ) . '</a>';
        }
        echo '</div></div>';
    } elseif ( $virtual === 'categories' ) {
        $categories = get_categories( array( 'hide_empty' => false, 'exclude' => novosti_get_special_category_ids() ) );
        echo '<div class="section-wrap"><div class="section-head"><h1 class="section-head__title">Категории новостей</h1></div><hr class="section-divider"><div class="topic-link-grid">';
        foreach ( $categories as $category ) {
            echo '<a class="topic-link" href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a>';
        }
        echo '</div></div>';
    } elseif ( $virtual === 'guides' ) {
        $guides = array(
            'Гражданство Германии',
            'ВНЖ в Германии',
            'Пособия',
            'Kindergeld',
            'Работа в Германии',
            'Налоги',
            'Регистрация автомобиля',
            'Страховки',
            'Недвижимость',
        );
        echo '<div class="section-wrap"><div class="section-head"><h1 class="section-head__title">Полезные материалы о Германии</h1></div><hr class="section-divider"><p class="city-hero__sub">Раздел для evergreen-материалов: документов, пособий, работы, налогов, автомобиля, страховок и недвижимости. Эти страницы должны расширяться отдельными подробными гайдами.</p><div class="topic-link-grid">';
        foreach ( $guides as $guide ) {
            echo '<span class="topic-link">' . esc_html( $guide ) . '</span>';
        }
        echo '</div></div>';
    } elseif ( in_array( $virtual, array( 'about', 'editorial_policy', 'authors', 'contacts', 'sources_page' ), true ) ) {
        $pages = array(
            'about' => array(
                'title' => 'О проекте',
                'body'  => 'Новости Германии — русскоязычный новостной сайт о Германии, городах, экономике, миграции, транспорте, недвижимости и социальной повестке. Цель проекта — быстро объяснять важные события и изменения для жителей Германии и тех, кто следит за немецкой повесткой.',
            ),
            'editorial_policy' => array(
                'title' => 'Редакционная политика',
                'body'  => 'Редакция опирается на официальные сообщения, публикации немецких и международных СМИ, данные ведомств и первоисточники. Материалы должны отделять факты от интерпретаций, указывать источники и обновляться при существенных изменениях.',
            ),
            'authors' => array(
                'title' => 'Авторы',
                'body'  => 'Материалы публикуются редакцией проекта Новости Германии. Для расширения E-E-A-T рекомендуется добавить отдельные профили авторов с биографией, темами специализации и ссылками на публикации.',
            ),
            'contacts' => array(
                'title' => 'Контакты',
                'body'  => 'По вопросам сотрудничества, исправлений и редакционных запросов используйте страницу сотрудничества или контактные данные, указанные в Impressum.',
            ),
            'sources_page' => array(
                'title' => 'Источники',
                'body'  => 'В материалах используются открытые источники: официальные сайты ведомств, пресс-релизы, статистика, сообщения городских служб и публикации СМИ. При пересказе новостей текст должен быть самостоятельным и не копировать исходные материалы дословно.',
            ),
        );
        $page = $pages[ $virtual ];
        echo '<article class="single-post"><h1 class="single-post__title">' . esc_html( $page['title'] ) . '</h1><div class="single-post__content"><p>' . esc_html( $page['body'] ) . '</p></div></article>';
    } elseif ( $virtual === 'topic' ) {
        $slug = get_query_var( 'novosti_slug' );
        $topic = novosti_get_topic_config( $slug );
        if ( ! $topic ) {
            status_header( 404 );
            echo '<div class="section-wrap"><h1 class="section-head__title">Раздел не найден</h1></div>';
        } else {
            $latest = novosti_topic_posts( $topic, 9 );
            $popular = novosti_topic_posts( $topic, 6, array( 'orderby' => array( 'comment_count' => 'DESC', 'date' => 'DESC' ) ) );
            echo '<div class="city-hero"><h1 class="city-hero__title">' . esc_html( $topic['seo_title'] ) . '</h1><p class="city-hero__sub">' . novosti_city_seo_text( $topic ) . '</p></div>';
            echo '<div class="section-wrap"><div class="section-head"><span class="section-head__title">Последние новости</span></div><hr class="section-divider">';
            novosti_render_post_grid( $latest );
            echo '</div>';
            if ( $popular ) {
                echo '<div class="section-wrap"><div class="section-head"><span class="section-head__title">Популярное по теме</span></div><hr class="section-divider">';
                novosti_render_post_grid( $popular );
                echo '</div>';
            }
            echo '<div class="section-wrap"><div class="section-head"><span class="section-head__title">Смежные разделы</span></div><hr class="section-divider"><div class="topic-link-grid">';
            $shown = 0;
            foreach ( novosti_get_topic_configs() as $related_slug => $related ) {
                if ( $related_slug === $slug || $shown >= 8 ) continue;
                echo '<a class="topic-link" href="' . esc_url( home_url( '/topics/' . $related_slug . '/' ) ) . '">' . esc_html( $related['name'] ) . '</a>';
                $shown++;
            }
            echo '</div></div>';
        }
    }

    echo '</div></main>';
    get_footer();
    exit;
}
add_action( 'template_redirect', 'novosti_render_virtual_page' );

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
        'hannover'    => 'Ганновер',
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
        'hannover'    => 'Ганновера',
    );
    return isset( $map[ $slug ] ) ? $map[ $slug ] : novosti_get_city_name( $slug );
}
