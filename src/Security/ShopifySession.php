<?php

namespace App\Security;

use App\Entity\Shop;
use Doctrine\ORM\EntityManagerInterface;
use PHPShopify\ShopifySDK;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class ShopifySession
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * ShopifySession constructor.
     * @param SessionInterface $session
     * @param EntityManagerInterface $em
     * @param Security $security
     * @param RouterInterface $router
     */
    public function __construct(SessionInterface $session, EntityManagerInterface $em, Security $security, RouterInterface $router)
    {
        $this->session = $session;
        $this->em = $em;
        $this->security = $security;
        $this->router = $router;
    }

    /**
     * @param string $shopUrl
     * @param string $token
     */
    public function set(string $shopUrl, string $token)
    {
        $this->session->set('shopUrl', $shopUrl);
        $this->session->set('token', $token);
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'AccessToken' => $this->session->get('token'),
            'ShopUrl' => $this->session->get('shopUrl'),
        ];
    }

    /**
     * @return Shop
     */
    public function shop(): Shop
    {
        /** @var Shop $shop */
        $shop = $this->security->getUser();

        return $shop;
    }

    /**
     * API
     *
     * @return ShopifySDK
     */
    public function api(): ShopifySDK
    {
        return new ShopifySDK($this->getConfig());
    }

}