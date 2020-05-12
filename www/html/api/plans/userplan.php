<?php
/** 
 *  
 * **USERPLAN**: manages user's plan (returns paths and names).
 *
 * Client perspective:
 * - **GET** METHOD: gets data about the selected plan
 * - **POST** METHOD: sets a new plan
 * 
 * The script tries to connect to the database
 * and then checks the request method.
 * 
 * If the method is GET get_userplan() is called.
 * Else post_userplan() is called.
 * > userplan_isvalid() is used to check if plan's data is correct
 * 
 * Error codes:
 * - **500** - main: Request Method not supported by the target resource
 * - **501** - main: No data but also No errors
 * - **510** - get_userplan(): The query returned an unexpected number of records
 * - **511** - get_userplan(): Error during query execution
 * - **520** - userplan_isvalid(): file is not an SVG file
 * - **521** - userplan_isvalid(): file not found
 * - **530** - post_userplan(): Unable to change the current plan for the new one
 * - **531** - post_userplan(): Unable to execute UPDATE query
 * - **532** - post_userplan(): Parameter 'name' wasn't set
 * 
 * @file
 * @since 01_01
 * @author Stefano Zenaro (https://github.com/mario33881)
 * @copyright MIT
 * 
*/

// Set the response type to JSON
header("Content-type: application/json");

// =======================================================================================
// INCLUDES 

// Include db_connection.php: database connection, queryToArray() --> select query execution
include_once(__DIR__ . "/../utils/db_connection.php");

// =======================================================================================
// VARIABLES

/// Prepare default response array with no errors
$response = array("errors" => array());
/// Plan's path (file system)
$planspath = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "static" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "maps";

// =======================================================================================
// FUNCTIONS


/**
 * This function returns data relative to the plan selected from the user.
 * 
 * The function selects the plan name from the options table inside the database,
 * concatenates the expected plan path with the name and returns both path and name inside an array.
 * 
 * Errors list:
 * - 510: The query returned an unexpected number of records
 * - 511: Error during query execution
 * 
 * @since 01_01
 * @param array $t_conn_res file system path with plans
 * @return array $action_res array with errors or plans data 
*/
function get_userplan($t_conn_res){
    global $planspath;
    // array that will be returned by this function
    $action_res = array("errors" => array());
    
    // query string
    $query = "SELECT t_options.id, t_options.plan FROM t_options WHERE id = 1";
    
    // execute query
    $plan_arr = queryToArray($t_conn_res["connect_obj"], $query);
    
    // be sure that there were no errors
    if (count($plan_arr["errors"]) == 0){
        // should be one row
        if (count($plan_arr["query_data"]) == 1){
            // set "data" of the response
            $plan_name = $plan_arr["query_data"][0]["plan"];
            $plan_path = $planspath . DIRECTORY_SEPARATOR . $plan_name . ".svg";
            $action_res["data"] = array("name" => $plan_name, "path" => $plan_path);
        }
        else {
            // unknown error: the function returned more than one value
            array_push($action_res["errors"], array("id" => 510,
                                                    "htmlcode" => 500,
                                                    "message" => "The query returned an unexpected number of records"));
        }
    }
    else {
        // An error occured during query execution
        array_push($action_res["errors"], array("id" => 511,
                                                "htmlcode" => 500,
                                                "message" => "Error during query execution"));
    }
    return $action_res;
}


/**
 * Checks if plan data is valid.
 * 
 * To do this first its existance is checked using file_exists() function,
 * then the function checks the mime type (it must be "SVG")
 * If data is valid the "data" value will be true.
 * Else the returned array has errors data.
 * 
 * Errors list:
 * - **520**: file is not an SVG file
 * - **521**: file not found
 * 
 * @since 01_01
 * @param array $t_plan_data plan's data to validate
 * @return array $isvalid_res array with errors or true
*/
function userplan_isvalid($t_plan_data){
    // array that will be returned by this function
    $isvalid_res = array("errors" => array(), "data" => "false");

    if(file_exists($t_plan_data["path"])){
        // if the file exists, check mime type
        if (mime_content_type($t_plan_data["path"]) === "image/svg+xml"){
            // file is an SVG
            $isvalid_res["data"] = true;
        }
        else{
            // Save an error inside the 'errors' array
            array_push($isvalid_res["errors"], array("id" => 520,
                                                     "htmlcode" => 500,
                                                     "message" => "File '" . htmlspecialchars($t_plan_data["name"]) . "' is not an SVG file"));
        }
    }
    else{
        // Couldn't find the plan
        array_push($isvalid_res["errors"], array("id" => 521,
                                                 "htmlcode" => 500,
                                                 "message" => "File '" . htmlspecialchars($t_plan_data["name"]) . "' not found"));
    }

    return $isvalid_res;
}


