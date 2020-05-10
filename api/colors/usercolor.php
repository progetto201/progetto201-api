<?php
/**
 * 
 * **USERCOLOR**: manages user's color.
 * 
 * Client perspective: 
 * - **GET**  METHOD --> returns user color
 * - **POST** METHOD --> retrieves user color
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
 *     get_usercolor($t_conn_res)
 * Selects the user color from the database so that the script can echo it
 * 
 *     post_usercolor($t_conn_res)
 * Sets the user color inside the options table (colors param must be passed)
 * 
 * If the script isn't included it tries to connect to the database,
 * and checks the request method:
 * - if the method is GET it calls get_usercolor($t_conn_res)
 * - if the method is POST it calls post_usercolor($t_conn_res)
 * 
 * When the called function ends the connection to the DB gets terminated,
 * and data (or errors) is collected inside the response array.
 * The response array gets echoed to the client in JSON format
 * 
 * Errors list:
 * - **200** - main: Method not supported by the target resource
 * - **201** - main: No data but also No errors
 * - **210** - get_usercolor(): The query returned an unexpected number of records
 * - **211** - get_usercolor(): Error during query execution
 * - **220** - post_usercolor(): Unable to change the current color for the new one (the user sent the current color or a non existing one)
 * - **221** - post_usercolor(): Unable to execute UPDATE query (query error)
 * - **222** - post_usercolor(): Parameter 'color' wasn't set"
 * 
 * @file
 * @since 01_01
 * @author Stefano Zenaro (https://github.com/mario33881)
 * @copyright MIT
 * 
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
 * Returns the UI color data inside the 'data' property of an array.
 * 
 * The function, using the mysqli object inside the parameter $t_conn_res
 * ($t_conn_res['connect_obj']), executes the query '$query' which collects
 * the color previously selected by the user.
 * > There is always a value (the default one is 'red').
 * 
 * If the query execution throws an error the error is collected
 * inside the 'errors' property of the array (which is also an array).
 * The errors inside the 'errors' array have three properties:
 * - **id**: identifies the error, it is useful for the frontend
 * - **htmlcode**: it is the status code of the response that will be given to the client
 * - **message**: details of the error 
 * > the message is in english, language translation is going to be managed from the frontend
 * 
 * The user's color data is an array and is structured as following:
 * - **id**: identification number from the database
 * - **color_name**: name of the color
 * - **color_hex**: hexadecimal value of the color 
 * > format of color_hex: @code#RRGGBB@endcode
 * 
 * Example with no errors
 * @code
 * GET /api/colors/usercolor.php
 * {
 *   "errors": [],
 *   "data": {
 *       "id": "4384",
 *       "color_name": "red",
 *       "color_hex": "#f44336"
 *   }
 * }
 * @endcode
 * 
 * If there are errors they will be pushed inside the errors array.
 * Possible returned errors:
 * - **210**: The query returned an unexpected number of records
 * - **211**: Error during query execution
 * 
 * @since 01_01
 * @param array $t_conn_res array with the connection object (connection was successful)
 * @return array $action_res array with color data or errors
 * 
*/
function get_usercolor($t_conn_res) {
    // array that will be returned by this function
    $action_res = array('errors' => array());

    // Select id, color_name and color_hex FROM t_colors table
    // joined to the t_options table
    // where t_colors.color_name = t_options.color_scheme
    $query = "SELECT t_colors.id, t_colors.color_name, t_colors.color_hex FROM t_colors 
              JOIN t_options 
              ON t_colors.color_name = t_options.color_scheme";
    
    // get color data
    $color_data = queryToArray($t_conn_res['connect_obj'], $query);

    // be sure that there were no errors
    if (count($color_data['errors']) == 0){
        // should be one row
        if (count($color_data['query_data']) == 1){
            // set "data" of the response
            $action_res['data'] = $color_data['query_data'][0];
        }
        else {
            // unknown error: the function returned more than one value
            array_push($action_res['errors'], array('id' => 210,
                                                    'htmlcode' => 500,
                                                    'message' => "The query returned an unexpected number of records"));
        }
    }
    else {
        // An error occured during query execution
        array_push($action_res['errors'], array('id' => 211,
                                                'htmlcode' => 500,
                                                'message' => "Error during query execution"));
    }

    return $action_res;
}


