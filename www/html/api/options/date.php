<?php
/**
 * **DATE**: manages the dates (timestamps).
 * 
 * Client perspective: 
 * - **POST** METHOD --> sets the timestamps in the DB
 * 
 * 
 * ## Description
 * This script sets the returned **content type to JSON**,
 * **includes the db_connection.php script** 
 * > It is used to connect to MySQL and execute basic SELECT queries
 * and then defines the response array.
 * 
 * Two functions are defined:
 * 
 *      valid_times()
 * Checks if the request timestamps are valid
 *      
 *      set_times()
 * Sets the timestamp's range in the options table
 * 
 * If the script isn't included it tries to connect to the database,
 * and checks the request method:
 * - if the method is POST it calls valid_times($t_conn_res)
 * > the function calls the set_times() function
 * 
 * When the called function ends the connection to the DB gets terminated,
 * and data (or errors) is collected inside the response array.
 * The response array gets echoed to the client in JSON format
 * 
 * Errors list:
 * - **800** - main: Method not supported by the target resource
 * - **801** - main: No data but also No errors
 * - **810** - valid_times(): 'from' date should come before the 'to' date
 * - **811** - valid_times(): 'from' date should be different to the 'to' date (or both zero)
 * - **812** - valid_times(): 'from' date and/or 'to' date should be numeric
 * - **813** - valid_times(): 'mintime' and/or 'maxtime' are/is not set in the request
 * - **820** - set_times(): Unable to change 'from' date and 'to' date (maybe values are already set?)
 * - **821** - set_times(): Unable to execute UPDATE query (query error)
 *
 * @since 01_01
 * @author Stefano Zenaro (https://github.com/mario33881)
 * @license MIT
 * @todo the "error" 820 should be more like a warning
*/
    
// Set the response type to JSON
header('Content-type: application/json');
// =======================================================================================
// INCLUDES 

// Include db_connection.php: database connection, queryToArray() --> select query execution
include_once(__DIR__ . '/../utils/db_connection.php');

// =======================================================================================
// VARIABLES

/// Prepare default response array with no errors
$response = array('errors' => array());

// =======================================================================================
// FUNCTIONS


/** 
 * Checks if the request timestamps are valid.
 * 
 * The function collect the mintime and maxtime timestamps,
 * checks if they are numbers.
 * 
 * If they are numbers their values must be equal to zero
 * or the mintime value has to be lower than the maxtime value.
 * 
 * In these cases, the set_times() function is called to set
 * the time in the DB.
 * 
 * Otherwise an error is collected in the $action_res array
 * 
 * @since 01_01
 * @param array $t_conn_res array with the connection object
 * @return array $action_res array with color data or errors
*/
function valid_times($t_conn_res){

    // array that will be returned by this function
    $action_res = array('errors' => array());
    
    // check if mintime and maxtime are set in the request
    if (isset($_POST['mintime']) && isset($_POST['maxtime'])) {
        $mintime = $_POST['mintime']; // save mintime in variable
        $maxtime = $_POST['maxtime']; // save maxtime in variable

        // check if mintime and maxtime are numeric
        if (is_numeric($mintime) && is_numeric($maxtime)){
            $mintime = intval($mintime); // be sure that $mintime is integer
            $maxtime = intval($maxtime); // be sure that $maxtime is integer

            if ($mintime > $maxtime){
                // invalid data: $maxtime should be greater than $mintime
                array_push($action_res['errors'], array("id" => 810,
                                                        "htmlcode" => 422,
                                                        "message" => "'from' date should come before the 'to' date"));
            }
            elseif ($mintime === $maxtime && $mintime === 0){
                // valid data: both are 0, show all the data
                $action_res = set_times($t_conn_res, $mintime, $maxtime);
            }
            elseif ($mintime < $maxtime){
                // valid data: $maxtime is greater than $mintime
                $action_res = set_times($t_conn_res, $mintime, $maxtime);
            }
            else{
                // invalid data: $mintime is equal to $maxtime
                // but they are not equal to 0
                array_push($action_res['errors'], array("id" => 811,
                                                        "htmlcode" => 422,
                                                        "message" => "'from' date should be different to the 'to' date (or both zero)"));
            }
        }
        else{
            // $mintime and $maxtime are not numeric
            array_push($action_res['errors'], array("id" => 812,
                                                        "htmlcode" => 422,
                                                        "message" => "'from' date and/or 'to' date should be numeric"));
        }
    }
    else{
        // mintime and/or maxtime are/is not set in the request 
        array_push($action_res['errors'], array("id" => 813,
                                                        "htmlcode" => 400,
                                                        "message" => "'mintime' and/or 'maxtime' are/is not set in the request"));
    }

    return $action_res;
}


