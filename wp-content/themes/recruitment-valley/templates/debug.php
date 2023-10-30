<?php

use Integration\ActiveCampaign\ActiveCampaign;
use Model\Coupon;
use Model\Notification;
use Aws\Exception\AwsException;

defined('ABSPATH') || die("Can't access directly");

/**
 * Template Name: Debugging
 */


require_once get_stylesheet_directory() . "/vendor/autoload.php";

try {

  $dateNow = "2023-10-29";

  $s3 = new \Aws\S3\S3Client([
    'region' => 'eu-central-1',
    'version' => 'latest',
    'credentials' => [
      'key' => get_field('aws_key_id', 'option'),
      'secret' => get_field('aws_secret_key', 'option'),
    ]
  ]);

  $key = "NL/daily/" . $dateNow . "/jobs_new.0.jsonl.gz";
  $fileName = wp_upload_dir()["basedir"] . '/aws/job/new/' . $dateNow . '.gz';
  error_log(json_encode(wp_upload_dir()));

  $key = "NL/daily/" . $dateNow . "/jobs_new.0.jsonl.gz";
  $fileName = wp_upload_dir()["basedir"] . '/aws/job/new/' . $dateNow . '.gz';
  error_log(json_encode(wp_upload_dir()));

  $result = $s3->getObject([
    'Bucket' => 'jobfeed-intelligence-group',
    'Key'    => $key,
    'SaveAs' => $fileName,
  ]);

  // Raising this value may increase performance
  $buffer_size = 4096000; // read 4kb at a time
  $out_file_name = str_replace('.gz', '.jsonl', $fileName);

  // Open our files (in binary mode)
  $file = gzopen($fileName, 'rb');
  $out_file = fopen($out_file_name, 'wb');

  // Keep repeating until the end of the input file
  while (!gzeof($file)) {
    // Read buffer-size bytes
    // Both fwrite and gzread and binary-safe
    fwrite($out_file, gzread($file, $buffer_size));
  }

  // Files are done, close files
  fclose($out_file);
  gzclose($file);


  $vacancies = file_get_contents($out_file_name);
  $vacancies = explode("\n", $vacancies);

  foreach ($vacancies as $vacancy) {
    echo '<pre>';
    var_dump(json_decode($vacancy)->job_id);
    echo '</pre>';
  }
} catch (AwsException $e) {
  echo '<pre>';
  var_dump($e->getMessage());
  echo '</pre>';
}
