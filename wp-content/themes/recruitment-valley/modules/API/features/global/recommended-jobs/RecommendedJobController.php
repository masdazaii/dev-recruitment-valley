<?php

use Vacancy\Vacancy;

class RecommendedJobController
{

    public function get( $request )
    {
        $filters = [
            'page' => isset($request['page']) ? intval($request['page']) : 1,
            'perPage' =>  !empty($request['perPage']) || isset($request['perPage']) ? (int) $request['perPage'] : 6,
        ];

        $vacancy = new Vacancy;
        $recommendedJobs = $vacancy->getRecommendedJobs();

        // Calculate total number of items and pages
        $totalItems = count($recommendedJobs);
        $totalPages = ceil($totalItems / $filters['perPage']);

        // Validate current page value
        $page = max(1, min($filters['perPage'], $totalPages));

        // Calculate start and end indexes for the current page
        $startIndex = ($filters["page"] - 1) * $filters['perPage'];
        $endIndex = min($startIndex + $filters['perPage'] - 1, $totalItems - 1);

        // Extract the items for the current page
        $currentPageItems = array_slice($recommendedJobs, $startIndex, $endIndex - $startIndex + 1);

        // Return the items for the current page along with pagination information
        return [
            'message' => "success get recommendation jobs",
            'data'    => $currentPageItems,
            'meta'    => [
                'currentPage'   => isset($filters['page']) ? intval($filters['page']) : 1,
                'totalPage'     => $totalPages,
                'totalResult'   => $totalItems
            ],
            'status'  => 200
        ];
    }

}