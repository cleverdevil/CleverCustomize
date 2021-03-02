<?php
    
$mv_percent = round(($data->active_energy->qty / 500) * 100, 0);
$ex_percent = round(($data->apple_exercise_time->qty / 30) * 100, 0);
$st_percent = round(($data->apple_stand_hour->qty / 12) * 100, 0);

function calcLeft($min, $avg, $max) {
    $percentage = round(100 * (($avg - $min) / $max), 0);
    return $percentage;
}

function genBG($min, $avg, $max) {
    $pct = calcLeft($min, $avg, $max);
    return "background: linear-gradient(90deg, #C53F3F 0%, #70BED8 $pct%, #94D55C 100%);";
}

function genRow($title, $label, $min, $avg, $max) {
    $bg = genBG($min, $avg, $max);
    $pct = calcLeft($min, $avg, $max); 

    echo('<tr>');
    echo('  <td class="title">' . $title . '<small>' . $label . '</small></td>');
    echo('  <td>');
    echo('    <div class="bar" style="' . $bg . '"></div>');
    echo('    <div>');
    echo('      <span class="legend min">' . $min . '</span>');
    echo('      <span class="legend avg" style="margin-left: ' . $pct . '%;">' . $avg . '</span>');
    echo('      <span class="legend max">' . $max . '</span>');
    echo('    </div>');
    echo('  </td>');
    echo('</tr>');
}

?>

<style type="text/css">
    div.health-widget, div.health-history {
      font-family: 'Raleway', sans-serif !important;
      background: black;
      color: white;
      border-radius: 1em;
      padding: 1em 2em;
      overflow: hidden;
      min-width: 650px;
      max-width: 750px;
      margin: 0 auto;
    }
    div.health-history {
        padding-bottom: 0;
    }

    div.health-widget h1, div.health-history h1 {
      margin: -16px -32px 1em -32px;
      padding: 10px 20px;
      font-size: 24px !important;
      font-family: 'Raleway', sans-serif !important;
      background: #333;
    }
    div.health-widget h2, div.health-history h2 {
      font-size: 20px !important;
      font-family: 'Raleway', sans-serif !important;
      font-weight: bold;
    }
    
    .row {
      display: flex;
      flex-direction: row;
      flex-wrap: wrap;
      width: 100%;
    }
    
    .column {
      display: flex;
      flex-direction: column;
      flex-basis: 100%;
      flex: 1;
    }

    div.activity-ring {
      padding-left: 1em;
    }
    div.activity-ring table {
      font-size: 10px;
      color: white;
      margin: 1em 0;
      border-collapse: collapse;
      width: 120px;
      text-align: center;
      line-height: 1.2em !important;
    }
    div.activity-ring table td {
      margin: 0;
      padding: 0;
    }

    td.move-data, td.move-label {
      color: #c53f3d;
    }
    td.exercise-data, td.exercise-label {
      color: #94d55a;
    }
    td.stand-data, td.stand-label {
      color: #70bed7;
    }
    
    td.move-label, td.exercise-label, td.stand-label {
      font-weight: bold;
    }
    
    div.health-widget h2, div.health-history h2 {
      font-size: 20px;
      margin: 0 0 0.5em 0;
      padding: 0 0 5px 0;
      text-align: center;
      border-bottom: 1px solid #222;
    }

    table.metrics {
      color: white;
      font-size: 14px;
    }
    table.metrics td:nth-of-type(1) {
      text-align: right;
    }
    table.metrics td {
      padding: 3px 5px;
    }

    div.health-history {
        margin-top: 2em;
    }

    table.metrics td.title {
        font-weight: bold;
        vertical-align: top;
        width: 90px;
    }
    table.metrics td.title small {
        display: block;
        margin-top: -5px;
    }
    .bar {
        margin-top: 8px;
        width: 100%;
        height: 10px;
        border-radius: 5px;
    }
    .legend {
        display: inline-block;
        font-size: 9px;
        float: left;
    }
    .max {
        float: right;
    }

    @media only screen and (min-width: 428px) and (max-width: 767px) {
        div.health-widget, div.health-history {
            max-width: 400px;
            min-width: 400px;
        }
        div.ring-container {
            width: 350px;
            height: 350px;
            margin: 0 auto;
        }
        div.activity-ring table {
            width: 320px;
            font-size: 12px;
            margin-bottom: 30px;
        }
        .row {
            flex-direction: column;
        }
    }

