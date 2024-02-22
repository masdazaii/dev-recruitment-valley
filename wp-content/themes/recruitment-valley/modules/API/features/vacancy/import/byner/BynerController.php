<?php

namespace Vacancy\Import;

use Exception;
use Helper\EncryptionHelper;
use Log;
use Model\Option;
use Model\Term;
use Throwable;
use WP_Error;

class BynerController
{
    private $sample_dir = THEME_DIR . '/assets/sample/byner.json';
    private $_sourceUrl;
    private $_taxonomy;
    private $_terms;
    private $_keywords;
    private $_message;
    protected const source = 'byner';

    public function __construct($source)
    {
        if ($source) {
            $this->_sourceUrl = $source;

            $this->_taxonomy = [
                'hoursPerWeek'  => 'working-hours',
                'education'     => 'education',
                'jobtype'       => 'type',
                'experience'    => 'experiences',
                'sector'        => 'sector',
                'role'          => 'role',
                'location'      => 'location',
                'status'        => 'status'
            ];
        } else {
            throw new Exception("Please specify the source!");
        }
    }

    public function import($limit = 'all', $offset = 0)
    {
        /** Log Attempt */
        $logData = [
            'limit'     => $limit,
            'offset'    => $offset
        ];
        Log::info('Byner Import attempt.', json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_byner_import');

        try {
            /** Get All terms */
            $this->_getTerms();

            /** Get Mapped Keyword from option */
            $this->_getMappedKeyword();

            /** Parse and store data */
            $this->_parse($limit, $offset);
        } catch (WP_Error $wp_error) {
        } catch (Exception $e) {
        } catch (Throwable $th) {
        }
    }

    /**
     * Get Terms function
     *
     * @return void
     */
    private function _getTerms()
    {
        $terms = get_terms([
            'taxonomy' => array_values($this->_taxonomy),
            'hide_empty' => false
        ]);

        foreach ($terms as $key => $value) {
            $this->_terms[$value->taxonomy][] = [
                'term_id'   => $value->term_id,
                'name'      => strtolower($value->name),
                'slug'      => strtolower($value->slug)
            ];
        }
    }

    /**
     * Get Mapped Keyword function
     *
     * This function is to get mapped keyword from option.
     *
     * @return void
     */
    private function _getMappedKeyword()
    {
        try {
            $this->_keywords = [
                'role' => [],
                'sector' => [],
            ];

            $termModel = new Term();

            /** Map Role  */
            /** Get term role */
            $terms = $termModel->selectTermByTaxonomy('role', 'array');
            if ($terms) {
                foreach ($terms as $term) {
                    $this->_keywords['role'][$term['term_id']][] = $term['name'];
                }
            }

            /** Get keywords options and set each keyword to lowercase */
            $option = new Option();
            // $keywordOption = get_field('import_api_mapping_role', 'option');
            $keywordOption = $option->getImportMappingRole();
            if (is_iterable($keywordOption)) {
                foreach ($keywordOption as $keyword) {
                    if (is_array($keyword)) {
                        if (array_key_exists('import_api_mapping_role_term', $keyword) && array_key_exists('import_api_mapping_role_keywords', $keyword)) {
                            foreach ($keyword['import_api_mapping_role_keywords'] as $word) {
                                if (is_array($word)) {
                                    if (array_key_exists('import_api_mapping_role_eachword', $word))
                                        $this->_keywords['role'][$keyword['import_api_mapping_role_term']][] = strtolower($word['import_api_mapping_role_eachword']);
                                } else if (is_string($word)) {
                                    $this->_keywords['role'][$keyword['import_api_mapping_role_term']][] = strtolower($word);
                                }
                            }
                        }
                    }
                }
            }

            /** Map Sector */
            /** Get term sector */
            $terms = $termModel->selectTermByTaxonomy('sector', 'array');
            if ($terms) {
                foreach ($terms as $term) {
                    $this->_keywords['sector'][$term['term_id']][] = $term['name'];
                }
            }

            /** Get keywords options and set each keyword to lowercase */
            // $keywordOption = get_field('import_api_mapping_sector', 'option');
            $keywordOption = $option->getImportMappingSector();
            if (is_array($keywordOption)) {
                foreach ($keywordOption as $keyword) {
                    if (is_array($keyword)) {
                        if (array_key_exists('import_api_mapping_sector_term', $keyword) && array_key_exists('import_api_mapping_sector_keywords', $keyword)) {
                            foreach ($keyword['import_api_mapping_sector_keywords'] as $word) {
                                if (is_array($word)) {
                                    if (array_key_exists('import_api_mapping_sector_eachword', $word))
                                        $this->_keywords['sector'][$keyword['import_api_mapping_sector_term']][] = strtolower($word['import_api_mapping_sector_eachword']);
                                } else if (is_string($word)) {
                                    $this->_keywords['sector'][$keyword['import_api_mapping_sector_term']][] = strtolower($word);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
        }
    }

    private function _parse($limit, $start)
    {
        /** Log Data */
        $logData = [
            'limit'     => $limit,
            'offset'    => $start
        ];
        Log::info('Request and parse byner attempt.', json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_byner_parse');

        try {
            $accessToken = $this->authenticationByner();
        } catch (WP_Error $wp_error) {
        } catch (Exception $e) {
        } catch (Throwable $th) {
        }
    }

    private function authenticationByner()
    {
        /** Log Data */
        $logData = [];
        Log::info('Request Auth byner attempt.', json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_byner_auth');

        try {
            $optionModel = new Option();

            /** Get IV : initialization vector */
            $usedIV = $optionModel->getBynerIV();

            /** Get Credentials : Client ID */
            $clientID       = $optionModel->getBynerClientID();

            /** Get Credentials : Client Secret */
            $clientSecret   = $optionModel->getBynerClientKey();
            $clientSecret   = EncryptionHelper::decrypt('openssl', $clientSecret, DEV_PASSWORD, 'AES-256-CBC', ['option' => 0, 'iv' => $usedIV]);

            /** Get Credentials : Username */
            $username   = $optionModel->getBynerUsername();

            /** Get Credentials : Password */
            $password   = $optionModel->getBynerPassword();
            $password   = EncryptionHelper::decrypt('openssl', $password, DEV_PASSWORD, 'AES-256-CBC', ['option' => 0, 'iv' => $usedIV]);

            /** Get Credentials : grant type */
            $grantType  = $optionModel->getGrantType();

            /** Prepare request Body */
            $requestBody = [
                'grant_type'    => $grantType ? $grantType : NULL,
                'client_id'     => $clientID ? $clientID : NULL,
                'client_secret' => $clientSecret ? $clientSecret : NULL,
                'username'      => $username ? $username : NULL,
                'password'      => $password ? $password : NULL
            ];

            /** Init the curl */
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL             => $this->_sourceUrl,   // Source url
                CURLOPT_CUSTOMREQUEST   => 'POST',   // Used HTTP Method
                // CURLOPT_HTTPHEADER      => ["Authorization: Bearer " . FLEXFEED_API_KEY],  // Add header to request
                CURLOPT_HEADER          => false,   // true to include the header in the output.
                CURLOPT_RETURNTRANSFER  => true,    // true to return the transfer as a string of the return value of curl_exec() instead of outputting it directly.
                CURLOPT_CONNECTTIMEOUT  => 120,  // time-out on connect
                CURLOPT_TIMEOUT         => 120,  // time-out on response
            ]);

            $response       = curl_exec($curl);
            $responseCode   = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            /** If Curl error */
            if (curl_errno($curl)) {
                error_log('Curl Error in fetch flexfeed API - ' . curl_error($curl));
            }

            curl_close($curl);
        } catch (WP_Error $wp_error) {
        } catch (Exception $e) {
        } catch (Throwable $th) {
        }
    }
}
