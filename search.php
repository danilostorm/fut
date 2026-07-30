<?php get_header(); ?>

<main class="site-container" style="padding: 3rem 1.5rem; max-width: 800px;">
    <header class="archive-header">
        <p class="archive-header__label">Busca</p>
        <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">
            Resultados para: "<?php echo get_search_query(); ?>"
        </h1>
        <p style="color: var(--fa-text-muted); font-size: 0.9rem;">
            <?php global $wp_query; echo $wp_query->found_posts; ?> resultados encontrados
        </p>
    </header>

    <?php if (have_posts()): ?>
        <div class="posts-grid" style="grid-template-columns: 1fr;">
            <?php while (have_posts()): the_post(); ?>
            <article class="post-card" style="display: grid; grid-template-columns: 200px 1fr;">
                <a href="<?php the_permalink(); ?>" class="post-card__img-wrap" style="aspect-ratio:16/9; border-radius: var(--fa-radius-lg) 0 0 var(--fa-radius-lg);">
                    <?php if (has_post_thumbnail()): ?>
                        <img src="<?php the_post_thumbnail_url('fut_thumb'); ?>" alt="<?php the_title(); ?>"
                             class="post-card__img" loading="lazy" decoding="async">
                    <?php endif; ?>
                </a>
                <div class="post-card__content">
                    <?php $cats = get_the_category(); if ($cats): ?>
                        <span class="post-card__category"><?php echo esc_html($cats[0]->name); ?></span>
                    <?php endif; ?>
                    <h3 class="post-card__title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    <p style="font-size:0.9rem; color:var(--fa-text-muted); margin-bottom:0.5rem;">
                        <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                    </p>
                    <div class="post-card__meta">
                        <span><?php the_date('d M Y'); ?></span>
                    </div>
                </div>
            </article>
            <?php endwhile; ?>
        </div>

        <div class="pagination">
            <?php the_posts_pagination([
                'mid_size' => 2,
                'prev_text' => '&larr; Anterior',
                'next_text' => 'Próxima &rarr;',
            ]); ?>
        </div>
    <?php else: ?>
        <div style="text-align:center; padding: 4rem 0;">
            <div style="font-size:4rem; margin-bottom:1rem;">🔍</div>
            <h2 style="font-size:1.5rem; margin-bottom:1rem;">Nenhum resultado encontrado</h2>
            <p style="color:var(--fa-text-muted); margin-bottom:2rem;">
                Tente outros termos de busca ou navegue pelas categorias.
            </p>
            <a href="<?php echo home_url('/'); ?>" class="share-btn share-btn--twitter" style="display:inline-flex;">
                Voltar ao início
            </a>
        </div>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
