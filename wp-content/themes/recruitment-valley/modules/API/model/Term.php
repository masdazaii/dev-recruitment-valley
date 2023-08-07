<?php

namespace Model;

class Term
{
    public $term;
    public $arguments;

    public function __construct()
    {
        $this->arguments = [
            'orderby' => 'id',
            'order' => 'asc',
            'hide_empty' => false,
            'include' => [],
            'exclude' => [],
            'fields'  => 'all'
        ];
    }

    // public function getTerm()
    // {

    // }

    public function selectTerm($taxonomy, $filters)
    {
        if (!$taxonomy) {
            return;
        }

        $theArguments = $this->_setArguments($taxonomy, $filters);

        $results = get_terms($theArguments);

        return $results;
    }

    private function _setArguments($taxonomy, $filters)
    {
        $this->arguments['taxonomy'] = $taxonomy;

        foreach ($filters as $key => $value) {
            $this->arguments[$key] = $value ?? $this->arguments[$key];
        }

        return $this->arguments;
    }
}
