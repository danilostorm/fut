<?php get_header(); ?>

<?php if (have_posts()): while (have_posts()): the_post(); ?>

<?php
$cats = get_the_category();
$primary_cat = $cats[0] ?? null;
$thumb = get_the_post_thumbnail_url(get_the_ID(), 'fut_hero');
?>

<!-- Reading Progress Bar -->
<div class="reading-progress" id="reading-progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>

<article class="single-post" itemscope itemtype="https://schema.org/NewsArticle">

    <!-- HEADER -->
    <div class="site-container">
        <header class="single-post__header">
            <?php if ($primary_cat): ?>
                <a href="<?php echo get_category_link($primary_cat->term_id); ?>" class="single-post__category">
                    <?php echo esc_html($primary_cat->name); ?>
                </a>
            <?php endif; ?>

            <h1 class="single-post__title" itemprop="headline"><?php the_title(); ?></h1>

            <div class="single-post__meta">
                <div class="single-post__meta-author" itemprop="author" itemscope itemtype="https://schema.org/Person">
                    <?php echo get_avatar(get_the_author_meta('ID'), 36); ?>
                    <span itemprop="name"><?php the_author(); ?></span>
                </div>
                <time datetime="<?php the_date('c'); ?>" itemprop="datePublished">
                    <?php the_date('d \d\e F \d\e Y \à\s H:i'); ?>
                </time>
                <span><?php echo futarena_reading_time(); ?></span>

                <!-- Share buttons -->
                <div style="margin-left: auto; display: flex; gap: 0.5rem;">
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>"
                       class="share-btn share-btn--twitter" target="_blank" rel="noopener" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                        𝕏
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>"
                       class="share-btn share-btn--whatsapp" target="_blank" rel="noopener" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                        💬
                    </a>
                    <a href="https://t.me/share/url?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>"
                       class="share-btn share-btn--telegram" target="_blank" rel="noopener" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                        ✈️
                    </a>
                </div>
            </div>
        </header>
    </div>

    <!-- FEATURED IMAGE -->
    <?php if (has_post_thumbnail()): ?>
    <div class="site-container">
        <img
            src="<?php echo esc_url($thumb); ?>"
            alt="<?php echo esc_attr(get_the_title()); ?>"
            class="single-post__featured-img"
            itemprop="image"
            loading="eager"
            fetchpriority="high"
            width="1280"
            height="720"
        >
    </div>
    <?php endif; ?>

    <!-- CONTENT -->
    <div class="site-container">
        <div style="display: grid; grid-template-columns: 1fr 300px; gap: 3rem; align-items: start;">

            <!-- Main Content -->
            <div>
                <!-- Breadcrumbs -->
                <nav class="breadcrumbs" style="margin-bottom: 2rem; font-size: 0.8rem; color: var(--fa-text-muted);" aria-label="Breadcrumb">
                    <a href="<?php echo home_url('/'); ?>" style="color: var(--fa-primary);">Home</a>
                    <?php if ($primary_cat): ?>
                        &rsaquo; <a href="<?php echo get_category_link($primary_cat->term_id); ?>" style="color: var(--fa-primary);"><?php echo esc_html($primary_cat->name); ?></a>
                    <?php endif; ?>
                    &rsaquo; <span style="color: var(--fa-text-muted);"><?php the_title(); ?></span>
                </nav>

                <div class="single-post__content" itemprop="articleBody">
                    <?php the_content(); ?>
                </div>

                <!-- Tags -->
                <?php
                $tags = get_the_tags();
                if ($tags): ?>
                <div style="margin-top: 2rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <?php foreach ($tags as $tag): ?>
                        <a href="<?php echo get_tag_link($tag->term_id); ?>"
                           style="background: var(--fa-dark-3); color: var(--fa-text-muted); padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; border: 1px solid var(--fa-border); transition: all 0.2s;">
                            #<?php echo esc_html($tag->name); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Share buttons -->
                <div class="single-post__share" style="margin-top: 2rem;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--fa-text-muted); align-self: center;">
                        📤 Compartilhe:
                    </span>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>"
                       class="share-btn share-btn--twitter" target="_blank" rel="noopener">
                        𝕏 Twitter
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>"
                       class="share-btn share-btn--whatsapp" target="_blank" rel="noopener">
                        💬 WhatsApp
                    </a>
                    <a href="https://t.me/share/url?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>"
                       class="share-btn share-btn--telegram" target="_blank" rel="noopener">
                        ✈️ Telegram
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>"
                       class="share-btn share-btn--facebook" target="_blank" rel="noopener">
                        📘 Facebook
                    </a>
                </div>

                <!-- Related Posts -->
                <?php
                $related = futarena_related_posts(get_the_ID(), 3);
                if ($related): ?>
                <div style="margin-top: 3rem;">
                    <div class="section-header">
                        <div class="section-header__title">
                            <span class="section-header__icon">🔗</span>
                            Relacionadas
                        </div>
                    </div>
                    <div class="posts-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <?php foreach ($related as $r): ?>
                        <article class="post-card">
                            <a href="<?php echo get_permalink($r); ?>" class="post-card__img-wrap">
                                <img src="<?php echo get_the_post_thumbnail_url($r, 'fut_card'); ?>"
                                     alt="<?php echo esc_attr(get_the_title($r)); ?>"
                                     class="post-card__img"
                                     loading="lazy" decoding="async"
                                     width="640" height="360">
                            </a>
                            <div class="post-card__content">
                                <h3 class="post-card__title">
                                    <a href="<?php echo get_permalink($r); ?>"><?php echo get_the_title($r); ?></a>
                                </h3>
                                <div class="post-card__meta">
                                    <span><?php echo get_the_date('d M', $r); ?></span>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Comments -->
                <?php if (comments_open() || get_comments_number()): ?>
                <div style="margin-top: 3rem;">
                    <div class="section-header">
                        <div class="section-header__title">
                            <span class="section-header__icon">💬</span>
                            Comentários
                        </div>
                    </div>
                    <?php comments_template(); ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="sidebar" style="position: sticky; top: 80px;">
                <div class="widget">
                    <h3 class="widget__title">📰 Últimas</h3>
                    <?php
                    $latest = get_posts(['posts_per_page' => 5, 'post__not_in' => [get_the_ID()]]);
                    if ($latest): ?>
                    <ul class="trending-list">
                        <?php foreach ($latest as $l): ?>
                        <li>
                            <span class="trending-list__num"><?php echo str_pad(array_search($l, $latest) + 1, 2, '0', STR_PAD_LEFT); ?></span>
                            <h4><a href="<?php echo get_permalink($l); ?>"><?php echo get_the_title($l); ?></a></h4>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <div class="widget">
                    <h3 class="widget__title">📧 Newsletter</h3>
                    <p style="font-size: 0.85rem; color: var(--fa-text-muted); margin-bottom: 1rem;">
                        Receba as melhores análises diretamente no seu e-mail.
                    </p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="seu@email.com.br" required>
                        <button type="submit">Inscrever</button>
                    </form>
                </div>
            </aside>

        </div>
    </div>

</article>

<script>
// Reading progress bar
document.addEventListener('DOMContentLoaded', function() {
    const bar = document.getElementById('reading-progress');
    if (!bar) return;
    const article = document.querySelector('.single-post__content');
    if (!article) return;

    window.addEventListener('scroll', function() {
        const rect = article.getBoundingClientRect();
        const total = article.offsetHeight;
        const scrolled = Math.max(0, -rect.top);
        const progress = Math.min(100, (scrolled / total) * 100);
        bar.style.width = progress + '%';
        bar.setAttribute('aria-valuenow', Math.round(progress));
    });
});
</script>

<?php endwhile; endif; ?>
<?php get_footer(); ?>
