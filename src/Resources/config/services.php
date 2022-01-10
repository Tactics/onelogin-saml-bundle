<?php declare(strict_types=1);
// SPDX-License-Identifier: BSD-3-Clause

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\EntityManagerInterface;
use Nbgrp\OneloginSamlBundle\Controller;
use Nbgrp\OneloginSamlBundle\EventListener;
use Nbgrp\OneloginSamlBundle\Idp;
use Nbgrp\OneloginSamlBundle\Onelogin;
use Nbgrp\OneloginSamlBundle\Security;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

return static function (ContainerConfigurator $container): void {
    $src = \dirname(__DIR__, 2);
    $container->services()
        ->defaults()
            ->autoconfigure()

        ->load('Nbgrp\\OneloginSamlBundle\\', $src.'/*')
            ->exclude($src.'/{DependencyInjection,Event,Resources}')

        ->set(Controller\Login::class)
            ->args([
                service('security.firewall.map'),
            ])

        ->set(Idp\IdpResolverInterface::class, Idp\IdpResolver::class)
            ->args([
                param('nbgrp_onelogin_saml.idp_parameter_name'),
            ])

        ->set(Onelogin\AuthRegistryInterface::class, Onelogin\AuthRegistry::class)

        ->set(Security\Http\Authenticator\SamlAuthenticator::class)
            ->args([
                /* 0 */ service(HttpUtils::class),
                /* 1 */ abstract_arg('user provider'),
                /* 2 */ service(Idp\IdpResolverInterface::class),
                /* 3 */ service(Onelogin\AuthRegistryInterface::class),
                /* 4 */ abstract_arg('success handler'),
                /* 5 */ abstract_arg('failure handler'),
                /* 6 */ abstract_arg('options'),
                /* 7 */ null,  // user factory
                /* 8 */ service(EventDispatcherInterface::class)->nullOnInvalid(),
                /* 9 */ service(LoggerInterface::class)->nullOnInvalid(),
                /* 10 */ param('nbgrp_onelogin_saml.idp_parameter_name'),
            ])

        ->set(EventListener\User\UserCreatedListener::class)
            ->abstract()
            ->args([
                service(EntityManagerInterface::class)->nullOnInvalid(),
                false,  // persist_user
            ])

        ->set(EventListener\User\UserModifiedListener::class)
            ->abstract()
            ->args([
                service(EntityManagerInterface::class)->nullOnInvalid(),
                false,  // persist_user
            ])
    ;
};
