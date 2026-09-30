<?php

/* =========================================================
   ESP-SWITCH7
   DISPLAY TODAY'S SCHEDULE ONLY
   Render + TiDB Cloud
   ========================================================= */

date_default_timezone_set("Asia/Kolkata");


/* =========================================================
   DATABASE SETTINGS
   Read from Render Environment Variables
   ========================================================= */

$host     = getenv("DB_HOST");
$user     = getenv("DB_USER");
$password = getenv("DB_PASSWORD");
$database = getenv("DB_NAME");
$port     = getenv("DB_PORT");


/* =========================================================
   CONNECT TO TiDB CLOUD
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
   INDIA TIME
   ========================================================= */

$current_time = date("Y-m-d H:i:s");

$current_display = date(
    "d-m-Y H:i:s"
);

$today_day = date("l");


/* =========================================================
   GET TODAY'S SCHEDULE ONLY
   ========================================================= */

$today_day_safe = mysqli_real_escape_string(
    $conn,
    $today_day
);


$sql = "
    SELECT *
    FROM weekly_schedule
    WHERE day_week = '$today_day_safe'
    LIMIT 1
";


$result = mysqli_query(
    $conn,
    $sql
);


$row = false;


if ($result) {

    $row = mysqli_fetch_assoc(
        $result
    );

}


/* =========================================================
   FUNCTION
   DISPLAY DATE AND TIME
   ========================================================= */

function display_datetime($value)
{
    if (
        $value === NULL ||
        $value === "" ||
        $value == "0000-00-00 00:00:00"
    ) {

        return "";

    }


    $timestamp = strtotime($value);


    if ($timestamp === false) {

        return "";

    }


    return date(
        "d-m-Y H:i",
        $timestamp
    );
}


/* =========================================================
   FUNCTION
   CHECK ACTIVE / INACTIVE
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
        $start_timestamp === false ||
        $end_timestamp === false ||
        $current_timestamp === false
    ) {

        return "INACTIVE";

    }


    if (
        $current_timestamp >= $start_timestamp &&
        $current_timestamp <= $end_timestamp
    ) {

        return "ACTIVE";

    }


    return "INACTIVE";
}


/* =========================================================
   PERIOD DATA
   ========================================================= */

$periods = array();


if ($row) {


    /* -----------------------------------------------------
       PERIOD 1
       ----------------------------------------------------- */

    $periods[1] = array(

        "start" =>
            $row["start_time_1"],

        "end" =>
            $row["end_time_1"],

        "pins" =>
            $row["pins_output_1"],

        "status" =>
            check_status(
                $row["start_time_1"],
                $row["end_time_1"],
                $current_time
            )

    );


    /* -----------------------------------------------------
       PERIOD 2
       ----------------------------------------------------- */

    $periods[2] = array(

        "start" =>
            $row["start_time_2"],

        "end" =>
            $row["end_time_2"],

        "pins" =>
            $row["pins_output_2"],

        "status" =>
            check_status(
                $row["start_time_2"],
                $row["end_time_2"],
                $current_time
            )

    );


    /* -----------------------------------------------------
       PERIOD 3
       ----------------------------------------------------- */

    $periods[3] = array(

        "start" =>
            $row["start_time_3"],

        "end" =>
            $row["end_time_3"],

        "pins" =>
            $row["pins_output_3"],

        "status" =>
            check_status(
                $row["start_time_3"],
                $row["end_time_3"],
                $current_time
            )

    );

}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>ESP-SWITCH7 Schedule</title>


<style>

/* =========================================================
   BODY
   ========================================================= */

body {

    font-family: Arial, sans-serif;

    background-color: #f2f2f2;

    margin: 0;

    padding: 20px;

}


/* =========================================================
   HEADING
   ========================================================= */

h1 {

    text-align: center;

    color: #333;

}


/* =========================================================
   CURRENT TIME
   ========================================================= */

.current-time {

    width: 500px;

    max-width: 90%;

    margin: 20px auto;

    padding: 15px;

    background-color: #222;

    color: white;

    text-align: center;

    font-size: 22px;

    font-weight: bold;

    border-radius: 10px;

}


/* =========================================================
   TODAY
   ========================================================= */

.today {

    text-align: center;

    font-size: 20px;

    font-weight: bold;

    margin-bottom: 20px;

}


/* =========================================================
   PERIOD BOX
   ========================================================= */

.period {

    width: 700px;

    max-width: 95%;

    margin: 20px auto;

    padding: 20px;

    border-radius: 12px;

    cursor: pointer;

    box-sizing: border-box;

}


/* =========================================================
   PERIOD COLORS
   ========================================================= */

.period-1 {

    background-color: #dbeafe;

    border: 3px solid #2563eb;

}


.period-2 {

    background-color: #dcfce7;

    border: 3px solid #16a34a;

}


.period-3 {

    background-color: #ffedd5;

    border: 3px solid #ea580c;

}


/* =========================================================
   PERIOD TITLE
   ========================================================= */

.period-title {

    font-size: 24px;

    font-weight: bold;

    margin-bottom: 15px;

}


/* =========================================================
   DATE TIME
   ========================================================= */

.date-time {

    font-size: 18px;

    line-height: 1.8;

}


