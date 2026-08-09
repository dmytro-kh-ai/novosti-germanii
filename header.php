<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html,body{background:#f0f2f5;min-height:100%;-webkit-text-size-adjust:100%;text-size-adjust:100%}
    body{font-family:'PT Sans',Arial,sans-serif;font-size:15px;color:#222;line-height:1.6}
    a{color:inherit;text-decoration:none}
    .screen-reader-text{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}

    .site-wrapper{max-width:1100px;margin:0 auto;background:#fff;box-shadow:0 0 40px rgba(0,0,0,.15);min-height:100vh}
    .top-bar{background:#fff;border-bottom:1px solid #e0e0e0;padding:5px 16px;display:flex;align-items:center;justify-content:space-between}
    .top-bar__date{font-size:12px;color:#666}
    .top-bar__time{background:#cc0000;color:#fff;font-size:12px;font-weight:700;padding:2px 8px;border-radius:3px}

    .site-header{background:#fff;border-bottom:1px solid #e0e0e0;padding:18px 16px 14px}
    .site-header__brand{display:flex;align-items:center;justify-content:center;gap:18px}
    .site-header__text{display:flex;flex-direction:column;align-items:flex-start;text-align:left}
    .site-header__logo-text{display:block;font-family:'PT Serif',Georgia,serif;font-size:26px;font-weight:700;color:#1a1a2e}
    .site-header__tagline{font-size:13px;color:#cc0000;margin-top:3px}

    .site-nav{background:#fff;border-bottom:3px solid #cc0000;position:sticky;top:0;z-index:100;box-shadow:0 2px 8px rgba(0,0,0,.08)}
    .site-nav__inner{display:flex;align-items:center;padding:0 16px;position:relative}

    .site-nav__menu,
    .site-nav__menu ul,
    .site-nav__menu li{
      list-style:none!important;
      list-style-type:none!important;
      margin:0!important;
      padding:0!important;
    }

    .site-nav__menu li::before,
    .site-nav__menu li::marker{
      content:none!important;
      display:none!important;
    }

    .site-nav__menu{
      display:flex!important;
      align-items:center;
      flex-wrap:wrap;
      flex:1;
    }

    .site-nav__menu li{display:block}

    .site-nav__menu a{
      display:block;
      padding:10px 9px;
      font-size:13px;
      color:#333;
      border-bottom:3px solid transparent;
      margin-bottom:-3px;
      white-space:nowrap;
    }

    .site-nav__menu a:hover,
    .site-nav__menu .current-menu-item > a{
      color:#cc0000;
      border-bottom-color:#cc0000;
    }

    .site-nav__actions{display:flex;align-items:center;gap:8px;padding:4px 0}
    .site-nav__search{background:none;border:none;cursor:pointer;font-size:16px;color:#666;padding:4px;min-width:44px;min-height:44px;display:inline-flex;align-items:center;justify-content:center}
    .site-nav__bell{background:#cc0000;color:#fff;border:none;border-radius:4px;padding:5px 10px;font-size:14px;cursor:pointer;min-width:44px;min-height:44px;display:inline-flex;align-items:center;justify-content:center}
    .site-nav__social{border:none;border-radius:4px;color:#fff;min-width:44px;min-height:44px;display:inline-flex;align-items:center;justify-content:center;transition:opacity .15s,transform .15s}
    .site-nav__social:hover{color:#fff;opacity:.9;transform:translateY(-1px)}
    .site-nav__social svg{width:20px;height:20px;display:block;fill:currentColor}
    .site-nav__social--telegram{background:#229ed9}
    .site-nav__social--facebook{background:#1877f2}

    .site-nav__burger{display:none;background:none;border:none;cursor:pointer;padding:8px;flex-direction:column;gap:5px;min-width:44px;min-height:44px;align-items:center;justify-content:center}
    .site-nav__burger span{display:block;width:22px;height:2px;background:#333;border-radius:2px}

    @media(max-width:600px){
      body{font-size:16px}
      .site-nav__burger{display:flex}

      .site-nav__menu{
        display:none!important;
        position:absolute;
        top:100%;
        left:0;
        right:0;
        background:#fff;
        border-bottom:2px solid #cc0000;
        box-shadow:0 4px 12px rgba(0,0,0,.12);
        flex-direction:column;
        align-items:flex-start;
        z-index:200;
      }

      .site-nav__menu.is-open{display:flex!important}

      .site-nav__menu li{
        width:100%;
      }

      .site-nav__menu a{
        width:100%;
        padding:12px 16px;
        border-bottom:1px solid #e0e0e0;
        font-size:16px;
        margin-bottom:0;
        min-height:44px;
        display:flex;
        align-items:center;
      }

      .site-header__brand{justify-content:center;gap:12px}
      .site-header__text{min-width:0}
      .site-header__logo-text{font-size:20px;line-height:1.18}
      .site-header__tagline{font-size:12px;line-height:1.35}
      .site-header__logo img{max-height:56px}
    }
  </style>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="site-wrapper">

<div class="top-bar">
  <span class="top-bar__date" id="js-date"></span>
  <span class="top-bar__time" id="js-time"></span>
</div>

<header class="site-header">
  <div class="site-header__brand">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-header__logo" aria-label="<?php echo esc_attr( get_bloginfo('name') ); ?>">
      <?php
      if ( has_custom_logo() ) {
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        echo wp_get_attachment_image(
          $custom_logo_id,
          'full',
          false,
          array(
            'class' => 'custom-logo',
            'alt'   => get_bloginfo( 'name' ),
          )
        );
      }
      ?>
    </a>

    <div class="site-header__text">
    <span class="site-header__logo-text"><?php bloginfo('name'); ?></span>

    <?php if (get_bloginfo('description')) : ?>
      <p class="site-header__tagline"><?php bloginfo('description'); ?></p>
    <?php endif; ?>
    </div>
  </div>
</header>

<nav class="site-nav">
  <div class="site-nav__inner">

    <button class="site-nav__burger" id="js-burger" aria-label="Меню">
      <span></span><span></span><span></span>
    </button>

    <?php
    wp_nav_menu(array(
      'theme_location' => 'primary',
      'container'      => false,
      'menu_class'     => 'site-nav__menu',
      'menu_id'        => 'js-menu',
      'fallback_cb'    => 'novosti_render_primary_menu_fallback',
    ));
    ?>

    <div class="site-nav__actions">
      <button class="site-nav__search" aria-label="Поиск">🔍</button>
      <a class="site-nav__social site-nav__social--telegram" href="https://t.me/novostigermaniide" target="_blank" rel="noopener noreferrer" aria-label="Новости Германии в Telegram">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path d="M21.94 4.16c.32-1.34-.94-2.14-2.07-1.66L2.7 9.74c-1.17.5-1.15 2.15.08 2.53l4.37 1.35 1.67 5.2c.38 1.18 1.88 1.46 2.65.5l2.38-2.96 4.46 3.27c1.03.76 2.5.2 2.8-1.05l2.83-14.42ZM8.2 12.69l9.96-6.17c.33-.2.67.25.38.5l-8.23 7.4-.32 3.55-1.2-3.74 7.18-6.45-8.77 5Z"/>
        </svg>
      </a>
      <a class="site-nav__social site-nav__social--facebook" href="https://www.facebook.com/novostigermaniide" target="_blank" rel="noopener noreferrer" aria-label="Новости Германии в Facebook">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.19 8.44 9.94v-7.03H7.9v-2.91h2.54V9.84c0-2.52 1.49-3.91 3.77-3.91 1.09 0 2.24.2 2.24.2V8.6h-1.26c-1.24 0-1.63.78-1.63 1.57v1.89h2.78l-.44 2.91h-2.34V22C18.34 21.25 22 17.08 22 12.06Z"/>
        </svg>
      </a>
      <button class="site-nav__bell" aria-label="Подписка">🔔</button>
    </div>

  </div>
</nav>
