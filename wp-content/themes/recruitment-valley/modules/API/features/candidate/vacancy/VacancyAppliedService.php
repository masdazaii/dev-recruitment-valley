<?php

namespace Candidate\Vacancy;

use ResponseHelper;
use WP_REST_Request;
use BD\Emails\Email;
use Candidate\Profile\Candidate;
use Vacancy\Vacancy;
use Constant\Message;
use Helper\ValidationHelper;
use Model\Applicant;
use Model\Company;

class VacancyAppliedService
{
    protected $_message;
    public $vacancyAppliedController;

    public function __construct()
    {
        $this->_message = new Message();
        $this->vacancyAppliedController = new VacancyAppliedController;
    }

    public function applyVacancy(WP_REST_Request $request)
    {
        $validator = new ValidationHelper('applyVacancy', $request->get_params());

        if (!$validator->tempValidate()) {
            $errors = $validator->getErrors();
            return ResponseHelper::build([
                'message' => $this->_message->get('candidate.favorite.vacancy_not_found'),
                'errors' => $errors,
                'status' => 400
            ]);
        }

        // $params = $request->get_params();

        $validator->tempSanitize();
        $params = $validator->getData();
        $response = $this->vacancyAppliedController->applyVacancy($params);

        if ($response['status'] === 201 && $params['user_id']) {
            $this->_send_into_candidate($response, $params);
            $this->_send_into_company($response, $params);

            unset($response["cv"]);
            unset($response["vacancy_id"]);
            unset($response["applicant"]);
        }

        // $this->_send_when_success_apply($response, $params);

        return ResponseHelper::build($response);
    }

    private function _send_when_success_apply($response, $params)
    {
        if ($response['status'] === 201 && $params['user_id']) {
            $this->_send_into_candidate($response, $params);

            $this->_send_into_company($response, $params);
        }
    }

    private function _send_into_candidate($response, $params)
    {
        $user = get_user_by('id', $params['user_id']);
        $vacancy = new Vacancy($params["vacancy"]);
        $company = get_user_by('id', $vacancy->getAuthor());
        $company_name = get_field('ucma_company_name', 'user_' . $company->ID);
        $company_name = (bool) $company_name ? $company_name : "";

        $args = [
            'applicant.firstName' => $user->first_name,
            'applicant.lastName' => $user->last_name,
            'applicant.vacancy.title' => $vacancy->getTitle(),
            'applicant.vacancy.company.name' => $company_name
        ];

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );

        $site_title = get_bloginfo('name');
        $content = Email::render_html_email('aplication-confirmation-candidate.php', $args);
        wp_mail($user->user_email, "Bevestiging van sollicitatie - $site_title", $content, $headers);
    }

    private function _send_into_company($response, $params)
    {
        $vacancy = new Vacancy($params["vacancy"]);
        $user = get_user_by('id', $vacancy->getAuthor());
        $company = new Company($user->ID);
        $candidate = new Candidate($params['user_id']);
        $applicant = new Applicant($response["applicant"]);

        $attachment = get_attached_file($response['cv']);

        $args = [
            'applicant.company' => $company->getName(),
            'applicant.candidate_name' => $candidate->getFirstName() . ' ' . $candidate->getLastName(),
            'applicant.vacancy.title' => $vacancy->getTitle(),
            'applicant.phoneNumber' => $applicant->getPhoneNumberCode() . $applicant->getPhoneNumber(),
            'applicant.cover_letter' => $applicant->getCoverLetter()
        ];

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );

        $content = Email::render_html_email('new-candidate-company.php', $args);

        $site_title = get_bloginfo('name');
        wp_mail($user->user_email, "Bevestiging van sollicitatie - $site_title", $content, $headers, [$attachment]);
    }

    public function history( WP_REST_Request $request )
    {
        $response = $this->vacancyAppliedController->history( $request );
        return ResponseHelper::build($response);
    }
}
