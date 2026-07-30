    </main><!-- #main-content -->

    <footer class="site-footer" role="contentinfo">
        <div class="site-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <?php if (has_custom_logo()): the_custom_logo(); else: ?>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo">
                            <span class="site-logo__icon">⚽</span>
                            <span><?php bloginfo('name'); ?></span>
                        </a>
                    <?php endif; ?>
                    <p><?php bloginfo('description'); ?></p>
                    <div style="margin-top: 1rem; display: flex; gap: 0.75rem;">
                        <a href="#" aria-label="Twitter" style="color: #1da1f2; font-size: 1.2rem;">𝕏</a>
                        <a href="#" aria-label="Instagram" style="color: #e1306c; font-size: 1.2rem;">📷</a>
                        <a href="#" aria-label="YouTube" style="color: #ff0000; font-size: 1.2rem;">▶</a>
                        <a href="#" aria-label="WhatsApp" style="color: #25d366; font-size: 1.2rem;">💬</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Categorias</h4>
                    <?php wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'menu_class' => '']); ?>
                    <ul>
                        <li><a href="/category/futebol/">Futebol</a></li>
                        <li><a href="/category/volei/">Vôlei</a></li>
                        <li><a href="/category/basquete/">Basquete</a></li>
                        <li><a href="/category/tenis/">Tênis</a></li>
                        <li><a href="/category/formula-1/">Fórmula 1</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Guias</h4>
                    <ul>
                        <li><a href="/times/">Times</a></li>
                        <li><a href="/jogadores/">Jogadores</a></li>
                        <li><a href="/campeonatos/">Campeonatos</a></li>
                        <li><a href="/ranking/">Ranking</a></li>
                        <li><a href="/estatisticas/">Estatísticas</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Sobre</h4>
                    <ul>
                        <li><a href="/sobre/">Sobre</a></li>
                        <li><a href="/contato/">Contato</a></li>
                        <li><a href="/privacidade/">Política de Privacidade</a></li>
                        <li><a href="/termos/">Termos de Uso</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Todos os direitos reservados.</p>
                <p>Feito com ❤️ no Brasil</p>
            </div>
        </div>
    </footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
