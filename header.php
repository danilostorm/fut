<?php
/**
 * Header Template
 * SEO: <title>, meta, preloads, schema
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <header class="site-header" role="banner">
        <div class="site-container">
            <div class="site-header__inner">

                <?php if (has_custom_logo()): ?>
                    <?php the_custom_logo(); ?>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" rel="home">
                        <span class="site-logo__icon">⚽</span>
                        <span><?php bloginfo('name'); ?></span>
                    </a>
                <?php endif; ?>

                <nav class="main-nav" role="navigation" aria-label="<?php esc_attr_e('Menu principal', 'fut-arena'); ?>">
                    <?php
                    // Try registered menu first, fallback to custom pages menu
                    $has_menu = has_nav_menu('primary');
                    if ($has_menu) {
                        wp_nav_menu([
                            'theme_location' => 'primary',
                            'container' => false,
                            'menu_class' => '',
                        ]);
                    } else {
                        // Fallback: show pages as menu items
                        $pages = [
                            ['title' => 'HOME', 'url' => home_url('/')],
                            ['title' => 'SOBRE', 'url' => get_permalink(get_page_by_path('sobre'))],
                            ['title' => 'CONTATO', 'url' => get_permalink(get_page_by_path('contato'))],
                            ['title' => 'POLÍTICA', 'url' => get_permalink(get_page_by_path('politica-de-privacidade-2'))],
                            ['title' => 'TERMOS', 'url' => get_permalink(get_page_by_path('termos-de-uso'))],
                        ];
                        echo '<ul class="fallback-pages-menu">';
                        foreach ($pages as $page) {
                            if ($page['url']) {
                                echo '<li><a href="' . esc_url($page['url']) . '">' . esc_html($page['title']) . '</a></li>';
                            }
                        }
                        echo '</ul>';
                    }
                    ?>
                </nav>

                <button class="menu-toggle" aria-label="Abrir menu" aria-expanded="false">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main id="main-content" role="main">
