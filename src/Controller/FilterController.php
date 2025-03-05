<?php

namespace App\Controller;

use App\Entity\Filter;
use App\Repository\FilterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

final class FilterController extends AbstractController
{
    private function checkToken($request, $session): bool
    {
        $secret = $this->getParameter('token');
        $token = $session->get('token');

        if ($token && $token === $secret) {
            return true;
        }

        if ($request->get('token') === $secret) {
            $session->set('token', $secret);
            return true;
        }

        return false;
    }

    #[Route('/', name: 'app_filter_index', methods: ['GET'])]
    public function index(Request $request, FilterRepository $filterRepository, SessionInterface $session): Response
    {
        if (!$this->checkToken($request, $session)) {
            return new Response(status: 403);
        }

        return $this->render('filter/index.html.twig', [
            'filters' => $filterRepository->findAll(),
        ]);
    }

    #[Route('/get', name: 'app_filter_json',methods: ['GET'])]
    public function list(Request $request, FilterRepository $filterRepository, SerializerInterface $serializer, SessionInterface $session)
    {
        if (!$this->checkToken($request, $session)) {
            return new Response(status: 403);
        }

        return new Response(
            $serializer->serialize($filterRepository->findAll(), 'json'),
            headers: ['content-type' => 'application/json']
        );
    }

    #[Route('/new', name: 'app_filter_new', methods: ['POST'])]
    public function store(
        Request $request,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response {
        if (!$this->checkToken($request, $session)) {
            return new Response(status: 403);
        }

        $data = $request->toArray();

        $validator = Validation::createValidator();

        $constraints = new Assert\Collection([
            'name' => new Assert\Type('string'),
            'criteria' => new Assert\Type('array'),
        ]);

        $violations = $validator->validate($data, $constraints);

        if ($violations->count() > 0) {
            return new Response('Errors: '.$violations, 422);
        }

        $entityManager->persist(
            (new Filter())
                ->setName($data['name'])
                ->setCriteria($data['criteria'])
        );
        $entityManager->flush();

        return $this->redirectToRoute('app_filter_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/put', name: 'app_filter_edit', methods: ['POST'])]
    public function update(Request $request, Filter $filter, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        if (!$this->checkToken($request, $session)) {
            return new Response(status: 403);
        }

        $data = $request->toArray();

        $validator = Validation::createValidator();

        $constraints = new Assert\Collection([
            'name' => [new Assert\Required(), new Assert\Type('string'), new Assert\NotBlank()],
            'criteria' => [
                new Assert\Required(),
                new Assert\Type('array'),
                new Assert\Count(['min' => 1]),
                new Assert\All([
                    new Assert\Collection([
                        'type' => [new Assert\Required(), new Assert\Choice(['amount', 'date', 'title'])],
                        'op' => [new Assert\Required(), new Assert\Choice(['lt', 'eq', 'gt'])],
                        'value' => [new Assert\Required(), new Assert\NotBlank()],
                    ])
                ]),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);
        if ($violations->count() > 0) {
            return new Response('Errors: '.$violations, 422);
        }

        $entityManager->persist(
            $filter
                ->setName($data['name'])
                ->setCriteria($data['criteria'])
        );
        $entityManager->flush();

        return $this->redirectToRoute('app_filter_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete', name: 'app_filter_delete', methods: ['POST'])]
    public function delete(Request $request, Filter $filter, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        if (!$this->checkToken($request, $session)) {
            return new Response(status: 403);
        }

        $entityManager->remove($filter);
        $entityManager->flush();

        return $this->redirectToRoute('app_filter_index', [], Response::HTTP_SEE_OTHER);
    }
}
