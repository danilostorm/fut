<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('sidebar-main')): ?>
<div class="widget">
    <h3 class="widget__title">📰 Últimas Notícias</h3>
    <?php
    $latest = get_posts(['posts_per_page' => 5]);
    if ($latest): ?>
    <ul class="trending-list">
        <?php foreach ($latest as $i => $post): setup_postdata($post); ?>
        <li>
            <span class="trending-list__num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></span>
            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        </li>
        <?php endforeach; wp_reset_postdata(); ?>
    </ul>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('sidebar-main-2')): ?>
<div class="widget">
    <h3 class="widget__title">📧 Newsletter</h3>
    <p style="font-size:0.85rem; color:var(--fa-text-muted); margin-bottom:1rem;">
        Receba as melhores análises diretamente no seu e-mail.
    </p>
    <form class="newsletter-form">
        <input type="email" placeholder="seu@email.com.br" required>
        <button type="submit">Inscrever</button>
    </form>
</div>
<?php endif; ?>

<div class="widget">
    <h3 class="widget__title">🏆 Campeonatos</h3>
    <ul style="list-style:none; padding:0;">
        <?php
        $categories = get_categories(['hide_empty' => true, 'number' => 8]);
        foreach ($categories as $cat): ?>
        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--fa-border);">
            <a href="<?php echo get_category_link($cat->term_id); ?>"
               style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:0.9rem;"><?php echo esc_html($cat->name); ?></span>
                <span style="font-size:0.75rem; color:var(--fa-text-muted);"><?php echo $cat->count; ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="widget">
    <h3 class="widget__title">🔥 Mais Lidas</h3>
    <?php
    $popular = get_posts([
        'posts_per_page' => 5,
        'orderby' => 'comment_count',
        'order' => 'DESC',
    ]);
    if ($popular): ?>
    <ul class="trending-list">
        <?php foreach ($popular as $i => $post): setup_postdata($post); ?>
        <li>
            <span class="trending-list__num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></span>
            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        </li>
        <?php endforeach; wp_reset_postdata(); ?>
    </ul>
    <?php endif; ?>
</div>
