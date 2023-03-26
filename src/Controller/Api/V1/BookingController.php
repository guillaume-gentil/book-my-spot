<?php

namespace App\Controller\Api\V1;

use App\Entity\Booking;
use App\Repository\BookingRepository;
use App\Repository\UserRepository;
use App\Service\CalendarManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1', name: 'api_v1_')]
class BookingController extends AbstractController
{
    /**
     * Gets the list of all Bookings of the current User.
     * 
     * @param BookingRepository $br
     * @return JsonResponse
     */
    #[Route('/bookings', name: 'read_User_bookings', methods: ['GET'])]
    public function browseBookings(BookingRepository $br): JsonResponse
    {
        // gets current User from JWT
        $currentUser = $this->getUser();

        // gets all the bookings for the current User
        $userBookings = $br->findBy(['foodtruck' => $currentUser]);

        return $this->json(['userBookings' => $userBookings], Response::HTTP_OK, [], ['groups' => 'booking:item']);
    }


    /**
     * Gets the list of the current date's Booking. Date format yyyymmdd.
     * 
     * @param String $date
     * @param BookingRepository $br
     * @return JsonResponse
     */
    #[Route('/bookings/{date}', name: 'read_Date_bookings', methods: ['GET'], requirements: ['date' => '\d{8}'])]
    public function readBooking(String $date = null, BookingRepository $br): JsonResponse
    {
        // gets all the booking for the current date
        $currentBookings = $br->findBy(['date' => new \DateTimeImmutable($date)]);

        return $this->json(['currentBookings' => $currentBookings], Response::HTTP_OK, [], ['groups' => 'booking:item']);
    }

    
    #[Route('/bookings', name: 'add_booking', methods: ['POST'])]
    public function addBooking(
        Request $req,
        BookingRepository $br,
        EntityManagerInterface $em,
        CalendarManager $cm): JsonResponse
    {
        // gets current User from JWT
        $currentUser = $this->getUser();

        // gets current Date from body request 
        $bookDate = json_decode($req->getContent(), true)['date'];
        
        // convert current Date
        $bookDateTime = new \DateTimeImmutable($bookDate);
        
        // gets all the booking of the current user
        $userBookings = $br->findBy(['foodtruck' => $currentUser]);
        // gets all the booking of the current date
        $currentBookings = $br->findBy(['date' => $bookDateTime]);
        
        
        if (!$cm->checkDateValidity($bookDateTime)) {
            return $this->json(
                [
                    'error' => 'The reservation is unprocessable',
                    'details' => 'The requested date is invalid'
                ],
                Response::HTTP_I_AM_A_TEAPOT
            );
        }
        
        
        if (!$cm->checkUserHistory($bookDate, $userBookings)) {
            return $this->json(
                [
                    'error' => 'The reservation is unprocessable',
                    'details' => 'Each foodtruck can\'t book more than one slot per week'
                ],
                Response::HTTP_I_AM_A_TEAPOT
            );
        }
        
        
        if (!$cm->CheckAvailability($bookDate, $currentBookings)) {
            return $this->json(
                [
                    'error' => 'The reservation is unprocessable',
                    'details' => 'All the spots are already booked!'
                ],
                Response::HTTP_I_AM_A_TEAPOT
            );
        }
        
        
        // save data
        $newBooking = new Booking();
        $newBooking->setDate($bookDateTime);
        $newBooking->setFoodtruck($currentUser);
        
        $em->persist($newBooking);
        $em->flush();


        return $this->json($newBooking, Response::HTTP_CREATED, [], ['groups' => 'booking:item']);
    }
}
