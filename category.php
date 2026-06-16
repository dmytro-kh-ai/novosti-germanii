<?php
/**
 * Шаблон категории.
 * Для городских категорий — полноценная «главная страница города».
 * Для остальных — стандартный архив.
 */

get_header();
novosti_breadcrumbs();

if ( novosti_is_city_category() ) :

    $city_obj  = get_queried_object();
    $city_slug = $city_obj->slug;
    $city_ru   = novosti_get_city_name( $city_slug );
    $city_link = get_category_link( $city_obj->term_id );
    $paged     = max( 1, (int) get_query_var( 'paged' ) );

?>

<main class="site-main city-page">
<div class="container">

  <!-- H1 города — на всех страницах -->
  <div class="city-hero">
    <h1 class="city-hero__title">Новости <?php echo esc_html( novosti_city_genitive( $city_slug ) ); ?></h1>
    <p class="city-hero__sub">Последние новости и события — <?php echo esc_html( $city_ru ); ?></p>
  </div>

  <?php if ( $paged <= 1 ) :
    $ad_banner   = novosti_get_ad_banner();
    $city_afisha = novosti_get_city_afisha( $city_slug, 3 );
    $today_news  = novosti_get_city_latest_news( $city_slug, 6 );
    $yest_news   = novosti_get_city_yesterday_news( $city_slug, 3 );
    $today_ids   = wp_list_pluck( $today_news, 'ID' );
    $yest_ids    = wp_list_pluck( $yest_news,  'ID' );
  ?>

  <!-- РЕКЛАМА + АФИША -->
  <div class="ad-layout">

    <div class="ad-block">
      <div class="ad-block__label">Реклама</div>
      <div class="ad-block__banner">
        <?php if ( $ad_banner ) : ?>
          <div class="banner-carousel">
            <?php foreach ( $ad_banner as $i => $ad_post ) : ?>
              <div class="banner-carousel__slide <?php echo $i === 0 ? 'is-active' : ''; ?>">
                <?php if ( has_post_thumbnail( $ad_post->ID ) ) : ?>
                  <a href="https://khursenko.agency" target="_blank" rel="nofollow noopener">
                    <?php echo get_the_post_thumbnail(
                      $ad_post->ID,
                      'medium_large',
                      array( 'style' => 'width:100%;max-width:700px;height:auto;display:block;margin:0 auto;border-radius:4px;' )
                    ); ?>
                  </a>
                <?php else : ?>
                  <a href="https://khursenko.agency" target="_blank" rel="nofollow noopener"
                     style="display:block;width:100%;text-align:center;padding:20px;color:#999;font-size:13px;">
                    <?php echo esc_html( $ad_post->post_title ); ?>
                  </a>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
            <?php if ( count( $ad_banner ) > 1 ) : ?>
              <div class="banner-carousel__dots">
                <?php foreach ( $ad_banner as $i => $ad_post ) : ?>
                  <span class="banner-carousel__dot <?php echo $i === 0 ? 'is-active' : ''; ?>"
                        data-index="<?php echo $i; ?>"></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php else : ?>
          <div style="text-align:center;color:#bbb;font-size:12px;padding:20px;">
            <div style="font-size:28px;margin-bottom:6px;">&#x1F5BC;</div>
            Рекламный баннер<br>
            <span style="font-size:10px;">Добавьте запись в категорию «reklama»</span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="afisha-block">
      <div class="afisha-block__header">
        <span class="afisha-block__title">&#x1F4C5; Афиша событий</span>
        <span class="afisha-block__city"><?php echo esc_html( $city_ru ); ?></span>
      </div>

      <?php if ( $city_afisha ) :
        foreach ( $city_afisha as $event ) :
          $event_date = get_post_meta( $event->ID, '_event_date', true );
          $event_time = get_post_meta( $event->ID, '_event_time', true );
          $event_city = get_post_meta( $event->ID, '_event_city', true );
          $day   = $event_date
            ? date( 'd', strtotime( $event_date ) )
            : get_the_date( 'd', $event->ID );
          $month = $event_date
            ? mb_strtoupper( date( 'M', strtotime( $event_date ) ) )
            : mb_strtoupper( get_the_date( 'M', $event->ID ) );
          echo '<div class="afisha-event">';
          echo '<div class="afisha-event__date">'
             . '<div class="afisha-event__day">'   . esc_html( $day )   . '</div>'
             . '<div class="afisha-event__month">' . esc_html( $month ) . '</div>'
             . '</div>';
          echo '<div class="afisha-event__info">';
          echo '<div class="afisha-event__name"><a href="' . esc_url( get_permalink( $event->ID ) ) . '">'
             . esc_html( $event->post_title ) . '</a></div>';
          echo '<div class="afisha-event__meta">';
          if ( $event_time ) echo esc_html( $event_time );
          if ( $event_city ) echo ' · ' . esc_html( $event_city );
          echo '</div></div></div>';
        endforeach;
      else :
        $afisha_cat = get_category_by_slug( 'afisha' );
        echo '<div class="afisha-event">'
           . '<div class="afisha-event__date"><div class="afisha-event__day">—</div></div>'
           . '<div class="afisha-event__info"><div class="afisha-event__name" style="color:#bbb;font-size:12px;">'
           . 'Нет событий. Назначьте посту категории «afisha» + «' . esc_html( $city_slug ) . '»'
           . '</div></div></div>';
      endif;

      $afisha_cat = get_category_by_slug( 'afisha' );
      $afisha_url = $afisha_cat ? get_category_link( $afisha_cat->term_id ) : '#';
      ?>

      <div class="afisha-block__footer">
        <a href="<?php echo esc_url( $afisha_url ); ?>">Все события &rarr;</a>
      </div>
    </div>

  </div><!-- /.ad-layout -->

  <!-- СЕГОДНЯ -->
  <?php if ( $today_news ) :
    $today_label = 'Сегодня · ' . wp_date( 'j F' );
  ?>
  <div class="section-wrap">
    <div class="section-head">
      <span class="section-head__title"><?php echo esc_html( $today_label ); ?></span>
      <a class="section-head__link" href="<?php echo esc_url( $city_link ); ?>">Все новости &rarr;</a>
    </div>
    <hr class="section-divider">
    <div class="news-grid">
      <?php foreach ( $today_news as $post ) :
        setup_postdata( $post );
        $cats = get_the_category( $post->ID );
        $cat  = $cats ? $cats[0] : null;
      ?>
      <article class="news-card">
        <div class="news-card__thumb">
          <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
            <?php if ( has_post_thumbnail( $post->ID ) ) :
              echo get_the_post_thumbnail( $post->ID, 'news-card', array(
                'onerror' => "this.style.display='none';this.closest('.news-card__thumb').classList.add('is-empty');"
              ) );
            else : ?>
              <div style="width:100%;height:100%;background:#e8e8e8;"></div>
            <?php endif; ?>
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
            <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
              <?php echo esc_html( get_the_title( $post->ID ) ); ?>
            </a>
          </h2>
          <div class="news-card__time"><?php echo novosti_time_ago( $post->ID ); ?></div>
        </div>
      </article>
      <?php endforeach; wp_reset_postdata(); ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ВЧЕРА -->
  <?php if ( $yest_news ) :
    $yest_label = 'Вчера · ' . wp_date( 'j F', strtotime( '-1 day' ) );
  ?>
  <div class="section-wrap">
    <div class="section-head">
      <span class="section-head__title"><?php echo esc_html( $yest_label ); ?></span>
    </div>
    <hr class="section-divider">
    <div class="news-list">
      <?php foreach ( $yest_news as $post ) :
        $cats = get_the_category( $post->ID );
        $cat  = $cats ? $cats[0] : null;
      ?>
      <div class="news-list-item">
        <div class="news-list-item__thumb">
          <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
            <?php if ( has_post_thumbnail( $post->ID ) ) :
              echo get_the_post_thumbnail( $post->ID, 'news-list' );
            else : ?>
              <div style="width:100%;height:100%;background:#e8e8e8;"></div>
            <?php endif; ?>
          </a>
        </div>
        <div>
          <?php if ( $cat ) : ?>
            <div class="news-list-item__cat">
              <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>">
                <?php echo esc_html( $cat->name ); ?>
              </a>
            </div>
          <?php endif; ?>
          <h3 class="news-list-item__title">
            <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
              <?php echo esc_html( get_the_title( $post->ID ) ); ?>
            </a>
          </h3>
          <div class="news-list-item__time"><?php echo novosti_time_ago( $post->ID ); ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php endif; /* end: $paged <= 1 */ ?>

  <!-- ВСЕ НОВОСТИ (paginated WP main loop, reklama/afisha/partner уже исключены через pre_get_posts) -->
  <div class="section-wrap">
    <div class="section-head">
      <span class="section-head__title">
        <?php echo $paged > 1
          ? 'Новости ' . esc_html( novosti_city_genitive( $city_slug ) ) . ' — стр. ' . $paged
          : 'Архив новостей — ' . esc_html( $city_ru );
        ?>
      </span>
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
      <p style="color:#888;padding:20px 0;">
        Новостей ещё нет. Назначьте посту категорию «<?php echo esc_html( $city_slug ); ?>».
      </p>
    <?php endif; ?>
  </div>

</div>
</main>

<?php

else : /* ====== Обычная категория — стандартный вид ====== */

?>

<main class="site-main">
<div class="container">

<div class="section-wrap">
  <div class="section-head">
    <span class="section-head__title">
      <?php
      if ( is_category() )  echo single_cat_title( '', false );
      elseif ( is_tag() )   echo 'Тег: ' . single_tag_title( '', false );
      elseif ( is_date() )  echo get_the_date( 'j F Y' );
      else                  the_archive_title();
      ?>
    </span>
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

<?php endif; ?>

<?php get_footer(); ?>
