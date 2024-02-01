<?php

namespace Model;

use Exception;
use Helper;
use Vacancy\Vacancy;
use WP_Query;

class Company
{
    public $user_id;
    public $user;
    public $vacancyModel;

    protected $dateOfBirth = "ucma_date_of_birth";
    protected $phone = "ucma_phone";
    protected $phoneCode = "ucma_phone_code";
    protected $country = "ucma_country";
    protected $city = "ucma_city";
    protected $isFullRegistered = "ucma_is_full_registered";
    protected $image = "ucma_image";
    protected $name = "ucma_company_name";
    protected $description = "ucma_short_decription";
    protected $totalEmployee = "ucma_employees";
    protected $website = "ucma_website_url";

    protected $facebook = "ucma_facebook_url";
    protected $twitter = "ucma_twitter_url";
    protected $instagram = "ucma_instagram_url";
    protected $linkedin = "ucma_linkedin_url";

    protected $videoUrl = "ucma_company_video_url";
    protected $gallery = "ucma_gallery_photo";

    protected $credit = "company_credit";

    protected $secondaryEmploymentCondition = "ucma_benefit";

    protected $_isOnUnlimited = "company_on_unlimited";
    protected $_unlimitedExpiredDate = "company_unlimited_expired_date";

    protected $_companyLatitude = "ucma_company_latitude";
    protected $_companyLongitude = "ucma_company_longitude";

    /** Added Line */
    protected $_acfSector = "ucma_sector";
    protected $_acfCountryCode = "ucma_company_country_code";

    /** Indicate if current initialization is for child company */
    protected $isChild = false;

    public function __construct($userId = false)
    {
        $this->vacancyModel = new Vacancy;
        if ($userId) {
            $this->user_id = $userId;
            $user = get_user_by('id', $this->user_id);
            if (!$user) {
                throw new Exception("company not found", 400);
            }

            $this->user = $user;
        }
    }

    public function setUserId($userId)
    {
        $this->user_id = $userId;
    }

    public function getVacancyByStatus($status)
    {
        $args = [
            "post_type" => $this->vacancyModel->vacancy,
            "author__in" => [$this->user_id],
            "posts_per_page" => -1,
            "tax_query" => [
                [
                    'taxonomy' => 'status',
                    'field' => 'slug',
                    'terms' => array($status),
                    'operator' => 'IN'
                ],
            ],
        ];

        $vacancies = new WP_Query($args);

        return $vacancies->found_posts;
    }

    public function getThumbnail($result = 'url')
    {
        if ($result === 'object') {
            $attachment = $this->getProp($this->image, true);
            if (!empty($attachment)) {
                return [
                    'id' => $attachment['ID'],
                    'title' => $attachment['title'],
                    'url' => $attachment['url']
                ];
            } else {
                return null;
            }
        } else {
            $attachment_id = $this->getProp($this->image);
            return wp_get_attachment_url($attachment_id) ? wp_get_attachment_url($attachment_id) : null;
        }
    }

    public function getId()
    {
        return $this->user_id;
    }

    public function getName()
    {
        return $this->getProp($this->name);
    }

    public function getDescription()
    {
        return  $this->getProp($this->description);
    }

    public function getPhone()
    {
        return  $this->getProp($this->phone);
    }

    public function getPhoneCode()
    {
        return $this->getProp($this->phoneCode);
    }

    public function getEmail()
    {
        return $this->user->user_email;
    }

    public function getTotalEmployees()
    {
        return $this->getProp($this->totalEmployee);
    }

    public function getFacebook()
    {
        return $this->getProp($this->facebook);
    }

    public function getTwitter()
    {
        return $this->getProp($this->twitter);
    }
    public function getLinkedin()
    {
        return $this->getProp($this->linkedin);
    }
    public function getInstagram()
    {
        return $this->getProp($this->instagram);
    }

    public function getWebsite()
    {
        return $this->getProp($this->website);
    }

    // public function getGallery( $object = false )
    public function getGallery($object = false, $raw = false)
    {
        $gallery = $this->getProp($this->gallery);

        if (!$gallery) {
            return [];
        }

        /** Added line start here */
        if ($raw) {
            return $gallery;
        }
        /** Added line end here */

        $gallery = array_map(function ($attachmentId) use ($object) {
            if ($object) {
                return [
                    "id" => $attachmentId,
                    "url" => wp_get_attachment_url($attachmentId),
                    "title" => get_the_title($attachmentId)
                ];
            }

            return wp_get_attachment_url($attachmentId);
        }, $gallery);

        return $gallery;
    }

