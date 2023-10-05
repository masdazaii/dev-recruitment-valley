<?php

namespace Model;

class ModelHelper
{
    public static function handle_upload($name, $custom_dir = null)
    {
        if (!isset($_FILES[$name])) return false;

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        /** this code for upload image from ACF repeater */

        if (!isset($_FILES[$name]['name'])) return false;

        $value = [];
        $uploadedfile = [
            'name'      => $_FILES[$name]['name'],
            'full_path' => $_FILES[$name]['full_path'],
            'type'      => $_FILES[$name]['type'],
            'tmp_name'  => $_FILES[$name]['tmp_name'],
            'error'     => $_FILES[$name]['error'],
            'size'      => $_FILES[$name]['size'],
        ];

        if ($custom_dir) add_filter('upload_dir', function ($dir_data) use ($custom_dir) {
            return [
                'path' => $dir_data['basedir'] . '/' . $custom_dir,
                'url' => $dir_data['baseurl'] . '/' . $custom_dir,
                'subdir' => '/' . $custom_dir,
                'basedir' => $dir_data['error'],
                'error' => $dir_data['error'],
            ];
        });
        $movefile = wp_handle_upload($uploadedfile, ['test_form' => false]);
        if ($custom_dir) remove_filter('upload_dir', function ($dir_data) use ($custom_dir) {
            return [
                'path' => $dir_data['basedir'] . '/' . $custom_dir,
                'url' => $dir_data['basedir'] . '/' . $custom_dir,
                'subdir' => '/' . $custom_dir,
                'basedir' => $dir_data['error'],
                'error' => $dir_data['error'],
            ];
        });

        if ($movefile && isset($movefile['file'])) {
            $attach_url = $movefile['url'];
            $attachment = array(
                'guid'           => $movefile['url'] . '/' . basename($movefile['file']),
                'post_mime_type' => $movefile['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            $value[$name] = [
                'url'        => $attach_url,
                'attachment' => $attachment,
                'file'       => $movefile['file']
            ];
        }

        return $value;
    }

    public static function handle_uploads($name, $custom_dir = null)
    {
        if (!isset($_FILES[$name])) return false;

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        /** this code for upload image from ACF repeater */

        if (!isset($_FILES[$name]['name'])) return false;

        $value = [];
        if ($custom_dir) add_filter('upload_dir', function ($dir_data) use ($custom_dir) {
            return [
                'path' => $dir_data['basedir'] . '/' . $custom_dir,
                'url' => $dir_data['baseurl'] . '/' . $custom_dir,
                'subdir' => '/' . $custom_dir,
                'basedir' => $dir_data['error'],
                'error' => $dir_data['error'],
            ];
        });
        foreach ($_FILES[$name]['name'] as $key => $val) {
            $uploadedfile = [
                'name'      => $_FILES[$name]['name'][$key],
                'full_path' => $_FILES[$name]['full_path'][$key],
                'type'      => $_FILES[$name]['type'][$key],
                'tmp_name'  => $_FILES[$name]['tmp_name'][$key],
                'error'     => $_FILES[$name]['error'][$key],
                'size'      => $_FILES[$name]['size'][$key],
            ];

            $movefile = wp_handle_upload($uploadedfile, ['test_form' => false]);

            if ($movefile && isset($movefile['file'])) {
                $attach_url = $movefile['url'];
                $attachment = array(
                    'guid'           => $movefile['url'] . '/' . basename($movefile['file']),
                    'post_mime_type' => $movefile['type'],
                    'post_title'     => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );
                $value[$key] = [
                    'url'        => $attach_url,
                    'attachment' => $attachment,
                    'name'       => $name,
                    'file'       => $movefile['file']
                ];
            }
        }
        if ($custom_dir) remove_filter('upload_dir', function ($dir_data) use ($custom_dir) {
            return [
                'path' => $dir_data['basedir'] . '/' . $custom_dir,
                'url' => $dir_data['basedir'] . '/' . $custom_dir,
                'subdir' => '/' . $custom_dir,
                'basedir' => $dir_data['error'],
                'error' => $dir_data['error'],
            ];
        });

        return $value;
    }
}