</style>

<!-- daily health metrics -->
    <div class="health-widget col-md-20 col-md-offset-0">
      <h1>
        <i class="fa fa-medkit"></i> <?= date('F j, Y', $date) ?>
        <div style="text-align: right; margin-top: -1em;">
          <a href="/health/<?= date('Y/m/d', $prev) ?>"><i class="fa fa-chevron-circle-left"></i></a>
          <a href="/health/<?= date('Y/m/d', $next) ?>"><i class="fa fa-chevron-circle-right"></i></a>
        </div>
      </h1>
      <div class="row">
<?php
if (!$data_found) {
?>
    <p>No data found for this date. It may be in the future, or before I started collecting health data.</p>
<?php
} else {
?>
        <div class="activity-ring column">
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

          <table>
            <tr>
              <td class="move-label">MV</td>
              <td class="exercise-label">EX</td>
              <td class="stand-label">ST</td>
            <tr>
              <td class="move-data"><?= round($data->active_energy->qty, 0) ?> cal</td>
              <td class="exercise-data"><?= round($data->apple_exercise_time->qty, 0) ?> min</td>
              <td class="stand-data"><?= round($data->apple_stand_hour->qty, 2) ?> hr</td>
            </tr>
          </table>
        </div>

        <div class="activity-metrics column">
          <h2><i class="fa fa-street-view"></i> Activity</h2>
          <table class="metrics">
            <tr><td><b>Steps</b></td><td><?= round($data->step_count->qty, 0) ?></td></tr>
            <tr><td><b>Flights</b></td><td><?= round($data->flights_climbed->qty, 0) ?></td></tr>
            <tr><td><b>Distance</b></td><td><?= round($data->walking_running_distance->qty, 2) ?> mi</td></tr>
            <tr><td><b>Speed</b></td><td><?= round($data->walking_speed->qty, 2) ?> mph</td></tr>
          </table>
        </div>

<?php
$heart_count = 0;
?>
        <div class="heart-metrics column">
          <h2><i class="fa fa-heartbeat"></i> Heart</h2>
          <table class="metrics">
<?php if ($data->resting_heart_rate->qty > 0) { $heart_count++; ?>
            <tr><td><b>Resting</b></td><td><?= round($data->resting_heart_rate->qty, 0) ?> bpm</td></tr>
<?php } ?>
<?php if ($data->heart_rate->avg > 0) { $heart_count++; ?>
            <tr><td><b>Average</b></td><td><?= round($data->heart_rate->avg, 0) ?> bpm</td></tr>
<?php } ?>
<?php if ($data->heart_rate->min > 0) { $heart_count++; ?>
            <tr><td><b>Min</b></td><td><?= round($data->heart_rate->min, 0) ?> bpm</td></tr>
<?php } ?>
<?php if ($data->heart_rate->max > 0) { $heart_count++; ?>
            <tr><td><b>Max</b></td><td><?= round($data->heart_rate->max, 0) ?> bpm</td></tr>
<?php } ?>
<?php if ($heart_count == 0) { ?>
            <tr><td style="text-align: center"><i>No data</i></td></tr>
<?php } ?>
          </table>
        </div>

        <div class="sleep-analysis column">
          <h2><i class="fa fa-bed"></i> Sleep</h2>
          <table class="metrics">
<?php if ($data->sleep_analysis->inbed > 1) { ?>
            <tr><td><b>In Bed</b></td><td><?= round($data->sleep_analysis->inbed, 2) ?> hr</td></tr>
<?php } ?>
<?php if ($data->sleep_analysis->asleep > 1) { ?>
            <tr><td><b>Asleep</b></td><td><?= round($data->sleep_analysis->asleep, 2) ?> hr</td></tr>
<?php } ?>
<?php if (($data->sleep_analysis->asleep < 1) && ($data->sleep_analysis->inbed < 1)) { ?>
            <tr><td style="text-align: center"><i>No data</i></td></tr>
<?php } ?>
          </table>
        </div>
<?php
}
?>
      </div>
    </div>

    <!-- global health metrics -->
    <div class="health-history col-md-20 col-md-offset-0">
      <h1>
        <i class="fa fa-line-chart"></i> Historical Metrics
      </h1>
      <div class="row">
        <!-- activity -->
        <div class="activity-metrics column">
          <h2><i class="fa fa-street-view"></i> Activity</h2>
          <table class="metrics">
                <?php
                    genRow('Steps', '', 
                        round($global_data->daily_min_step_count, 0),
                        round($global_data->daily_avg_step_count, 0),
                        round($global_data->daily_max_step_count, 0)
                    );
                    genRow('Flights', '',
                        round($global_data->daily_min_flights_climbed, 0),
                        round($global_data->daily_avg_flights_climbed, 0),
                        round($global_data->daily_max_flights_climbed, 0)
                    );
                    genRow('Distance', 'mi',
                        round($global_data->daily_min_walking_running_distance, 0),
                        round($global_data->daily_avg_walking_running_distance, 0),
                        round($global_data->daily_max_walking_running_distance, 0)
                    );
                    genRow('Speed', 'mph',
                        round($global_data->daily_min_walking_speed, 2),
                        round($global_data->daily_avg_walking_speed, 2),
                        round($global_data->daily_max_walking_speed, 2)
                    );
                ?>
          </table>
        </div>
        
        <!-- heart -->
        <div class="heart-metrics column">
          <h2><i class="fa fa-heartbeat"></i> Heart</h2>
          <table class="metrics">
                <?php
                    genRow('Resting', 'bpm', 
                        round($global_data->daily_min_resting_heart_rate, 0),
                        round($global_data->daily_avg_resting_heart_rate, 0),
                        round($global_data->daily_max_resting_heart_rate, 0)
                    );
                    genRow('Average', 'bpm',
                        round($global_data->daily_min_heart_rate_avg, 0),
                        round($global_data->daily_avg_heart_rate_avg, 0),
                        round($global_data->daily_max_heart_rate_avg, 0)
                    );
                    genRow('Min', 'bpm',
                        round($global_data->daily_min_heart_rate_min, 0),
                        round($global_data->daily_avg_heart_rate_min, 0),
                        round($global_data->daily_max_heart_rate_min, 0)
                    );
                    genRow('Max', 'bpm',
                        round($global_data->daily_min_heart_rate_max, 0),
                        round($global_data->daily_avg_heart_rate_max, 0),
                        round($global_data->daily_max_heart_rate_max, 0)
                    );
                ?>
          </table>
        </div>
        
        <div class="sleep-metrics column">
          <h2><i class="fa fa-bed"></i> Sleep</h2>
          <table class="metrics">
                <?php
                    genRow('In Bed', 'hr', 
                        round($global_data->daily_min_sleep_analysis_inbed, 1),
                        round($global_data->daily_avg_sleep_analysis_inbed, 1),
                        round($global_data->daily_max_sleep_analysis_inbed, 1)
                    );
                    genRow('Asleep', 'hr',
                        round($global_data->daily_min_sleep_analysis_asleep, 1),
                        round($global_data->daily_avg_sleep_analysis_asleep, 1),
                        round($global_data->daily_max_sleep_analysis_asleep, 1)
                    );
                ?>
          </table>
        </div>

      </div>
    </div>
