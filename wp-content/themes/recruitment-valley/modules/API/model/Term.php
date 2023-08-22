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

    public function selectTerm($taxonomy, $filters = [])
    {
        if (!$taxonomy) {
            return;
        }

        /** Old one start here */
        // $theArguments = $this->_setArguments($taxonomy, $filters);
        // $results = get_terms($theArguments);

        /** changes start here */
        global $wpdb;
        $status = $filters['post_status'] ?? '24';
        $results = $wpdb->get_results("SELECT wpt.term_id, wpt.name, wpt.slug, COUNT(wptr.object_id) as count FROM wp_terms as wpt LEFT JOIN wp_term_relationships as wptr ON wpt.term_id = wptr.term_taxonomy_id WHERE wpt.term_id IN ( SELECT term_id FROM wp_term_taxonomy WHERE taxonomy = '$taxonomy') AND wptr.object_id IN ( SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id = '$status') GROUP BY wpt.term_id", OBJECT);

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
