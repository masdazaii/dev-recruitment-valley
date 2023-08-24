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
            "data" => $this->_setResponse($terms, 'single')
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
