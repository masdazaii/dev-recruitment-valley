<?php

class Package
{
    private $post_type = "package";

    private $package;

    private $package_id;
    private $price = "rv_package_price";
    private $credit = "rv_package_credit_quantity";
    private $favorite = "is_favorit";

    private $_acfBenefit = "rv_package_benefit";

    public function __construct($packageId = false)
    {
        if ($packageId) {
            $this->package_id = $packageId;

            $package = get_post($packageId);
            if ($package) {
                $this->package = $package;
            } else {
                throw new Exception("Package not found!");
            }
        }
    }

    public function setPackageId($packageId)
    {
        $this->package_id = $packageId;
    }

    public function getPackageId()
    {
        return $this->package->ID ?? $this->package_id;
    }

    public function getPrice()
    {
        $price = $this->getProp($this->price);
        return $price;
    }

    public function getCredit()
    {
        $credit = $this->getProp($this->credit);
        return (int) $credit < 0 ? "unlimited" : $credit;
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

    public function getPricePerVacany()
    {
        $price = $this->getPrice();
        $credit = $this->getCredit();

        if (!is_numeric($credit)) {
            return "unlimited";
        }

        $priceperVacany = $price / $credit;

        return number_format((float) $priceperVacany, 2, '.', '');
    }

    public function getProp($field)
    {
        return get_field($field, $this->package_id);
    }

    public function getBenefit()
    {
        return $this->getProp($this->_acfBenefit);
    }
}
