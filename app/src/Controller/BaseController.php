<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * This class is for functions that all controllers need but aren't included in the AbstractController
 */
class BaseController extends AbstractController
{

    /**
     * Source:
     * https://stackoverflow.com/questions/14504913/verify-valid-date-using-phps-datetime-class
     */
    public function verifyDate($date, $strict = true): bool
    {
        $dateTime = \DateTime::createFromFormat('d-m-Y', $date);
        if ($strict) {
            $errors = \DateTime::getLastErrors();
            if (!empty($errors['warning_count'])) {
                return false;
            }
        }
        return $dateTime !== false;
    }
}