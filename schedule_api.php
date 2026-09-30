<?php

/* =========================================================
   ESP-SWITCH7
   ESP8266 SCHEDULE API
   Render + TiDB Cloud
   ========================================================= */

header(
    "Content-Type: application/json"
);

date_default_timezone_set(
    "Asia/Kolkata"
);


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

    echo json_encode(
        array(

            "status" => "error",

            "message" =>
                "Database connection failed: " .
                mysqli_connect_error()

        )
    );

    exit;
}


/* =========================================================
   CURRENT INDIA DATE/TIME
   ========================================================= */

$current_datetime =
    date("Y-m-d H:i:s");


$current_day =
    date("l");


/* =========================================================
   ACTIVE PERIOD FUNCTION
   ========================================================= */

function isPeriodActive(
    $start,
    $end,
    $current
) {

    if (
        empty($start) ||
        empty($end)
    ) {

        return false;

    }


    /*
       DATETIME format:

       YYYY-MM-DD HH:MM:SS

       Therefore normal string comparison
       works correctly.
    */

    if (
        $current >= $start &&
        $current <= $end
    ) {

        return true;

    }


    return false;
}


/* =========================================================
   GET ALL SCHEDULES
   ========================================================= */

$sql = "

SELECT

    id,
    day_week,

    start_time_1,
    end_time_1,
    pins_output_1,

    start_time_2,
    end_time_2,
    pins_output_2,

    start_time_3,
    end_time_3,
    pins_output_3

FROM weekly_schedule

ORDER BY id

";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {

    echo json_encode(
        array(

            "status" => "error",

            "message" =>
                mysqli_error($conn)

        )
    );

    mysqli_close($conn);

    exit;
}


/* =========================================================
   ARRAYS
   ========================================================= */

$periods =
    array();


$active_periods =
    array();


$active_pins =
    array();


/* =========================================================
   READ ALL ROWS
   ========================================================= */

while (
    $row =
    mysqli_fetch_assoc($result)
) {


    /* =====================================================
       CHECK THREE PERIODS
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
           IGNORE COMPLETELY EMPTY PERIOD
           ------------------------------------------------- */

        if (
            empty($start) &&
            empty($end) &&
            empty($pins)
        ) {

            continue;

        }


        /* -------------------------------------------------
           CHECK ACTIVE
           ------------------------------------------------- */

        $active =
            isPeriodActive(
                $start,
                $end,
                $current_datetime
            );


        if ($active) {

            $status =
                "ACTIVE";

        }
        else {

            $status =
                "INACTIVE";

        }


        /* -------------------------------------------------
           PERIOD INFORMATION
           ------------------------------------------------- */

        $period_data = array(

            "id" =>
                $row["id"],

            "day" =>
                $row["day_week"],

            "period" =>
                $period_number,

            "start" =>
                $start,

            "end" =>
                $end,

            "pins" =>
                $pins,

            "active" =>
                $active,

            "status" =>
                $status

        );


        /* -------------------------------------------------
           ADD TO ALL PERIODS
           ------------------------------------------------- */

        $periods[] =
            $period_data;


        /* -------------------------------------------------
           ACTIVE PERIOD
           ------------------------------------------------- */

        if ($active) {


            $active_periods[] =
                $period_data;


            /* ---------------------------------------------
               CONVERT PIN STRING TO ARRAY
               --------------------------------------------- */

            if (!empty($pins)) {


                $pin_array =
                    explode(
                        ",",
                        $pins
                    );


                foreach (
                    $pin_array as $pin
                ) {


                    $pin =
                        trim($pin);


                    /* -------------------------------------
                       ACCEPT ONLY D1-D8
                       ------------------------------------- */

                    if (
                        preg_match(
                            '/^D[1-8]$/',
                            $pin
                        )
                    ) {


                        /* ---------------------------------
                           REMOVE DUPLICATES
                           --------------------------------- */

                        if (
                            !in_array(
                                $pin,
                                $active_pins
                            )
                        ) {

                            $active_pins[] =
                                $pin;

                        }

                    }

                }

            }

        }

    }

}


/* =========================================================
   FINAL RESPONSE
   ========================================================= */

$response = array(

    "status" =>
        "success",

    "current_datetime" =>
        $current_datetime,

    "current_day" =>
        $current_day,

    "periods" =>
        $periods,

    "active_periods" =>
        $active_periods,

    "active_pins" =>
        implode(
            ",",
            $active_pins
        )

);


/* =========================================================
   SEND JSON
   ========================================================= */

echo json_encode(
    $response
);


/* =========================================================
   CLOSE DATABASE
   ========================================================= */

mysqli_close($conn);

?>