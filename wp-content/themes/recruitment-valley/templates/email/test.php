<?php

$body = $data['message'];
$body .= '<hr>';
$body .= 'Sender data : ' . PHP_EOL;

if ($template === 'contact-company') {
    $body .= 'Company Name : ' . $data['companyName'] . ' [' . $data['name'] . ']' . PHP_EOL;
} else {
    $body .= 'Candidate Name : ' . $data['firstName'] . ' - ' . $data['lastName'] . PHP_EOL;
}
$body .= 'Email : ' . $data['email'] . PHP_EOL;
$body .= 'Phone : ' . '(' . $data['phoneNumberCode'] . ') ' . $data['phoneNumber'] . PHP_EOL;

echo $body;
