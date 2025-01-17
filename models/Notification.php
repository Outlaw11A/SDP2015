<?php

class Notification {

    /**
     * Send an email using the Mandrill API
     *
     * @param $address
     * @param $name
     * @param $subject
     * @param $contents
     * @return bool
     */
    static function sendEmail($address, $name, $subject, $contents) {

        if (User::getUser()['email_enabled'] != "1") {
            return true;
        }

        try {

            // Create mandrill object
            $mandrill = new Mandrill($GLOBALS['mandrill_key']);

            // Message
            $message = [
                'html' => $contents,
                'subject' => $subject,
                'from_email' => $GLOBALS['from_email'],
                'from_name' => $GLOBALS['from_name'],
                'to' => [
                    [
                        'email' => $address,
                        'name' => $name,
                        'type' => 'to'
                    ],
                ],
                'important' => true,
                'track_opens' => true,
                'track_clicks' => true,
            ];

            // Send the email
            $mandrill->messages->send($message, false, 'Main Pool', null);

            return true;

        } catch(Mandrill_Error $e) {
            return false;
        }

    }

    /**
     * Render template with variables for sending in an email
     *
     * @param $template
     * @param $page
     * @return string
     */
    static function renderEmail($template, $page) {

        // H2o object for rendering
        $h2o = new h2o(null, array('autoescape' => false));

        // Load template and render it
        $h2o->loadTemplate(__DIR__ . '/../views/' . $template);
        return $h2o->render(compact('page'));

    }

    /**
     * Get notification settings for a particular booking
     *
     * @param $bookingId
     * @return null
     */
    static function getNotification($bookingId) {

        $mysql = new MySQL();
        $results = $mysql->query('SELECT * FROM notification WHERE booking_id = :booking_id', [':booking_id' => $bookingId]);

        if ($results['success'] == true && !empty($results['results']) && $results['results'] != null) {
            return $results['results'];
        }

        return null;

    }

    static function setNotification($bookingId, $oneWeek, $threeDays, $oneDay) {

        $mysql = new MySQL();

        if (self::getNotification($bookingId) === null) {

            $results = $mysql->query('INSERT INTO notification(booking_id, one_week, three_days, one_day) VALUES (:booking_id, :one_week, :three_days, :one_day)', [
                ':booking_id' => $bookingId,
                ':one_week' => $oneWeek,
                ':three_days' => $threeDays,
                ':one_day' => $oneDay
            ]);

        } else {

            $results = $mysql->query('UPDATE notification SET one_week = :one_week, three_days = :three_days, one_day = :one_day WHERE booking_id = :booking_id', [
                ':booking_id' => $bookingId,
                ':one_week' => $oneWeek,
                ':three_days' => $threeDays,
                ':one_day' => $oneDay
            ]);

        }

        return $results['success'];

    }
}