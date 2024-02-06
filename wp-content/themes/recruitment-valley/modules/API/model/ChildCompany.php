<?php

namespace Model;

use Exception;
use WP_Error;
use WP_Post;
use Model\Company;
use WP_Query;

/**
 * CPT Child company Model class
 *
 * IMPORTANT NOTE
 * Inherit Company Model only for the acf meta property.
 * Function won't work since this is CPT and company is a user role.
 *
 * $this->user is used as post object
 * $this->user_id is used as post_id
 * it written as user_id because Company is a user Model
 *
 */
class ChildCompany extends Company
{
    public const post_type = 'child-company';

    public const acf_child_company_email    = 'rv_urecruiter_child_company_email';
    public const acf_child_company_owner    = 'rv_urecruiter_child_company_owner';

    public const acf_child_company_uuid     = 'rv_urecruiter_child_company_uuid';

    public function __construct(Mixed $childCompany = null)
    {
        $this->isChild = true;

        if ($childCompany) {
            if (is_numeric($childCompany)) {
                $this->user_id  = $childCompany;
                $this->user     = get_post($childCompany);
            } else if ($childCompany instanceof WP_Post) {
                $this->user_id  = $childCompany->ID;
                $this->user     = $childCompany;
            }
        }
    }

    /**
     * Create / insert new Post in child-company cpt function
     *
     * @param array $data
     * @return self
     */
    public static function insert(array $data): self
    {
        $postModel      = new PostModel();
        $childCompany   = $postModel->create($data);
        if ($childCompany) {
            if ($childCompany instanceof WP_Error) {
                throw $childCompany;
            }

            return new self($childCompany);
        } else {
            return false;
        }
    }

    /**
     * Select Post in child-company cpt function
     *
     * @param array $filters
     * @param array $args
     * @return WP_Query
     */
    public static function select(array $filters = [], array $args = []): WP_Query
    {
        $args       = self::setArguments($filters, $args);
        $results    = new WP_Query($args);

        return $results;
    }

    /**
     * Set arguments for select function function
     *
     * @param array $filters
     * @param array $args
     * @return array
     */
    private function setArguments(array $filters = [], array $args = []): array
    {
        if (empty($args)) {
            $args = [
                "post_type"         => self::post_type,
                "posts_per_page"    => $filters['perPage'] ?? -1,
                "offset"            => $filters['offset'] ?? 0,
                "orderby"           => $filters['orderBy'] ?? "date",
                "order"             => $filters['sort'] ?? 'ASC',
                "post_status"       => "publish",
            ];
        }

        if (!empty($filters)) {
            if (array_key_exists('perPage', $filters)) {
                $args['posts_per_page'] = $filters['perPage'];
            }

            if (array_key_exists('offset', $filters)) {
                $args['offset'] = $filters['offset'];
            }

            if (array_key_exists('search', $filters)) {
                $args['search'] = $filters['search'];
            } else if (array_key_exists('s', $filters)) {
                $args['search'] = $filters['s'];
            }

            if (array_key_exists('search_columns', $filters)) {
                $args['search_columns'] = $filters['search_columns'];
            }

            if (array_key_exists('orderBy', $filters)) {
                if (is_array($filters['orderBy'])) {
                    $args['meta_key']   = $filters['orderBy']['key'];
                    $args['orderby']    = $filters['orderBy']['by'];

                    if (isset($filters['orderBy']['type'])) {
                        $args['meta_type']  = $filters['orderBy']['type'];
                    }
                } else {
                    $args['orderby'] = $filters['orderBy'];
                }
            }

            if (array_key_exists('sort', $filters)) {
                $args['order'] = $filters['sort'];
            }

            if (array_key_exists('include', $filters)) {
                if (is_array($filters['include'])) {
                    $args['include'] = $filters['include'];
                } else {
                    $args['include'] = [$filters['include']];
                }
            }

            if (array_key_exists('exclude', $filters)) {
                if (is_array($filters['exclude'])) {
                    $args['exclude'] = $filters['exclude'];
                } else {
                    $args['exclude'] = [$filters['exclude']];
                }
            }

            if (array_key_exists('fields', $filters)) {
                $args['fields'] = $filters['fields'];
            }

            if (array_key_exists('owner', $filters)) {
                $filters['meta'][] = [
                    'key'       => self::acf_child_company_owner,
                    'value'     => $filters['owner'],
                    'compare'   => '='
                ];

                $args['meta_query'] = $filters['meta'];
            }

            if (array_key_exists('meta', $filters)) {
                $args['meta_query'] = $filters['meta'];
            }

            if (array_key_exists('taxonomy', $filters)) {
                $args['tax_query'] = $filters['taxonomy'];
            }

            if (array_key_exists('postPerPage', $filters)) {
                $args['posts_per_page'] = $filters['postPerPage'];
            }

            if (array_key_exists('offset', $filters)) {
                $args['offset'] = $filters['offset'];
            }

            if (array_key_exists('orderBy', $filters)) {
                /** Other orderby value please look at WP_Query docs. */
                switch ($filters['orderBy']) {
                    case 'ID':
                    case 'id':
                        $args['orderby'] = 'ID';
                        break;
                    default:
                        $args['orderby'] = $filters['orderBy'];
                        break;
                }
            }

            if (array_key_exists('order', $filters)) {
                switch ($filters['order']) {
                    case 'DESC':
                    case 'desc':
                    case 'descending':
                        $args['order'] = 'DESC';
                        break;
                    case 'ASC':
                    case 'asc':
                    case 'ascending':
                    default:
                        $args['order'] = 'ASC';
                        break;
                }
            }

            if (array_key_exists('post_status', $filters)) {
                $args['post_status'] = $filters['post_status'];
            }

            if (array_key_exists('author', $filters)) {
                $args['author '] = $filters['author'];
            }

            if (array_key_exists('in', $filters)) {
                $args['post__in'] = $filters['in'];
            }
        }

        return $args;
    }

