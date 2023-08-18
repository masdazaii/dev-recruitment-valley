<?php

class Package
{
    private $post_type = "package";

    private $package;

    private $package_id;
    private $price = "rv_package_price";
    private $credit = "rv_package_credit_quantity";
    private $favorite = "is_favorit";

    public function __construct( $packageId = false )
    {
        if($packageId)
        {
            $this->package_id = $packageId;

            $package = get_post($packageId);
            if($package)
            {
                $this->package = $package;
            }
        }
    }

    public function setPackageId( $packageId )
    {
        $this->package_id = $packageId;
    }

    public function getPackageId()
    {
        return $this->package->ID ?? $this->package_id;
    }

    public function getPrice()
    {
        return $this->getProp($this->price);
    }

    public function getCredit()
    {
        return $this->getProp($this->credit);
    }

    public function getFavorite()
    {
        return $this->getProp($this->favorite);
    }

    public function getDescription()
    {
        return $this->package->post_content ?? "";
    }

    public function getTitle()
    {
        return $this->package->post_title ?? "";
    }

    public function getProp( $field )
    {
        return get_field($field, $this->package_id);
    }
}