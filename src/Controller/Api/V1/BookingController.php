<?php

namespace App\Controller\Api\V1;

use App\Repository\BookingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1', name: 'api_v1_')]
class BookingController extends AbstractController
{
    /**
     * Gets the list of the current date's Booking. Date format yyyymmdd.
     * 
     * @param String $date
     * @param BookingRepository $br
     * @return JsonResponse
     */
    #[Route('/bookings/{date}', name: 'current_bookings', methods: ['GET'], requirements: ['date' => '\d{8}'])]
    public function bookingsByDate(String $date = null, BookingRepository $br): JsonResponse
    {
        // gets all the booking for the current date
        $currentBookings = $br->findBy(['date' => new \DateTimeImmutable($date)]);

        return $this->json(['currentBookings' => $currentBookings], Response::HTTP_OK, [], ['groups' => 'booking:item']);
    }
}
