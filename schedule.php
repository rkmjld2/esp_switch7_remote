```php
<?php

/* =========================================================
   ESP-SWITCH7
   SCHEDULE ENTRY PROGRAM
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
   FUNCTION
   DATE + TIME → DATETIME
   ========================================================= */

function make_datetime($date, $time)
{
    if (
        empty($date) ||
        empty($time)
    ) {
        return NULL;
    }

    return $date . " " . $time . ":00";
}


/* =========================================================
   FUNCTIONS FOR HTML DATE/TIME
   IMPORTANT:
   Safely handle NULL / empty database values
   ========================================================= */

function html_date($value)
{
    if (
        $value === NULL ||
        $value === ""
    ) {
        return "";
    }

    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return "";
    }

    return date(
        "Y-m-d",
        $timestamp
    );
}


function html_time($value)
{
    if (
        $value === NULL ||
        $value === ""
    ) {
        return "";
    }

    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return "";
    }

    return date(
        "H:i",
        $timestamp
    );
}


/* =========================================================
   FUNCTION
   DISPLAY DATETIME SAFELY
   Used in Saved Schedules table
   ========================================================= */

function display_datetime($value)
{
    if (
        $value === NULL ||
        $value === ""
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
   DELETE SCHEDULE
   ========================================================= */

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    mysqli_query(
        $conn,
        "DELETE FROM weekly_schedule
         WHERE id = $id"
    );

    header("Location: schedule.php");

    exit;
}


/* =========================================================
   MESSAGE
   ========================================================= */

$message = "";


/* =========================================================
   SAVE / UPDATE SCHEDULE
   ========================================================= */

if (isset($_POST["save_schedule"])) {

    $id = intval($_POST["id"]);


    /* -----------------------------------------------------
       DAY
       ----------------------------------------------------- */

    $day_week = mysqli_real_escape_string(
        $conn,
        $_POST["day_week"]
    );


    /* -----------------------------------------------------
       PERIOD 1
       ----------------------------------------------------- */

    $start_datetime_1 = make_datetime(
        $_POST["start_date_1"],
        $_POST["start_time_1"]
    );

    $end_datetime_1 = make_datetime(
        $_POST["end_date_1"],
        $_POST["end_time_1"]
    );

    $pins_output_1 = mysqli_real_escape_string(
        $conn,
        $_POST["pins_output_1"]
    );


    /* -----------------------------------------------------
       PERIOD 2
       ----------------------------------------------------- */

    $start_datetime_2 = make_datetime(
        $_POST["start_date_2"],
        $_POST["start_time_2"]
    );

    $end_datetime_2 = make_datetime(
        $_POST["end_date_2"],
        $_POST["end_time_2"]
    );

    $pins_output_2 = mysqli_real_escape_string(
        $conn,
        $_POST["pins_output_2"]
    );


    /* -----------------------------------------------------
       PERIOD 3
       ----------------------------------------------------- */

    $start_datetime_3 = make_datetime(
        $_POST["start_date_3"],
        $_POST["start_time_3"]
    );

    $end_datetime_3 = make_datetime(
        $_POST["end_date_3"],
        $_POST["end_time_3"]
    );

    $pins_output_3 = mysqli_real_escape_string(
        $conn,
        $_POST["pins_output_3"]
    );


    /* =====================================================
       NEW RECORD
       ===================================================== */

    if ($id == 0) {

        $sql = "INSERT INTO weekly_schedule
        (
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
        )
        VALUES
        (
            '$day_week',

            " .
            (
                $start_datetime_1 === NULL
                ? "NULL"
                : "'$start_datetime_1'"
            ) .
            ",

            " .
            (
                $end_datetime_1 === NULL
                ? "NULL"
                : "'$end_datetime_1'"
            ) .
            ",

            '$pins_output_1',

            " .
            (
                $start_datetime_2 === NULL
                ? "NULL"
                : "'$start_datetime_2'"
            ) .
            ",

            " .
            (
                $end_datetime_2 === NULL
                ? "NULL"
                : "'$end_datetime_2'"
            ) .
            ",

            '$pins_output_2',

            " .
            (
                $start_datetime_3 === NULL
                ? "NULL"
                : "'$start_datetime_3'"
            ) .
            ",

            " .
            (
                $end_datetime_3 === NULL
                ? "NULL"
                : "'$end_datetime_3'"
            ) .
            ",

            '$pins_output_3'
        )";


        if (mysqli_query($conn, $sql)) {

            $message =
                "Schedule saved successfully.";

        }
        else {

            $message =
                "Error: " . mysqli_error($conn);

        }

    }


    /* =====================================================
       UPDATE EXISTING RECORD
       ===================================================== */

    else {

        $sql = "UPDATE weekly_schedule SET

            day_week = '$day_week',

            start_time_1 =
            " .
            (
                $start_datetime_1 === NULL
                ? "NULL"
                : "'$start_datetime_1'"
            ) .
            ",

            end_time_1 =
            " .
            (
                $end_datetime_1 === NULL
                ? "NULL"
                : "'$end_datetime_1'"
            ) .
            ",

            pins_output_1 =
            '$pins_output_1',

            start_time_2 =
            " .
            (
                $start_datetime_2 === NULL
                ? "NULL"
                : "'$start_datetime_2'"
            ) .
            ",

            end_time_2 =
            " .
            (
                $end_datetime_2 === NULL
                ? "NULL"
                : "'$end_datetime_2'"
            ) .
            ",

            pins_output_2 =
            '$pins_output_2',

            start_time_3 =
            " .
            (
                $start_datetime_3 === NULL
                ? "NULL"
                : "'$start_datetime_3'"
            ) .
            ",

            end_time_3 =
            " .
            (
                $end_datetime_3 === NULL
                ? "NULL"
                : "'$end_datetime_3'"
            ) .
            ",

            pins_output_3 =
            '$pins_output_3'

            WHERE id = $id";


        if (mysqli_query($conn, $sql)) {

            $message =
                "Schedule updated successfully.";

        }
        else {

            $message =
                "Error: " . mysqli_error($conn);

        }

    }

}


/* =========================================================
   DEFAULT FORM VALUES
   ========================================================= */

$edit_id = 0;

$row = array(

    "day_week" => "",

    "start_time_1" => "",
    "end_time_1" => "",
    "pins_output_1" => "",

    "start_time_2" => "",
    "end_time_2" => "",
    "pins_output_2" => "",

    "start_time_3" => "",
    "end_time_3" => "",
    "pins_output_3" => ""

);


/* =========================================================
   EDIT RECORD
   ========================================================= */

if (isset($_GET["edit"])) {

    $edit_id =
        intval($_GET["edit"]);


    $result_edit = mysqli_query(
        $conn,
        "SELECT *
         FROM weekly_schedule
         WHERE id = $edit_id"
    );


    if (
        $result_edit &&
        mysqli_num_rows($result_edit) > 0
    ) {

        $row =
            mysqli_fetch_assoc(
                $result_edit
            );

    }

}


/* =========================================================
   GET ALL SCHEDULES
   ========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT *
     FROM weekly_schedule
     ORDER BY id"
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

<title>ESP-SWITCH7 Schedule</title>

<style>

body {

    font-family: Arial, sans-serif;

    background: #f2f2f2;

    margin: 20px;

}


.container {

    max-width: 1100px;

    margin: auto;

}


h1 {

    text-align: center;

}


.form-box {

    background: white;

    padding: 20px;

    border-radius: 10px;

    margin-bottom: 25px;

    box-shadow: 0 2px 8px #aaa;

}


.period {

    border: 2px solid #ccc;

    padding: 15px;

    margin-top: 15px;

    border-radius: 8px;

}


.period h3 {

    margin-top: 0;

}


input,
select {

    padding: 7px;

    margin: 4px;

}


button {

    padding: 10px 20px;

    font-size: 16px;

    cursor: pointer;

}


.save {

    background: green;

    color: white;

    border: none;

    border-radius: 5px;

}


table {

    width: 100%;

    border-collapse: collapse;

    background: white;

}


th,
td {

    border: 1px solid #ccc;

    padding: 8px;

    text-align: center;

}


th {

    background: #333;

    color: white;

}


.edit {

    background: orange;

    color: black;

    padding: 5px 10px;

    text-decoration: none;

}


.delete {

    background: red;

    color: white;

    padding: 5px 10px;

    text-decoration: none;

}


.message {

    background: #d9ffd9;

    padding: 10px;

    margin-bottom: 15px;

    border: 1px solid green;

}


.view-button {

    display: inline-block;

    background: blue;

    color: white;

    text-decoration: none;

    padding: 10px 15px;

    border-radius: 5px;

}

</style>

</head>


<body>

<div class="container">

<h1>ESP-SWITCH7 Weekly Schedule</h1>


<?php if ($message != "") { ?>

<div class="message">

<?php echo htmlspecialchars($message); ?>

</div>

<?php } ?>


<div class="form-box">

<h2>

<?php

if ($edit_id > 0) {

    echo "Edit Schedule";

}
else {

    echo "Add Schedule";

}

?>

</h2>


<form method="POST">

<input
    type="hidden"
    name="id"
    value="<?php echo $edit_id; ?>"
>


<!-- DAY -->

<label>

<b>Day:</b>

</label>


<select
    name="day_week"
    required
>

<option value="">
Select Day
</option>


<?php

$days = array(

    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday"

);


foreach ($days as $day) {

    $selected = "";

    if ($row["day_week"] == $day) {

        $selected = "selected";

    }

?>

<option
    value="<?php echo $day; ?>"
    <?php echo $selected; ?>
>

<?php echo $day; ?>

</option>

<?php

}

?>

</select>


<!-- =====================================================
     PERIOD 1
     ===================================================== -->

<div class="period">

<h3>Period 1</h3>


<label>
Start Date:
</label>

<input
    type="date"
    name="start_date_1"
    value="<?php
        echo html_date(
            $row["start_time_1"]
        );
    ?>"
>


<label>
Start Time:
</label>

<input
    type="time"
    name="start_time_1"
    value="<?php
        echo html_time(
            $row["start_time_1"]
        );
    ?>"
>


<br>


<label>
End Date:
</label>

<input
    type="date"
    name="end_date_1"
    value="<?php
        echo html_date(
            $row["end_time_1"]
        );
    ?>"
>


<label>
End Time:
</label>

<input
    type="time"
    name="end_time_1"
    value="<?php
        echo html_time(
            $row["end_time_1"]
        );
    ?>"
>


<br>


<label>
Output Pins:
</label>

<input
    type="text"
    name="pins_output_1"
    placeholder="D1,D2,D3"
    value="<?php
        echo htmlspecialchars(
            $row["pins_output_1"]
        );
    ?>"
>

</div>


<!-- =====================================================
     PERIOD 2
     ===================================================== -->

<div class="period">

<h3>Period 2</h3>


<label>
Start Date:
</label>

<input
    type="date"
    name="start_date_2"
    value="<?php
        echo html_date(
            $row["start_time_2"]
        );
    ?>"
>


<label>
Start Time:
</label>

<input
    type="time"
    name="start_time_2"
    value="<?php
        echo html_time(
            $row["start_time_2"]
        );
    ?>"
>


<br>


<label>
End Date:
</label>

<input
    type="date"
    name="end_date_2"
    value="<?php
        echo html_date(
            $row["end_time_2"]
        );
    ?>"
>


<label>
End Time:
</label>

<input
    type="time"
    name="end_time_2"
    value="<?php
        echo html_time(
            $row["end_time_2"]
        );
    ?>"
>


<br>


<label>
Output Pins:
</label>

<input
    type="text"
    name="pins_output_2"
    placeholder="D1,D2,D3"
    value="<?php
        echo htmlspecialchars(
            $row["pins_output_2"]
        );
    ?>"
>

</div>


<!-- =====================================================
     PERIOD 3
     ===================================================== -->

<div class="period">

<h3>Period 3</h3>


<label>
Start Date:
</label>

<input
    type="date"
    name="start_date_3"
    value="<?php
        echo html_date(
            $row["start_time_3"]
        );
    ?>"
>


<label>
Start Time:
</label>

<input
    type="time"
    name="start_time_3"
    value="<?php
        echo html_time(
            $row["start_time_3"]
        );
    ?>"
>


<br>


<label>
End Date:
</label>

<input
    type="date"
    name="end_date_3"
    value="<?php
        echo html_date(
            $row["end_time_3"]
        );
    ?>"
>


<label>
End Time:
</label>

<input
    type="time"
    name="end_time_3"
    value="<?php
        echo html_time(
            $row["end_time_3"]
        );
    ?>"
>


<br>


<label>
Output Pins:
</label>

<input
    type="text"
    name="pins_output_3"
    placeholder="D1,D2,D3"
    value="<?php
        echo htmlspecialchars(
            $row["pins_output_3"]
        );
    ?>"
>

</div>


<br>


<button
    type="submit"
    name="save_schedule"
    class="save"
>

Save Schedule

</button>


</form>

</div>


<h2>Saved Schedules</h2>


<table>

<tr>

<th>ID</th>

<th>Day</th>

<th>Period 1</th>

<th>Pins</th>

<th>Period 2</th>

<th>Pins</th>

<th>Period 3</th>

<th>Pins</th>

<th>Action</th>

</tr>


<?php

while (
    $schedule =
    mysqli_fetch_assoc($result)
) {

?>

<tr>

<td>
<?php echo $schedule["id"]; ?>
</td>


<td>
<?php
echo htmlspecialchars(
    $schedule["day_week"]
);
?>
</td>


<!-- PERIOD 1 -->

<td>

<?php

if (
    !empty(
        $schedule["start_time_1"]
    )
) {

    echo htmlspecialchars(
        display_datetime(
            $schedule["start_time_1"]
        )
    );

    echo "<br>to<br>";

    if (
        !empty(
            $schedule["end_time_1"]
        )
    ) {

        echo htmlspecialchars(
            display_datetime(
                $schedule["end_time_1"]
            )
        );

    }
    else {

        echo "-";

    }

}

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $schedule["pins_output_1"]
);

?>

</td>


<!-- PERIOD 2 -->

<td>

<?php

if (
    !empty(
        $schedule["start_time_2"]
    )
) {

    echo htmlspecialchars(
        display_datetime(
            $schedule["start_time_2"]
        )
    );

    echo "<br>to<br>";

    if (
        !empty(
            $schedule["end_time_2"]
        )
    ) {

        echo htmlspecialchars(
            display_datetime(
                $schedule["end_time_2"]
            )
        );

    }
    else {

        echo "-";

    }

}

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $schedule["pins_output_2"]
);

?>

</td>


<!-- PERIOD 3 -->

<td>

<?php

if (
    !empty(
        $schedule["start_time_3"]
    )
) {

    echo htmlspecialchars(
        display_datetime(
            $schedule["start_time_3"]
        )
    );

    echo "<br>to<br>";

    if (
        !empty(
            $schedule["end_time_3"]
        )
    ) {

        echo htmlspecialchars(
            display_datetime(
                $schedule["end_time_3"]
            )
        );

    }
    else {

        echo "-";

    }

}

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $schedule["pins_output_3"]
);

?>

</td>


<!-- ACTION -->

<td>

<a
    href="schedule.php?edit=<?php
        echo $schedule["id"];
    ?>"
    class="edit"
>
Edit
</a>

<br><br>

<a
    href="schedule.php?delete=<?php
        echo $schedule["id"];
    ?>"
    class="delete"
    onclick="
        return confirm(
            'Delete this schedule?'
        );
    "
>
Delete
</a>

</td>

</tr>

<?php

}

?>

</table>


<br><br>


<a
    href="display_schedule.php"
    class="view-button"
>
View Schedule Display
</a>


</div>

</body>

</html>


<?php

mysqli_close($conn);

?>
```

### What I changed

The main change is these three safe functions:

```text
html_date()
html_time()
display_datetime()
```

They first check:

```php
if ($value === NULL || $value === "") {
    return "";
}
```

and only then call:

```php
strtotime($value)
```

So if Period 2 or Period 3 has no schedule, PHP will **not** call `strtotime(NULL)`.

For example:

```text
Period 1
30-09-2026 09:32
to
02-10-2026 18:00

Period 2
(empty)

Period 3
(empty)
```

will now display correctly without the **Deprecated** warning and without `01-01-1970 05:30`.

You can replace your present `schedule.php` with the above version.
