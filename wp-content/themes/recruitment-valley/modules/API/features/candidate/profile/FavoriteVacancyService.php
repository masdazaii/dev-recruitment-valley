<?php

namespace Candidate\Profile;

use WP_REST_Request;
use ResponseHelper;
use Constant\Message;

class FavoriteVacancyService
{
    protected $_message;
    public $favoriteVacancyController;

    public function __construct()
    {
        $this->favoriteVacancyController = new FavoriteVacancyController;
        $this->_message = new Message();
    }

    public function addFavoriteVacancy(WP_REST_Request $request)
    {
        $validateVacancy = $this->_validate_vacancy($request->get_param('vacancyId'));
        if (!$validateVacancy) {
            return ResponseHelper::build([
                "message" => $this->_message->get('candidate.favorite.vacancy_not_found'),
                "data" => $request->get_param('vacancyId'),
                "status" => 400,
            ]);
        }

        $body = $request->get_params();
        $response = $this->favoriteVacancyController->store($body);
        return ResponseHelper::build($response);
    }

    public function list(WP_REST_Request $request)
    {
        $body = $request->get_params();
        $response = $this->favoriteVacancyController->list($body);
        return ResponseHelper::build($response);
    }

    public function destroy(WP_REST_Request $request)
    {
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
