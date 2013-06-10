<?php

namespace Galaxy\FrontendBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;

class GalaxyFactory extends AbstractFactory
{
    /*public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.galaxy.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('galaxy.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'security.authentication.listener.galaxy.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('galaxy.security.authentication.listener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }*/
    
    public function getPosition()
    {
        return 'pre_auth';
        //return 'form';
    }

    public function getKey()
    {
        return 'galaxy';
    }
    public function __construct()
    {
        $this->addOption('username_parameter', '_username');
        $this->addOption('password_parameter', '_password');
        $this->addOption('csrf_parameter', '_csrf_token');
        $this->addOption('intention', 'authenticate');
        $this->addOption('post_only', true);
    }

    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);
    }

    protected function getListenerId()
    {
        return 'security.authentication.listener.galaxy.';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'security.authentication.provider.galaxy.'.$id;
        $container
            ->setDefinition($provider, new DefinitionDecorator('galaxy.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProviderId))
            //->replaceArgument(2, $id)
        ;

        return $provider;
    }

    protected function createListener($container, $id, $config, $userProvider)
    {
        /*$listenerId = 'security.authentication.listener.galaxy.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('galaxy.security.authentication.listener'));
        if (isset($config['csrf_provider'])) {
            $container
                ->getDefinition($listenerId)
                ->addArgument(new Reference($config['csrf_provider']))
            ;
        }*/

        $listenerId = 'galaxy.security.authentication.listener';
        
        $listener = new DefinitionDecorator($listenerId);
        $listener->replaceArgument(4, $id);
        $listener->replaceArgument(5, new Reference($this->createAuthenticationSuccessHandler($container, $id, $config)));
        $listener->replaceArgument(6, new Reference($this->createAuthenticationFailureHandler($container, $id, $config)));
        $listener->replaceArgument(7, array_intersect_key($config, $this->options));

        $listenerId .= '.'.$id;
        $container->setDefinition($listenerId, $listener);

        return $listenerId;
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId = 'security.authentication.form_entry_point.'.$id;
        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('security.authentication.form_entry_point'))
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument($config['login_path'])
            ->addArgument($config['use_forward'])
        ;

        return $entryPointId;
    }
    
}