<?php

namespace Resource;

use Model\Company;
use Model\CompanyRecruiter;
use Model\ChildCompany;
use WP_User;

class ChildCompanyResource
{
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

    public static function single(WP_User $companyRecruiter): array
    {
        return [];
    }
}
