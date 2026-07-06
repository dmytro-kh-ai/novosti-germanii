<?php get_header(); ?>

<main class="site-main">
<div class="container">

<?php
$ad_banner     = novosti_get_ad_banner();
$partner_posts = novosti_get_partner_posts(3);
$afisha        = novosti_get_afisha(3);
?>

<div class="ad-layout">

  <div class="ad-block">
    <div class="ad-block__label">Реклама</div>

    <div class="ad-block__banner">

      <?php if ( $ad_banner ) { ?>

        <div class="banner-carousel">
          <?php foreach ( $ad_banner as $index => $ad_post ) {
            $banner_url = get_post_meta($ad_post->ID, '_banner_url', true);
            if ( ! $banner_url ) $banner_url = 'https://khursenko.agency';
          ?>
            <div class="banner-carousel__slide <?php echo $index === 0 ? 'is-active' : ''; ?>">
              <?php if ( has_post_thumbnail($ad_post->ID) ) { ?>
                <a href="<?php echo esc_url($banner_url); ?>" target="_blank" rel="nofollow noopener">
                  <?php echo get_the_post_thumbnail(
                    $ad_post->ID,
                    'medium_large',
                    array('style' => 'width:100%;max-width:700px;height:auto;display:block;margin:0 auto;border-radius:4px;')
                  ); ?>
                </a>
              <?php } else { ?>
                <a href="<?php echo esc_url($banner_url); ?>" target="_blank" rel="nofollow noopener" style="display:block;width:100%;text-align:center;padding:20px;color:#999;font-size:13px;">
                  <?php echo esc_html($ad_post->post_title); ?>
                </a>
              <?php } ?>
            </div>
          <?php } ?>

          <?php if ( count($ad_banner) > 1 ) { ?>
            <div class="banner-carousel__dots">
              <?php foreach ( $ad_banner as $index => $ad_post ) { ?>
                <span class="banner-carousel__dot <?php echo $index === 0 ? 'is-active' : ''; ?>"
                      data-index="<?php echo $index; ?>"></span>
              <?php } ?>
            </div>
          <?php } ?>
        </div>

      <?php } else { ?>

        <div style="text-align:center;color:#bbb;font-size:12px;padding:20px;">
          <div style="font-size:28px;margin-bottom:6px;">&#x1F5BC;</div>
          Рекламный баннер<br>
          <span style="font-size:10px;">Добавьте запись в категорию «reklama»</span>
        </div>

      <?php } ?>

    </div>
  </div>

  <div class="afisha-block">

    <div class="afisha-block__header">
      <span class="afisha-block__title">&#x1F4C5; Афиша событий</span>
      <span class="afisha-block__city">Германия</span>
    </div>

    <?php
    if ( $afisha ) {
      foreach ( $afisha as $event ) {
        $event_date = get_post_meta($event->ID, '_event_date', true);
        $event_time = get_post_meta($event->ID, '_event_time', true);
        $event_city = get_post_meta($event->ID, '_event_city', true);

        $day   = $event_date ? date('d', strtotime($event_date)) : get_the_date('d', $event->ID);
        $month = $event_date ? mb_strtoupper(date('M', strtotime($event_date))) : mb_strtoupper(get_the_date('M', $event->ID));

        echo '<div class="afisha-event">';
        echo '<div class="afisha-event__date"><div class="afisha-event__day">' . esc_html($day) . '</div><div class="afisha-event__month">' . esc_html($month) . '</div></div>';
        echo '<div class="afisha-event__info">';
        echo '<div class="afisha-event__name"><a href="' . esc_url(get_permalink($event->ID)) . '">' . esc_html($event->post_title) . '</a></div>';
        echo '<div class="afisha-event__meta">';

        if ($event_time) echo esc_html($event_time);
        if ($event_city) echo ' · ' . esc_html($event_city);

        echo '</div></div></div>';
      }
    } else {
      echo '<div class="afisha-event"><div class="afisha-event__date"><div class="afisha-event__day">—</div></div><div class="afisha-event__info"><div class="afisha-event__name" style="color:#bbb;font-size:12px;">Добавьте записи в категорию «afisha»</div></div></div>';
    }

    $afisha_cat = get_category_by_slug('afisha');
    $afisha_url = $afisha_cat ? esc_url(get_category_link($afisha_cat->term_id)) : '#';
    ?>

    <div class="afisha-block__footer">
      <a href="<?php echo $afisha_url; ?>">Все события &rarr;</a>
    </div>
  </div>

</div>

<?php
$today_news = novosti_get_common_latest_news(6);

if ( $today_news ) :
  $today_label = 'Главные новости · ' . wp_date('j F');
?>

<div class="section-wrap">
  <div class="section-head">
    <span class="section-head__title"><?php echo esc_html($today_label); ?></span>
    <a class="section-head__link" href="<?php echo esc_url( get_year_link( wp_date('Y') ) ); ?>">Все новости &rarr;</a>
  </div>

  <hr class="section-divider">

  <div class="news-grid">
    <?php foreach ( $today_news as $post ) :
      setup_postdata($post);

      $cats = get_the_category($post->ID);
      $cat  = $cats ? $cats[0] : null;
    ?>

    <article class="news-card">
      <div class="news-card__thumb">
        <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
          <?php
          if ( has_post_thumbnail($post->ID) ) {
            echo get_the_post_thumbnail(
              $post->ID,
              'news-card',
              array(
                'onerror' => "this.style.display='none';this.closest('.news-card__thumb').classList.add('is-empty');"
              )
            );
          } else {
            echo '<div style="width:100%;height:100%;background:#e8e8e8;"></div>';
          }
          ?>
        </a>
      </div>

      <div class="news-card__body">
        <?php if ($cat) : ?>
          <div class="news-card__cat">
            <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>">
              <?php echo esc_html($cat->name); ?>
            </a>
          </div>
        <?php endif; ?>

        <h2 class="news-card__title">
          <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
            <?php echo esc_html(get_the_title($post->ID)); ?>
          </a>
        </h2>

        <div class="news-card__time">
          <?php echo novosti_time_ago($post->ID); ?>
        </div>
      </div>
    </article>

    <?php endforeach; wp_reset_postdata(); ?>
  </div>
