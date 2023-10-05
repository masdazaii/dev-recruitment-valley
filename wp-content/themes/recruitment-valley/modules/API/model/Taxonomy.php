<?php

namespace Model;

use WP_Taxonomy;

class Taxonomy
{
    public $taxonomy;

    public function __construct($taxonomy)
    {
        $tax = get_taxonomy($taxonomy);
        if ($tax) {
            $this->taxonomy = $tax;
        }
    }

    public function isTaxonomy()
    {
        return $this->taxonomy instanceof WP_Taxonomy;
    }

    public function getName()
    {
        return $this->taxonomy->name;
    }

    public function getLabel()
    {
        return $this->taxonomy->label;
    }
}
