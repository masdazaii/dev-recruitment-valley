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
            'hideEmpty' => isset($parameters['hideEmpty']) && $parameters['hideEmpty'] === 'true' ? true : false
        ];
        $termData = $this->_setResponse($this->termModel->selectAllTerm($filters));

        return [
            "status" => 200,
            "message" => $this->_message->get('vacancy.term.get_term_success'),
            "data" => $termData
        ];
    }

    /** The 2 above is merged to 1 function */
    public function getSpesificTaxonomyTerm($parameters)
    {
        /** Get Term */
        // $terms = get_terms([
        //     'taxonomy' => $parameters['taxonomy'],
        //     'hide_empty' => $parameters['hideEmpty'] ?? false
        // ]);

        /** Changes Start Here */
        $termStatusOpen = get_term_by('slug', 'open', 'status', 'OBJECT');
        $filters = [
            'post_status' => $termStatusOpen->term_id,
            'hideEmpty' => isset($parameters['hideEmpty']) && $parameters['hideEmpty'] === 'true' ? true : false
        ];

        $terms = $this->_setResponse($this->termModel->selectTerm($parameters['taxonomy'], $filters), 'single');

        return [
            "status" => 200,
            "message" => $this->_message->get('vacancy.term.show_term_success'),
            "data" => $terms
        ];
    }

    public function testGetAllTerm()
    {
        /** Get Taxonomy */
        $taxonomies = get_object_taxonomies('vacancy', 'names');
        foreach ($taxonomies as $value) {
            /** Get Terms each taxonomy */
            // $termData[$value] = $this->_setResponse($this->termModel->selectInTerm($value, []));
            $termData[$value] = $this->_setResponse($this->termModel->selectInTerm($value, []));
        }

        return [
            "status" => 200,
            "message" => $this->_message->get('vacancy.term.show_term_success'),
            "data" => $termData
        ];
    }

    private function _setResponse($terms, $format = 'all')
    {
        $response = [];

        foreach ($terms as $key => $value) {
            $term = [
                'label' => $value->name,
                'value' => (int)$value->term_id,
                'total' => (int)$value->count
            ];
            if ($format == 'all') {
                $response[$value->taxonomy][] = $term;
            } else if ($format == 'single') {
                $response[] = $term;
            }
        }

        return $response;
    }
}
