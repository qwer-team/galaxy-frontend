<?php

namespace Galaxy\FrontendBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Galaxy\FrontendBundle\DependencyInjection\Security\Factory\GalaxyFactory;

class GalaxyFrontendBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new GalaxyFactory());
    }

}
