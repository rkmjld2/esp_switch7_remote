<?php

/* =========================================================
   ESP-SWITCH7
   SCHEDULE DISPLAY
   Render + TiDB Cloud
   ========================================================= */

date_default_timezone_set("Asia/Kolkata");


/* =========================================================
   DATABASE SETTINGS
   ========================================================= */

$host     = getenv("DB_HOST");
$user     = getenv("DB_USER");
$password = getenv("DB_PASSWORD");
$database = getenv("DB_NAME");
$port     = getenv("DB_PORT");


/* =========================================================
   TiDB CLOUD SSL CONNECTION
   ========================================================= */

$conn = mysqli_init();

mysqli_ssl_set(
    $conn,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL
);

mysqli_real_connect(
    $conn,
    $host,
    $user,
    $password,
    $database,
    intval($port),
    NULL,
    MYSQLI_CLIENT_SSL
);


if (mysqli_connect_errno()) {

    die(
        "Database connection failed: " .
        mysqli_connect_error()
    );

}


/* =========================================================
   CURRENT INDIA TIME
   ========================================================= */

$current_time =
    date("Y-m-d H:i:s");

$current_display =
    date("d-m-Y H:i:s");

$today_day =
    date("l");


/* =========================================================
   CHECK ACTIVE STATUS
   ========================================================= */

function check_status(
    $start,
    $end,
    $current_time
) {

    if (
        empty($start) ||
        empty($end)
    ) {

        return "INACTIVE";

    }


    $start_timestamp =
        strtotime($start);

    $end_timestamp =
        strtotime($end);

    $current_timestamp =
        strtotime($current_time);


    if (
        $current_timestamp >= $start_timestamp &&
        $current_timestamp <= $end_timestamp
    ) {

        return "ACTIVE";

    }


    return "INACTIVE";
}


/* =========================================================
   GET ALL SCHEDULES
   ========================================================= */

$sql = "
SELECT *
FROM weekly_schedule
ORDER BY id
";


$result = mysqli_query(
    $conn,
    $sql
);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>ESP-SWITCH7 Schedule Display</title>


<style>

body {

    font-family: Arial, sans-serif;

    background: #eeeeee;

    margin: 0;

    padding: 20px;

}


h1 {

    text-align: center;

}


.schedule-container {

    max-width: 1000px;

    margin: auto;

}


.current-box {

    background: white;

    border-radius: 10px;

    padding: 15px;

    text-align: center;

    font-size: 20px;

    margin-bottom: 20px;

    box-shadow: 0 2px 8px #aaa;

}


.menu {

    text-align: center;

    margin-bottom: 20px;

}


.menu a {

    background: #333;

    color: white;

    text-decoration: none;

    padding: 10px 18px;

    border-radius: 5px;

}


.period {

    border-radius: 12px;

    padding: 20px;

    margin-bottom: 20px;

    color: white;

    cursor: pointer;

    box-shadow: 0 3px 8px #999;

}


.period1 {

    background: #1976d2;

}


.period2 {

    background: #2e7d32;

}


.period3 {

    background: #ef6c00;

}


.period.inactive {

    opacity: 0.55;

}


.period.active {

    opacity: 1;

    border: 5px solid yellow;

}


.period-title {

    font-size: 25px;

    font-weight: bold;

}


.datetime {

    font-size: 18px;

    margin-top: 10px;

}


.status {

    font-size: 24px;

    font-weight: bold;

    margin-top: 15px;

}


.active-status {

    color: yellow;

}


.inactive-status {

    color: white;

}


.pins {

    display: flex;

    flex-wrap: wrap;

    gap: 8px;

    margin-top: 15px;

}


.pin {

    background: white;

    color: #333;

    border-radius: 5px;

    padding: 8px 12px;

    font-weight: bold;

}


.pin.selected {

    background: #00c853;

    color: white;

}


.no-schedule {

    background: white;

    padding: 20px;

    border-radius: 10px;

    text-align: center;

    font-size: 20px;

}

</style>


<script>

