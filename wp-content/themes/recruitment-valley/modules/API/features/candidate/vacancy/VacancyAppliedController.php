<?php

namespace Candidate\Vacancy;

use Constant\Message;
use Model\ModelHelper;

class VacancyAppliedController
{
    private $_message;

    public function __construct()
    {
        $this->_message = new Message;
    }

    public function applyVacancy($request)
    {
        $user = get_user_by('ID', $request['user_id']);

        // $title = 'Application - ' . $user->user_nicename . ' - Job Title';

        // $arguments = [
        //     'post_title' => $title,
        //     // 'post_author' => $request['user_id'],
        //     'post_author' => get_current_user_id(),
        //     'post_date' => date('Y-m-d H:i:s', time()),
        //     'post_date_gmt' => gmdate('Y-m-d H:i:s', time()),
        //     'post_status' => 'publish',
        //     'post_type' => 'applicants',
        //     'comment_status' => 'closed',
        //     'ping_status' => 'closed',
        //     'post_parent' => 0,
        //     'page_template' => 'default'
        // ];

        // $doApply = wp_insert_post($arguments, false, true);

        // if (is_wp_error($doApply)) {
        //     return [
        //         "message"   => $this->_message->get('candidate.apply_vacancy.apply_failed'),
        //         "status"    => 400
        //     ];
        // }

        // /** Store meta and acf */
        // // $nowInUTC = new \DateTime("now", new \DateTimeZone("UTC"));
        // update_post_meta($doApply->ID, 'user_id', $user->ID);
        // update_post_meta($doApply->ID, 'job_id', $request['job_id']);
        // update_field('apply_date', date('Y-m-d H:i:s', time()), $doApply->ID);
        // update_field('phone_code_area', $request['phoneNumberCode'], $doApply->ID);
        // update_field('phone_number', $request['phoneNumber'], $doApply->ID);
        // update_field('cover_letter', $request['coverLetter'], $doApply->ID);

        return [
            "message"   => $this->_message->get('candidate.apply_vacancy.apply_success'),
            "data"      => $user->ID,
            "acf"       => get_fields($user->ID),
            "status"    => 201
        ];
    }
}