/**
 * Sets user's desired plan
 * 
 * First it checks if the client passed the plan name,
 * then it checks if its expected path is valid using the userplan_isvalid() function.
 * 
 * After that it executes an UPDATE query to set the new plan.
 * 
 * Errors list:
 * - **530**: Unable to change the current plan for the new one
 * - **531**: Unable to execute UPDATE query
 * - **532**: Parameter 'name' wasn't set
 * 
 * @since 01_01
 * @param array $t_conn_res array with connection object or errors
 * @return array $action_res array with color data or errors
 * 
*/
function post_userplan($t_conn_res){
    global $planspath;
    // array that will be returned by this function
    $action_res = array("errors" => array());

    if (isset($_POST["name"])) {
        $plan_name = $_POST["name"];
        $plan_path = $planspath . DIRECTORY_SEPARATOR . $plan_name . ".svg";
        $plan_data = array("name" => $plan_name, "path" => $plan_path);
        
        $check_isvalid = userplan_isvalid($plan_data);

        if ($check_isvalid["data"] === true){

            $query = "UPDATE t_options
                      SET t_options.plan = ?
                      WHERE id = 1";
            
            // prepare the query expecting one string ("s"), and execute it    
            $stmt = $t_conn_res["connect_obj"]->prepare($query);
            $stmt->bind_param("s", $plan_name);
            $stmt->execute();
            
            // check if there was an error
            if (!$stmt->error) {
                // the query should affect one row
                if($stmt->affected_rows === 0) {
                    // sql instruction gave 0 results
                    array_push($action_res["errors"], array("id" => 530,
                                                            "htmlcode" => 500,
                                                            "message" => "Unable to change the current plan for the new one ('" . 
                                                                          htmlspecialchars($plan_name)  . "'), be sure to select a new and existing plan"));
                }
                else{
                    // everything went ok
                    $action_res["data"] = htmlspecialchars($plan_name);
                }
            }
            else {
                // executing the query gave an error
                array_push($action_res["errors"], array("id" => 531,
                                                        "htmlcode" => 500,
                                                        "message" => "Unable to execute UPDATE query"));
            }
        }
        else{
            foreach ($check_isvalid["errors"] as &$error) {
                array_push($action_res["errors"], $error);
            }
        }
        
    }
    else{
        // the caller forgot the name param
        array_push($action_res["errors"], array("id" => 532,
                                                "htmlcode" => 400,
                                                "message" => "Parameter 'name' wasn't set"));
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
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            // If the request method is GET
            $request_res = array("errors" => []);
            $userplan_res = get_userplan($conn_res);
            
            // check if there were errors
            if (count($userplan_res["errors"]) == 0){
                $userplanisvalid_res = userplan_isvalid($userplan_res["data"]);
                
                if ($userplanisvalid_res["data"]){
                    // user's plan is valid: it exists and is an SVG
                    $planpath_rootrel = str_replace($_SERVER["DOCUMENT_ROOT"], "", str_replace(DIRECTORY_SEPARATOR, "/", $userplan_res["data"]));
                    $request_res["data"] = $planpath_rootrel;
                }
                else{
                    // Add all the errors inside the response's errors array (if present)
                    foreach ($userplanisvalid_res["errors"] as &$error) {
                        array_push($request_res["errors"], $error);
                    }
                }
            }
            else{
                // Add all the errors inside the response's errors array (if present)
                foreach ($userplan_res["errors"] as &$error) {
                    array_push($request_res["errors"], $error);
                }
            }
        }
        elseif ($_SERVER["REQUEST_METHOD"] === "POST"){
            // if the method is POST
            $request_res = post_userplan($conn_res);
        }
        else {
            // Unexpected request method
            // 405: The method received in the request-line is known by the origin server but not supported by the target resource. 
            $request_res = array('errors' => array());
            array_push($request_res['errors'], array("id" => 500,
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
        array_push($response['errors'], array("id" => 501,
                                              "htmlcode" => 500,
                                              "message" => "No data but also No errors"));
        http_response_code(500);
    }
    
    // echo the result
    echo json_encode($response);    
}

?>