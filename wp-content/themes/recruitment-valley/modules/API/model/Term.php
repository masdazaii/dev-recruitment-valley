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

        global $wpdb;
        $status = $filters['post_status'] ?? '24';
        // $results = $wpdb->get_results("SELECT wpt.term_id, wpt.name, wpt.slug, COUNT(wptr.object_id) as count FROM wp_terms as wpt LEFT JOIN wp_term_relationships as wptr ON wpt.term_id = wptr.term_taxonomy_id WHERE wpt.term_id IN ( SELECT term_id FROM wp_term_taxonomy WHERE taxonomy = '$taxonomy') AND wptr.object_id IN ( SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id = '$status') GROUP BY wpt.term_id", OBJECT);

        if ($filters['hideEmpty']) {
            $results = $wpdb->get_results("SELECT wpt.term_id, wpt.name, wpt.slug, wptt.taxonomy, wptr.count FROM wp_terms as wpt LEFT JOIN ( SELECT wptr.term_taxonomy_id as term_taxonomy_id, count(wptr.object_id) as count FROM wp_term_relationships as wptr WHERE wptr.object_id IN ( SELECT object_id from wp_term_relationships WHERE term_taxonomy_id = '$status' ) GROUP BY wptr.term_taxonomy_id ) as wptr ON wpt.term_id = wptr.term_taxonomy_id LEFT JOIN wp_term_taxonomy as wptt ON wpt.term_id = wptt.term_taxonomy_id WHERE wpt.term_id IN ( SELECT term_id FROM wp_term_taxonomy WHERE taxonomy = '$taxonomy' ) AND ( wptr.count IS NOT NULL OR wptr.count > 0 )");
        } else {
            $results = $wpdb->get_results("SELECT wpt.term_id, wpt.name, wpt.slug, wptt.taxonomy, wptr.count FROM wp_terms as wpt LEFT JOIN ( SELECT wptr.term_taxonomy_id as term_taxonomy_id, count(wptr.object_id) as count FROM wp_term_relationships as wptr WHERE wptr.object_id IN ( SELECT object_id from wp_term_relationships WHERE term_taxonomy_id = '$status' ) GROUP BY wptr.term_taxonomy_id ) as wptr ON wpt.term_id = wptr.term_taxonomy_id LEFT JOIN wp_term_taxonomy as wptt ON wpt.term_id = wptt.term_taxonomy_id WHERE wpt.term_id IN ( SELECT term_id FROM wp_term_taxonomy WHERE taxonomy = '$taxonomy' )");
        }

        return $results;
    }

    public function selectAllTerm($filters = [])
    {
        global $wpdb;
        $status = $filters['post_status'] ?? '24';

        if ($filters['hideEmpty']) {
            $results = $wpdb->get_results("SELECT wpt.term_id, wpt.name, wpt.slug,wptt.taxonomy, wptr.count FROM wp_terms as wpt LEFT JOIN ( SELECT wptr.term_taxonomy_id as term_taxonomy_id, count(wptr.object_id) as count FROM wp_term_relationships as wptr WHERE wptr.object_id in ( select object_id from wp_term_relationships WHERE term_taxonomy_id = $status ) GROUP BY wptr.term_taxonomy_id ) as wptr ON wpt.term_id = wptr.term_taxonomy_id LEFT JOIN wp_term_taxonomy as wptt ON wpt.term_id = wptt.term_taxonomy_id WHERE wpt.term_id NOT IN ( SELECT term_id FROM wp_term_taxonomy WHERE taxonomy = 'status' OR taxonomy = 'payment_status' OR taxonomy = 'category' ) AND ( wptr.count IS NOT NULL OR wptr.count > 0 ) GROUP BY wpt.term_id");
        } else {
            $results = $wpdb->get_results("SELECT wpt.term_id, wpt.name, wpt.slug,wptt.taxonomy, wptr.count FROM wp_terms as wpt LEFT JOIN ( SELECT wptr.term_taxonomy_id as term_taxonomy_id, count(wptr.object_id) as count FROM wp_term_relationships as wptr WHERE wptr.object_id in ( select object_id from wp_term_relationships WHERE term_taxonomy_id = $status ) GROUP BY wptr.term_taxonomy_id ) as wptr ON wpt.term_id = wptr.term_taxonomy_id LEFT JOIN wp_term_taxonomy as wptt ON wpt.term_id = wptt.term_taxonomy_id WHERE wpt.term_id NOT IN ( SELECT term_id FROM wp_term_taxonomy WHERE taxonomy = 'status' OR taxonomy = 'payment_status' OR taxonomy = 'category' ) GROUP BY wpt.term_id");
        }

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
