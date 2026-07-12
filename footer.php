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

<script>
(function() {
  var slides = document.querySelectorAll('.banner-carousel__slide');
  var dots   = document.querySelectorAll('.banner-carousel__dot');
  if (!slides.length) return;

  var current = 0;

  function goTo(n) {
    slides[current].classList.remove('is-active');
    dots[current] && dots[current].classList.remove('is-active');
    current = n % slides.length;
    slides[current].classList.add('is-active');
    dots[current] && dots[current].classList.add('is-active');
  }

  dots.forEach(function(dot) {
    dot.addEventListener('click', function() {
      goTo(parseInt(this.dataset.index));
    });
  });

  setInterval(function() { goTo(current + 1); }, 10000);
})();
</script>

</body>
</html>
