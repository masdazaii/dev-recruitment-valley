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
use Constant\Message;
use Helper\ValidationHelper;
use Model\Option;

class VacancyCrudService
{
    public $vacancyCrudController;

    public $vacancyResponse;

    private $_message;

    public function __construct()
    {
        $this->_message = new Message();
        $this->vacancyCrudController = new VacancyCrudController;
        $this->vacancyResponse = new VacancyResponse;
    }

    public function getAll(WP_REST_Request $request)
    {
        $getAllRequest = new CandidateVacanciesRequest($request);
        if (!$getAllRequest->validate()) {
            $errors = $getAllRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $params = $getAllRequest->getData();
        $response = $this->vacancyCrudController->getAll($params);
        $this->vacancyResponse->setCollection($response["data"]);

        $formattedResponse = $this->vacancyResponse->format();

        if (isset($params['placementAddress'])) {
            if ($params['placementAddress'] !== "") {
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
        if (!$singleVacancyRequest->validate()) {
            $errors = $singleVacancyRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $singleVacancyRequest->sanitize();
        $params = $singleVacancyRequest->getData();
        $response = $this->vacancyCrudController->get($params);
        $this->vacancyResponse->setUserPayload($tokenPayload);

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
        if (!$createFreeJobRequest->validate()) {
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

    public function createPaidJob(WP_REST_Request $request)
    {
        $createPaidJobRequest = new CreatePaidJobRequest($request);
        if (!$createPaidJobRequest->validate()) {
            $errors = $createPaidJobRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $createPaidJobRequest->sanitize();

        $params = $createPaidJobRequest->getData();
        $params["user_id"] = $request["user_id"];
        $response = $this->vacancyCrudController->createPaid($params);

        $this->_send_mail_when_make_vacancy($response, $params);

        return ResponseHelper::build($response);
    }

    public function update(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request["user_id"];

        if (get_post_status($params['vacancy_id']) === false) return ResponseHelper::build(['status' => 400, 'message' => $this->_message->get('other.invalid_post')]);

        $response = $this->vacancyCrudController->update($params);
        return ResponseHelper::build($response);
    }

    public function updateFree(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request["user_id"];

        if (get_post_status($params['vacancy_id']) === false) return ResponseHelper::build(['status' => 400, 'message' => $this->_message->get('other.invalid_post')]);

        /** Changes start here */
        $validator = new ValidationHelper('vacancyUpdateFree', $request->get_params());

        if (!$validator->tempValidate()) {
            $errors = $validator->getErrors();
            $keys = array_keys($errors);
            $message = $errors[$keys[0]][0] ?? $this->_message->get('vacancy.update.paid.fail');
            return ResponseHelper::build([
                // 'message' => $this->_message->get('vacancy.update.paid.fail'),
                // 'errors' => $errors,
                'message' => $message,
                'status' => 400
            ]);
        }

        $params = $validator->tempSanitize();
        $params = $validator->getData();
        $params["user_id"] = $request["user_id"];
        /** Changes end here */

        $response = $this->vacancyCrudController->updateFree($params);
        return ResponseHelper::build($response);
    }

    public function updatePaid(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request["user_id"];

        if (get_post_status($params['vacancy_id']) === false) return ResponseHelper::build(['status' => 400, 'message' => $this->_message->get('other.invalid_post')]);

        /** Changes start here */
        $validator = new ValidationHelper('vacancyUpdatePaid', $request->get_params());

        if (!$validator->tempValidate()) {
            $errors = $validator->getErrors();
            $keys = array_keys($errors);
            $message = $errors[$keys[0]][0] ?? $this->_message->get('vacancy.update.paid.fail');
            return ResponseHelper::build([
                // 'message' => $this->_message->get('vacancy.update.paid.fail'),
                // 'errors' => $errors,
                'message' => $message,
                'status' => 400
            ]);
        }

        $params = $validator->tempSanitize();
        $params = $validator->getData();
        $params["user_id"] = $request["user_id"];
        /** Changes end here */

        $response = $this->vacancyCrudController->updatePaid($params);
        return ResponseHelper::build($response);
    }

    public function trash(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request["user_id"];
        $response = $this->vacancyCrudController->trash($params);
        return ResponseHelper::build($response);
    }

    private function _send_mail_when_make_vacancy($response, $params)
    {
        if ($response['status'] === 201) {
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

            /** Email to admin */
            /** Get recipient email */
            // $adminEmail = get_option('admin_email', false);
            $optionModel = new Option();
            $approvalMainRecipient  = $optionModel->getEmailApprovalMainAddress();
            $approvalCCRecipients = $optionModel->getEmailApprovalCC();
            $approvalBCCRecipients = $optionModel->getEmailApprovalBCC();

            /** Set headers */
            $headers[] = 'Content-Type: text/html; charset=UTF-8';

            /** Set cc / bcc */
            if (isset($approvalCCRecipients) && is_array($approvalCCRecipients)) {
                $ccRecipient = [];
                foreach ($approvalCCRecipients as $recipient) {
                    $ccRecipient[] = 'Cc: ' . $recipient['rv_email_approval_cc_address'];
                }
                array_unique($ccRecipient);
                $headers = array_merge($headers, $ccRecipient);
            }

            if (isset($approvalBCCRecipients) && is_array($approvalBCCRecipients)) {
                $bccRecipient = [];
                foreach ($approvalBCCRecipients as $recipient) {
                    $bccRecipient[] = 'Bcc: ' . $recipient['rv_email_approval_bcc_address'];
                }
                array_unique($bccRecipient);
                $headers = array_merge($headers, $bccRecipient);
            }

            error_log(json_encode($headers));

            $approvalArgs = [
                // 'url' => menu_page_url('import-approval'),
            ];

            $content = Email::render_html_email('admin-new-vacancy-approval.php', $approvalArgs);
            wp_mail($approvalMainRecipient, ($this->_message->get('vacancy.approval_subject') ?? 'Approval requested - RecruitmentValley'), $content, $headers);
        }
    }



    /**
     * repostJob
     *
     * @param  mixed $request
     * @return WP_REST_Response
     */
    public function repostJob(WP_REST_Request $request): WP_REST_Response
    {
        $params = $request->get_params();
        $response = $this->vacancyCrudController->repost($params);
        return ResponseHelper::build($response);
    }

    public function export(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->vacancyCrudController->export($params);
        return ResponseHelper::build($response);
    }
}
