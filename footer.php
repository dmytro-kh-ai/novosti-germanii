<footer class="site-footer">
  <div class="site-footer__inner">
    <span class="site-footer__copy">© <?php echo date('Y'); ?> <?php bloginfo('name'); ?></span>
    <nav class="site-footer__menu">
      <?php wp_nav_menu(array(
        'theme_location' => 'footer',
        'container'      => false,
        'items_wrap'     => '%3$s',
        'fallback_cb'    => function() {
          $pages = array(
            array('url'=>home_url('/usloviya/'),        'label'=>'Условия'),
            array('url'=>home_url('/sotrudnichestvo/'), 'label'=>'Сотрудничество'),
            array('url'=>home_url('/impressum/'),       'label'=>'Impressum'),
            array('url'=>home_url('/datenschutz/'),     'label'=>'Datenschutz'),
          );
          foreach ($pages as $p)
            echo '<a href="'.esc_url($p['url']).'">'.esc_html($p['label']).'</a>';
        },
      )); ?>
    </nav>
  </div>
</footer>

</div><!-- .site-wrapper -->

<?php wp_footer(); ?>

</body>
</html>
