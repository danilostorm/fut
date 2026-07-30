<?php
/**
 * Fut Arena — Functions & Features
 * SEO Optimized | Core Web Vitals | Schema.org | Performance
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

// ============================================
// THEME SETUP
// ============================================
function futarena_setup() {
    // Title tag support
    add_theme_support('title-tag');

    // Featured images
    add_theme_support('post-thumbnails');
    add_image_size('fut_hero', 1280, 720, true);
    add_image_size('fut_card', 640, 360, true);
    add_image_size('fut_thumb', 320, 180, true);

    // RSS feeds
    add_theme_support('automatic-feed-links');

    // HTML5 support
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    // Custom logo
    add_theme_support('custom-logo', [
        'height' => 50,
        'width' => 200,
        'flex-height' => true,
        'flex-width' => true,
    ]);

    // Gutenberg - disable blocks we don't need
    add_theme_support('editor-styles');

    // Register menus
    register_nav_menus([
        'primary'   => __('Menu Principal', 'fut-arena'),
        'footer'    => __('Menu Footer', 'fut-arena'),
        'mobile'    => __('Menu Mobile', 'fut-arena'),
    ]);

    // Custom background
    add_theme_support('custom-background');

    // Block patterns
    add_theme_support('block-templates');
}
add_action('after_setup_theme', 'futarena_setup');

// ============================================
// ENQUEUE STYLES & SCRIPTS — Performance
// ============================================
function futarena_scripts() {
    $version = wp_get_theme()->get('Version');

    // Main stylesheet
    wp_enqueue_style('futarena-style', get_stylesheet_uri(), [], $version);

    // Google Fonts — preconnect for speed
    wp_enqueue_style('futarena-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700&display=swap', [], null);

    // Preconnect to font server
    add_action('wp_head', function() {
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    }, 1);

    // Lazy loading + main script
    wp_enqueue_script('futarena-main', get_template_directory_uri() . '/assets/js/main.js', ['jquery'], $version, true);

    // Comment reply
    if (is_singular() && comments_open()) {
        wp_enqueue_script('comment-reply');
    }

    // Pass PHP data to JS
    wp_localize_script('futarena-main', 'futarenaData', [
        'ajaxUrl'  => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('futarena_nonce'),
        'homeUrl'  => home_url('/'),
    ]);
}
add_action('wp_enqueue_scripts', 'futarena_scripts');

// ============================================
// CRITICAL CSS — Inline for Core Web Vitals
// ============================================
function futarena_critical_css() {
    if (!is_singular()) return;
    echo '<style id="futarena-critical" media="print" onload="this.media=\'all\'">
        body{background:#0a0e17;color:#e8eaf6;font-family:Inter,sans-serif;margin:0}
        .site-header{background:#131b2e;border-bottom:1px solid #2a3555;position:sticky;top:0;z-index:1000}
        .site-container{max-width:1280px;margin:0 auto;padding:0 1.5rem}
    </style>';
}
add_action('wp_head', 'futarena_critical_css', 1);

// ============================================
// SEO — Title Tag
// ============================================
function futarena_seo_title($title) {
    if (is_front_page()) {
        return get_bloginfo('name') . ' — ' . get_bloginfo('description');
    }
    if (is_singular()) {
        return get_the_title() . ' — ' . get_bloginfo('name');
    }
    if (is_category()) {
        return single_cat_title('', false) . ' — ' . get_bloginfo('name');
    }
    if (is_tag()) {
        return single_tag_title('', false) . ' — ' . get_bloginfo('name');
    }
    if (is_search()) {
        return 'Busca: ' . get_search_query() . ' — ' . get_bloginfo('name');
    }
    if (is_archive()) {
        return wp_get_document_title();
    }
    return $title;
}
add_filter('pre_get_document_title', 'futarena_seo_title');

// ============================================
// SEO — Meta Description (Open Graph)
// ============================================
function futarena_meta_tags() {
    if (!is_singular()) return;

    global $post;
    $description = get_the_excerpt($post) ?: wp_trim_words($post->post_content, 25);
    $thumb = get_the_post_thumbnail_url($post, 'fut_card');
    $title = get_the_title();
    $url = get_permalink();

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";

    // Open Graph
    echo '<meta property="og:type" content="article">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    echo '<meta property="og:locale" content="pt_BR">' . "\n";
    if ($thumb) {
        echo '<meta property="og:image" content="' . esc_url($thumb) . '">' . "\n";
        echo '<meta property="og:image:width" content="640"><meta property="og:image:height" content="360">' . "\n";
    }

    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    if ($thumb) echo '<meta name="twitter:image" content="' . esc_url($thumb) . '">' . "\n";

    // Article meta
    $author = get_the_author();
    $date = get_the_date('c');
    $modified = get_the_modified_date('c');
    echo '<meta property="article:author" content="' . esc_attr($author) . '">' . "\n";
    echo '<meta property="article:published_time" content="' . esc_attr($date) . '">' . "\n";
    echo '<meta property="article:modified_time" content="' . esc_attr($modified) . '">' . "\n";

    // Category
    $cats = get_the_category();
    if ($cats) {
        echo '<meta property="article:section" content="' . esc_attr($cats[0]->name) . '">' . "\n";
    }
}
add_action('wp_head', 'futarena_meta_tags', 2);

// ============================================
// SCHEMA.ORG — Structured Data (JSON-LD)
// ============================================
function futarena_schema() {
    if (!is_singular()) return;

    global $post;
    $thumb = get_the_post_thumbnail_url($post, 'fut_card');
    $author = get_the_author_meta('display_name');
    $author_url = get_author_posts_url(get_the_author_meta('ID'));
    $date = get_the_date('c');
    $modified = get_the_modified_date('c');
    $cats = get_the_category();
    $primary_cat = $cats[0]->name ?? 'Esportes';

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'      => 'NewsArticle',
        'headline'   => get_the_title(),
        'description' => get_the_excerpt($post) ?: wp_trim_words($post->post_content, 25),
        'image'      => $thumb ?: '',
        'author'     => [
            '@type' => 'Person',
            'name'  => $author,
            'url'   => $author_url,
        ],
        'publisher'  => [
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => get_template_directory_uri() . '/assets/img/logo.png',
            ],
        ],
        'datePublished' => $date,
        'dateModified'  => $modified,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id'   => get_permalink(),
        ],
        'articleSection' => $primary_cat,
        'timeRequired'   => 'PT5M',
        'keywords'        => implode(', ', wp_get_post_tags($post->ID, ['fields' => 'names'])),
    ];

    // Breadcrumb Schema
    $breadcrumb_schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $primary_cat, 'item' => get_category_link($cats[0]->term_id ?? 0)],
            ['@type' => 'ListItem', 'position' => 3, 'name' => get_the_title(), 'item' => get_permalink()],
        ],
    ];

    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    echo '<script type="application/ld+json">' . json_encode($breadcrumb_schema, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'futarena_schema', 3);

// ============================================
// SCHEMA.ORG — WebSite + Organization
// ============================================
function futarena_schema_website() {
    if (!is_front_page()) return;

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'WebSite',
        'name'        => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url'         => home_url('/'),
        'inLanguage'  => 'pt-BR',
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => home_url('/?s={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ];

    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'futarena_schema_website', 1);

// ============================================
// LAZY LOADING — Native + Fallback
// ============================================
function futarena_lazy_load($content) {
    // Add loading="lazy" to images without it
    $content = preg_replace('/<img(?![^>]*loading=["\'])(.*?)>/i', '<img loading="lazy"$1>', $content);
    // Add decoding="async"
    $content = preg_replace('/<img(.*?)>/i', '<img decoding="async"$1>', $content);
    return $content;
}
add_filter('the_content', 'futarena_lazy_load');
add_filter('post_thumbnail_html', 'futarena_lazy_load');

// ============================================
// LAZY LOAD — Featured Image
// ============================================
function futarena_featured_image_attrs($attr, $attachment, $size) {
    $attr['loading'] = 'eager'; // LCP image — eager load
    $attr['fetchpriority'] = 'high';
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'futarena_featured_image_attrs', 10, 3);

// ============================================
// REMOVE BLOAT — Emoji, embeds, etc.
// ============================================
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');
remove_action('rest_api_init', 'wp_oembed_register_route');
remove_filter('oembed_datamode', 'wp_oembed_datamode');

add_filter('embed_oembed_discover', '__return_false');
add_filter('tiny_mce_plugins', function($plugins) {
    return array_diff($plugins, ['wpemoji']);
});

// Disable WordPress embeds — but keep responsive
function futarena_disable_embeds() {
    wp_deregister_script('wp-embed');
}
add_action('wp_footer', 'futarena_disable_embeds');

// ============================================
// PRELOAD CRITICAL ASSETS
// ============================================
function futarena_preload() {
    // Preload main stylesheet
    $style = get_stylesheet_uri();
    echo '<link rel="preload" href="' . esc_url($style) . '" as="style">' . "\n";

    // Preload fonts
    echo '<link rel="preload" href="https://fonts.gstatic.com/s/inter/v18/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa2JL7W1Q5n-wU.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
}
add_action('wp_head', 'futarena_preload', 0);

// ============================================
// EXCERPT — Custom length
// ============================================
function futarena_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'futarena_excerpt_length');

function futarena_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'futarena_excerpt_more');

// ============================================
// READING TIME
// ============================================
function futarena_reading_time($post_id = null) {
    $post = get_post($post_id ?: get_the_ID());
    $content = strip_tags($post->post_content);
    $word_count = str_word_count($content);
    $minutes = ceil($word_count / 200);
    return $minutes . ' min de leitura';
}
add_shortcode('reading_time', function() {
    return '<span class="reading-time">' . futarena_reading_time() . '</span>';
});

// ============================================
// BREADCRUMBS — SEO Friendly
// ============================================
function futarena_breadcrumbs() {
    if (!is_singular()) return;

    $cats = get_the_category();
    $cat = $cats[0] ?? null;

    echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
    echo '<ol itemscope itemtype="https://schema.org/BreadcrumbList">';

    echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
    echo '<a itemprop="item" href="' . esc_url(home_url('/')) . '">';
    echo '<span itemprop="name">Home</span></a>';
    echo '<meta itemprop="position" content="1"></li>';

    if ($cat) {
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a itemprop="item" href="' . esc_url(get_category_link($cat->term_id)) . '">';
        echo '<span itemprop="name">' . esc_html($cat->name) . '</span></a>';
        echo '<meta itemprop="position" content="2"></li>';
    }

    echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
    echo '<span itemprop="name">' . get_the_title() . '</span>';
    echo '<meta itemprop="position" content="3"></li>';

    echo '</ol></nav>';
}

// ============================================
// RELATED POSTS — SEO Internal Links
// ============================================
function futarena_related_posts($post_id, $count = 3) {
    $cats = wp_get_post_categories($post_id);
    if (empty($cats)) return [];

    $args = [
        'post_type'      => 'post',
        'posts_per_page' => $count,
        'post__not_in'   => [$post_id],
        'category__in'   => $cats,
        'orderby'       => 'rand',
    ];

    return get_posts($args);
}

// ============================================
// SITEMAP — Dynamic XML
// ============================================
function futarena_xml_sitemap() {
    // Hook into WordPress sitemap
}
add_action('init', function() {
    // WordPress 5.5+ has built-in sitemap
});

// ============================================
// SECURITY — Headers
// ============================================
function futarena_security_headers() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }
}
add_action('send_headers', 'futarena_security_headers');

// ============================================
// PERFORMANCE — Cache headers
// ============================================
function futarena_cache_headers() {
    if (!is_admin() && !is_user_logged_in()) {
        header('Cache-Control: public, max-age=86400, stale-while-revalidate=3600');
        header('Vary: Accept-Encoding');
    }
}
add_action('send_headers', 'futarena_cache_headers');

// ============================================
// REGISTER WIDGET AREAS
// ============================================
function futarena_widgets() {
    register_sidebar([
        'name'          => __('Sidebar Principal', 'fut-arena'),
        'id'            => 'sidebar-main',
        'description'   => __('Widgets da sidebar principal', 'fut-arena'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget__title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Footer Coluna 1', 'fut-arena'),
        'id'            => 'footer-1',
        'before_widget' => '',
        'after_widget'  => '',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ]);

    register_sidebar([
        'name'          => __('Footer Coluna 2', 'fut-arena'),
        'id'            => 'footer-2',
        'before_widget' => '',
        'after_widget'  => '',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ]);

    register_sidebar([
        'name'          => __('Footer Coluna 3', 'fut-arena'),
        'id'            => 'footer-3',
        'before_widget' => '',
        'after_widget'  => '',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ]);
}
add_action('widgets_init', 'futarena_widgets');

// ============================================
// CUSTOM POST TYPES — Times e Jogadores
// ============================================
function futarena_custom_post_types() {
    // Times
    register_post_type('time', [
        'labels'       => ['name' => 'Times', 'singular_name' => 'Time'],
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-groups',
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
        'rewrite'      => ['slug' => 'times'],
        'show_in_graphql' => true,
    ]);

    // Jogadores
    register_post_type('jogador', [
        'labels'       => ['name' => 'Jogadores', 'singular_name' => 'Jogador'],
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-admin-users',
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
        'rewrite'      => ['slug' => 'jogadores'],
    ]);
}
add_action('init', 'futarena_custom_post_types');

// ============================================
// CUSTOM TAXONOMIES
// ============================================
function futarena_taxonomies() {
    // Campeonato
    register_taxonomy('campeonato', 'post', [
        'labels'       => ['name' => 'Campeonatos'],
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'campeonato'],
    ]);

    // Liga
    register_taxonomy('liga', 'post', [
        'labels'       => ['name' => 'Ligas'],
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'liga'],
    ]);
}
add_action('init', 'futarena_taxonomies');

// ============================================
// OPTIMIZE — Remove query strings
// ============================================
function futarena_remove_query_strings($src) {
    if (strpos($src, 'ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
add_filter('style_loader_src', 'futarena_remove_query_strings', 9999);
add_filter('script_loader_src', 'futarena_remove_query_strings', 9999);

// ============================================
// PREVENT HEARTBEAT — Save server resources
// ============================================
function futarena_heartbeat_settings($settings) {
    $settings['interval'] = 60; // 1 minute instead of 15 seconds
    return $settings;
}
add_filter('heartbeat_settings', 'futarena_heartbeat_settings');

// ============================================
// DISABLE ADMIN BAR for non-admins
// ============================================
add_filter('show_admin_bar', function($show) {
    return current_user_can('administrator') ? $show : false;
});
