<div id="summary">
<div id="summary-container" class="col-md-20 col-md-offset-0">

<h1><i class="fa fa-social-home"></i>Summary for <?= $description ?></h1>

<p>In <?= $description ?>, <a href="/archive/<?= $year ?>/<?= $month ?>">Jonathan posted <?= $count ?> times</a>.</p>

<ul id="content-types">
<?php
foreach (array_keys($entities) as $category) {
?>
    <li>
        <?= $typemap[$category] ?>
        <?= count($entities[$category]) ?> <?= ($category == "Photos") ? "Photo albums" : $category ?>
    </li>
<?php
} 

if ($health_found) {
?>
    <li>
        <i class="fa fa-heartbeat"></i>
        <?= count((array) $health_data) ?>
        Health
    </li>
<?php
}
?>
</ul>


<!-- posts -->
<?php
if (array_key_exists("Posts", $entities)) {
?>
<section id="posts">
    <h2><?= $typemap["Posts"] ?>Posts</h2>
    <ul>
<?php
    foreach ($entities["Posts"] as $post) {
?>
        <li><a href="<?= $post->getURL() ?>"><?= htmlentities(strip_tags($post->getTitle())) ?></a></li>
<?php
    }
?>
    </ul>
</section>
<?php
}
?>


<!-- reviews -->
<?php
if (array_key_exists("Reviews", $entities)) {
?>
<section id="reviews">
    <h2><?= $typemap["Reviews"] ?>Reviews</h2>
    <ul>
<?php
    foreach ($entities["Reviews"] as $post) {
?>
        <li>
            <a href="<?= $post->getURL() ?>">
                <?= htmlentities(strip_tags($post->getTitle())) ?>
                - <?php
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $post->getRating()) {
                        echo '<i class="fas fa-star"></i>';
                    } else {
                        echo '<i class="far fa-star"></i>';
                    }
                }
                ?>
            </a>
        </li>
<?php
    }
?>
    </ul>
</section>
<?php
}
?>


<!-- recipes -->
<?php
if (array_key_exists("Recipes", $entities)) {
?>
<section id="recipes">
    <h2><?= $typemap["Recipes"] ?>Recipes</h2>
    <ul>
<?php
    foreach ($entities["Recipes"] as $post) {
?>
        <li><a href="<?= $post->getURL() ?>"><?= htmlentities(strip_tags($post->getTitle())) ?></a></li>
<?php
    }
?>
    </ul>
</section>
<?php
}
?>


<!-- rsvps -->
<?php
if (array_key_exists("RSVP", $entities)) {
?>
<section id="rsvps">
    <h2><?= $typemap["RSVP"] ?>RSVPs</h2>
    <ul>
<?php
    foreach ($entities["RSVP"] as $post) {
?>
        <li><a href="<?= $post->getURL() ?>"><?= htmlentities(strip_tags($post->getTitle())) ?></a></li>
<?php
    }
?>
    </ul>
</section>
<?php
}
?>


<!-- photos -->
<?php
if (array_key_exists("Photos", $entities)) {
?>    
<section id="photos">
    <h2><?= $typemap["Photos"] ?>Photos</h2>
<?php 
   foreach ($entities["Photos"] as $photo) {
        $thumbs = [];
        if ($photo['thumbnail_large'] != null) {
            array_push($thumbs, $photo['thumbnail_large']);
        } else if ($photo['thumbs_large'] != null) {
            foreach ($photo['thumbs_large'] as $thumb) {
                array_push($thumbs, $thumb['url']);
            }
        } else {
            continue;
        }

        foreach ($thumbs as $thumb) {
?>
            <a href="<?= $photo->getURL() ?>">
            <div class="photo" style="background-image: url(<?= $thumb ?>); background-size: cover; background-position: 50%"></div>
            </a>
<?php
        }
    }
}
?>
</section>


