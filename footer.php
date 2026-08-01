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
            array('url'=>home_url('/o-proekte/'),       'label'=>'О проекте'),
            array('url'=>home_url('/redaktsiya/'),      'label'=>'Редакция'),
            array('url'=>home_url('/authors/'),         'label'=>'Авторы'),
            array('url'=>home_url('/kontakty/'),        'label'=>'Контакты'),
            array('url'=>home_url('/istochniki/'),      'label'=>'Источники'),
            array('url'=>home_url('/usloviya/'),        'label'=>'Условия'),
            array('url'=>home_url('/sotrudnichestvo/'), 'label'=>'Сотрудничество'),
            array('url'=>home_url('/cookies/'),         'label'=>'Cookies'),
            array('url'=>home_url('/impressum/'),       'label'=>'Impressum'),
            array('url'=>home_url('/datenschutz/'),     'label'=>'Datenschutz'),
          );
          foreach ($pages as $p)
            echo '<a href="'.esc_url($p['url']).'">'.esc_html($p['label']).'</a>';
        },
      )); ?>
    </nav>
    <nav class="site-footer__menu site-footer__trust" aria-label="Информация о проекте">
      <a href="<?php echo esc_url( home_url('/o-proekte/') ); ?>">О проекте</a>
      <a href="<?php echo esc_url( home_url('/redaktsiya/') ); ?>">Редакция</a>
      <a href="<?php echo esc_url( home_url('/authors/') ); ?>">Авторы</a>
      <a href="<?php echo esc_url( home_url('/kontakty/') ); ?>">Контакты</a>
      <a href="<?php echo esc_url( home_url('/istochniki/') ); ?>">Источники</a>
      <a href="<?php echo esc_url( home_url('/cookies/') ); ?>">Cookies</a>
    </nav>
  </div>
</footer>

</div><!-- .site-wrapper -->

<?php wp_footer(); ?>

</body>
</html>
