<?php
/**
 * 
 * **COLORS**: manages the colors table.
 * 
 * Client perspective: 
 * - **GET** METHOD --> returns the colors
 * 
 * 
 * ## Description
 * This script sets the content type to "JSON",
 * includes the db_connection.php script
 * > Which is used to connect to MySQL and to execute simple select queries (no prepared statements)
 * 
 * and then it defines the response array.
 * 
 * 
 * A function is defined:
 * 
 *     get_colors($t_conn_res)
 * Returns colors data inside an associative array. 
 * The name of its key is 'data'.
 * 
 * 
 * If the script is not included in other scripts, the script tries to connect to the database,
 * and checks the request method:
 * - if the method is GET the script returns the colors data
 *
 * When the function finishes, the script closes the connection to the DB,
 * and the data (or errors) is collected in the "response" array.
 * The response array is then returned to the client in JSON format
 * 
 * 
 * List of possible errors:
 * - **300** - main: Method not supported by the target resource
 * - **301** - main: No data but also No errors
 * - **310** - get_colors(): Query returned nothing
 * - **311** - get_colors(): Error executing query
 * - **312** - get_colors(): If 'min' is set so needs to be 'max' (and viceversa)
 * 
 * @file
 * @since 01_01
 * @author Stefano Zenaro (https://github.com/mario33881)
 * @copyright MIT
 * @todo Check if n is numeric and positive (now returns all the colors if n<0)
 * @todo Check if min and max are numeric and min < max (currently returns "Query Returned nothing" in these cases)) 
*/

// Set the response type to JSON
header('Content-type: application/json');

// =======================================================================================
// INCLUDES 

// Includes the db_connection.php script: it connects to the database, queryToArray() --> executes queries and returns an array
include_once(__DIR__ . '/../utils/db_connection.php');

// =======================================================================================
// VARIABLES

/// Default response array
$response = array('errors' => array());

// =======================================================================================
// FUNCTION


