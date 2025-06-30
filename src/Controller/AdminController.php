<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\AddProductForm;
use App\Form\EditProductForm;
use App\Repository\DetailOrderRepository;
use App\Repository\OrderRepository;
use App\Services\AdminService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{

    public function __construct(
        private readonly AdminService $adminService
    ) {

    }

    #[Route('/admin', name: 'app_admin', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $en): Response
    {
        $search = $request->query->get('search');
        $isLowStock = $request->query->get('low_stock');

        $qb = $en->getRepository(Product::class)->createQueryBuilder('p');

        if ($search) {
            $qb->andWhere('p.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }


        if ($isLowStock) {
            $qb->andWhere('p.inventory < :qty')
                ->setParameter('qty', 10);
        }

        $data = $qb->getQuery()->getResult();

        return $this->render('/admin/home_admin.html.twig', [
            'data' => $data,
            'search' => $search,
            'low_stock' => $isLowStock,
        ]);
    }

    #[Route('/addProduct', name: 'addProduct')]
    public function addProduct(EntityManagerInterface $em, Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(AddProductForm::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $imageFile = $form->get('url_img')->getData();

                if ($imageFile) {
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    try {
                        $imageFile->move(
                            $this->getParameter('img_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Error when upload: ' . $e->getMessage());
                        return $this->redirectToRoute('addProduct');
                    }
                    $product->setUrlImg('img/' . $newFilename);
                }

                $em->persist($product);
                $em->flush();
                return $this->redirectToRoute('app_task_index');
            } else {
                $this->addFlash('error', 'Form not valid.');
            }
        }

        return $this->render('admin/add_product.html.twig', [
            'addProductForm' => $form->createView(),
        ]);
    }

    #[Route('/admin_del_product/{id}', name: 'admin_del_product')]
    public function del_product(int $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        $em->remove($product);
        $em->flush();
        return $this->redirectToRoute('app_admin');
    }
    #[Route("/edit_product/{id}", name: 'editProduct')]

    public function edit(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EditProductForm::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Product updated successfully!');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/edit_product.html.twig', [
            'editProductForm' => $form->createView(),
        ]);
    }

    #[Route('/admin/order', name: 'admin_order')]
    public function dashboard(Request $request, OrderRepository $orderRepository): Response
    {
        $search = $request->query->get('search');
        $status = $request->query->get('status');

        $queryBuilder = $orderRepository->createQueryBuilder('o');

        if ($search) {
            $queryBuilder->andWhere('o.id LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            $queryBuilder->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        $orders = $queryBuilder->getQuery()->getResult();

        return $this->render('admin/order.html.twig', [
            'orders' => $orders,
        ]);
    }
    #[Route('/detail_order/{id}', name: 'admin_order_detail')]
    public function order_detail($id,DetailOrderRepository $detailOrderRepository,OrderRepository $order): Response
    {
        $data=$detailOrderRepository->findBy(['order'=>$id]);
        $total=0;
        foreach ($data as $dt) {
            $total += $dt->getTotal();
        }
        $consignee = $order->find($id);
        return $this->render('admin/order_detail.html.twig', [
            'detailOrder' => $data,
            'total' => $total,
            'consignee' => $consignee,
        ]);
    }
    #[Route('/admin/revenue', name: 'admin_revenue')]
    public function revenue(Request $request, DetailOrderRepository $repo): Response
    {
        $selectedMonth = $request->query->getInt('month', 0);
        [$productStats, $monthlyRevenue, $totalRevenue, $filteredOrders] =
            $this->adminService->calculate($selectedMonth, $repo);

        return $this->render('admin/revenue.html.twig', [
            'orders' => $filteredOrders,
            'monthlyRevenue' => $monthlyRevenue,
            'productStats' => $productStats,
            'total_revenue' => $totalRevenue,
            'selectedMonth' => $selectedMonth,
        ]);
    }
    #[Route('/pdf', name: 'admin_revenue_pdf')]
    public function exportPdf(Request $request, DetailOrderRepository $repo): Response
    {
        $selectedMonth = $request->query->getInt('month', 0);
        [$productStats, , $totalRevenue] =
            $this->adminService->calculate($selectedMonth, $repo);

        $html = $this->renderView('admin/revenue_pdf.html.twig', [
            'productStats' => $productStats,
            'total_revenue' => $totalRevenue,
            'selectedMonth' => $selectedMonth,
        ]);

        $dompdf = new Dompdf(['defaultFont' => 'DejaVu Sans']);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="revenue-report.pdf"',
        ]);
    }
    #[Route('/admin/users', name: 'admin_user_list')]
    public function user(Request $request, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('search');
        $qb = $em->getRepository(User::class)->createQueryBuilder('u');
        if ($search) {
            $qb->where('u.username LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $users = $qb->getQuery()->getResult();

        return $this->render('admin/user_list.html.twig', [
            'users' => $users,
            'search' => $search,
        ]);
    }

}
