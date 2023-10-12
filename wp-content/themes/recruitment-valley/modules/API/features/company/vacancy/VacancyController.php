<?php

namespace Company\Vacancy;

use Constant\Message;
use Model\Company;
use Model\Term;
use Vacancy\Vacancy;
use Candidate\Profile\Candidate;
use Model\Applicant;
use WP_Post;
use WP_Query;
use WP_Term;
use Helper\DateHelper;
use Helper\StringHelper;

class VacancyController
{

    public $vacancyModel;
    public $termModel;
    public $company;
    private $_message;

    public function __construct()
    {
        $this->company = new Company;
        $this->vacancyModel = new Vacancy;
        $this->termModel = new Term;
        $this->_message = new Message;
    }

    public function getByStatus($request)
    {
        $status = $request["status"];
        $vacancies = $this->vacancyModel->getByStatus($status);
        return [
            "status" => 200,
            "data" => $vacancies,
            "message" => $this->_message->get("vacancy.get_all")
        ];
    }

    public function getTermCount($request)
    {
        $this->company->setUserId($request["user_id"]);

        return [
            "status" => 200,
            "data" => [
                "open" => $this->company->getVacancyByStatus('open'),
                "close" => $this->company->getVacancyByStatus('close'),
                "declined" => $this->company->getVacancyByStatus('declined'),
                "processing" => $this->company->getVacancyByStatus('processing'),
            ],
        ];
    }

