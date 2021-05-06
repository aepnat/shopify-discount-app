<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Security\ShopifySession;
use PHPShopify\AuthHelper;
use PHPShopify\Exception\ApiException;
use PHPShopify\Exception\CurlException;
use PHPShopify\Exception\SdkException;
use PHPShopify\ShopifySDK;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class OAuthController extends AbstractController
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(ShopifySession $session, EventDispatcherInterface $dispatcher)
    {
        $this->session = $session;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        if (!$shopUrl = $request->get('shop')) {
            throw new BadRequestHttpException('Request is missing required parameter "shop".');
        }

        $scopes = 'read_price_rules,write_price_rules';
        $redirectUrl = $this->getParameter('ngrok_url') . '/authenticate';

        try {
            ShopifySDK::config([
                'ShopUrl' => $shopUrl,
                'ApiKey' => $this->getParameter('shopify_api_key'),
            ]);
            $url = AuthHelper::createAuthRequest($scopes, $redirectUrl, uniqid(), null, true);
        } catch (SdkException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new RedirectResponse($url);
    }

    /**
     * @Route("/authenticate", name="authenticate")
     * @param Request $request
     * @return RedirectResponse
     * @throws SdkException
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $authCode  = $request->get('code');
        $shopUrl = $request->query->get('shop');
        $nonce = $request->get('state');
        $hmac = $request->get('hmac');

        // Required parameters
        if (!$authCode || !$shopUrl || !$nonce || !$hmac) {
            throw new BadRequestHttpException('Request is missing one or more of required parameters: "code", "shop", "state", "hmac".');
        }

        // Set Config
        ShopifySDK::config([
            'ApiKey' => $this->getParameter('shopify_api_key'),
            'SharedSecret' => $this->getParameter('shopify_api_secret'),
            'ShopUrl' => $shopUrl,
        ]);

        // Verify Request
        if (!AuthHelper::verifyShopifyRequest()) {
            throw new BadRequestHttpException('Invalid HMAC Signature');
        }

        // Get Access Token
        $accessToken = AuthHelper::getAccessToken();

        // Find exists store
        $shop = $this->getDoctrine()->getRepository(Shop::class)
            ->findOneBy([
                'domain' => $shopUrl,
            ]);
        if (!$shop) {
            // Get Shop Properties
            $config = array(
                'ShopUrl' => $shopUrl,
                'AccessToken' => $accessToken,
            );
            $shopify = new ShopifySDK($config);
            try {
                $shopProperties = $shopify->Shop->get();
            } catch (ApiException | CurlException $e) {
                throw new BadRequestHttpException("Failed to get shop properties data.");
            }

            $shop = new Shop();
            $shop->setDomain($shopUrl);
            $shop->setName($shopProperties['name']);
        }

        // Save Shop and token to DB
        $shop->setToken($accessToken);
        $em = $this->getDoctrine()->getManager();
        $em->persist($shop);
        $em->flush();

        $this->session->set($shopUrl, $accessToken);

        // redirect to app
        return new RedirectResponse($this->generateUrl('app'));
    }
}
