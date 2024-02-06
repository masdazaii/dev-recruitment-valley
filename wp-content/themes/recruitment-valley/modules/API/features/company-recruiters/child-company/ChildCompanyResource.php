<?php

namespace Resource;

use Model\Company;
use Model\CompanyRecruiter;
use Model\ChildCompany;
use WP_Post;
use WP_User;

class ChildCompanyResource
{
    /**
     * List childcompany formatter function
     *
     * @param array $childCompanies
     * @return array
     */
    public static function format(array $childCompanies): array
    {
        $results    = [];
        foreach ($childCompanies as $company) {
            $childCompany = ChildCompany::find('id', $company->ID);

            $results[] = [
                // "ID"    => $company->ID,
                "ID"    => $childCompany->getUUID(),
                "slug"  => $company->post_name,
                "UUID"  => $childCompany->getUUID(),
                "companyName"   => $childCompany->getName() ?? NULL,
                "email"         => $childCompany->getEmail() ?? NULL,
            ];
        }

        return $results;
    }

    /**
     * Undocumented function
     *
     * @param Mixed $childCompany : Either Numeric | WP_POST
     * @return array
     */
    public static function single(Mixed $childCompany): array
    {
        $childCompanyModel = new ChildCompany($childCompany);

        /** Prepare response detail.address */
        $childCompanyCountry    = $childCompanyModel->getCountry() ?? '';
        $childCompanyCity       = $childCompanyModel->getCity() ?? '';

        $responseDetailAddress      = '';
        $responseDetailAddress      .= $childCompanyCity ?? '';
        $responseDetailAddress      .= !empty($childCompanyCity) && !empty($childCompanyCountry) ? ", {$childCompanyCountry}" : '';

        return [
            "detail"        => [
                "companyName"   => $childCompanyModel->getName() ?? NULL,
                "email"         => $childCompanyModel->user->user_email ?? NULL,
                "address"       => $responseDetailAddress,
                "website"       => $childCompanyModel->getWebsite(),
                "kvk"           => $childCompanyModel->getKvkNumber(),
                "btw"           => $childCompanyModel->getBtwNumber(),
                "employees"     => $childCompanyModel->getTotalEmployees('object'),
                "phoneNumberCode"   => $childCompanyModel->getPhoneNumber('code'),
                "phoneNumber"       => $childCompanyModel->getPhoneNumber('number'),
                "sector"            => $childCompanyModel->getSector('object'),
                "image"             => $childCompanyModel->getThumbnail('object'),
            ],
            "socialMedia"   => [
                "facebook"  => $childCompanyModel->getFacebook(),
                "linkedin"  => $childCompanyModel->getLinkedin(),
                "instagram" => $childCompanyModel->getInstagram(),
                "twitter"   => $childCompanyModel->getTwitter()
            ],
            "address"       => [
                "country"   => $childCompanyCountry,
                "city"      => $childCompanyCity,
                "countryCode"   => $childCompanyModel->getCountryCode(),
                "street"        => $childCompanyModel->getStreet(),
                "postcode"      => $childCompanyModel->getPostCode(),
                "longitude"     => $childCompanyModel->getLongitude(),
                "latitude"      => $childCompanyModel->getLatitude(),
            ],
            "information"   => [
                "shortDescription"  => $childCompanyModel->getDescription(),
                "secondaryEmploymentConditions" => $childCompanyModel->getSecondaryEmploymentCondition(),
                "videoUrl"  => $childCompanyModel->getVideoUrl(),
                "gallery"   => $childCompanyModel->getGallery('object'),
            ]
        ];
    }
}
