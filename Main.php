<?php

    namespace IdnoPlugins\CleverCustomize {
    
        use Idno\Core\Webservice;

        class Main extends \Idno\Common\Plugin {

            function registerPages() {
                // register timeline, activity, and on this day pages
                \Idno\Core\site()->routes()->addRoute('/timeline', '\IdnoPlugins\CleverCustomize\Pages\Timeline');
                \Idno\Core\site()->routes()->addRoute('/activity', '\IdnoPlugins\CleverCustomize\Pages\Activity');
                \Idno\Core\site()->routes()->addRoute('/onthisday/([0-9]+)/([0-9]+)/?', '\IdnoPlugins\CleverCustomize\Pages\OnThisDay');
                \Idno\Core\site()->routes()->addRoute('/onthisday/', '\IdnoPlugins\CleverCustomize\Pages\OnThisDay');
                \Idno\Core\site()->routes()->addRoute('/8675', '\IdnoPlugins\CleverCustomize\Pages\Jenny');
                \Idno\Core\site()->routes()->addRoute('/archive/([0-9]+)/([0-9]+)/([0-9]+)/?', '\IdnoPlugins\CleverCustomize\Pages\Archive');
                \Idno\Core\site()->routes()->addRoute('/archive/([0-9]+)/([0-9]+)/?', '\IdnoPlugins\CleverCustomize\Pages\Archive');
                \Idno\Core\site()->routes()->addRoute('/archive', '\IdnoPlugins\CleverCustomize\Pages\ArchiveIndex');
                \Idno\Core\site()->routes()->addRoute('/health', '\IdnoPlugins\CleverCustomize\Pages\Health');
                \Idno\Core\site()->routes()->addRoute('/health/([0-9]+)/([0-9]+)/([0-9]+)/?', '\IdnoPlugins\CleverCustomize\Pages\Health');
                \Idno\Core\site()->routes()->addRoute('/overview', '\IdnoPlugins\CleverCustomize\Pages\Overview');
                \Idno\Core\site()->routes()->addRoute('/now', '\IdnoPlugins\CleverCustomize\Pages\Now');
                \Idno\Core\site()->routes()->addRoute('/map', '\IdnoPlugins\CleverCustomize\Pages\Map');
                \Idno\Core\site()->routes()->addRoute('/following', '\IdnoPlugins\CleverCustomize\Pages\Following');
                \Idno\Core\site()->routes()->addRoute('/summary/([0-9]+)/([0-9]+)/([0-9]+)/?', '\IdnoPlugins\CleverCustomize\Pages\Summary');
                \Idno\Core\site()->routes()->addRoute('/summary/([0-9]+)/([0-9]+)/?', '\IdnoPlugins\CleverCustomize\Pages\Summary');
                \Idno\Core\site()->routes()->addRoute('/listen/hook/', '\IdnoPlugins\CleverCustomize\Pages\ListenEndpoint', true);
                \Idno\Core\site()->routes()->addRoute('/play/hook/', '\IdnoPlugins\CleverCustomize\Pages\PlayEndpoint', true);

                // override header
                \Idno\Core\Idno::site()->template()->replaceTemplate('shell/toolbar/main','clevercustomize/toolbar');
                \Idno\Core\Idno::site()->template()->extendTemplate('entity/feed','clevercustomize/summaries');
                \Idno\Core\Idno::site()->template()->extendTemplate('entity/annotations/comment/main', 'clevercustomize/location', true);

                // add in my CSS / JavaScript includes via a template extend
                \Idno\Core\Idno::site()->template()->extendTemplate('shell/head', 'clevercustomize/shell/head');
                \Idno\Core\Idno::site()->template()->extendTemplate('shell/footer', 'clevercustomize/shell/footer');
            }
            
            function registerEventHooks() {
                // Hook into the "published" event to inform micro.blog that my feed has been updated 
                \Idno\Core\site()->events()->addListener('published', function (\Idno\Core\Event $event) {
                    $obj = $event->data()['object'];
                    
                    // Notify Micro.blog of an update
                    Webservice::post("https://micro.blog/ping", array(
                        'url' => "https://cleverdevil.io/content/all/?_t=microblog"
                    ));
                    
                    // add location and weather information if not already present
                    $location_meta = $obj->getAnnotations('location-metadata');
                    if (empty($location_meta)) {
                        $status_file = fopen("current.json", "r");
                        $raw_json = fgets($status_file);
                        $status = json_decode($raw_json, true);
                        
                        $ann = $obj->addAnnotation(
                            'location-metadata', 'cleverdevil.io', 'https://cleverdevil.io/', 
                            null, '', null, null, null, ["location" => $status], false
                        );
                        
                        // Look up current weather conditions and add as an annotation based upon my
                        // current location.
                        /*
                        $darksky_api_key = \Idno\Core\Idno::site()->config()->darksky_api_key;
                        
                        $weather_url = "https://api.darksky.net/forecast/" . $darksky_api_key . "/";
                        $weather_url .= $status['y'] . "," . $status['x'];
                        
                        $response = Webservice::get($weather_url);
                        if ($response['response'] == 200) {
                            $weather = json_decode($response['content']);

                            $icon_url = \Idno\Core\site()->config()->url;
                            $icon_url .= "IdnoPlugins/CleverCustomize/images/weather/";
                            $icon_url .= $weather->currently->icon . ".svg";

                            $weather_content = 'Weather at time/location of posting &mdash; ';
                            $weather_content .= $weather->currently->summary . ' with a temperature of ';
                            $weather_content .= $weather->currently->temperature . '&deg;F and ';
                            $weather_content .= ($weather->currently->humidity * 100) . '% humidity.';

                            $ann = $obj->addAnnotation(
                                'reply', 'Dark Sky', 'https://darksky.net/', 
                                $icon_url, $weather_content, null, null, null,
                                ["weather" => $weather ],
                                false
                            );
                        }
                         */
                    }
                });
                
                // Hook into the "publish" (pre-publish) event to potentially implement better
                // @-mention shortcuts 
                \Idno\Core\site()->events()->addListener('publish', function (\Idno\Core\Event $event) {
                    //$obj = $event->data()['object'];
                    //if ($obj instanceof \IdnoPlugins\Status\Status) {
                    //    $obj->body = preg_replace('/@mb:(\w+)/', '<a href="https://micro.blog/$1">@$1</a>', $obj->body);
                    //}
                });

                // Hook into the annotation/add/* events in order to push notifications to my Microsub server
                \Idno\Core\site()->events()->addListener('notify', function (\Idno\Core\Event $event) {
                    $eventdata    = $event->data();
                    $user         = $eventdata['user'];
                    $notification = $eventdata['notification'];
                    $annotation   = $notification->getObject();

                    $notifications_endpoint = \Idno\Core\Idno::site()->config()->notifications_endpoint;
                    $notifications_token = \Idno\Core\Idno::site()->config()->notifications_token;
                    
                    if (($obj = $notification->getObject()) && isset($obj['permalink'])) {
                        $permalink = $obj['permalink'];
                    }

                    $vars = [
                        'user' => $user,
                        'notification' => $notification,
                    ];
                    $template_name = $notification->getMessageTemplate();
                    $tmpl = clone \Idno\Core\Idno::site()->template();
                    $tmpl->setTemplateType('websub_notifications');
                    $body = $tmpl->__($vars)->draw($template_name);

                    $payload = [ 
                        'type' => ['h-entry'],
                        'properties' => [
                            'name' => [$notification->getMessage()],
                            'author' => [
                                [
                                    "type" => ['h-card'],
                                    "properties" => [ 
                                        "photo" => [
                                            $annotation['owner_image']
                                        ],
                                        "name" => [
                                            $annotation['owner_name']
                                        ],
                                        "url" => [
                                            $annotation['owner_url']
                                        ]
                                    ]
                                ]
                            ],
                            'content' => [
                                ['html' => $body, 'value' => '']
                            ]
                        ]
                    ];
                    $json_payload = json_encode($payload);
                    
                    $ch = curl_init($notifications_endpoint);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',                                                                                
                        'Content-Length: ' . strlen($json_payload),
                        'Authorization: Bearer ' . $notifications_token 
                    ));
                    $result = curl_exec($ch);
                });
            }

        }

    }
