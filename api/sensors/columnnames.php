<?php
/**
 * **COLUMNNAMES**: manages columns' names.
 * 
 * Client perspective:
 * - **GET** METHOD  --> returns columns' names
 * 
 * 
 * ## Description
 * This script sets the returned **content type to JSON**,
 * **includes the db_connection.php script** 
 * > It is used to connect to MySQL and execute basic SELECT queries
 * and then defines the response array.
 * 
 * One function is defined:
 * 
 *      getColumNames()
 * Returns the name of the colums in a data table.
 * 
 * If the script isn't included it tries to connect to the database,
 * and checks the request method:
 * - if the method is GET it calls the getColumNames() function
 * 
 * When the called function ends the connection to the DB gets terminated,
 * and data (or errors) is collected inside the response array.
 * The response array gets echoed to the client in JSON format
 * 
 * Errors list:
 * - **1000** - main: Method not supported by the target resource
 * - **1001** - main: No data but also No errors
 * - **1010** - getColumNames(): Query returned nothing
 * - **1011** - getColumNames(): Error executing query
 * - **1012** - getColumNames(): Nodetype not recognised
 * - **1013** - getColumNames(): typeid is not set in request
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

$nodetypes = array("0" => "t_type0_data");

// =======================================================================================
// FUNCTIONS


/**
 * Returns the name of the colums in a data table.
 * 
 * First it checks that typeid is set both in the request and in the $nodetypes array,
 * then a SELECT query is executed to retrieve the column names.
 * 
 * @param array $t_conn_res array with the DB connection object
 * @return array $action_res array with errors or data
*/
function getColumNames($t_conn_res){

    global $nodetypes;

    // array that will be returned by this function
    $action_res = array('errors' => array());

    // check that typeid is set both in the request and in the $nodetypes array
    if (isset($_GET['typeid'])){        
        if (isset($nodetypes[$_GET['typeid']])){
            // select the name of the columns in the table $_GET['typeid']
            $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_SCHEMA = 'db100_100' AND TABLE_NAME = ?";
    
            $stmt = $t_conn_res["connect_obj"]->prepare($query);

            // BIND ? con variabili di tipo "i" -> integer
            $stmt->bind_param('s', $nodetypes[$_GET['typeid']]);

            // execute query and get the result
            $stmt->execute();
            $result = $stmt->get_result();
            
            // check if there was an error
            if (!$stmt->error) {
                // the query should return at least one row
                if (!empty($result) && $result->num_rows > 0) {
                    $arr = $result->fetch_all(MYSQLI_ASSOC);
                    $action_res['data'] = $arr;
                }
                else{
                    // sql instruction gave 0 results
                    array_push($action_res["errors"], array('id' => 1010,
                                                            'htmlcode' => 500,
                                                            'message' => "Query returned nothing"));
                }
            }
            else {
                // executing the query gave an error
                array_push($action_res["errors"], array('id' => 1011,
                                                        'htmlcode' => 500,
                                                        'message' => "Error executing query"));
            }
        }
        else{
            // the nodetype is unknown to this script, someone needs to add it to the $nodetypes array
            array_push($action_res["errors"], array('id' => 1012,
                                                        'htmlcode' => 500,
                                                        'message' => "Nodetype not recognised"));
        }
    }
    else{
        // the client didn't specify the typeid
        array_push($action_res["errors"], array('id' => 1013,
                                                        'htmlcode' => 500,
                                                        'message' => "typeid is not set in request"));
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

            $request_res = getColumNames($conn_res);
        }
        else {
            // Unexpected request method
            // 405: The method received in the request-line is known by the origin server but not supported by the target resource.
            $request_res = array('errors' => array());
            array_push($request_res['errors'], array("id" => 1000,
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
        array_push($response['errors'], array("id" => 1001,
                                              "htmlcode" => 500,
                                              "message" => "No data but also No errors"));
        http_response_code(500);
    }
    
    // echo the result
    echo json_encode($response);
}
?>