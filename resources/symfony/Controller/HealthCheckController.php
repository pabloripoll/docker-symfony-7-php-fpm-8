<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController extends AbstractController
{
    #[Route('/api/v1/health', methods:['GET'], name:'health_api')]
    public function api(): Response
    {
        return $this->json([
            'status' => true
        ]);
    }

    #[Route('/api/v1/health/db', methods:['GET'], name:'health_database')]
    public function database(EntityManagerInterface $em): Response
    {
        try {
            return $this->json([
                'status' => $em->getConnection()->connect()
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status'  => false,
                'message' => 'Connect to database failed - Check connection params.',
                'error'   => $e->getMessage()
            ]);
        }

    }

}