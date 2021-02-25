<?php

    namespace IdnoPlugins\CleverCustomize\Pages {

        use Idno\Core\Webmention;
        use Idno\Entities\Notification;
        use Idno\Entities\User;
        use Idno\Core\Webservice;

        class Summary extends \Idno\Common\Page
        {

            function getContent()
            {
                $friendly_types = array('all');

                // Check for an empty site
                if (!\Idno\Entities\User::get()) {
                    $this->forward(\Idno\Core\Idno::site()->config()->getURL() . 'begin/');
                }

                // Set the homepage owner for single-user sites
                if (!$this->getOwner() && \Idno\Core\Idno::site()->config()->single_user) {
                    $owners = \Idno\Entities\User::get(['admin' => true]);
                    if (count($owners) === 1) {
                        $this->setOwner($owners[0]);
                    } else {
                        \Idno\Core\Idno::site()->logging()->warning('Expected exactly 1 admin user for single-user site; got '.count($owners));
                    }
                }

                $types = \Idno\Common\ContentType::getRegisteredClasses();

                $search = array();
                $search['publish_status'] = 'published';

                /* identify target date range */
                date_default_timezone_set('America/Los_Angeles');
                
                $year = date('Y');
                $month = '01';
                $day = null;
                $start = null;
                $end = null;
                $range = '';
                if (count($this->arguments) == 2) {
                    $year = $this->arguments[0];
                    $month = $this->arguments[1];

                    $start = strtotime($year . '-' . $month . '-01');
                    $end = strtotime($year . '-' . $month . ' +1 month');

                    $range = date('F, Y', $start);
                } else if (count($this->arguments) == 3) {
                    $year = $this->arguments[0];
                    $month = $this->arguments[1];
                    $day = $this->arguments[2];

                    $start = strtotime($year . '-' . $month . '-' . $day);
                    $end = strtotime($year . '-' . $month . '-' . $day . ' +1 day');
                    
                    $range = date('F d, Y', $start);
                }

                $count = 0;
                $feed = array();
                
                $search['created']['$gt'] = date('Y-m-d 00:00:00', $start);
                $search['created']['$lt'] = date('Y-m-d 00:00:00', $end);  
                    
                $count = \Idno\Common\Entity::countFromX($types, $search, array(), PHP_INT_MAX-1);
                $feed = \Idno\Common\Entity::getFromX($types, $search, array(), PHP_INT_MAX-1); 
                $feed = array_reverse($feed); 

                $description = "Summary for " . $range;
                
                // If we have a feed, set our last modified flag to the time of the latest returned entry
                if (!empty($feed)) {
                    if (is_array($feed)) {
                        $feed = array_filter($feed);
                        $this->setLastModifiedHeader(reset($feed)->updated);
                    }
                }
                    
                // fetch monthly health summary
                $base = \Idno\Core\Idno::site()->config()->healthlake_url;
                $response = Webservice::get($base . '/summary/' . $year . '-' . $month);
                $health_data_found = $response['response'] == 200;
                $health_data = json_decode($response['content']);

                // collect by type
                $entities = array();
                $typemap = array();
                foreach ($feed as $entity) {
                    $category = $entity->getContentTypeCategoryTitle();
                    if (!array_key_exists($category, $entities)) {
                        $entities[$category] = array();
                        $typemap[$category] = $entity->getContentType()->getIcon(); 
                    }
                    $entities[$category][] = $entity;
                }
                
                $t = \Idno\Core\Idno::site()->template();
                $t->__(array(

                    'title'       => $description,
                    'description' => $description,
                    'content'     => $friendly_types,
                    'body'        => $t->__(array(
                        'items'        => $feed,
                        'entities'     => $entities,
                        'typemap'      => $typemap,
                        'contentTypes' => array(),
                        'count'        => $count,
                        'date'         => $start,
                        'month'        => $month,
                        'year'         => $year,
                        'description'  => $range,
                        'health_data'  => (array) $health_data,
                        'health_found' => $health_data_found
                    ))->draw('pages/summary'),

                ))->drawPage();
            }

        }

    }
