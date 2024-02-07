<?php

namespace Resource;

use Model\Company;
use Model\CompanyRecruiter;
use WP_User;

class CompanyRecruiterResource
{
    public static function single(WP_User $companyRecruiter): array
    {
        $companyRecruiterModel = new CompanyRecruiter($companyRecruiter);

        /** Prepare response detail.address */
        $companyRecruiterCountry    = $companyRecruiterModel->getCountry() ?? '';
        $companyRecruiterCity       = $companyRecruiterModel->getCity() ?? '';

        $responseDetailAddress      = '';
        $responseDetailAddress      .= $companyRecruiterCity ?? '';
        $responseDetailAddress      .= !empty($companyRecruiterCity) && !empty($companyRecruiterCountry) ? ", {$companyRecruiterCountry}" : '';

        return [
            "detail"        => [
                "companyName"   => $companyRecruiterModel->getName() ?? NULL,
                "email"         => $companyRecruiter->user_email ?? NULL,
                "address"       => $responseDetailAddress,
                "website"       => $companyRecruiterModel->getWebsiteUrl(),
                "kvk"           => $companyRecruiterModel->getKvkNumber(),
                "btw"           => $companyRecruiterModel->getBtwNumber(),
                "employees"     => $companyRecruiterModel->getTotalEmployees('object'),
                "phoneNumberCode"   => $companyRecruiterModel->getPhoneNumber('code'),
                "phoneNumber"       => $companyRecruiterModel->getPhoneNumber('number'),
                "sector"            => $companyRecruiterModel->getSector('object'),
                "image"             => $companyRecruiterModel->getImage('object'),
            ],
            "socialMedia"   => [
                "facebook"  => $companyRecruiterModel->getFacebookUrl(),
                "linkedin"  => $companyRecruiterModel->getLinkedinUrl(),
                "instagram" => $companyRecruiterModel->getInstagramUrl(),
                "twitter"   => $companyRecruiterModel->getTwitterUrl()
            ],
            "address"       => [
                "country"   => $companyRecruiterCountry,
                "city"      => $companyRecruiterCity,
                "countryCode"   => $companyRecruiterModel->getCountryCode(),
                "street"        => $companyRecruiterModel->getStreet(),
                "postcode"      => $companyRecruiterModel->getPostCode(),
                "longitude"     => $companyRecruiterModel->getLongitude(),
                "latitude"      => $companyRecruiterModel->getLatitude(),
            ],
            "information"   => [
                "shortDescription"  => $companyRecruiterModel->getShortDescription(),
                "secondaryEmploymentConditions" => $companyRecruiterModel->getBenefit(),
                "videoUrl"  => $companyRecruiterModel->getVideoUrl(),
                "gallery"   => $companyRecruiterModel->getGallery('object'),
            ]
        ];
    }
}
