<?php

    namespace IdnoPlugins\CleverCustomize {
    
        use Idno\Core\Webservice;

        class Main extends \Idno\Common\Plugin {

            function registerPages() {
                // register timeline, activity, and on this day pages
                \Idno\Core\site()->addPageHandler('/timeline', '\IdnoPlugins\CleverCustomize\Pages\Timeline');
                \Idno\Core\site()->addPageHandler('/activity', '\IdnoPlugins\CleverCustomize\Pages\Activity');
                \Idno\Core\site()->addPageHandler('/onthisday/([0-9]+)/([0-9]+)/?', '\IdnoPlugins\CleverCustomize\Pages\OnThisDay');
                \Idno\Core\site()->addPageHandler('/onthisday/', '\IdnoPlugins\CleverCustomize\Pages\OnThisDay');
                \Idno\Core\site()->addPageHandler('/8675', '\IdnoPlugins\CleverCustomize\Pages\Jenny');
                \Idno\Core\site()->addPageHandler('/archive/([0-9]+)/([0-9]+)/([0-9]+)/?', '\IdnoPlugins\CleverCustomize\Pages\Archive');
                \Idno\Core\site()->addPageHandler('/archive/([0-9]+)/([0-9]+)/?', '\IdnoPlugins\CleverCustomize\Pages\Archive');
                \Idno\Core\site()->addPageHandler('/archive', '\IdnoPlugins\CleverCustomize\Pages\ArchiveIndex');
                \Idno\Core\site()->addPageHandler('/overview', '\IdnoPlugins\CleverCustomize\Pages\Overview');
                \Idno\Core\site()->addPageHandler('/now', '\IdnoPlugins\CleverCustomize\Pages\Now');
                \Idno\Core\site()->addPageHandler('/map', '\IdnoPlugins\CleverCustomize\Pages\Map');
                \Idno\Core\site()->addPageHandler('/nicknames', '\IdnoPlugins\CleverCustomize\Pages\Nicknames');
                \Idno\Core\site()->addPageHandler('/summary/([0-9]+)/([0-9]+)/([0-9]+)/?', '\IdnoPlugins\CleverCustomize\Pages\Summary');
                \Idno\Core\site()->addPageHandler('/summary/([0-9]+)/([0-9]+)/?', '\IdnoPlugins\CleverCustomize\Pages\Summary');

                // override header
                \Idno\Core\Idno::site()->template()->replaceTemplate('shell/toolbar/main','clevercustomize/toolbar');
                
                // add in my CSS / JavaScript includes via a template extend
                \Idno\Core\Idno::site()->template()->extendTemplate('shell/head', 'clevercustomize/shell/head');
                \Idno\Core\Idno::site()->template()->extendTemplate('shell/footer', 'clevercustomize/shell/footer');
            }
            
            function registerEventHooks() {
                // Hook into the "published" event to inform micro.blog that my feed has been updated 
                \Idno\Core\site()->addEventHook('published', function (\Idno\Core\Event $event) {
                    Webservice::post("https://micro.blog/ping", array(
                        'url' => "https://cleverdevil.io/content/all/?_t=microblog"
                    ));
                });
                
                // Hook into the "publish" (pre-publish) event to potentially implement better
                // @-mention shortcuts 
                \Idno\Core\site()->addEventHook('publish', function (\Idno\Core\Event $event) {
                    //$obj = $event->data()['object'];
                    //if ($obj instanceof \IdnoPlugins\Status\Status) {
                    //    $obj->body = preg_replace('/@mb:(\w+)/', '<a href="https://micro.blog/$1">@$1</a>', $obj->body);
                    //}
                });

                // Hook into the annotation/add/* events in order to push notifications to my Microsub server
                \Idno\Core\site()->addEventHook('notify', function (\Idno\Core\Event $event) {
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
