<?php

namespace Vacancy\Term;

use Constant\Message;
use Model\Term;
use Model\Taxonomy;

class VacancyTermController
{
    private $_message;
    private $termModel;

    public function __construct()
    {
        $this->_message = new Message;
        $this->termModel = new Term;
    }

    public function getAllTerm($parameters)
    {
        /** Get Taxonomy */
        $taxonomies = get_object_taxonomies('vacancy', 'names');
        foreach ($taxonomies as $value) {
            /** Get Terms each taxonomy */
            $termData[$value] = $this->_setResponse($this->termModel->selectTerm($value, []));
        }

        return [
            "status" => 200,
            "message" => $this->_message->get('vacancy.term.get_term_success'),
            "data" => $termData
        ];
    }

    private function _setResponse($terms)
    {
        $response = [];

        foreach ($terms as $key => $value) {
            $term = [
                'label' => $value->name,
                'value' => $value->term_id,
                'total' => $value->count
            ];

            array_push($response, $term);
        }

        return $response;
    }
}
