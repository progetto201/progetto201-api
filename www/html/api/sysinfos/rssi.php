<?php
/** 
 * 
 * **RSSI**: manages RSSI of the nodes.
 *
 * Client perspective:
 * - **GET** METHOD: gets RSSI, type description and location of the nodes.
 * 
 * The script tries to connect to the database
 * and then checks the request method.
 * 
 * If the method is GET get_rssi() is called.
 * 
 * Error codes:
 * - **600** - main: Request Method not supported by the target resource
 * - **601** - main: No data but also No errors
 * - **610** - get_rssi(): Error during query execution
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
/// Array with data tables
$data_tables = array("t_type0_data");

// =======================================================================================
// FUNCTIONS


/**
 * Returns all RSSI of the nodes. 
 * 
 * A loop uses $data_tables to make the query string.
 * These strings are executed by the queryToArray() function.
 * 
 * Errors list:
 * - 610: Error during query execution
 * 
 * @since 01_01
 * @param array $t_conn_res file system path with plans
 * @return array $action_res array with errors or plans data
*/
function get_rssi($t_conn_res){

    global $data_tables;

    // array that will be returned by this function
    $action_res = array("errors" => array(), "data" => array());

    foreach ($data_tables as &$t_table) {
        // query string
        $query = "SELECT t_nodi.mac, t_nodi.type_id,
                         t_locations.description AS location_description,  -- seleziona il nome della location 
                         t_types.description AS type_description,          -- il nome del tipo di nodo
                         $t_table.node_id,                                 -- l'id del nodo
                         $t_table.rssi                                     -- l'RSSI della rilevazione
                  FROM `$t_table`                                          -- dalla tabella rilevazioni
                  JOIN t_nodi ON t_nodi.id = $t_table.node_id              -- unita alla tabella nodi con l'id del nodo
                  JOIN t_locations ON t_locations.id = t_nodi.location_id  -- unita alla tabella delle location attraverso l'id della location
                  JOIN t_types ON t_types.id = t_nodi.type_id              -- unita alla tabella dei tipi attraverso l'id del tipo
                  WHERE $t_table.id IN (                                   -- dove l'id e' nel record set
                    SELECT MAX(id)                                         -- degli id massimi (ultime rilevazioni)
                    FROM `$t_table`                                        -- dalla tabella rilevazioni
                    GROUP BY node_id                                       -- per ogni nodo
                  )
                  ORDER BY node_id
                ";
        
        // execute query
        $rssi_arr = queryToArray($t_conn_res["connect_obj"], $query);

        // be sure that there were no errors
        if (count($rssi_arr["errors"]) == 0){
            // add 
            // add rssi to the "data" array of $action_res
            $action_res["data"] = array_merge($action_res["data"], $rssi_arr["query_data"]);
        }
        else {
            // An error occured during query execution
            array_push($action_res["errors"], array("id" => 610,
                                                    "htmlcode" => 500,
                                                    "message" => "Error during query execution"));
            // unset "data" array
            unset($action_res["data"]);
            break; // stop the loop
        }
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
            $request_res = get_rssi($conn_res);
        }
        else {
            // Unexpected request method
            // 405: The method received in the request-line is known by the origin server but not supported by the target resource.
            $request_res = array('errors' => array());
            array_push($request_res['errors'], array("id" => 600,
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
        array_push($response['errors'], array("id" => 601,
                                              "htmlcode" => 500,
                                              "message" => "No data but also No errors"));
        http_response_code(500);
    }
    
    // echo the result
    echo json_encode($response);
}
?>