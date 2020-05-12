<?php
/** 
 * **PLANLABELS**: manages plan's labels.
 * 
 * Client perspective:
 * - **GET** METHOD  --> returns labels data
 * - **POST** METHOD --> add or removes a label
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
 *      post_labels()
 * Inserts/removes label data to the database.
 *      
 *      get_labels()
 *  Returns labels data
 * 
 * If the script isn't included it tries to connect to the database,
 * and checks the request method:
 * - if the method is GET it calls the get_labels() function
 * - if the method is POST it calls the post_labels() function
 * 
 * When the called function ends the connection to the DB gets terminated,
 * and data (or errors) is collected inside the response array.
 * The response array gets echoed to the client in JSON format
 * 
 * Errors list:
 * - **1100** - main: Method not supported by the target resource
 * - **1101** - main: No data but also No errors
 * - **1110** - post_labels(): No rows affected (maybe the same values where sent?)
 * - **1111** - post_labels(): Unable to execute INSERT/UPDATE query
 * - **1112** - post_labels(): Couldn't remove the label, no rows affected
 * - **1113** - post_labels(): Unable to execute DELETE query
 * - **1114** - post_labels(): the required parameters are not set in the request
 * - **1115** - post_labels(): There is an error in the SQL instruction
 * - **1120** - get_labels(): Error during query execution
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

// =======================================================================================
// FUNCTIONS


/**
 * Inserts/removes label data to the database.
 * 
 * The function checks if all the parameters
 * have been passed inside the request.
 * 
 * Then it tries to insert the data
 * and if the data is a duplicate,
 * it updates the old data.
 * 
 * If the client sends only an id and a remove parameter,
 * the function tries to remove the label from the table
 * 
 * @param array $t_conn_res array with the DB connection object
 * @return array $action_res array with errors or data
 * @todo should log the SQL error to a file... (should also log everything else)
 * @todo the "error" 1110 should be a warning
*/
function post_labels($t_conn_res){

    // array that will be returned by this function
    $action_res = array("errors" => array(), "data" => array());

    // check if client is tring to add a new label
    if (isset($_POST["eltype"]) && isset($_POST["nodeid"]) && isset($_POST["nodedata"]) && 
        isset($_POST["id_user"]) && isset($_POST["top"]) && isset($_POST["left"]) && 
        isset($_POST["width"]) && isset($_POST["height"]) && isset($_POST["id"]) && isset($_POST["text"])){
            
            $id = $_POST["id"];
            $eltype = $_POST["eltype"];
            $nodeid = $_POST["nodeid"];
            $nodedata = $_POST["nodedata"];
            $id_user = $_POST["id_user"];
            $fromtop = $_POST["top"];
            $fromleft = $_POST["left"];
            $width = $_POST["width"];
            $height = $_POST["height"];
            $text = $_POST["text"];

            $query = "INSERT INTO t_planlabels
                      (t_planlabels.id,
                       t_planlabels.eltype,
                       t_planlabels.nodeid,
                       t_planlabels.nodedata,
                       t_planlabels.id_user,
                       t_planlabels.fromtop,
                       t_planlabels.fromleft,
                       t_planlabels.width,
                       t_planlabels.height,
                       t_planlabels.textcontent
                      )

                      VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE               -- if the insert is duplicated (same id)...
                      eltype=VALUES(eltype),                -- set the eltype to the new eltype, etc...
                      nodeid=VALUES(nodeid),
                      nodedata=VALUES(nodedata),
                      id_user=VALUES(id_user),
                      fromtop=VALUES(fromtop),
                      fromleft=VALUES(fromleft),
                      width=VALUES(width),
                      height=VALUES(height),
                      textcontent=VALUES(textcontent)
        ";
            
        // prepare the query expecting one string ("s"), and execute it    
        $stmt = $t_conn_res["connect_obj"]->prepare($query);
            
        if (! $stmt){
            array_push($action_res["errors"], array("id" => 1115,
                                                    "htmlcode" => 500,
                                                    "message" => "There is an error in the SQL instruction"));
            
            //$error = $t_conn_res["connect_obj"]->errno . ' ' . $t_conn_res["connect_obj"]->error;
            //echo $error;
        }
        else {
            // prepared statements
            $stmt->bind_param("isssssssss",
                              $id, 
                              $eltype,
                              $nodeid,
                              $nodedata,
                              $id_user,
                              $fromtop,
                              $fromleft,
                              $width,
                              $height,
                              $text
            );
            $stmt->execute();
            
            // check if there was an error
            if (!$stmt->error) {
                // the query should affect one row
                if($stmt->affected_rows === 0) {
                    // sql instruction gave 0 results
                    array_push($action_res["errors"], array("id" => 1110,
                                                            "htmlcode" => 500,
                                                            "message" => "No rows affected (maybe the same values where sent?)"));
                }
                else{
                    // everything went ok, send the received data back to the client
                    $action_res["data"] = array("id" => htmlspecialchars($id),
                                                "eltype" => htmlspecialchars($eltype),
                                                "nodeid" => htmlspecialchars($nodeid),
                                                "nodedata" => htmlspecialchars($nodedata),
                                                "id_user" => htmlspecialchars($id_user),
                                                "fromtop" => htmlspecialchars($fromtop),
                                                "fromleft" => htmlspecialchars($fromleft),
                                                "width" => htmlspecialchars($width),
                                                "height" => htmlspecialchars($height),
                                                "text" => htmlspecialchars($text)
                    ); 
                }
            }
            else {
                // executing the query gave an error
                array_push($action_res["errors"], array("id" => 1111,
                                                        "htmlcode" => 500,
                                                        "message" => "Unable to execute INSERT/UPDATE query"));
            }
        }       
    }
    elseif (isset($_POST["remove"]) && isset($_POST["id"])){
        // the client wants to delete a label

        $id = isset($_POST["id"]);
        $query = "DELETE FROM t_planlabels WHERE t_planlabels.id = ?";

        // prepare the query expecting one string ("s"), and execute it    
        $stmt = $t_conn_res["connect_obj"]->prepare($query);
        $stmt->bind_param("i", $id);

        $stmt->execute();
            
            // check if there was an error
            if (!$stmt->error) {
                // the query should affect one row
                if($stmt->affected_rows === 0) {
                    // sql instruction gave 0 results
                    array_push($action_res["errors"], array("id" => 1112,
                                                            "htmlcode" => 500,
                                                            "message" => "Couldn't remove the label, no rows affected"));
                }
                else{
                    // everything went ok
                    $action_res["data"] = array("id" => htmlspecialchars($id));
                }
            }
            else {
                // executing the query gave an error
                array_push($action_res["errors"], array("id" => 1113,
                                                        "htmlcode" => 500,
                                                        "message" => "Unable to execute DELETE query"));
            }

    }
    else{
        // missing parameters
        array_push($action_res['errors'], array("id" => 1114,
                                                "htmlcode" => 400,
                                                "message" => "the required parameters are not set in the request"));
    }

    return $action_res;
}


