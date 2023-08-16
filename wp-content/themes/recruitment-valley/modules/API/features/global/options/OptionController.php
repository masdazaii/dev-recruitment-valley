<?php

namespace Global;

use Constant\Message;

class OptionController
{
    protected $_message;

    public function __construct()
    {
        $this->_message = new Message();
    }

    public function getCompanyEmployeesOption($request)
    {
        $option = get_field('op_company_total_employees_option', 'option');
        // foreach ($option as $key => $value) {
        //     $optionData[] = [
        //         'value' =>
        //     ]
        // }

        return [
            "message" => $this->_message->get('option.company.employees_total.get_success'),
            "data" => $option,
            "status" => 200
        ];
    }
}
