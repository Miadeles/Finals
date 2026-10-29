<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid order data.',
                ], 400);
            }

            $name = trim((string) ($data['name'] ?? ''));
            $phone = trim((string) ($data['phone'] ?? ''));
            $address = trim((string) ($data['address'] ?? ''));
            $items = $data['items'] ?? [];

            if (
                $name === '' ||
                $phone === '' ||
                $address === '' ||
                !is_array($items) ||
                count($items) === 0
            ) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Please provide your details and at least one pizza.',
                ], 400);
            }

            $order = new Order();
            $order->setCustomerName($name);
            $order->setCustomerPhone($phone);
            $order->setCustomerAddress($address);
            $order->setStatus('Pending');

            $total = 0;

            // Existing prices remain unchanged.
            $prices = [
                'Pepperoni' => ['Small' => 399, 'Medium' => 449, 'Large' => 499],
                'Margherita' => ['Small' => 349, 'Medium' => 399, 'Large' => 449],
                'Creamy Spinach' => ['Small' => 429, 'Medium' => 479, 'Large' => 529],
                'New York Style' => ['Small' => 449, 'Medium' => 499, 'Large' => 549],
                'Hawaiian' => ['Small' => 429, 'Medium' => 479, 'Large' => 529],
                'Spinach & Glazed Bacon' => ['Small' => 459, 'Medium' => 509, 'Large' => 559],
                'Extravaganzza' => ['Small' => 499, 'Medium' => 549, 'Large' => 599],
                'Cheese Mania' => ['Small' => 449, 'Medium' => 499, 'Large' => 549],
                'American Bacon & Cheeseburger' => ['Small' => 479, 'Medium' => 529, 'Large' => 579],
            ];

            foreach ($items as $item) {
                if (!is_array($item)) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Invalid pizza item.',
                    ], 400);
                }

                $pizzaName = trim((string) ($item['name'] ?? ''));
                $pizzaSize = trim((string) ($item['size'] ?? ''));

                // Accept the menu's displayed name, which ends in "Pizza".
                if (str_ends_with($pizzaName, ' Pizza')) {
                    $pizzaName = substr($pizzaName, 0, -6);
                }

                $quantity = filter_var(
                    $item['quantity'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (
                    $pizzaName === '' ||
                    $pizzaSize === '' ||
                    $quantity === false ||
                    $quantity < 1 ||
                    $quantity > 99
                ) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'One of the pizza items is invalid.',
                    ], 400);
                }

                if (!isset($prices[$pizzaName][$pizzaSize])) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'A pizza name or size is not recognized.',
                    ], 400);
                }

                $price = $prices[$pizzaName][$pizzaSize];
                $total += $price * $quantity;

                $orderItem = (new OrderItem())
                    ->setPizzaName($pizzaName)
                    ->setPizzaSize($pizzaSize)
                    ->setQuantity($quantity)
                    ->setPrice($price);

                $order->addOrderItem($orderItem);
                $entityManager->persist($orderItem);
            }

            $order->setTotal($total);
            $entityManager->persist($order);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Your order has been placed successfully!',
                'orderId' => $order->getId(),
            ]);
        }

        return $this->render('checkout/index.html.twig');
    }
}