    public function getAll($request)
    {
        $vacancy = new Vacancy;
        $filters = [
            'page' => $request['page'] ?? 1,
            'postPerPage' => $request['perPage'] ?? 10,
            'search' => $request['search'] ?? null,
        ];

        $taxonomyFilters = [
            'status'     => array_key_exists('status', $request) ? explode(',', $request['status']) : NULL,
        ];

        $offset = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['postPerPage']));

        $args = [
            "post_type" => $vacancy->vacancy,
            "author__in" => [$request["user_id"]],
            "posts_per_page" => $filters['postPerPage'],
            "offset" => $offset,
            "order" => "ASC",
            "post_status" => "publish",
            "search_columns" => ['post_title']
        ];

        foreach ($taxonomyFilters as $key => $value) {
            if ($value && $value !== null && !empty($value)) {
                if (!array_key_exists('tax_query', $args)) {
                    $args['tax_query'] = [
                        "relation" => 'OR'
                    ];
                }

                array_push($args['tax_query'], [
                    'taxonomy' => $key,
                    'field'     => 'slug',
                    'terms'     => $value,
                    'compare'  => 'IN'
                ]);
            }
        }

        if (array_key_exists('search', $filters) && $filters['search'] !== '' && isset($filters['search'])) {
            $args['s'] = $filters['search'];
        }

        $vacancies = new WP_Query($args);

        return [
            'message' => $this->_message->get('vacancy.get_all'),
            'data'    => $vacancies->posts,
            'meta'    => [
                'currentPage' => (int) $filters['page'],
                'totalPage' => $vacancies->max_num_pages
            ],
            'status'  => 200
        ];
    }

    public function get($request)
    {
        $vacancy = new Vacancy;

        $vacancyId = $request['vacancy_id'];

        $vacancy = get_post($vacancyId);

        if ($vacancy instanceof WP_Post) {
            return [
                "status" => 200,
                "message" => $this->_message->get("vacancy.get_all"),
                "data" => $vacancy
            ];
        } else {
            return [
                "status" => 404,
                "message" => $this->_message->get("vacancy.not_found"),
            ];
        }
    }

    public function listApplicants($request)
    {
        try {
            $vacancy = new Vacancy($request['vacancy']);  // key "vacancy" is the last uri segment that meant to be vacancy id

            /** Validate : if this vacancy is belong to current user */
            if ($vacancy->getAuthor() != $request['user_id']) {
                return [
                    'status'    => 400,
                    'message'   => $this->_message->get('auth.unauthorize')
                ];
            }

            $company = new Company($vacancy->getAuthor());
            $applicants = $vacancy->getApplicants();

            /** Get Applicants candidate data */
            $applicantsCandidate = [];
            if (is_array($applicants)) {
                foreach ($applicants as $application) {
                    try {
                        $applicantId = $application->ID;
                        $application = new Applicant($applicantId);
                        $candidate   = new Candidate($application->getCandidate());

                        $candidateCV = $candidate->getCv() ?? NULL;
                        $applyDate   = $application->getApplyDate() ?? NULL;

                        $applicantsCandidate[] = [
                            'id'        => $applicantId,
                            'firstName' => $candidate->getFirstName(),
                            'lastName'  => $candidate->getLastName(),
                            'image'     => $candidate->getImage(),
                            'email'     => $candidate->getEmail(),
                            'country'   => $candidate->getCountry(),
                            'city'      => $candidate->getCity(),
                            'phoneNumber'       => $candidate->getPhoneNumber(),
                            'phoneNumberCode'   => $candidate->getPhoneNumberCode(),
                            'linkedin'  => $candidate->getLinkedinPage(),
                            'cv'        => [
                                'url'       => isset($candidateCV) && is_array($candidateCV) && array_key_exists("url", $candidateCV) ? $candidateCV["url"] : null,
                                'fileName'  => isset($candidateCV) && is_array($candidateCV) && array_key_exists("filename", $candidateCV) ? $candidateCV["filename"] : null,
                                'createdAt' => isset($candidateCV) && is_array($candidateCV) && array_key_exists("date", $candidateCV) ? strtotime($candidateCV["date"]) : null,
                            ],
                            'appliedAt' => isset($applyDate) ? DateHelper::doLocale(strtotime($applyDate), 'nl_NL', 'dd/m/yyyy') : null,
                        ];
                    } catch (\Exception $e) {
                        error_log($e->getMessage());
                    }
                }
            }

            /** Set social media response */
            $socialMedia = ["facebook", "linkedin", "instagram", "twitter"];

            $socialMediaResponse = [];
            foreach ($socialMedia as $key => $socmed) {
                $socialMediaResponse[$key] = [
                    "id" => $key + 1,
                    "type" => $socmed,
                    "url" => $vacancy->getSocialMedia($socmed) != null || $vacancy->getSocialMedia($socmed) != "" ? $vacancy->getSocialMedia($socmed) : $company->getSocialMedia($socmed)
                ];
            }

            /** Get video url */
            $jobVideo = $vacancy->getVideoUrl();
            $videoUrl = "";
            if ($jobVideo != "") {
                $videoUrl = strpos($vacancy->getVideoUrl(), "youtu") ? ["type" => "url", "url" => StringHelper::getYoutubeID($vacancy->getVideoUrl())] : ["type" => "file", "url" => $vacancy->getVideoUrl()];
            } else {
                $videoUrl = strpos($company->getVideoUrl(), "youtu") ? ["type" => "url", "url" => StringHelper::getYoutubeID($company->getVideoUrl())] : ["type" => "file", "url" => $company->getVideoUrl()];
            }

            return [
                "status"    => 200,
                "message"   => $this->_message->get('vacancy.get_applicant_success'),
                "data"      => [
                    "vacancy"    => [
                        "id"        => $request['vacancy'],
                        "isPaid"    => $vacancy->getIsPaid(),
                        "shortDescription" => $vacancy->getTaxonomy(false),
                        "title"         => $vacancy->getTitle(),
                        "socialMedia"   => $socialMediaResponse,
                        "contents"      => [
                            "description"   => $vacancy->getDescription(),
                            "term"          => $vacancy->getTerm(),
                        ],
                        "country"       => $vacancy->getCountry(),
                        "countryCode"   => $vacancy->getCountryCode(),
                        "city"          => $vacancy->getCity(),
                        "externalUrl"   => $vacancy->getExternalUrl(),
                        "placementAddress" => $vacancy->getPlacementAddress(),
                        "videoId" => $videoUrl,
                        "gallery" => $vacancy->getGallery(),
                        "reviews" => $vacancy->getReviews(),
                        "applicationProcessTitle"       => $vacancy->getApplicationProcessTitle(),
                        "applicationProcessDescription" => $vacancy->getApplicationProcessDescription(),
                        "steps"         => $vacancy->getApplicationProcessStep(),
                        "salaryStart"   => $vacancy->getSalaryStart(),
                        "salaryEnd"     => $vacancy->getSalaryEnd(),
                        "postedDate"    => $vacancy->getPublishDate("d-m-Y H:i"),
                        "expiredDate"   => $vacancy->getExpiredAt("d-m-Y H:i"),
                        "longitude"     => $vacancy->getPlacementAddressLongitude(),
                        "latitude"      => $vacancy->getPlacementAddressLatitude(),
                        "applicationProcedure" => [
                            "title" => $vacancy->getApplicationProcessTitle(),
                            "text"  => $vacancy->getApplicationProcessDescription(),
                            "steps" => $vacancy->getApplicationProcessStep()
                        ]
                    ],
                    "applicants" => $applicantsCandidate
                ]
            ];
        } catch (\WP_Error $error) {
            error_log('ListApplications WP_Error: ' . $error->get_error_message());

            return [
                "status"    => 500,
                "message"   => $this->_message->get('system.overall_failed'),
            ];
        } catch (\Exception $e) {
            error_log('ListApplications Exception: ' . $e->getMessage());

            return [
                "status"    => 500,
                "message"   => $this->_message->get('system.overall_failed'),
            ];
        } catch (\Throwable $th) {
            error_log('ListApplications Throwable: ' . $th->getMessage());
            return [
                "status"    => 500,
                "message"   => $this->_message->get('system.overall_failed'),
            ];
        }
    }

    public function showApplicants($request)
    {
        try {
            $application = new Applicant($request['application']);
            $candidate   = new Candidate($application->getCandidate());

            return [
                "status"    => 200,
                "message"   => $this->_message->get('vacancy.get_application_success'),
                "data"      => [
                    "id"    => $request['application'],
                    "applicant" => [
                        'id'        => $request['application'],
                        'firstName' => $candidate->getFirstName(),
                        'lastName'  => $candidate->getLastName(),
                        'image'     => $candidate->getImage(),
                        'email'     => $candidate->getEmail(),
                        'country'   => $candidate->getCountry(),
                        'city'      => $candidate->getCity(),
                        'phoneNumber'       => $candidate->getPhoneNumber(),
                        'phoneNumberCode'   => $candidate->getPhoneNumberCode(),
                        'linkedin'  => $candidate->getLinkedinPage(),
                        'cv'        => [
                            'url'       => $candidate->getCv() ? $candidate->getCv()["url"] : null,
                            'fileName'  => $candidate->getCv() ? $candidate->getCv()["filename"] : null,
                            'createdAt' => strtotime($candidate->getCv()["date"]),
                        ],
                        'appliedAt' => $application->getApplyDate(),
                    ],
                    "motivationLetter" => $application->getCoverLetter(),
                ]
            ];
        } catch (\WP_Error $error) {
            error_log('ListApplications WP_Error: ' . $error->get_error_message());

            return [
                "status"    => 500,
                "message"   => $this->_message->get('system.overall_failed'),
            ];
        } catch (\Exception $e) {
            error_log('ListApplications Exception: ' . $e->getMessage());

            return [
                "status"    => 500,
                "message"   => $this->_message->get('system.overall_failed'),
            ];
        } catch (\Throwable $th) {
            error_log('ListApplications Throwable: ' . $th->getMessage());
            return [
                "status"    => 500,
                "message"   => $this->_message->get('system.overall_failed'),
            ];
        }
    }
}
