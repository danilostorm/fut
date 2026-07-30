<?php get_header(); ?>

<?php if (have_posts()): the_post(); ?>

<?php
// Hero — primeiro post sticky ou mais recente
$hero_post = get_posts(['posts_per_page' => 1, 'post__in' => get_option('sticky_posts'), 'ignore_sticky_posts' => 1]);
if (!$hero_post) $hero_post = get_posts(['posts_per_page' => 1]);
$hero_post = $hero_post[0] ?? null;
$sidebar_posts = get_posts(['posts_per_page' => 3, 'offset' => 1, 'post__not_in' => [$hero_post->ID ?? 0]]);
?>

<!-- HERO SECTION -->
<section class="hero">
    <div class="site-container">
        <div class="hero__grid">
            <?php if ($hero_post): ?>
            <a href="<?php echo get_permalink($hero_post); ?>" class="hero__featured">
                <img
                    src="<?php echo get_the_post_thumbnail_url($hero_post, 'fut_hero'); ?>"
                    alt="<?php echo esc_attr(get_the_title($hero_post)); ?>"
                    class="hero__featured-img"
                    loading="eager"
                    fetchpriority="high"
                >
                <div class="hero__featured-overlay">
                    <?php
                    $cats = get_the_category($hero_post->ID);
                    if ($cats): ?>
                        <span class="hero__badge"><?php echo esc_html($cats[0]->name); ?></span>
                    <?php endif; ?>
                    <h2 class="hero__title"><?php echo get_the_title($hero_post); ?></h2>
                    <p class="hero__meta">
                        <?php echo get_the_date('d \d\e F \d\e Y', $hero_post); ?>
                        &bull; <?php echo futarena_reading_time($hero_post->ID); ?>
                    </p>
                </div>
            </a>
            <?php endif; ?>

            <div class="hero__sidebar">
                <?php foreach ($sidebar_posts as $sp): ?>
                <a href="<?php echo get_permalink($sp); ?>" class="hero__sidebar-item">
                    <img
                        src="<?php echo get_the_post_thumbnail_url($sp, 'fut_thumb'); ?>"
                        alt="<?php echo esc_attr(get_the_title($sp)); ?>"
                        class="hero__sidebar-img"
                        loading="lazy"
                        decoding="async"
                        width="100"
                        height="70"
                    >
                    <div class="hero__sidebar-content">
                        <?php
                        $sp_cats = get_the_category($sp->ID);
                        if ($sp_cats): ?>
                            <span style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--fa-primary);"><?php echo esc_html($sp_cats[0]->name); ?></span>
                        <?php endif; ?>
                        <h3><?php echo get_the_title($sp); ?></h3>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Posts por categoria
$sections = [
    ['label' => '🔥 Principais', 'category' => 0, 'count' => 6],
    ['label' => '⚽ Futebol', 'category' => 'futebol', 'count' => 3],
    ['label' => '🏀 Basquete', 'category' => 'basquete', 'count' => 3],
    ['label' => '🎾 Tênis', 'category' => 'tenis', 'count' => 3],
];
?>

<?php foreach ($sections as $section):
    $args = ['posts_per_page' => $section['count']];
    if ($section['category']) {
        $args['category_name'] = $section['category'];
    }
    $posts = get_posts($args);
    if (!$posts) continue;
?>

<section style="padding: var(--fa-space-2xl) 0;">
    <div class="site-container">
        <div class="section-header">
            <div class="section-header__title">
                <span class="section-header__icon">📢</span>
                <?php echo esc_html($section['label']); ?>
            </div>
            <a href="<?php echo $section['category'] ? get_category_link(get_category_by_slug($section['category'])) : home_url('/'); ?>" class="section-header__link">
                Ver todos &rarr;
            </a>
        </div>

        <div class="posts-grid">
            <?php foreach ($posts as $p):
                $p_cats = get_the_category($p->ID);
                $p_cat = $p_cats[0]->name ?? '';
            ?>
            <article class="post-card">
                <a href="<?php echo get_permalink($p); ?>" class="post-card__img-wrap">
                    <img
                        src="<?php echo get_the_post_thumbnail_url($p, 'fut_card'); ?>"
                        alt="<?php echo esc_attr(get_the_title($p)); ?>"
                        class="post-card__img"
                        loading="lazy"
                        decoding="async"
                        width="640"
                        height="360"
                    >
                    <?php if ($p_cat): ?>
                        <span class="post-card__badge"><?php echo esc_html($p_cat); ?></span>
                    <?php endif; ?>
                </a>
                <div class="post-card__content">
                    <?php if ($p_cat): ?>
                        <span class="post-card__category"><?php echo esc_html($p_cat); ?></span>
                    <?php endif; ?>
                    <h3 class="post-card__title">
                        <a href="<?php echo get_permalink($p); ?>"><?php echo get_the_title($p); ?></a>
                    </h3>
                    <div class="post-card__meta">
                        <span><?php echo get_the_date('d M', $p); ?></span>
                        <span>&bull;</span>
                        <span><?php echo futarena_reading_time($p->ID); ?></span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php endforeach; ?>

<!-- NEWSLETTER CTA -->
<section style="padding: var(--fa-space-2xl) 0;">
    <div class="site-container">
        <div style="background: linear-gradient(135deg, var(--fa-dark-2) 0%, var(--fa-dark-3) 100%); border: 1px solid var(--fa-border); border-radius: var(--fa-radius-xl); padding: var(--fa-space-2xl); text-align: center;">
            <h2 style="font-size: 1.75rem; margin-bottom: var(--fa-space-md);">📬 Receba as novidades antes de todo mundo</h2>
            <p style="color: var(--fa-text-muted); margin-bottom: var(--fa-space-xl); max-width: 500px; margin-left: auto; margin-right: auto;">
                Cadastre seu e-mail e receba as principais notícias de esportes, análises e bastidores diretamente na sua caixa de entrada.
            </p>
            <form class="newsletter-form" style="max-width: 450px; margin: 0 auto; flex-direction: row; flex-wrap: wrap; justify-content: center; gap: var(--fa-space-sm);">
                <input type="email" placeholder="seu@email.com.br" required style="flex: 1; min-width: 250px;">
                <button type="submit">Quero me inscrever!</button>
            </form>
        </div>
    </div>
</section>

<?php endif; ?>
<?php get_footer(); ?>
