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
        foreach ($option as $key => $value) {
            $optionData[] = [
                'value' => $value['op_employees_total'],
                'label' => $value['op_employees_total'],
            ];
        }

        return [
            "message" => $this->_message->get('option.company.employees_total.get_success'),
            "data" => $optionData,
            "status" => 200
        ];
    }

    /**
     * Update job_expires option function
     *
     * @param Mixed $id : Vacancy ID
     * @param String $expired : String expired date
     * @param string $class_name : class name where this method called. This use for logging
     * @param string $method_name : method name where this method called. This use for logging
     * @return bool result of update_field options
     */
    public function updateExpiredOptions(Mixed $id, String $expired, String $class_name = '', String $method_name = '')
    {
        if (!empty($id) && !empty($expired)) {
            /** Get current options "job_expires" */
            $oldExpiredOption = maybe_unserialize(get_option("job_expires"));

            error_log('called from ' . ($class_name ?? 'self') . '::' . ($method_name ?? 'self') . ' - Inside try to update the expired date. - Logged from : OptionController::updateExpiredOptions');
            error_log('called from ' . ($class_name ?? 'self') . '::' . ($method_name ?? 'self') . ' - post : ' . $id . ' - old expired option : ' . json_encode($oldExpiredOption) . ' - Logged from : OptionController::updateExpiredOptions');

            $postFound          = false;
            $newExpiredOption   = array_map(function ($optionData) use ($id, $expired) {
                if ($optionData["post_id"] == $id) {
                    $optionData["expired_at"] = $expired;
                    $postFound = true;
                }

                return $optionData;
            }, $oldExpiredOption);

            if (!$postFound) {
                array_push($newExpiredOption, ["post_id" => $id, "expired_at" => $expired]);
            }

            $isOptionUpdate = update_option("job_expires", $newExpiredOption);

            error_log('called from ' . ($class_name ?? 'self') . '::' . ($method_name ?? 'self') . ' - post : ' . $id . ' - set expired to : ' . $expired . ' - Logged from : OptionController::updateExpiredOptions');
            error_log('called from ' . ($class_name ?? 'self') . '::' . ($method_name ?? 'self') . ' - post : ' . $id . ' - new expired option : ' . json_encode($newExpiredOption) . ' - Logged from : OptionController::updateExpiredOptions');
            error_log('called from ' . ($class_name ?? 'self') . '::' . ($method_name ?? 'self') . ' - post : ' . $id . ' - is option updated : ' . $isOptionUpdate . ' - Logged from : OptionController::updateExpiredOptions');

            return $isOptionUpdate;
        } else {
            if (empty($id)) {
                throw new \Exception('Please specify the vacancy ID! - Vacancy Helper - method : updateExpiredOptions');
            } else {
                throw new \Exception('Please specify the vacancy expired date! - Vacancy Helper - method : updateExpiredOptions');
            }
        }
    }
}
