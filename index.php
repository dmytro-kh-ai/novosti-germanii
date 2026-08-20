<?php get_header(); ?>

<main class="site-main">
<div class="container">

<h1 class="screen-reader-text">Новости Германии</h1>

<?php
$paged = max( 1, (int) get_query_var( 'paged' ) );

if ( $paged > 1 ) :
?>

<div class="section-wrap">
  <div class="section-head">
    <h2 class="section-head__title">Новости Германии — страница <?php echo esc_html( $paged ); ?></h2>
  </div>

  <hr class="section-divider">

  <?php if ( have_posts() ) : ?>
    <div class="news-grid">
      <?php while ( have_posts() ) : the_post();
        $cats = get_the_category();
        $cat  = $cats ? $cats[0] : null;
      ?>

      <article class="news-card">
        <div class="news-card__thumb is-empty">
          <a href="<?php the_permalink(); ?>">
            <?php if ( has_post_thumbnail() ) :
              the_post_thumbnail( 'news-card', array(
                'onerror' => "this.style.display='none';this.closest('.news-card__thumb').classList.add('is-empty');"
              ) );
            endif; ?>
          </a>
        </div>

        <div class="news-card__body">
          <?php if ( $cat ) : ?>
            <div class="news-card__cat">
              <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>">
                <?php echo esc_html( $cat->name ); ?>
              </a>
            </div>
          <?php endif; ?>

          <h2 class="news-card__title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          </h2>

          <div class="news-card__time"><?php echo novosti_time_ago(); ?></div>
        </div>
      </article>

      <?php endwhile; ?>
    </div>

    <div style="margin-top:20px;">
      <?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?>
    </div>
  <?php else : ?>
    <p style="color:#888;padding:20px 0;">Записи не найдены.</p>
  <?php endif; ?>
</div>

</div>
</main>

<?php get_footer(); return; ?>

<?php endif; ?>

<?php
$ad_banner     = novosti_get_ad_banner();
$partner_posts = novosti_get_partner_posts(3);
$afisha        = novosti_get_afisha(3);
?>

<div class="ad-layout <?php echo $ad_banner ? '' : 'ad-layout--single'; ?>">

  <?php if ( $ad_banner ) : ?>
  <div class="ad-block">
    <div class="ad-block__label">Реклама</div>

    <div class="ad-block__banner">
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
                    array(
                      'style'         => 'width:100%;max-width:700px;height:auto;display:block;margin:0 auto;border-radius:4px;',
                      'loading'       => $index === 0 ? 'eager' : 'lazy',
                      'fetchpriority' => $index === 0 ? 'high' : 'auto',
                    )
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
    </div>
  </div>
  <?php endif; ?>

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
$life_news = novosti_get_life_latest_articles(6);

if ( $life_news ) :
$life_tag = get_term_by( 'slug', 'ng-blog', 'post_tag' );
$life_link = $life_tag && ! is_wp_error( $life_tag ) ? get_tag_link( $life_tag->term_id ) : '';
?>

<div class="section-wrap">
  <div class="section-head">
    <span class="section-head__title">Жизнь в Германии</span>
    <?php if ( $life_link ) : ?>
      <a class="section-head__link" href="<?php echo esc_url( $life_link ); ?>">Все материалы &rarr;</a>
    <?php endif; ?>
  </div>

  <hr class="section-divider">

  <div class="news-grid">
    <?php foreach ( $life_news as $post ) :
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

<div class="section-wrap section-wrap--clusters">
  <div class="section-head">
    <span class="section-head__title">Разделы сайта</span>
  </div>

  <hr class="section-divider">

  <?php novosti_render_site_structure_links(); ?>
</div>

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
