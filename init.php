<?php
namespace Combine;

use FR3D\LdapBundle\Security\Authentication\LdapAuthenticationProvider;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

// autoloader
require_once __DIR__.'/vendor/autoload.php';

// handy constants
define('DAY', 60*60*24);
define('WEEK', DAY*7);

// Silex app
$app = new Application;
$app['debug'] = true;

// logging
$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/log/development.log',
));

// DB connectivity
$app->register(new DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'combine',
        'user'      => 'combine',
        'password'  => 'combine',
        'charset'   => 'utf8',
    ),
));
// Sessions
$app->register(new SessionServiceProvider());

// Url Generation
$app->register(new UrlGeneratorServiceProvider());

// Templating
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => __DIR__.'/tpl',
    'twig.options' => array(
        'strict_variables' => false,
    ),
));

// Combine custom stuff
$app->register(new CombineServiceProvider());

// Security & firewalls
$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'login' => array(
            'pattern' => '^/login$',
        ),
        'secured' => array(
            'pattern' => '^.*$',
            'mooLdap' => true,
            'form' => array(
                'login_path' => '/login',
                'check_path' => '/login_check'
            ),
            'logout' => array(
                'logout_path' => '/logout'
            ),
            'users' => $app['combine.ldap.user_provider'],
        ),
    ),
));

// Custom authentication factory for auth against MOO's ldap service
$app['security.authentication_listener.factory.mooLdap'] = $app->protect(function ($name, $options) use ($app) {
    // define the authentication provider object
    $app['security.authentication_provider.'.$name.'.mooLdap'] = $app->share(function () use ($app) {
        return new LdapAuthenticationProvider(
            $app['security.user_checker'],
            'secured',
            $app['combine.ldap.user_provider'],
            $app['combine.ldap.manager'],
            false
        );
    });
    $app['security.authentication_listener.'.$name.'.mooLdap'] = $app['security.authentication_listener.form._proto']($name, $options);

    return array(
        'security.authentication_provider.'.$name.'.mooLdap',
        'security.authentication_listener.'.$name.'.mooLdap',
        null,
        'pre_auth'
    );
});

$app->boot();

return $app;