    /**
     * Find Post in child company cpt by given clue function
     *
     * @param String $by
     * @param Mixed $clue
     * @return self
     */
    public static function find(String $by, Mixed $clue)
    {
        if (in_array($by, ["id", "ID", "email", "login"])) {
            $user = get_post($clue);
            return new self($user);
        } else if (in_array($by, ["slug", "uuid"])) {
            $childCompany = new ChildCompany();
            if ($by == 'slug') {
            } else if ($by == 'uuid') {
                $filter = [];
            }
            $user = self::select($filter, []);
            if ($user->get_total() > 0) {
                return new self($user->get_results()[0]);
            } else {
                return new self();
            }
        } else {
            throw new Exception('First parameter must be one of : id | ID | slug | email | login.');
        }
    }

    /**
     * Getter acf value function
     *
     * Override method in Company Model with same name.
     *
     * @param String $key
     * @param boolean $single or formatted value
     * @return void
     */
    public function getProp(String $key, Bool $single = false, String $type = 'acf')
    {
        $this->checkID();

        if ($type == 'acf') {
            return get_field($key, $this->user_id, $single);
        } else {
            return get_post_meta($this->user_id, $key, $single);
        }
    }

    /**
     * Setter acf value function
     *
     * Override method in Company Model with same name.
     *
     * @param String $acf_field
     * @param boolean $single
     * @return mixed Int|Bool on false
     */
    public function setProp($acf_field, $value, $repeater = false, $type = 'acf'): mixed
    {
        $this->checkID();

        if ($type == 'meta') {
            return update_post_meta($this->user_id, $acf_field, $value);
        } else {
            return update_field($acf_field, $value, $this->user_id);
        }
    }

    /**
     * Set Child Company Name function.
     *
     * Override method in Company Model with same name.
     *
     * @return mixed Int|Bool on false
     */
    public function setName(String $value): mixed
    {
        return $this->setProp($this->name, $value, false);
    }

    /**
     * Get Child Company Name function.
     *
     * Override method in Company Model with same name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->user->title;
    }

    /**
     * Get Child Company Email function.
     *
     * Override method in Company Model with same name.
     *
     * @return mixed
     */
    public function getEmail()
    {
        return $this->getProp(self::acf_child_company_email, true, 'acf');
    }

    /**
     * Get Phone Number function
     *
     * Get Company phone number.
     * Either it :
     * - code only with parameter               : code
     * - number only with parameter             : number
     * - complete Phone number with parameter   : full
     *
     * default is full
     *
     * @param string $result
     * @return Mixed
     */
    public function getPhoneNumber($result = 'full')
    {
        $this->checkID();
        switch ($result) {
            case 'code':
            case 'phonecode':
            case 'phone-code':
            case 'code-only':
                return $this->getPhoneCode();
                break;
            case 'number':
            case 'phonenumber':
            case 'phone-number':
            case 'number-only':
                return $this->getPhone();
                break;
            case 'full':
            default:
                return $this->getPhoneCode() . ' ' . $this->getPhone();
                break;
        }
    }

    /**
     * Set child company owner function
     *
     * @param Int $value
     * @return mixed
     */
    public function setChildCompanyOwner(Int $value): mixed
    {
        return $this->setProp(self::acf_child_company_owner, $value, false, 'acf');
    }

    /**
     * Get Company Recruiter name function
     *
     * @return mixed
     */
    public function getChildCompanyOwner()
    {
        return $this->getProp(self::acf_child_company_owner, true, 'acf');
    }

    /**
     * Set child company uuid function
     *
     * @param Int $value
     * @return mixed
     */
    public function setUUID(String $value)
    {
        return $this->setProp(self::acf_child_company_uuid, $value, false, 'acf');
    }

    /**
     * Get Child Company UUID function
     *
     * @return mixed
     */
    public function getUUID()
    {
        return $this->getProp(self::acf_child_company_uuid, true, 'acf');
    }
}