/**
 * Returns labels data.
 * 
 * The function executes a SELECT query
 * to select the labels data.
 * 
 * @since 01_01
 * @param array $t_conn_res array with the DB connection object
 * @return array $action_res array with errors or data
*/
function get_labels($t_conn_res){

    // array that will be returned by this function
    $action_res = array("errors" => array(), "data" => array());
    
    // query string
    $query = "SELECT 
                t_planlabels.id,          -- 0, 1, 2, ...
                t_planlabels.eltype,      -- reading, text, area
                t_planlabels.nodeid,      -- 1, 2, 3, 4
                t_planlabels.nodedata,    -- hum, temp, ...
                t_planlabels.id_user,     -- customizable id
                t_planlabels.fromtop,     -- move fromtop% from top
                t_planlabels.fromleft,    -- move fromleft% from left
                t_planlabels.width,       -- width of the element (%)
                t_planlabels.height,      -- height of the element (%)
                t_planlabels.textcontent  -- text inside the element
             FROM t_planlabels";          // table of the labels
        
    // execute query
    $labels_arr = queryToArray($t_conn_res["connect_obj"], $query);

    // be sure that there were no errors
    if (count($labels_arr["errors"]) == 0){ 
        // add rssi to the "data" array of $action_res
        $action_res["data"] = array_merge($action_res["data"], $labels_arr["query_data"]);
    }
    else {
        // An error occured during query execution
        array_push($action_res["errors"], array("id" => 1120,
                                                "htmlcode" => 500,
                                                "message" => "Error during query execution"));
        // unset "data" array
        unset($action_res["data"]);
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
            $request_res = get_labels($conn_res);
        }
        elseif ($_SERVER["REQUEST_METHOD"] === "POST"){
            // If the requesto method is POST
            $request_res = post_labels($conn_res);
        }
        else {
            // Unexpected request method
            // 405: The method received in the request-line is known by the origin server but not supported by the target resource.
            $request_res = array('errors' => array());
            array_push($request_res['errors'], array("id" => 1100,
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
        array_push($response['errors'], array("id" => 1101,
                                              "htmlcode" => 500,
                                              "message" => "No data but also No errors"));
        http_response_code(500);
    }
    
    // echo the result
    echo json_encode($response);
}
?>