<!-- health records -->
<?php
if ($health_found) {
?>
<section id="health">
    <h2><i class="fa fa-heartbeat"></i> Health</h2>
    
    <?php
    foreach (array_keys($health_data) as $month_day) {

        $data = $health_data[$month_day];

        $mv_percent = round(($data->active_energy->qty / 500) * 100, 0);
        $ex_percent = round(($data->apple_exercise_time->qty / 30) * 100, 0);
        $st_percent = round(($data->apple_stand_hour->qty / 12) * 100, 0);
        
        $target_date = str_replace('-', '/', $month_day);
    ?>
        <div class="activity-ring">
          <a href="/health/<?= $target_date ?>">
          <div class="ring-container">
            <svg class="ActivityRings" viewBox='0 0 37 37'>
              <g class="ring ring-move" style="transform: scale(1) rotate(-90deg);">
                  <circle stroke-width="3" r="15.915" cx="50%" cy="50%" class="background" />
                  <circle stroke-width="3" r="15.915" cx="50%" cy="50%" class="completed" stroke-dasharray="<?= $mv_percent ?>, 100" />
              </g>
              <g class="ring ring-exercise" style="transform: scale(0.75) rotate(-90deg);">
                  <circle stroke-width="4" r="15.915" cx="50%" cy="50%" class="background" />
                  <circle stroke-width="4" r="15.915" cx="50%" cy="50%" class="completed" stroke-dasharray="<?= $ex_percent ?>, 100" />
              </g>
              <g class="ring ring-stand" style="transform: scale(0.5) rotate(-90deg);">
                  <circle stroke-width="6" r="15.915" cx="50%" cy="50%" class="background" />
                  <circle stroke-width="6" r="15.915" cx="50%" cy="50%" class="completed" stroke-dasharray="<?= $st_percent ?>, 100" />
              </g>
            </svg>
          </div>
          </a>
        </div>
    <?php
    }
    ?>
</section>
<?php
}
?>

<!-- checkins -->
<?php
if (array_key_exists("Locations", $entities)) {
?>
<section id="locations">
    <h2><?= $typemap["Locations"] ?>Checkins</h2>
    <div id="map"></div>
    
    <script>
    var map = L.map('map', {
        touchZoom: false,
        scroolWheelZoom: false
    }).setView([0, 0], 16);
    var layer = new L.StamenTileLayer('toner-lite');
    map.addLayer(layer);

    var group = new L.featureGroup();

    <?php
    foreach ($entities["Locations"] as $location) {
    ?>
        var marker<?php echo $location->_id?> = L.marker([<?php echo $location->lat?>, <?php echo $location->long?>], {
            title: "<?php echo $location->placename ?>"
        });
        marker<?php echo $location->_id?>.addTo(map);
        group.addLayer(marker<?php echo $location->_id?>);
        marker<?php echo $location->_id?>.bindPopup(
            "<b>" + <?php echo json_encode(htmlspecialchars($location->getTitle()))?> + "</b><br><a href=\"<?php echo $location->getURL()?>\"><?php echo date('F j, Y', $location->created)?></a>"
        );
    <?php
    }
    ?>
    map.fitBounds(group.getBounds());
    map.scrollWheelZoom.disable();
    map.touchZoom.disable();
    map.doubleClickZoom.disable();
    </script>
</section>
<?php
}
?>



<!-- watched -->
<?php
if (array_key_exists("Watching", $entities)) {
?>
<section id="watching">
    <h2><?= $typemap["Watching"] ?>Watched</h2>
    <?php
    foreach ($entities["Watching"] as $watch) {
        $poster = '';
        if ($attachments = $watch->getAttachments()) {
            foreach ($attachments as $attachment) {
                $poster = '<img src="' . $attachment['url'] . '" class="poster" alt="' . $watch->getTitle()  . '" title="' . $watch->getTitle() . '">';
                break;
            }
        }
    ?>
        <div class="watched">
            <a href="<?= $watch->getURL() ?>">
                <?= $poster ?>
            </a>
        </div>
    <?php
    }
    ?>
</section>
<?php
}
?>


