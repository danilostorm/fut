<?php get_header(); ?>

<main style="min-height: 60vh; display: flex; align-items: center; justify-content: center; padding: 4rem 1.5rem; text-align: center;">
    <div>
        <div style="font-size: 6rem; margin-bottom: 1rem; line-height: 1;">⚽</div>
        <h1 style="font-size: 5rem; font-weight: 900; color: var(--fa-secondary); margin-bottom: 1rem;">404</h1>
        <h2 style="font-size: 1.5rem; margin-bottom: 1rem;">Lance pra fora! Essa página não existe.</h2>
        <p style="color: var(--fa-text-muted); margin-bottom: 2rem; max-width: 400px; margin-left: auto; margin-right: auto;">
            A página que você procura pode ter sido removida ou nunca existiu. Que tal voltar pro jogo?
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo home_url('/'); ?>" class="share-btn share-btn--twitter" style="display: inline-flex;">
                🏠 Voltar ao início
            </a>
            <a href="/category/futebol/" class="share-btn share-btn--whatsapp" style="display: inline-flex; background: var(--fa-dark-3);">
                ⚽ Ver futebol
            </a>
        </div>
    </div>
</main>

<?php get_footer(); ?>