/* =========================================================
   STATUS
   ========================================================= */

.active {

    color: green;

    font-size: 22px;

    font-weight: bold;

}


.inactive {

    color: red;

    font-size: 22px;

    font-weight: bold;

}


/* =========================================================
   PIN AREA
   ========================================================= */

.pins-title {

    margin-top: 15px;

    font-size: 18px;

    font-weight: bold;

}


.pins {

    display: flex;

    flex-wrap: wrap;

    gap: 8px;

    margin-top: 10px;

}


.pin {

    width: 55px;

    height: 40px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 6px;

    font-weight: bold;

}


.pin-selected {

    background-color: green;

    color: white;

}


.pin-not-selected {

    background-color: #cccccc;

    color: #333333;

}


/* =========================================================
   NO SCHEDULE
   ========================================================= */

.no-schedule {

    text-align: center;

    background-color: white;

    padding: 30px;

    border-radius: 10px;

    font-size: 20px;

}

</style>


<script>

/* =========================================================
   REFRESH PAGE EVERY 1 SECOND
   ========================================================= */

setTimeout(function() {

    location.reload();

}, 1000);


/* =========================================================
   PERIOD CLICK
   ========================================================= */

function periodClicked(period)
{

    alert(
        "You clicked " + period
    );

}

</script>

</head>


<body>


<h1>

ESP-SWITCH7 Schedule

</h1>


<!-- =====================================================
     CURRENT INDIA TIME
     ===================================================== -->

<div class="current-time">

    Current India Time:

    <br>

    <?php

    echo htmlspecialchars(
        $current_display
    );

    ?>

</div>


<!-- =====================================================
     TODAY
     ===================================================== -->

<div class="today">

    Today:

    <?php

    echo htmlspecialchars(
        $today_day
    );

    ?>

</div>


<?php

/* =========================================================
   NO SCHEDULE FOR TODAY
   ========================================================= */

if (!$row) {

?>

<div class="no-schedule">

    No schedule found for today.

</div>

<?php

}


/* =========================================================
   DISPLAY TODAY'S THREE PERIODS
   ========================================================= */

if ($row) {


    for (
        $p = 1;
        $p <= 3;
        $p++
    ) {


        $start =
            $periods[$p]["start"];


        $end =
            $periods[$p]["end"];


        $pins =
            $periods[$p]["pins"];


        $status =
            $periods[$p]["status"];


        /* -------------------------------------------------
           DO NOT DISPLAY EMPTY PERIOD
           ------------------------------------------------- */

        if (
            empty($start) ||
            empty($end)
        ) {

            continue;

        }


        /* -------------------------------------------------
           STATUS CLASS
           ------------------------------------------------- */

        if (
            $status == "ACTIVE"
        ) {

            $status_class =
                "active";

        }
        else {

            $status_class =
                "inactive";

        }

?>

<!-- =====================================================
     PERIOD BOX
     ===================================================== -->

<div
    class="period period-<?php echo $p; ?>"
    onclick="
        periodClicked(
            'Period <?php echo $p; ?>'
        )
    "
>


    <div class="period-title">

        Period <?php echo $p; ?>

    </div>


    <!-- START -->

    <div class="date-time">

        <b>Start:</b>

        <?php

        echo htmlspecialchars(
            display_datetime($start)
        );

        ?>

    </div>


    <!-- END -->

    <div class="date-time">

        <b>End:</b>

        <?php

        echo htmlspecialchars(
            display_datetime($end)
        );

        ?>

    </div>


    <!-- STATUS -->

    <div
        class="<?php echo $status_class; ?>"
    >

        Status:

        <?php

        echo htmlspecialchars(
            $status
        );

        ?>

    </div>


    <!-- PINS -->

    <div class="pins-title">

        Output Pins:

    </div>


    <div class="pins">


<?php


        /* -------------------------------------------------
           CHECK D1 TO D8
           ------------------------------------------------- */

        for (
            $i = 1;
            $i <= 8;
            $i++
        ) {


            $pin =
                "D" . $i;


            /* -------------------------------------------------
               Check selected pin
               ------------------------------------------------- */

            $selected = false;


            if (
                !empty($pins)
            ) {


                /*
                 * Convert the stored pin list
                 * to uppercase.
                 */

                $pin_list =
                    strtoupper($pins);


                /*
                 * Split:
                 *
                 * D1,D2,D3
                 *
                 * into:
                 *
                 * D1
                 * D2
                 * D3
                 */

                $pin_array =
                    array_map(
                        "trim",
                        explode(
                            ",",
                            $pin_list
                        )
                    );


                if (
                    in_array(
                        $pin,
                        $pin_array
                    )
                ) {

                    $selected = true;

                }

            }


            /* -------------------------------------------------
               DISPLAY PIN
               ------------------------------------------------- */

            if ($selected) {

                echo '

                <div
                    class="pin pin-selected"
                >
                    ' . $pin . ' ✓
                </div>

                ';

            }
            else {

                echo '

                <div
                    class="pin pin-not-selected"
                >
                    ' . $pin . '
                </div>

                ';

            }

        }

?>


    </div>

</div>


<?php

    }

}

?>


</body>

</html>


<?php

mysqli_close($conn);

?>
