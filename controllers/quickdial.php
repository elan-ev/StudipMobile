<?php

require "AuthenticatedController.php";
require dirname(__FILE__) . "/../models/quickdail.php";

use Studip\Mobile\Quickdail;

/**
 *    The Start Screen of studipmobile
 *    @author Nils Bussmann - nbussman@uos.de
 *    @author Marcus Lunzenauer - mlunzena@uos.de
 *    @author André Klaßen - aklassen@uos.de
 */
class QuickdialController extends AuthenticatedController
{
    function index_action()
    {
        // get next n courses of the day
        $this->next_courses = Quickdail::get_next_courses($this->currentUser()->id, '5');
        $this->user_id = $this->currentUser()->id;


        // get numbers of new mails
        $this->number_unread_mails = Quickdail::get_number_unread_mails($this->currentUser()->id);
    }
}
