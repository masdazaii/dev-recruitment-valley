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

    private $dateOfBirth = "ucma_date_of_birth";
    private $phone = "ucma_phone";
    private $phoneCode = "ucma_phone_code";
    private $country = "ucma_country";
    private $city = "ucma_city";
    private $isFullRegistered = "ucma_is_full_registered";
    private $image = "ucma_image";
    private $name = "ucma_company_name";
    private $description = "ucma_short_decription";
    private $totalEmployee = "ucma_employees";
    private $website = "ucma_website_url";

    private $facebook = "ucma_facebook_url";
    private $twitter = "ucma_twitter_url";
    private $instagram = "ucma_instagram_url";
    private $linkedin = "ucma_linkedin_url";

    private $videoUrl = "ucma_company_video_url";
    private $gallery = "ucma_gallery_photo";

    private $credit = "company_credit";

    private $secondaryEmploymentCondition = "ucma_benefit";

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

    public function getThumbnail()
    {
        $attachment_id = $this->getProp($this->image);
        return wp_get_attachment_url($attachment_id) ? wp_get_attachment_url($attachment_id) : null;
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
                    "title" => wp_get_attachment_caption($attachmentId)
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

    public function getProp($acf_field, $single = false)
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
}
