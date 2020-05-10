<?php
/**
 * **DATA**: manages sensors data.
 * 
 * Client perspective:
 * - **GET** METHOD  --> returns the data of a sensor
 * 
 * 
 * ## Description
 * This script sets the returned **content type to JSON**,
 * **includes the db_connection.php script** 
 * > It is used to connect to MySQL and execute basic SELECT queries
 * and then defines the response array.
 * 
 * Many functions are defined:
 * 
 *      getOptions()
 * Returns user's options from the t_options table.
 * 
 *      getNodetype()
 * Returns the node type of a node with id $nodeid.
 * 
 *      getData()
 * Returns the data of the node id requested by calling the correct getDataTypeX() function.
 * 
 *      getDataType0()
 * Returns data of the node type 0, with id $nodeid.
 * 
 * If the script isn't included it tries to connect to the database,
 * and checks the request method:
 * - if the method is GET it calls the getData() function
 * > Which calls all the other functions
 * 
 * When the called function ends the connection to the DB gets terminated,
 * and data (or errors) is collected inside the response array.
 * The response array gets echoed to the client in JSON format
 * 
 * Errors list:
 * - **900** - main: Method not supported by the target resource
 * - **901** - main: No data but also No errors
 * - **910** - getNodetype(): Query returned nothing
 * - **911** - getNodetype(): Error executing query
 * - **920** - getDataType0(): Query returned nothing
 * - **921** - getDataType0(): Error executing query
 * - **930** - getData(): can't get data for this node type (not supported)
 * - **931** - getData(): nodeid is not set in the request
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
 * Returns user's options from the t_options table.
 *
 * It executes the SELECT query to get this data:
 * - color_scheme: color name selected by the user
 * - min_timestamp: timestamp set as the start date for data plotting
 * - max_timestamp: timestamp set as the end date for data plotting
 *
 * @since 01_01
 * @param array $t_conn_res array with the DB connection object
 * @return array $query_arr array with errors or data
*/
function getOptions($t_conn_res){

    // QUERY PER OTTENERE I DATI DALLA TABELLA
    $query = sprintf("SELECT t_options.color_scheme, 
                             t_options.min_timestamp, 
                             t_options.max_timestamp 
                      FROM t_options");

    // ESEGUI QUERY, OTTIENI DATI IN ARRAY
    $query_arr = queryToArray($t_conn_res['connect_obj'], $query);
        
    // RESTITUISCI I DATI JSON
    return $query_arr;
};


/** 
 * Returns the node type of a node with id $nodeid.
 * 
 * The function selects the type id using the node id.
 * 
 * @since 01_01
 * @param array $t_conn_res array with the DB connection object
 * @param int $nodeid integer that identifies the node
 * @return array $action_res array with errors or data
*/
function getNodetype($t_conn_res, $nodeid){

    // array that will be returned by this function
    $action_res = array('errors' => array());

    $query = "SELECT t_nodi.id, 
                     t_nodi.type_id 
              FROM t_nodi
              WHERE t_nodi.id = ?";

    $stmt = $t_conn_res["connect_obj"]->prepare($query);

    // BIND ? con variabili di tipo "i" -> integer
    $stmt->bind_param('i', $nodeid);

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
            array_push($action_res["errors"], array('id' => 910,
                                                    'htmlcode' => 500,
                                                    'message' => "Query returned nothing"));
        }
    }
    else {
        // executing the query gave an error
        array_push($action_res["errors"], array('id' => 911,
                                                'htmlcode' => 500,
                                                'message' => "Error executing query"));
    }

    return $action_res;
}


