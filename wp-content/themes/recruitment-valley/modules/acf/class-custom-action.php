<?php

use Vacancy\Vacancy;

class AcfCustomAction
{
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        add_action('acf/save_post', 'my_acf_save_post');
    }

    public function my_acf_save_post( $post_id ) 
    {
        $this->wpdb->query("START TRANSACTION");
        try {
            $vacancy = new Vacancy($post_id);

            $expired_at = get_field('expired_at', $post_id);
            $expired_dates = maybe_unserialize(get_option("job_expires"));

            $new_expired_dates = array_map(function( $expired_date ) use($vacancy, $expired_at) {
                if($expired_date["post_id"] == $vacancy->vacancy_id) {
                    $expired_date["expired_at"] = $expired_at;
                }

                return $expired_date;
            }, $expired_dates);

            update_option("job_expires", $new_expired_dates);

            $this->wpdb->query("COMMIT");
            error_log("options job expired updated");
        } catch (WP_Error $err) {
            $this->wpdb->query("ROLLBACK");
            error_log( $err->get_error_message() );
        } catch (Exception $e) {
            $this->wpdb->query("ROLLBACK");
            error_log( $e->getMessage() );
        } catch (Throwable $th) {
            $this->wpdb->query("ROLLBACK");
            error_log( $th->getMessage() );
        }
    }
}

new AcfCustomAction;