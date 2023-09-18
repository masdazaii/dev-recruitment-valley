<?php

namespace Helper;

use BD\Emails\Email;
use Exception;
use IntlDateFormatter;
use Model\Company;
use Package;
use Transaction;
use Vacancy\Vacancy;
use JWTHelper;

class EmailHelper
{

    public $templates;

    public static function send(String $template, array $sender, String $receipient, String $subject, array $data)
    {
        ob_start();

        switch ($template) {
            case 'contact-candidate':
            case 'contact-company':
                include THEME_DIR . '/templates/email/test.php';
                $output = ob_get_clean();
                break;
        }
        include THEME_DIR . '/templates/email/test.php';
        $output = ob_get_clean();

        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $sender['name'] . '<' . $sender['email']  . '>';

        wp_mail($receipient, $subject, $output, $headers);
    }

    public static function sendPaymentConfirmation($transactionId)
    {
        try {
            $transaction = new Transaction($transactionId);

            $companyId = $transaction->getUserId();

            error_log($companyId);

            $packageId = $transaction->getPackageId();

            $company = new Company($companyId);
            $package = new Package($packageId);

            $args = [
                "company.contactPerson" => $company->getEmail(),
                "company.name" => $company->getName(),
            ];

            $content = Email::render_html_email('payment-confirmation-credits-company.php', $args);
            $site_title = get_bloginfo('name');

            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );

            wp_mail($company->getEmail(), "Bevestiging van aankoop credits - $site_title", $content, $headers);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    public static function sendJobAlert($jobAlertData, $displayedJobItemCount = 5)
    {
        try {
            $email = $jobAlertData["email"];
            $jobs = array_values($jobAlertData["jobs"]);
            $jobIds = $jobAlertData["jobIds"];
            $jobCount = count($jobs);
            $jobListUrl = FRONTEND_URL . '/vacatures?perPage=10&vacancyId=' . implode(",", $jobIds) . '&orderBy=date&sort=DESC';

            $jobHtml = '';
            foreach ($jobIds as $key => $jobId) {
                if ($key >= 5) break;
                $jobHtml .= self::generateJobItemHtml($jobId);
            }

            /** create unsubs token */
            $token = JWTHelper::generate(['job_alert_id' => $jobIds]);

            // error_log($jobHtml);

            $args = [
                "application.job.url" => $jobListUrl,
                "application.unsubscribe.url" => FRONTEND_URL . '/job-alert/uitschrijven?token=' . $token,
                "applicant.email" => $email,
                "applicant.job.count" => $jobCount,
                "applicant.job.item_html" => $jobHtml
            ];

            error_log(json_encode($args));

            $content = Email::render_html_email('job-alert.php', $args);

            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );

            $site_title = get_bloginfo('name');

            wp_mail($email, "Kennisgeving JobAlert - $site_title", $content, $headers);
        } catch (\Throwable $th) {
            error_log("error sending email because " . $th->getMessage() . " with payload " . json_encode($jobAlertData));
        }
    }

    private static function generateJobItemHtml($jobId)
    {
        $vacancy = new Vacancy($jobId);
        $vacancyTaxonomy = $vacancy->getTaxonomy(false);


        $postedDate = DateHelper::doLocale($vacancy->getPublishDate(), 'nl_NL', 'dd MMMM yyyy, HH:mm a');

        $vacancyAuthor = $vacancy->getAuthor();
        $company = new Company($vacancyAuthor);

        ob_start();
?>
        <tr>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="padding-top: 34px;" width="100%">
                    <tbody>
                        <tr>
                            <td width="56">
                                <img width="56" height="56" src="<?= $company->getThumbnail() == "" ? THEME_URL . '/assets/images/company-placeholder.png' : $company->getThumbnail() ?>" style="
                            border: 0;
                            display: block;
                            outline: none;
                            text-decoration: none;
                            width: 56px;
                            height: 56px;
                            font-size: 13px;
                            border-radius: 8px;
                            overflow: hidden;
                        " />
                            </td>
                            <td>
                                <p align="left" style="
                            font-family: Neue Montreal Regular,
                            Helvetica;
                            font-size: 22px;
                            font-style: normal;
                            font-weight: 500;
                            line-height: 28px;
                            color: #1f1f1f;
                            padding-left: 10px;
                            margin: 0;
                        ">
                                    <?= $vacancy->getTitle() ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                    <tbody>
                        <tr>
                        <tr>
                            <td>
                                <p align="left" style="
                                font-family: Neue Montreal Regular,
                                Helvetica;
                                font-size: 14px;
                                font-style: normal;
                                font-weight: 400;
                                line-height: 20px;
                                letter-spacing: 0.25px;
                                color: #B7B7B7;
                                padding-top: 10px;
                                padding-bottom: 10px;
                                margin: 0;
                            ">
                                    <?= self::generateTaxonomyDescription($vacancyTaxonomy) ?>
                                </p>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <p align="left" style="
                                                font-family: Neue Montreal Regular,
                                                Helvetica;
                                                font-size: 11px;
                                                font-style: normal;
                                                font-weight: 500;
                                                line-height: 16px;
                                                letter-spacing: 0.5px;
                                                color: #878787;
                                                background-color: #E9E9E9;
                                                padding-left: 8px;
                                                padding-right: 8px;
                                                border-radius: 20px;
                                                margin: 0;
                                            ">
                                                    <?= $postedDate ?>
                                                </p>
                                                <div style="padding-bottom: 34px;"></div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
    <?php
        $jobItem = ob_get_contents();
        ob_end_clean();

        return $jobItem;
    }

    private static function generateTaxonomyDescription($taxonomies)
    {
        $taxonomyDescription = "";
        $taxonomyCount = count($taxonomies);

        ob_start();
        foreach ($taxonomies as $key => $taxonomy) {
            error_log($key);
            error_log($taxonomyCount);
            $taxonomyDescription .= $key != $taxonomyCount - 1 ? $taxonomy["name"] . '<span style="padding-right: 4px;">•</span>' : $taxonomy["name"];
        }
    ?>
        <?= $taxonomyDescription; ?>
<?php
        $description = ob_get_contents();
        ob_end_clean();

        return $description;
    }
}
