<?php

namespace Candidate\Vacancy;

use BD\Emails\Email;
use ResponseHelper;
use Vacancy\Vacancy;
use WP_REST_Request;

class VacancyAppliedService
{
    public $vacancyAppliedController;

    public function __construct()
    {
        $this->vacancyAppliedController = new VacancyAppliedController;
    }

    public function applyVacancy(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->vacancyAppliedController->applyVacancy($params);

        $this->_send_when_success_apply($response, $params);

        return ResponseHelper::build($response);
    }

    private function _send_when_success_apply($response, $params)
    {
        if($response['status'] === 201 && $params['user_id']) {
            $user = get_user_by('id', $params['user_id']);
            $vacancy = new Vacancy($params["vacancy"]);
            $company = get_user_by('id', $vacancy->getAuthor());

            $args = [
                'applicant.firstName' => $user->first_name,
                'applicant.lastName' => $user->last_name,
                'applicant.vacancy.title' => $vacancy->getTitle(),
                'applicant.vacancy.company.name' => get_field('ucma_company_name', 'user_' . $company->ID)
            ];

            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );

            $site_title = get_bloginfo('name');
            $content = Email::render_html_email('aplication-confirmation-candidate.php', $args);
            wp_mail($user->user_email, "Bevestiging van sollicitatie - $site_title", $content, $headers);
        }
    }
}
