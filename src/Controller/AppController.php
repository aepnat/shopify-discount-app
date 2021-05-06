<?php

namespace App\Controller;

use App\Entity\DiscountCode;
use App\Security\ShopifySession;
use DateTime;
use PHPShopify\Exception\ApiException;
use PHPShopify\Exception\CurlException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    private $shopifySession;

    public function __construct(ShopifySession $session)
    {
        $this->shopifySession = $session;
    }

    /**
     * @Route("/app", name="app")
     */
    public function index(): Response
    {
        $discountCodes = $this->shopifySession->shop()->getDiscountCodes();

        $response = new Response($this->renderView('app/index.html.twig', compact('discountCodes')), 200);

        return $this->render('app/index.html.twig', compact('discountCodes'));
    }

    /**
     * @Route("/app/new", name="app.new")
     * @param Request $request
     * @return Response
     * @throws ApiException
     * @throws CurlException
     */
    public function new(Request $request): Response
    {
        $discountCode = new DiscountCode();

        $form = $this->createFormBuilder($discountCode)
            ->add('name', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'PERCENTAGE' => 'PERCENTAGE',
                    'FIXED AMOUNT' => 'FIXED_AMOUNT',
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('value', TextType::class)
            ->add('once_per_customer', CheckboxType::class, [
                'label' => 'Limit to one use per customer',
                'required' => false
            ])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DiscountCode $discountCode */
            $discountCode = $form->getData();

            try {
                $priceRule = $this->addPriceRule($discountCode);
            } catch (ApiException | CurlException $e) {
                throw new BadRequestHttpException("Failed to add price rule. Please try again!. message:" . $e->getMessage());
            }

            try {
                $this->addDiscountCode($priceRule['id'], $discountCode->getName());
            } catch (ApiException | CurlException $e) {
                $this->shopifySession->api()->PriceRule($priceRule['id'])->delete();
                throw new BadRequestHttpException("Failed to add discount code to price rule. Please try again!. message:". $e->getMessage());
            }

            $discountCode->setPriceRuleId($priceRule['id']);
            $discountCode->setShop($this->shopifySession->shop());
            $em = $this->getDoctrine()->getManager();
            $em->persist($discountCode);
            $em->flush();

            return $this->redirectToRoute('app');
        }

        return $this->render('app/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param DiscountCode $discountCode
     * @return array
     * @throws ApiException
     * @throws CurlException
     */
    private function addPriceRule(DiscountCode $discountCode): array
    {
        $starts_at = new DateTime();

        $value = $discountCode->getValue();
        return $this->shopifySession->api()->PriceRule->post([
            'title' => $discountCode->getName(),
            'value_type' => strtolower($discountCode->getType()),
            'value' => intval("-$value"),
            'once_per_customer' => $discountCode->getOncePerCustomer() ?: false,
            'allocation_method' => 'across',
            'target_type' => 'line_item',
            'target_selection' => 'all',
            'customer_selection' => 'all',
            'starts_at' => $starts_at->format(DateTime::ATOM),
        ]);
    }

    /**
     * @param int $priceRuleId
     * @param string $discountCode
     * @throws ApiException
     * @throws CurlException
     */
    private function addDiscountCode(int $priceRuleId, string $discountCode)
    {
        $this->shopifySession->api()->PriceRule($priceRuleId)->DiscountCode->post([
            'code' => $discountCode,
        ]);
    }
}
