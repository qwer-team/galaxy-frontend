<?php

namespace Galaxy\FrontendBundle\Service\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Galaxy\FrontendBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAware;

class UserProvider extends ContainerAware implements UserProviderInterface
{

    public function loadUserByUsername($username)
    {
        $userService = $this->container->get("galaxy.user.service");
        $response = $userService->getUser($username);
        
        $user = new User();
        $userId = $response->data->id;

        $userInfoService = $this->container->get("galaxy.user_info.service");
        $fundsInfo = $userInfoService->getFundsInfo($userId);
        $gameInfo = $userInfoService->getGameInfo($userId);
        
        $user->setFundsInfo($fundsInfo);
        $user->setGameInfo($gameInfo);
        $user->setId($userId);
        $user->setUsername($username);
        $user->setPassword($response->data->password);
        $user->setEnabled($response->data->enabled);
        $user->setEmail($response->data->email);
        $user->setEmailCanonical($response->data->email);
        $user->setExpired($response->data->expired);
        $user->setRoles($response->data->roles);
        $user->setCredentialsExpired($response->data->credentials_expired);
        $user->setLockedExpiresAt(isset($response->data->locked_expires_at) ? new \DateTime($response->data->locked_expires_at) : NULL);
        $user->setBirthday(isset($response->data->birthday) ? new \DateTime($response->data->birthday) : NULL);
        $user->setMessage(isset($response->data->message) ? new \DateTime($response->data->message) : NULL);
        /* $user = new User();
          $user->setUsername('vassa');
          $user->setPassword('123');
          $user->setEnabled(true);
          $user->setEmail('vassa@v.v');
          $user->setExpired(false);
          $user->setRoles(array('ROLE_USER', 'ROLE_ADMIN'));
          $user->setCredentialsExpired(false); */
        //print_r($user);

        return $user;
    }

    public function refreshUser(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        // $user = $this->loadUserByUsername($user->getUsername());
        return $user;
    }

    public function supportsClass($class)
    {
        return ($class == "Galaxy\FrontendBundle\Entity\User" ||
                $class == "\Galaxy\FrontendBundle\Entity\User");
    }
}