/**
 * Sets the timestamp's range in the options table.
 * 
 * The function prepares the UPDATE query
 * with two integers: $mintime e $maxtime.
 * 
 * If the query executing throws an error
 * or it doesn't affect any row then
 * the error is collected in the $action_res array.
 * 
 * If there where no errors, the 'data' of the $action_res array
 * has the values sent by the request.
 * 
 * @since 01_01
 * @param array $t_conn_res array with the connection object
 * @param int $mintime minimum timestamp
 * @param int $maxtime maximum timestamp
 * @return array $action_res array with color data or errors
*/
function set_times($t_conn_res, $mintime, $maxtime){
    // array that will be returned by this function
    $action_res = array('errors' => array());

    // PREPARA STRINGA SQL ( con Prepared Statements)
    $query = "UPDATE t_options
              SET t_options.min_timestamp = ?, t_options.max_timestamp = ?
              WHERE t_options.id = 1";

    $stmt = $t_conn_res["connect_obj"]->prepare($query);

    // BIND ? con variabili di tipo "i" -> integer
    $stmt->bind_param('ii', $mintime, $maxtime);
    $stmt->execute();

    // check if there was an error
    if (!$stmt->error) {
        // the query should affect one row
        if($stmt->affected_rows === 0) {
            // sql instruction gave 0 results
            array_push($action_res["errors"], array("id" => 820,
                                                    "htmlcode" => 500,
                                                    "message" => "Unable to change 'from' date and 'to' date (maybe values are already set?)"));
        }
        else{
            // everything went ok
            $sec_mintime = intval(htmlspecialchars($mintime));
            $sec_maxtime = intval(htmlspecialchars($maxtime));
            $action_res["data"] = array("mintime" => $sec_mintime, "maxtime" => $sec_maxtime);
        }
    }
    else {
        // executing the query gave an error
        array_push($action_res["errors"], array("id" => 821,
                                                "htmlcode" => 500,
                                                "message" => "Unable to execute UPDATE query"));
    }

    return $action_res;
}


// =======================================================================================
// MAIN

if (!count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))) {
    // connect to the database
    $conn_res = dbconn($db_dbname);
    
    // check if no errors happened (connection successful)
    if (count($conn_res["errors"]) == 0){
        
        // check the request method
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $request_res = valid_times($conn_res);
        }
        else {
            // Unexpected request method
            // 405: The method received in the request-line is known by the origin server but not supported by the target resource.
            $request_res = array('errors' => array());
            array_push($request_res['errors'], array("id" => 800,
                                                        "htmlcode" => 405,
                                                        "message" => "Method not supported by the target resource"));
        }
        
        // close DB connection
        $conn_res["connect_obj"] -> close();
    }

    // Add all the connection errors inside the response's errors array (if present)
    foreach ($conn_res["errors"] as &$error) {
        array_push($response["errors"], $error);
    }

    // Add all the request errors inside the response's errors array (if present)
    foreach ($request_res['errors'] as &$error) {
        array_push($response['errors'], $error);
    }
    
    // insert data inside the response or set the response code 
    if (count($response['errors']) > 0){
        // If there were errors, set the the response code to the one of the first error
        http_response_code($response['errors'][0]["htmlcode"]);
    }
    else if (isset($request_res["data"])){
        // there were no errors and the data is set, put it inside the response
        $response["data"] = $request_res["data"];
    }
    else {
        // no errors and no data... Shouldn't happen, but set an error anyway
        array_push($response['errors'], array("id" => 801,
                                              "htmlcode" => 500,
                                              "message" => "No data but also No errors"));
        http_response_code(500);
    }
    
    // echo the result
    echo json_encode($response);
}

?>