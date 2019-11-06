<?php

$theme = 'default';
if (isset($_COOKIE['theme'])) {
    $theme = $_COOKIE['theme'];
}

?>

<div style="width:100%; height:100%; background-image:url(https://cleverdevil.io/file/ea148c8ee95a255990e82cbf2c46ae3a); background-size:cover; position: fixed; z-index:-1; top: 0;"></div>

<script type="text/javascript">
  function setThemePreference(preference) {
    Cookies.set('theme', preference);
    window.location.reload(false);
  }
</script>

<div id="theme-switcher">
  <a href="#" onclick="setThemePreference('default'); return null;">
    <div>
      <i class="fa fa-globe-w <?= $theme == 'default' ? 'active' : '' ?>"></i>
      <span class="label">OS theme</span>
    </div>
  </a>
  <a href="#" onclick="setThemePreference('dark'); return null;">
    <div>
      <i class="fa fa-moon <?= $theme == 'dark' ? 'active' : '' ?>"></i>
      <span class="label">Dark theme</span>
    </div>
  </a>
  <a href="#" onclick="setThemePreference('light'); return null;">
    <div>
      <i class="fa fa-sun <?= $theme == 'light' ? 'active' : '' ?>"></i>
      <span class="label">Light theme</span>
    </div>
  </a>
</div>

<?php
if ($_SERVER['REQUEST_URI'] != "/now") {
?>


<div id="current-status" onclick="window.location.href='/now'">
<?php
    $status_file = fopen("current.json", "r");
    $raw_json = fgets($status_file);
    $status = json_decode($raw_json, true);
    
    $battery_icon = "fa-battery-full";
    if ($status['battery_level'] <= 0.75) {
        $battery_icon = "fa-battery-three-quarters";
    }
    if ($status['battery_level'] <= 0.5) {
        $battery_icon = "fa-battery-half";
    }
    if ($status['battery_level'] <= 0.25) {
        $battery_icon = "fa-battery-quarter";
    }
    if ($status['battery_level'] <= 0.05) {
        $battery_icon = "fa-battery-empty";
    }

    $wifi_state = "wifi-disconnected";
    if (strlen($status['wifi']) > 0) {
        $wifi_state = "wifi-connected";
    }

    $motion_icon = "fa-male"; // stationary
    foreach ($status['motion'] as $value) {
        if ($value == "driving") {
            $motion_icon = "fa-car";
        } else if ($value == "walking") {
            $motion_icon = "fa-street-view";
        } else if ($value == "running") {
            $motion_icon = "fa-street-view";
        } else if ($value == "cycling") {
            $motion_icon = "fa-bicycle";
        }
    }
?>
    <div id="battery">
        <i class="fa <?= $battery_icon ?> <?= $status['battery_state'] ?>"></i>
        <span class="label">My iPhone battery</span>
    </div>
    <div id="wifi">
        <i class="fa fa-wifi <?= $wifi_state ?>"></i>
        <span class="label">My iPhone Wi-Fi</span>
    </div>
    <div id="motion">
        <i class="fa <?= $motion_icon ?>"></i>
        <span class="label">Current motion</span>
    </div>
    <div id="location">
        <i class="fa fa-map-marker"></i>
        <span class="label">Click for current location</span>
    </div>
</div>

<?php
}
?>

<div class="🕸💍">
  <a href="https://🕸💍.ws/⛈🛍✈️/previous">👈🏼</a>
  <a href="https://🕸💍.ws/">🕸💍</a>
  <a href="https://🕸💍.ws/⛈🛍✈️/next">👉🏼</a>
</div>

<?php

    include("templates/default/shell/toolbar/main.tpl.php");

?>

