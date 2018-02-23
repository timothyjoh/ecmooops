<?php

if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli' && 'lando' !== $_ENV['PANTHEON_ENVIRONMENT'] ) {
  // Redirect to https://$primary_domain/ in the Live environment
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
    /** Replace www.example.com with your registered domain name */
    $primary_domain = 'learn.innovativeecmo.com';
  }
  else {
    // Redirect to HTTPS on every Pantheon environment.
    $primary_domain = $_SERVER['HTTP_HOST'];
  }
  $base_url = 'https://'. $primary_domain;
  define('WP_SITEURL', $base_url);
  define('WP_HOME', $base_url);
  if ($_SERVER['HTTP_HOST'] != $primary_domain
      || !isset($_SERVER['HTTP_X_SSL'])
      || $_SERVER['HTTP_X_SSL'] != 'ON' ) {

    # Name transaction "redirect" in New Relic for improved reporting (optoinal)
    if (extension_loaded('newrelic')) {
      newrelic_name_transaction("redirect");
    }

    header('HTTP/1.0 301 Moved Permanently');
    header('Location: '. $base_url . $_SERVER['REQUEST_URI']);
    exit();
  }
}
