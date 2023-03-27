<?php

namespace App\Service;

class CalendarManager
{
  /**
   * Compares the date in parameter $date with the current date $today. If the $date is prior or equal to $today, the method returns false.
   *
   * @param \DateTimeImmutable $date
   * @return boolean
   */
  public function checkDateValidity(\DateTimeImmutable $date): bool
  {
    $today = new \DateTime;

    if ($date <= $today) {
      // Error: the reservation date is invalid
      return false;
    }

    return true;
  }

  /**
   * Checks if the user has already booked a spot in the same week. If he had, this method returns false.
   *
   * @param String $date
   * @param Array $bookingHistory
   * @return boolean
   */
  public function checkUserHistory(String $date, Array $bookingHistory): bool
  {
    $weekOfYear = date('W', strtotime($date));

    foreach ($bookingHistory as $booking) {
        if (date('W', strtotime($booking->getDate()->format('Ymd'))) == $weekOfYear){
            // Error: the reservation date is invalid
            return false;
        }
    }

    return true;
  }


  /**
   * Checks if the request date is available. If not, this method returns false.
   *
   * @param String $date
   * @param Array $currentBookings
   * @return boolean
   */
  public function CheckAvailability(String $date, Array $currentBookings): bool
  {
    $dayName = date('N', strtotime($date));

    // day = 5 = friday => 6 spots available, other days => 7 spots available
    switch ($dayName) {
        case 5:
            $maxSpot = 6;
            break;
        
        default:
            $maxSpot = 7;
            break;
    }

    if (count($currentBookings) >= $maxSpot) {
        // Error: the reservation date is invalid
        return false;
    }

    return true;
  }
}