/* =========================================================
   REFRESH EVERY SECOND
   ========================================================= */

setTimeout(
    function() {

        location.reload();

    },
    1000
);


/* =========================================================
   PERIOD CLICK
   ========================================================= */

function periodClicked(
    period,
    id
) {

    alert(
        "Period " +
        period +
        "\nSchedule ID: " +
        id
    );

}

</script>

</head>


<body>


<h1>
ESP-SWITCH7 Schedule
</h1>


<div class="menu">

<a href="schedule.php">
Edit Schedule
</a>

</div>


<div class="current-box">

<b>
Current India Time
</b>

<br>

<?php

echo $current_display;

?>

<br>

<b>
Today:
</b>

<?php

echo $today_day;

?>

</div>


<div class="schedule-container">


<?php

$found_schedule = false;


/* =========================================================
   READ ALL DATABASE ROWS
   ========================================================= */

while (
    $row =
    mysqli_fetch_assoc($result)
) {


    /* =====================================================
       THREE PERIODS
       ===================================================== */

    for (
        $period_number = 1;
        $period_number <= 3;
        $period_number++
    ) {


        $start =
            $row[
                "start_time_" .
                $period_number
            ];


        $end =
            $row[
                "end_time_" .
                $period_number
            ];


        $pins =
            $row[
                "pins_output_" .
                $period_number
            ];


        /* -------------------------------------------------
           EMPTY PERIOD
           ------------------------------------------------- */

        if (
            empty($start) ||
            empty($end)
        ) {

            continue;

        }


        $found_schedule = true;


        /* -------------------------------------------------
           ACTIVE / INACTIVE
           ------------------------------------------------- */

        $status =
            check_status(
                $start,
                $end,
                $current_time
            );


        if ($status == "ACTIVE") {

            $status_class =
                "active";

            $status_text_class =
                "active-status";

        }
        else {

            $status_class =
                "inactive";

            $status_text_class =
                "inactive-status";

        }


        $period_class =
            "period" .
            $period_number;


        /* -------------------------------------------------
           PIN ARRAY
           ------------------------------------------------- */

        $pin_array = array();


        if (!empty($pins)) {

            $pin_array =
                explode(
                    ",",
                    $pins
                );

        }

?>

<div
    class="period
           <?php echo $period_class; ?>
           <?php echo $status_class; ?>"
    onclick="
        periodClicked(
            <?php
                echo $period_number;
            ?>,
            <?php
                echo $row["id"];
            ?>
        )
    "
>


<div class="period-title">

Period
<?php echo $period_number; ?>

</div>


<div>

<b>Day:</b>

<?php

echo htmlspecialchars(
    $row["day_week"]
);

?>

</div>


<div class="datetime">

<b>Start:</b>

<?php

echo date(
    "d-m-Y H:i:s",
    strtotime($start)
);

?>


<br>


<b>End:</b>

<?php

echo date(
    "d-m-Y H:i:s",
    strtotime($end)
);

?>

</div>


<div
    class="status
           <?php
               echo $status_text_class;
           ?>"
>

<?php

echo $status;

?>

</div>


<div class="pins">


<?php

for (
    $pin_number = 1;
    $pin_number <= 8;
    $pin_number++
) {


    $pin_name =
        "D" . $pin_number;


    $selected = false;


    foreach (
        $pin_array as $saved_pin
    ) {

        if (
            trim($saved_pin)
            == $pin_name
        ) {

            $selected = true;

        }

    }

?>


<div
    class="pin
    <?php

    if ($selected) {

        echo "selected";

    }

    ?>"
>


<?php

if ($selected) {

    echo "✓ ";

}

echo $pin_name;

?>

</div>


<?php

}

?>


</div>


</div>


<?php

    }

}


/* =========================================================
   NO SCHEDULE
   ========================================================= */

if (!$found_schedule) {

?>

<div class="no-schedule">

No working schedule is currently configured.

</div>

<?php

}

?>


</div>


</body>

</html>


<?php

mysqli_close($conn);

?>