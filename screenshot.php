<?php
/**
 * Template Name: Screenshot
 * Placeholder para captura de tela do tema
 */
get_header();
?>

<main style="padding: 4rem; text-align: center;">
    <h1>Fut Arena Theme Screenshot</h1>
    <p>Esta página serve como referência visual do tema.</p>
    <img src="<?php echo get_template_directory_uri(); ?>/screenshot.png" alt="Fut Arena Theme Preview" style="max-width: 100%; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.5);">
</main>

<?php get_footer(); ?>
