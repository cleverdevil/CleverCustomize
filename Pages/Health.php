<?php

    /**
     * Defines the site homepage
     */

    namespace IdnoPlugins\CleverCustomize\Pages {

        use Idno\Core\Webmention;
        use Idno\Entities\Notification;
        use Idno\Entities\User;
        use Idno\Core\Webservice;

        /**
         * Default class to serve the homepage
         */
        class Health extends \Idno\Common\Page
        {

            // Handle GET requests to the homepage

            function getContent()
            {
                if (!empty(\Idno\Core\Idno::site()->config()->description)) {
                    $description = \Idno\Core\Idno::site()->config()->description;
                } else {
                    $description = 'An independent social website, powered by Known.';
                }
                $description = $description . ': Health'; 
                $title = $description;

                // determine the target date
                date_default_timezone_set('America/Los_Angeles');
                $target = strtotime('yesterday');
                if (!empty($this->arguments)) {
                    $target = strtotime(implode('-', $this->arguments));
                }
                $next = strtotime(date('Y-m-d', $target) . ' + 1 day');
                $prev = strtotime(date('Y-m-d', $target) . ' - 1 day');

                // send a request to our health endpoint to grab
                $base = \Idno\Core\Idno::site()->config()->healthlake_url;
                
                $response = Webservice::get($base . '/detail/' . date('Y-m-d', $target));
                $data_found = $response['response'] == 200;
                $data = json_decode($response['content']);
                
                $response = Webservice::get($base . '/global');
                $global_data = array();
                if ($response['response'] == 200) {
                    $global_data = json_decode($response['content']);
                }

                $t = \Idno\Core\Idno::site()->template();
                $t->__(array(
                    'title'       => $title,
                    'description' => $description,
                    'content'     => array('all'),
                    'body'        => $t->__(array(
                        'data_found'  => $data_found,
                        'date'        => $target,
                        'next'        => $next,
                        'prev'        => $prev,
                        'data'        => $data,
                        'global_data' => $global_data
                    ))->draw('pages/health'),

                ))->drawPage();
            }

        }

    }
