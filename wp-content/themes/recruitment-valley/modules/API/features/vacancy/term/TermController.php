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
        $termStatusOpen = get_term_by('slug', 'open', 'status', 'OBJECT');

        /** Changes Start Here */
        $filters = [
            'post_status' => $termStatusOpen->term_id,
            'hideEmpty' => $parameters['hideEmpty'] ?? false
        ];
        $termData = $this->_setResponse($this->termModel->selectAllTerm($filters));

        return [
            "status" => 200,
            "message" => $this->_message->get('vacancy.term.get_term_success'),
            // "filters" => $filters,
            "data" => $termData
        ];
    }

    /** The 2 above is merged to 1 function */
    public function getSpesificTaxonomyTerm($parameters)
    {
        /** Get Term */
        $terms = get_terms([
            'taxonomy' => $parameters['taxonomy']
        ]);

        return [
            "status" => 200,
            "message" => $this->_message->get('vacancy.term.show_term_success'),
            "data" => $this->_setResponse($terms)
        ];
    }

    private function _setResponse($terms)
    {
        $response = [];

        foreach ($terms as $key => $value) {
            $term = [
                'label' => $value->name,
                'value' => (int)$value->term_id,
                'total' => (int)$value->count
            ];

            switch ($value->taxonomy) {
                case 'sector':
                    $response['sector'][] = $term;
                    break;
                case 'type':
                    $response['employmentType'][] = $term;
                    break;
                case 'role':
                    $response['role'][] = $term;
                    break;
                case 'education':
                    $response['education'][] = $term;
                    break;
                case 'working-hours':
                    $response['workingHours'][] = $term;
                    break;
                case 'location':
                    $response['location'][] = $term;
                    break;
                case 'experiences':
                    $response['experiences'][] = $term;
                    break;
            }
        }

        return $response;
    }
}
