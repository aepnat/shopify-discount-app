<?php

namespace App\Security;

use App\Entity\Shop;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class ShopifyAdminUserProvider implements \Symfony\Component\Security\Core\User\UserProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @inheritDoc
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        /** @var Shop $shop */
        $shop = $this->em->getRepository(Shop::class)
            ->findOneBy([
                'domain' => $username,
            ]);
        if (! $shop) {
            throw new UsernameNotFoundException();
        }

        return $shop;
    }

    /**
     * @inheritDoc
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function supportsClass(string $class): bool
    {
        return true;
    }
}