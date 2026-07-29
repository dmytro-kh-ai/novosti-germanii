<?php get_header(); ?>

<?php novosti_breadcrumbs(); ?>

<main class="site-main">
<div class="container">

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<article class="single-post page-content">
  <h1 class="single-post__title"><?php the_title(); ?></h1>

  <div class="single-post__content">
    <?php the_content(); ?>
  </div>
</article>

<?php endwhile; endif; ?>

</div>
</main>

<?php get_footer(); ?>
