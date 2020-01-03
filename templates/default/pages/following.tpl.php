<div>
  <div class="row following">
    <div class="col-md-20 col-md-offset-0">
      <div class="h-feed row">
        <div class="following-overview">
          <h1 class="p-name"><i class="fa fa-rss"></i> Following</h1>
          <a class="u-url" href="/following" style="display:none">Following</a>
          <a class="u-author" href="https://cleverdevil.io"></a>
            
          <p>
            Below is a list of my subscriptions from around the web that I read in
            <a href="https://alltogethernow.io">my social reader of choice</a>. 
            Subscriptions are pulled directly from 
            <a href="https://aperture.p3k.io">Aperture</a>, which is a service
            that handles fetching content from my subscriptions, tracking what
            content I have and have not read, and more.
          </p>
        </div> 

        <div class="channels">
        <?php
        foreach ($channels as $channel) {
        ?>
        <div class="channel">
          <h2 class="channel-name"><?= $channel['name'] ?></h2>
          <ul class="feeds">
          <?php
          foreach ($channel['feeds'] as $feed) {
            $name = empty($feed->name) ? $feed->url : $feed->name;
          ?>
            <li class="h-entry" id="<?= md5($feed->url) ?>">
              <div class="follow-of-meta" style="display: none">
                <a class="u-author" href="https://cleverdevil.io"></a>
                <span class="p-name">Following <?= $name ?></span>
                <a class="u-url" href="/following#<?= md5($feed->url) ?>"></a>
                <div class="e-content">
                  Following <a href="<?= $feed->url ?>"><?= $name ?></a> since 
                  <time class="dt-published" datetime="<?= date() ?>"></time>
                </div>
              </div>
                
              <a class="u-follow-of" onclick="window.open('https://monocle.p3k.io/preview?url=<?= $feed->url ?>')" href="<?= $feed->url ?>"><?= $name ?></a>
            </li>
          <?php
          }
          ?>
          </ul>
        </div>
        <?php
        }
        ?>
        </div>
      </div>
    </div>
  </div>
</div>