    public function getVideoUrl()
    {
        return $this->getProp($this->videoUrl) ?? "";
    }

    public function getProp(String $acf_field, Bool $single = false, String $type = 'acf')
    {
        return get_field($acf_field, "user_" . $this->user_id, $single);
    }

    /**
     * grant
     * granting spesific credit to company base on package that already bought
     *
     * @param  mixed $totalCredit
     * @return void
     */
    public function grant($totalCredit)
    {
        $currentCredit = $this->getCredit() != "" || $this->getCredit() != false ? $this->getCredit() : 0;
        $currentCredit += (int) $totalCredit;
        return $this->setCredit($currentCredit);
    }

    public function getSocialMedia(String $platform)
    {
        switch ($platform) {
            case "facebook":
                return $this->getFacebook() ?? '';
            case "twitter":
                return $this->getTwitter() ?? '';
            case "linkedin":
                return $this->getLinkedin() ?? '';
            case "instagram":
                return $this->getInstagram() ?? '';
            default:
                return '';
        }
    }

    public function getCredit()
    {
        $credit = get_user_meta($this->user_id, $this->credit, true);

        $credit = is_numeric($credit) ? (int) $credit : 0;

        return $credit;
    }

    public function setCredit($total)
    {
        return update_user_meta($this->user_id, $this->credit, $total);
    }

    public function getSecondaryEmploymentCondition()
    {
        return $this->getProp($this->secondaryEmploymentCondition);
    }

    public function getTerms(String $taxonomy)
    {
        switch ($taxonomy) {
            case ('sector'):
                $acf = $this->getProp($this->_acfSector) ?? [];
                break;
            default:
                $acf = [];
        }

        if (!empty($acf)) {
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'include' => $acf,
                'hide_emtpy' => false
            ]);

            $termsResponse = [];

            foreach ($terms as $term) {
                $termsResponse[] = [
                    'label' => htmlspecialchars_decode($term->name),
                    'value' => $term->term_id,
                ];
            }

            return $termsResponse;
        } else {
            return [];
        }
        return [];
    }

    public function getCity()
    {
        return $this->getProp($this->city);
    }

    public function getCountry()
    {
        return $this->getProp($this->country);
    }

    /**
     * grant unlimited
     * granting unlimited credit to company when purchased unlimited package
     * set exp date for 1 year
     *
     * @param  mixed $totalCredit
     * @return void
     */
    public function grantUnlimited()
    {
        $now = new \DateTimeImmutable("now");

        update_user_meta($this->user_id, $this->_unlimitedExpiredDate, $now->modify("+1 year")->format("Y-m-d H:i:s"));
        return update_user_meta($this->user_id, $this->_isOnUnlimited, 1);
    }

    public function checkUnlimited()
    {
        return $this->getProp($this->_isOnUnlimited, true);
    }

    public function getUnlimitedExpired()
    {
        return $this->getProp($this->_unlimitedExpiredDate, true);
    }

    public function getLongitude()
    {
        return $this->getProp($this->_companyLongitude, true);
    }

    public function getLatitude()
    {
        return $this->getProp($this->_companyLatitude, true);
    }

    public function checkFullregistered()
    {
        return $this->getProp($this->isFullRegistered, true);
    }

    public function setVideoUrl($videoUrl)
    {
        return $this->setProp($this->videoUrl, $videoUrl);
    }

    public function setProp($acf_field, $value, $repeater = false)
    {
        return update_field($acf_field, $value, "user_" . $this->user_id);
    }

    public function getCountryCode()
    {
        return $this->getProp($this->_acfCountryCode, true);
    }

    /**
     * Check ID function
     *
     * Check if current initialization is already specify ID or not.
     * This method should call for model that connected or belong to another stronger entity.
     * e.g : user meta, post meta, term meta, etc.
     *
     * @return void
     */
    public function checkID()
    {
        if (!isset($this->user_id) || empty($this->user_id)) {
            throw new Exception('Please specify the ID!');
        }
    }
}
