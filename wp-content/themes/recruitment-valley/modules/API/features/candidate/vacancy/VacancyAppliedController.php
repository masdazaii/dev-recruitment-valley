<?php

namespace Candidate\Vacancy;

use Constant\Message;
use DateTime;
use Model\ModelHelper;
use Vacancy\Vacancy;

class VacancyAppliedController
{
    private $_message;
    private $wpdb;

    public function __construct()
    {
        global $wpdb;

        $this->_message = new Message;
        $this->wpdb = $wpdb;
    }

    public function applyVacancy($request)
    {
        $user = get_user_by('ID', $request['user_id']);

        $vacancy = new Vacancy($request["vacancy"]);
        $company = $vacancy->getAuthor();
        $vacancyTitle = $vacancy->getTitle();
        $expiredAt = new DateTime($vacancy->getExpiredAt());
        $now = new DateTime();

        $applicantArgs = [
            "post_type" => "applicants",
            "post_status" => "publish",
            "numberposts" => -1,
            "meta_query" => [
                "relation" => "AND",
                [
                    "key" => "applicant_candidate",
                    "value" => $request['user_id'],
                    "compare" => "="
                ],
                [
                    "key" => "applicant_vacancy",
                    "value" => $request['vacancy'],
                    "compare" => "="
                ]
            ]
        ];

        $applicants = get_posts($applicantArgs);
        
        if(count($applicants) > 0)
        {
            return [
                "status" => 400,
                "message" => "Je hebt al op deze functie gesolliciteerd"
            ];
        }

        if ($now > $expiredAt) {
            return [
                "status" => 400,
                "message" => $this->_message->get('candidate.apply_vacancy.expired_job')
            ];
        }

        $title = 'Application - ' . $user->user_nicename . ' - ' . $vacancyTitle;

        $this->wpdb->query('START TRANSACTION');
        try {

            $arguments = [
                'post_title' => $title,
                // 'post_author' => $request['user_id'],
                'post_author' => get_current_user_id(),
                'post_date' => date('Y-m-d H:i:s', time()),
                'post_date_gmt' => gmdate('Y-m-d H:i:s', time()),
                'post_status' => 'publish',
                'post_type' => 'applicants',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_parent' => 0,
                'page_template' => 'default'
            ];
    
            $doApply = wp_insert_post($arguments, false, true);
    
            if (is_wp_error($doApply)) {
                return [
                    "message"   => $this->_message->get('candidate.apply_vacancy.apply_failed'),
                    "status"    => 400
                ];
            }
    
            $cv_id = 0;

            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
                $fileExtension = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
                if ($fileExtension != "pdf" && $fileExtension != "jpg" && $fileExtension != "png") {
                    return [
                        "status" => 400,
                        "message" => "bestandstype wordt niet ondersteund",
                    ];
                }
                
                $cv = ModelHelper::handle_upload('cv');
                if ($cv) {
                    $cv_id = wp_insert_attachment($cv['cv']['attachment'], $cv['cv']['file']);
                    update_field('apply_cv', $cv_id, $doApply);
                }
            }

            $attachment = get_post( $request["cv"] );
            if($attachment)
            {
                $cv_id = $attachment->ID;
                update_field('apply_cv', $attachment->ID, $doApply);
            }
    
            update_field('apply_date', date('Y-m-d H:i:s', time()), $doApply);
            update_field('phone_code_area', $request['phoneNumberCode'], $doApply);
            update_field('phone_number', $request['phoneNumber'], $doApply);
            update_field('cover_letter', $request['coverLetter'], $doApply);
            update_field('applicant_candidate', $request["user_id"], $doApply);
            update_field('applicant_company', $company, $doApply);
            update_field('applicant_vacancy', $request["vacancy"], $doApply);
    
            // add one time data that not affected by update
            update_post_meta($doApply, 'applicant_data', get_fields('user_' . $request["user_id"]));
            update_post_meta($doApply, 'vacancy_data', get_fields($request["vacancy"]));
            update_post_meta($doApply, 'company_data', get_fields("user_" . $company));    

            $this->wpdb->query('COMMIT');

            return [
                "message"   => $this->_message->get('candidate.apply_vacancy.apply_success'),
                "status"    => 201,
                "cv" => $cv_id,
                "vacancy_id" => $request["vacancy"],
                "applicant" => $doApply,
            ];
        } catch (\Throwable $th) {
            $this->wpdb->query('ROLLBACK');

            return [
                "message"   => $th->getMessage(),
                "status"    => 400
            ];
        }
    }
}