/** 
 * Returns data of the node type 0, with id $nodeid.
 * 
 * If the request has a "latest" parameter, return the
 * last data from that node,
 * else check if the client wants all the data ($min_timestamp e $max_timestamp are zero)
 * or part of the data ( $min_timestamp <= timestamp <= $max_timestamp).
 * 
 * The data is then returned by the function.
 * 
 * @since 01_01
 * @param array $t_conn_res array with the DB connection object
 * @param int $nodeid integer that identifies the node
 * @param int $min_timestamp timestamp set as the start date for data plotting
 * @param int $max_timestamp timestamp set as the end date for data plotting
 * @return array $action_res array with errors or data
*/
function getDataType0($t_conn_res, $nodeid, $min_timestamp, $max_timestamp){

    // array that will be returned by this function
    $action_res = array('errors' => array());

    // check if client desires latest data
    if (isset($_GET['latest'])){
        $query = "SELECT id, node_id, hum, temp   -- select this data 
                  FROM t_type0_data               -- from the data table
                  WHERE id IN (                   -- where the id is in the record set
                    SELECT MAX(id)                -- of the maximum ids
                    FROM t_type0_data             -- from the data table
                    GROUP BY node_id              -- per each node
                  )
                  AND node_id = ?                 -- and the id corresponds to the request id
                  ORDER BY node_id
        ";
        
        $stmt = $t_conn_res["connect_obj"]->prepare($query);

        // BIND ? con variabili di tipo "i" -> integer
        $stmt->bind_param('i', $nodeid);
    }
    else{
        // client wants more data

        if ($min_timestamp == 0 && $max_timestamp == 0){
            // if the timestamps are 0, select the last 1200 records
            $query = "SELECT tstamp, node_id, temp, hum 
                    FROM (SELECT tstamp, node_id, temp, hum
                            FROM t_type0_data
                            ORDER BY tstamp DESC
                            LIMIT 1200
                        ) foo
                    ORDER BY tstamp";

            $stmt = $t_conn_res["connect_obj"]->prepare($query);
        }
        else{
            // get the data between the timestamps
            $query = "SELECT tstamp, node_id, temp, hum
                    FROM t_type0_data
                    WHERE  ? <= tstamp AND tstamp <= ?
                    ORDER BY tstamp";
            
            $stmt = $t_conn_res["connect_obj"]->prepare($query);

            // BIND ? con variabili di tipo "i" -> integer
            $stmt->bind_param('ii', $min_timestamp, $max_timestamp);
        
        }

    }

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
            array_push($action_res["errors"], array('id' => 920,
                                                    'htmlcode' => 500,
                                                    'message' => "Query returned nothing"));
        }
    }
    else {
        // executing the query gave an error
        array_push($action_res["errors"], array('id' => 921,
                                                'htmlcode' => 500,
                                                'message' => "Error executing query"));
    }

    return $action_res;
}


/**
 * Returns the data of the node id requested by calling the correct getDataTypeX() function.
 *
 * First checks if a node id was specified in the request,
 * then gets the timestamps from the options table by calling the getOptions() function.
 * 
 * Next it gets the node type of the node by calling the  getNodetype() function.
 * Based on the node type, the correct function is called to collect the data of that node.
 * 
 * Example:
 * - if the node type is "0", the table is "t_type0_data" and the function that collects
 *   the data is called getDataType0()
 *
 * @since 01_01
 * @param array $t_conn_res array with the DB connection object
 * @return array $action_res array with errors or data
*/
function getData($t_conn_res){

    // array that will be returned by this function
    $action_res = array('errors' => array());

    // check if nodeid is set
    if (isset($_GET['nodeid'])){
        // save nodeid in a variable
        $nodeid = $_GET['nodeid'];

        // get options
        $array_opts = getOptions($t_conn_res);

        // check if there were no errors
        if (empty($array_opts['errors'])) {
            
            
            $min_timestamp = $array_opts['query_data'][0]['min_timestamp']; // estraggo il timestamp minimo
            $max_timestamp = $array_opts['query_data'][0]['max_timestamp'];

            // try to get nodetype data
            $nodetype_data = getNodetype($t_conn_res, $nodeid);

            // check if there were errors
            if (empty($nodetype_data['errors'])){
                // save nodetype in variable
                $nodetype = $nodetype_data['data'][0]['type_id'];
                
                if ($nodetype === 0){
                    $action_res = getDataType0($t_conn_res, $nodeid, $min_timestamp, $max_timestamp);
                }
                else{
                    // errore: tipo non riconosciuto
                    array_push($action_res['errors'], array('id' => 930,
                                                            'htmlcode' => 422,
                                                            'message' => "can't get data for this node type (not supported)"));
                }
            } 
            else{
                // collect errors
                foreach ($nodetype_data["errors"] as &$error) {
                    array_push($action_res["errors"], $error);
                }
            }

        }
        else {
            // collect errors
            foreach ($array_opts["errors"] as &$error) {
                array_push($action_res["errors"], $error);
            }
        }
    }
    else{
        // error: nodeid is not set in request
        array_push($action_res['errors'], array('id' => 931,
                                                'htmlcode' => 422,
                                                'message' => "nodeid is not set in the request"));
    }
    
    return $action_res;
};


// =======================================================================================
// MAIN
    
if (!count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))) {
    // connect to the database
    $conn_res = dbconn($db_dbname);
    
    // check if no errors happened (connection successful)
    if (count($conn_res["errors"]) == 0){
        
        // check the request method
        if ($_SERVER["REQUEST_METHOD"] === "GET") {

            $request_res = getData($conn_res); // ottiene i dati in json
        }
        else {
            // Unexpected request method
            // 405: The method received in the request-line is known by the origin server but not supported by the target resource.
            $request_res = array('errors' => array());
            array_push($request_res['errors'], array("id" => 900,
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
        array_push($response['errors'], array("id" => 901,
                                              "htmlcode" => 500,
                                              "message" => "No data but also No errors"));
        http_response_code(500);
    }
    
    // echo the result
    echo json_encode($response);
}
?>