/**
 * Set's the new UI color passed by the client.
 * 
 * The function checks if a POST parameter called 'color' was passed,
 * if so it executes the query that makes sure that the color exists
 * inside the colors table and modifies the set color inside the options table.
 * For last it checks if the query modified one row (as expected) and returns
 * the color inside the 'data' property of the returned array.
 * 
 * Example with no errors
 * @code
 * POST /api/colors/usercolor.php (color=red)
 * {
 *   "errors": [],
 *   "data": "red"
 * }
 * @endcode
 * 
 * If there are errors they will be pushed inside the errors array.
 * Possible returned errors:
 * - **220**: Unable to change the current color for the new one (the user sent the current color or a non existing one)
 * - **221**: Unable to execute UPDATE query (query error)
 * - **222**: Parameter 'color' wasn't set"
 * 
 * The errors inside the 'errors' array have three properties:
 * - **id**: identifies the error, it is useful for the frontend
 * - **htmlcode**: it is the status code of the response that will be given to the client
 * - **message**: details of the error 
 * > the message is in english, language translation is going to be managed from the frontend
 * 
 * @since 01_01
 * @param array $t_conn_res array with the connection object
 * @return array $action_res array with color data or errors
 * 
*/
function post_usercolor($t_conn_res){
    // array that will be returned by this function
    $action_res = array('errors' => array());

    // be sure that color is set
    if (isset($_POST["color"])) {
        // save the color
        $color_scheme = $_POST["color"];
        
        // modify t_options table:
        // set color_scheme to $color_scheme if it is present in t_colors, else set the current one
        // where id=1 (just to be safe)
        $query = "UPDATE t_options 
                  SET t_options.color_scheme = IF (? IN (SELECT color_name FROM t_colors), ?, t_options.color_scheme) 
                  WHERE id = 1";
            
        // prepare the query expecting two strings ("s"), and execute it    
        $stmt = $t_conn_res['connect_obj']->prepare($query);
        $stmt->bind_param("ss", $color_scheme, $color_scheme);
        $stmt->execute();
            
        // check if there was an error
        if (!$stmt->error) {
            // the query should affect one row
            if($stmt->affected_rows === 0) {
                // sql instruction gave 0 results
                array_push($action_res["errors"], array('id' => 220,
                                                        'htmlcode' => 500,
                                                        'message' => "Unable to change the current color for the new one ('" . 
                                                                      htmlspecialchars($color_scheme)  . "'), be sure to select a new and existing color"));
            }
            else{
                // everything went ok
                $action_res['data'] = htmlspecialchars($color_scheme);
            }
        }
        else {
            // executing the query gave an error
            array_push($action_res["errors"], array('id' => 221,
                                                    'htmlcode' => 500,
                                                    'message' => "Unable to execute UPDATE query"));
        }
    }      
    else{
        // the caller forgot the color param
        array_push($action_res["errors"], array('id' => 222,
                                                'htmlcode' => 400,
                                                'message' => "Parameter 'color' wasn't set"));
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
            $request_res = get_usercolor($conn_res);
        }
        elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
            // If the request method is POST
            $request_res = post_usercolor($conn_res);     
        }    
        else {
            // Unexpected request method
            // 405: The method received in the request-line is known by the origin server but not supported by the target resource.
            $request_res = array('errors' => array());
            array_push($request_res['errors'], array("id" => 200,
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
        array_push($response['errors'], array("id" => 201,
                                              "htmlcode" => 500,
                                              "message" => "No data but also No errors"));
        http_response_code(500);
    }
    
    // echo the result
    echo json_encode($response);
        
}

?>