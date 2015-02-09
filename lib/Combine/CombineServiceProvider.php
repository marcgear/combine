<?php
namespace Combine;

use FR3D\LdapBundle\Driver\ZendLdapDriver;
use FR3D\LdapBundle\Ldap\LdapManager;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Zend\Ldap\Ldap;

class CombineServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['combine.ldap.config'] = array(
            'host'                => 'your.ldap.host',
            'port'                => 389,
            'username'            => 'cn=admin,dc=yourdc,dc=com',
            'password'            => 'yourpass',
            'baseDn'              => 'ou=Staff Directory,dc=yourdc,dc=com',
            'bindRequiresDn'      => true,
            'accountFilterFormat' => '(&(objectClass=top)(uid=%s))',
        );

        $app['combine.last_cutoff'] = function() {
            return strtotime('last Thursday');
        };

        $app['combine.next_cutoff'] = function($app) {
            return $app['combine.last_cutoff'] + WEEK;
        };

        // msg array always gets set on the template
        $app['combine.msg'] = new \ArrayObject;

        $app['combine.msg.collect'] = function ($app) {
            // handle successes
            if ($app['session']->get('combine.message.success')) {
                $app['combine.msg']['success'] = $app['session']->get('combine.message.success');
                $app['session']->remove('combine.message.success');
            }

            // handle errors
            if ($app['session']->get('combine.message.error')) {
                $app['combine.msg']['error'] = $app['session']->get('combine.message.error');
                $app['session']->remove('combine.message.error');
            }

            return $app['combine.msg'];
        };

        $app['combine.gateway.user'] = $app->share(function ($app) {
            return new UserGateway($app['db']);
        });
        $app['combine.gateway.entry'] = $app->share(function ($app) {
            return new EntryGateway($app['db']);
        });

        $app['combine.ldap.user_provider'] = $app->share(function ($app) {
            return new LdapUserProvider($app['combine.gateway.user']);
        });

        $app['combine.ldap.manager'] = $app->share(function ($app) {
            return new LdapManager(
                new ZendLdapDriver(new Ldap($app['combine.ldap.config']), $app['logger']),
                $app['combine.ldap.user_provider'],
                array(
                    'attributes' => array(
                        array(
                            'ldap_attr' => array(),
                        ),
                    ),
                )
            );
        });
    }

    public function boot(Application $app)
    {
    }
}