</div>

<?php endif; ?>

<?php
$city_news = novosti_get_all_city_latest_news(6);

if ( $city_news ) :
$berlin_cat = get_category_by_slug('berlin');
$city_link  = $berlin_cat ? get_category_link($berlin_cat->term_id) : '#';
?>

<div class="section-wrap">
  <div class="section-head">
    <span class="section-head__title">Новости городов</span>
    <a class="section-head__link" href="<?php echo esc_url( $city_link ); ?>">Берлин &rarr;</a>
  </div>

  <hr class="section-divider">

  <div class="news-grid">
    <?php foreach ( $city_news as $post ) :
      setup_postdata($post);

      $cats = get_the_category($post->ID);
      $cat  = $cats ? $cats[0] : null;
    ?>

    <article class="news-card">
      <div class="news-card__thumb">
        <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
          <?php
          if ( has_post_thumbnail($post->ID) ) {
            echo get_the_post_thumbnail(
              $post->ID,
              'news-card',
              array(
                'onerror' => "this.style.display='none';this.closest('.news-card__thumb').classList.add('is-empty');"
              )
            );
          } else {
            echo '<div style="width:100%;height:100%;background:#e8e8e8;"></div>';
          }
          ?>
        </a>
      </div>

      <div class="news-card__body">
        <?php if ($cat) : ?>
          <div class="news-card__cat">
            <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>">
              <?php echo esc_html($cat->name); ?>
            </a>
          </div>
        <?php endif; ?>

        <h2 class="news-card__title">
          <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
            <?php echo esc_html(get_the_title($post->ID)); ?>
          </a>
        </h2>

        <div class="news-card__time">
          <?php echo novosti_time_ago($post->ID); ?>
        </div>
      </div>
    </article>

    <?php endforeach; wp_reset_postdata(); ?>
  </div>
</div>

<?php endif; ?>

<?php if ( $partner_posts ) : ?>

<div class="partner-block">
  <div class="partner-block__head">
    <div class="section-head__title">Партнёрский материал</div>
    <span class="partner-block__label">Реклама</span>
  </div>

  <hr class="section-divider">

  <div class="partner-grid">
    <?php foreach ( $partner_posts as $partner_post ) : ?>

    <div class="partner-card">
      <div class="partner-card__thumb">
        <?php
        if ( has_post_thumbnail($partner_post->ID) ) {
          echo get_the_post_thumbnail($partner_post->ID, 'news-card');
        } else {
          echo '<div style="width:100%;height:100%;background:#e0e0e0;"></div>';
        }
        ?>
      </div>

      <div class="partner-card__body">
        <div class="partner-card__label">На правах рекламы</div>
        <div class="partner-card__title">
          <a href="<?php echo esc_url(get_permalink($partner_post->ID)); ?>">
            <?php echo esc_html(get_the_title($partner_post->ID)); ?>
          </a>
        </div>
      </div>
    </div>

    <?php endforeach; ?>
  </div>
</div>

<?php endif; ?>

<?php
$yesterday_news = novosti_get_yesterday_news(6);

if ( $yesterday_news ) :
  $yesterday_label = 'Вчера · ' . wp_date('j F', strtotime('-1 day'));
?>

<div class="section-wrap">
  <div class="section-head">
    <span class="section-head__title"><?php echo esc_html($yesterday_label); ?></span>
    <a class="section-head__link" href="<?php echo esc_url( get_day_link( wp_date('Y', strtotime('-1 day')), wp_date('m', strtotime('-1 day')), wp_date('d', strtotime('-1 day')) ) ); ?>">Все за вчера &rarr;</a>
  </div>

  <hr class="section-divider">

  <div class="news-list">
    <?php foreach ( $yesterday_news as $post ) :
      setup_postdata($post);

      $cats = get_the_category($post->ID);
      $cat  = $cats ? $cats[0] : null;
    ?>

    <div class="news-list-item">
      <div class="news-list-item__thumb">
        <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
          <?php
          if ( has_post_thumbnail($post->ID) ) {
            echo get_the_post_thumbnail($post->ID, 'news-list');
          } else {
            echo '<div style="width:100%;height:100%;background:#e8e8e8;"></div>';
          }
          ?>
        </a>
      </div>

      <div>
        <?php if ($cat) : ?>
          <div class="news-list-item__cat">
            <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>">
              <?php echo esc_html($cat->name); ?>
            </a>
          </div>
        <?php endif; ?>

        <h3 class="news-list-item__title">
          <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
            <?php echo esc_html(get_the_title($post->ID)); ?>
          </a>
        </h3>

        <div class="news-list-item__time">
          <?php echo novosti_time_ago($post->ID); ?>
        </div>
      </div>
    </div>

    <?php endforeach; wp_reset_postdata(); ?>
  </div>
</div>

<?php endif; ?>

</div>
</main>

<?php get_footer(); ?>
