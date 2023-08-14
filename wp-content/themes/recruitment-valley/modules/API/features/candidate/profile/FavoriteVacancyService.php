<?php

namespace Candidate\Profile;

use WP_REST_Request;
use ResponseHelper;
use Constant\Message;
use Vacancy\VacancyResponse;
use Helper\ValidationHelper;

class FavoriteVacancyService
{
    protected $_message;
    public $favoriteVacancyController;
    public $vacancyResponse;

    public function __construct()
    {
        $this->_message = new Message();
        $this->favoriteVacancyController = new FavoriteVacancyController;
        $this->vacancyResponse = new VacancyResponse;
    }

    public function addFavoriteVacancy(WP_REST_Request $request)
    {
        $extraRules = [
            "vacancyId" => ["not_exists:user/meta/favorite_vacancy," . $request['user_id']]
        ];

        $validator = new ValidationHelper('addFavorite', $request->get_params(), $extraRules);

        if (!$validator->validate()) {
            $errors = $validator->getErrors();
            return ResponseHelper::build([
                'message' => $this->_message->get('candidate.favorite.vacancy_not_found'),
                'errors' => $errors,
                'status' => 400
            ]);
        }

        // $validateVacancy = $this->_validate_vacancy($request->get_param('vacancyId'));
        // if (!$validateVacancy) {
        //     return ResponseHelper::build([
        //         "message" => $this->_message->get('candidate.favorite.vacancy_not_found'),
        //         "status" => 400,
        //     ]);
        // }

        $validator->sanitize();
        $body = $validator->getData();

        $body = $request->get_params();
        $response = $this->favoriteVacancyController->store($body);
        return ResponseHelper::build($response);
    }

    public function list(WP_REST_Request $request)
    {
        $body = $request->get_params();
        $response = $this->favoriteVacancyController->list($body);
        $this->vacancyResponse->setCollection($response["data"]);
        $formattedResponse = $this->vacancyResponse->formatFavorite();
        $response["data"] = $formattedResponse;
        return ResponseHelper::build($response);
    }

    public function destroy(WP_REST_Request $request)
    {
        $validateVacancy = $this->_validate_vacancy($request->get_param('vacancyId'));
        if (!$validateVacancy) {
            return ResponseHelper::build([
                "message" => $this->_message->get('candidate.favorite.vacancy_not_found'),
                "status" => 404,
            ]);
        }

        $body = $request->get_params();
        $response = $this->favoriteVacancyController->destroy($body);
        return ResponseHelper::build($response);
    }

    protected function _validate_vacancy($vacancyID)
    {
        if (!isset($vacancyID)) {
            return false;
        }

        $checkVacancy = get_posts(sanitize_text_field($vacancyID));

        return $checkVacancy;
    }
}