/**
 * Returns the colors data inside an associative array (the key is called 'data').
 * 
 * The function, using the mysqli object inside the $t_conn_res parameter
 * ($t_conn_res['connect_obj']), executes the '$query' query that collects the colors requested by the client.
 * 
 * The returned data depends on which parameters were passed with the request:
 * - if the request has an 'n' parameter: returns that number of colors
 * - if the request has the 'min' and 'max' parameters: returns the colors with an id value between those values
 * - otherwise: returns all the colors
 * 
 * The data of the each color is contained inside an associative array:
 * - **id**: identification number
 * - **color_name**: name of the color
 * - **color_hex**: hexadecimal value of the color 
 * > the format of color_hex is: @code#RRGGBB@endcode
 * 
 * If the query execution throws an error, the error is collected in the array 
 * with the 'errors' key.
 * The errors have 3 properties:
 * - **id**: identifies the error in the API
 * - **htmlcode**: its the response status code
 * - **message**: details about the error 
 * > the error message is written in english
 * 
 * ### Examples:
 * - Example with no errors and without parameters
 * @code
 * GET /api/colors/colors.php
 * {
 *   "errors": [],
 *   "data": [{
 *       "id": "4384",
 *       "color_name": "red",
 *       "color_hex": "#f44336"
 *   },
 *   {
 *       "id": "4385",
 *       "color_name": "randomname",
 *       "color_hex": "#f44337"
 *   },
 *   ...
 *   ]
 * }
 * @endcode
 * 
 * - Example with no errors and with the 'n' parameter:
 * @code
 * GET /api/colors/colors.php?n=3
 * {
 *   "errors": [],
 *   "data": [
 *       {
 *           "id": 4379,
 *           "color_name": "red lighten-5",
 *           "color_hex": "#ffebee"
 *       },
 *       {
 *           "id": 4380,
 *           "color_name": "red lighten-4",
 *           "color_hex": "#ffcdd2"
 *       },
 *       {
 *           "id": 4381,
 *           "color_name": "red lighten-3",
 *           "color_hex": "#ef9a9a"
 *       }
 *   ]
 * }
 * @endcode
 * 
 * - Example with no errors and with the 'min'/'max' parameter:
 * @code
 * GET /api/colors/colors.php?min=4385&max=4387
 * {
 *   "errors": [],
 *   "data": [
 *       {
 *           "id": 4385,
 *           "color_name": "red darken-1",
 *           "color_hex": "#e53935"
 *       },
 *       {
 *           "id": 4386,
 *           "color_name": "red darken-2",
 *           "color_hex": "#d32f2f"
 *       },
 *       {
 *           "id": 4387,
 *           "color_name": "red darken-3",
 *           "color_hex": "#c62828"
 *       }
 *   ]
 * }
 * @endcode
 * 
 * If there are errors, they are collected in the returned array.
 * The function can retun the following errors:
 * - **310**: Query returned nothing
 * - **311**: Error executing query
 * - **312**: If 'min' is set so needs to be 'max' (and viceversa)
 * 
 * @since 01_01
 * @param array $t_conn_res array with the connection object (connection is successfull)
 * @return array $action_res array with colors data (or error)
 * 
*/
function get_colors($t_conn_res) {
    // array returned by the function
    $action_res = array('errors' => array());

    if (isset($_GET["n"])) {
        // returns the first 'n' colors
        $query = "SELECT t_colors.id,         -- select the id,
                         t_colors.color_name, -- the color name,
                         t_colors.color_hex   -- the hexadecimal value of the color
                  FROM t_colors               -- from the t_colors table
                  LIMIT ?                     -- limiting the number of results to ?
                  ";                   
        
        // prepare the query
        $stmt = $t_conn_res['connect_obj']->prepare($query);
        // use the prepared statement 
        $stmt->bind_param("i", $_GET["n"]);
    }
    else if (isset($_GET["min"]) && isset($_GET["max"])){
        // return the colors with id between 'min' and 'max'
        $query = "SELECT t_colors.id, t_colors.color_name, t_colors.color_hex FROM t_colors
                  WHERE t_colors.id BETWEEN ? AND ?";

        $stmt = $t_conn_res['connect_obj']->prepare($query);
        $stmt->bind_param("ii", $_GET["min"], $_GET["max"]);
    }
    else{
        // collect all colors
        // Select id, color_name and color_hex FROM t_colors table
        $query = "SELECT t_colors.id, t_colors.color_name, t_colors.color_hex FROM t_colors";
        $stmt = $t_conn_res['connect_obj']->prepare($query);
    }
    
    // be sure that both or none 'min' and 'max' were set
    if (
        (isset($_GET["min"]) && ! isset($_GET["max"])) ||  // if 'min' is set and 'max' is not set
        (! isset($_GET["min"]) &&  isset($_GET["max"]))    // OR 'min' is not set and 'max' is set
        ){
            
            // error: one of the two params wasn't set
            array_push($action_res["errors"], array('id' => 312,
                                                    'htmlcode' => 400,
                                                    'message' => "If 'min' is set so needs to be 'max' (and viceversa)"));
    }
    else{
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
                array_push($action_res["errors"], array('id' => 310,
                                                        'htmlcode' => 500,
                                                        'message' => "Query returned nothing"));
            }
        }
        else {
            // executing the query gave an error
            array_push($action_res["errors"], array('id' => 311,
                                                    'htmlcode' => 500,
                                                    'message' => "Error executing query"));
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
    if (count($conn_res['errors']) == 0){
        
        // check the request method
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            // If the request method is GET
            $request_res = get_colors($conn_res);
        }
        else {
            // Unexpected request method
            // 405: The method received in the request-line is known by the origin server but not supported by the target resource.
            $request_res = array('errors' => array());
            array_push($request_res['errors'], array("id" => 300,
                                                   "htmlcode" => 405,
                                                   "message" => "Method not supported by the target resource"));
        }
            
        // close DB connection
        $conn_res['connect_obj'] -> close();
    }

    // Add all the connection errors inside the response's errors array (if present)
    foreach ($conn_res['errors'] as &$error) {
        array_push($response['errors'], $error);
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
        array_push($response['errors'], array("id" => 301,
                                              "htmlcode" => 500,
                                              "message" => "No data but also No errors"));
        http_response_code(500);
    }
    
    // echo the result
    echo json_encode($response);
}

?>