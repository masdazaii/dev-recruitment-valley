<?php

namespace Vacancy;

use BD\Emails\Email;
use JWTHelper;
use Request\CandidateVacanciesRequest;
use Request\CreateFreeJobRequest;
use Request\CreatePaidJobRequest;
use Request\SingleVacancyRequest;
use ResponseHelper;
use WP_REST_Request;
use WP_REST_Response;

class VacancyCrudService
{
    public $vacancyCrudController;

    public $vacancyResponse;

    public function __construct()
    {
        $this->vacancyCrudController = new VacancyCrudController;
        $this->vacancyResponse = new VacancyResponse;
    }

    public function getAll(WP_REST_Request $request)
    {
        $getAllRequest = new CandidateVacanciesRequest( $request );
        if(!$getAllRequest->validate())
        {
            $errors = $getAllRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $params = $getAllRequest->getData();
        $response = $this->vacancyCrudController->getAll($params);
        $this->vacancyResponse->setCollection($response["data"]);

        $formattedResponse = $this->vacancyResponse->format();
        
        if(isset($params['placementAddress'])) {
            if($params['placementAddress'] !== "") {
                $formattedResponse = $this->vacancyCrudController->getAllByLocations($formattedResponse, $params);
            }
        }


        $response["data"] = $formattedResponse;
        return ResponseHelper::build($response);
    }

    public function get(WP_REST_Request $request)
    {
        $tokenPayload = JWTHelper::has($request);
        $singleVacancyRequest = new SingleVacancyRequest($request);
        if(!$singleVacancyRequest->validate())
        {
            $errors = $singleVacancyRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $singleVacancyRequest->sanitize();
        $params = $singleVacancyRequest->getData();
        $response = $this->vacancyCrudController->get($params);

        if (isset($response["data"])) {
            $this->vacancyResponse->setCollection($response["data"]);
            $formattedResponse = $this->vacancyResponse->formatSingle();
            $response["data"] = $formattedResponse;
        }

        return ResponseHelper::build($response);
    }

    public function createFreeJob(WP_REST_Request $request)
    {
        $createFreeJobRequest = new CreateFreeJobRequest($request);
        if(!$createFreeJobRequest->validate())
        {
            $errors = $createFreeJobRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $createFreeJobRequest->sanitize();
        $params = $createFreeJobRequest->getData();
        $params["user_id"] = $request["user_id"];
        $response = $this->vacancyCrudController->createFree($params);

        $this->_send_mail_when_make_vacancy($response, $params);

        return ResponseHelper::build($response);
    }

    public function createPaidJob( WP_REST_Request $request)
    {
        $createPaidJobRequest = new CreatePaidJobRequest($request);
        if(!$createPaidJobRequest->validate())
        {
            $errors = $createPaidJobRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $createPaidJobRequest->sanitize();

        $params = $createPaidJobRequest->getData();
        $params["user_id"] = $request["user_id"];
        $response = $this->vacancyCrudController->createPaid($params);
        return ResponseHelper::build($response);
    }

    public function update(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request["user_id"];

        if(get_post_status( $params['vacancy_id']) === false) return ResponseHelper::build(['status' => 400, 'message' => 'invalid post']);

        $response = $this->vacancyCrudController->update( $params );
        return ResponseHelper::build($response);
    }

    public function updateFree( WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request["user_id"];

        if(get_post_status( $params['vacancy_id']) === false) return ResponseHelper::build(['status' => 400, 'message' => 'invalid post']);

        $response = $this->vacancyCrudController->updateFree( $params );
        return ResponseHelper::build($response);
    }

    public function updatePaid( WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request["user_id"];

        if(get_post_status( $params['vacancy_id']) === false) return ResponseHelper::build(['status' => 400, 'message' => 'invalid post']);

        $response = $this->vacancyCrudController->updatePaid( $params );
        return ResponseHelper::build($response);
    }

    public function trash( WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request["user_id"];
        $response = $this->vacancyCrudController->trash($params);
        return ResponseHelper::build( $response );
    }

    private function _send_mail_when_make_vacancy($response, $params)
    {
        if($response['status'] === 201) {
            $vacancy_name = $params['name'];
            $user = get_user_by('id', $params['user_id']);

            $args = [
                'vacancy_title' => $vacancy_name
            ];

            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );

            $site_title = get_bloginfo('name');
            $content = Email::render_html_email('confirmation-jobpost-company.php', $args);
            wp_mail($user->user_email, "Bevestiging plaatsing vacature - $site_title", $content, $headers);
        }
    }
    
    
    
    /**
     * repostJob
     *
     * @param  mixed $request
     * @return WP_REST_Response
     */
    public function repostJob(WP_REST_Request $request) : WP_REST_Response {
        $params = $request->get_params();
        $response = $this->vacancyCrudController->repost($params);
        return ResponseHelper::build( $response );
    }
}