<!-- played -->
<?php
if (array_key_exists("Play", $entities)) {
?>
<section id="play">
    <h2><?= $typemap["Play"] ?>Play</h2>
    <?php
    foreach ($entities["Play"] as $play) {
        $art = '';
        if ($attachments = $play->getAttachments()) {
            foreach ($attachments as $attachment) {
                $art = '<img src="' . $attachment['url'] . '" class="art" alt="' . $play->getTitle()  . '" title="' . $play->getTitle() . '">';
                break;
            }
        }
    ?>
        <div class="played">
            <a href="<?= $play->getURL() ?>">
                <?= $art ?>
            </a>
        </div>
    <?php
    }
    ?>
</section>
<?php
}
?>


<!-- listened -->
<?php
if (array_key_exists("Listen", $entities)) {
?>
<section id="listening">
    <h2><?= $typemap["Listen"] ?>Listened</h2>
    <?php
    foreach ($entities["Listen"] as $listen) {
        $poster = '';
        if ($attachments = $listen->getAttachments()) {
            foreach ($attachments as $attachment) {
                $poster = '<img src="' . $attachment['url'] . '" class="poster" alt="' . $listen->getTitle()  . '" title="' . $listen->getTitle() . '">';
                break;
            }
        }
    ?>
        <div class="listened">
            <a href="<?= $listen->getURL() ?>">
                <?= $poster ?>
            </a>
        </div>
    <?php
    }
    ?>
</section>
<?php
}
?>




<!-- status updates -->
<?php
if (array_key_exists("Status updates", $entities)) {
?>
<section id="statuses">
    <h2><?= $typemap["Status updates"] ?>Microblog</h2>
    
    <details>
        <summary><?= count($entities["Status updates"]) ?> updates</summary>
        <?php
        foreach ($entities["Status updates"] as $status) {
        ?>
        <div class="status">
            <?php
            if (!empty($status->inreplyto)) {
            ?>
            <div>
                <i class="fa fa-reply"></i>
                <?php
                if (is_array($status->inreplyto)) {
                ?>
                <a href="<?= $status->inreplyto[0] ?>"><?= $status->inreplyto[0] ?></a>
                <?php
                } else {
                ?>
                <a href="<?= $status->inreplyto ?>"><?= $status->inreplyto ?></a>
                <?php
                }
                ?>
            </div>
            <?php
            }
            ?>
            <div><?= $status->getBody() ?></div>
            <a href="<?= $status->getURL() ?>">
                <time datetime="<?= date(DATE_ISO8601, $status->created) ?>"><?= strftime('%d %b %Y', $status->created) ?></time>
            </a>
        </div>
        <?php
        }
        ?>
    </details>
<?php
}
?>
</section>


<!-- bookmarks -->
<?php
if (array_key_exists("Bookmarked pages", $entities)) {
?>
<section id="interactions">
    <h2><i class="fa fa-comments"></i>Interactions</h2>
    <details>
        <summary><?= count($entities["Bookmarked pages"]) ?> likes, reposts, and bookmarks</summary>
        <?php
        foreach ($entities["Bookmarked pages"] as $interaction) {
            $icon = '';
            $link = $interaction->body;

            if (!empty($interaction->targetURL)) {
                $icon = '<i class="fab fa-github"></i>';
                $link = $interaction->targetURL;
            } elseif (!empty($interaction->likeof)) {
                $icon = '<i class="fa fa-thumbs-up"></i>';
            } elseif (!empty($interaction->repostof)) {
                $icon = '<i class="fa fa-retweet"></i>';
            } else {
                $icon = '<i class="fa fa-bookmark"></i>';
            }

            $body = $interaction->body;
            if (!empty($interaction->pageTitle)) {
                $body = $interaction->pageTitle;
            } elseif (!empty($interaction->title)) {
                $body = $interaction->title;
            }
        ?>
            <div class="interaction">
                <?= $icon ?>
                <a href="<?= $link ?>" target="_blank">
                    <?= htmlentities(strip_tags($body)) ?>
                </a>
            </div>
        <?php
        }
        ?>
    </details>
</section>
<?php
}
?>



</div>
</div>
