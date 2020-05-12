<?php
/** 
 *  
 * **PLANS**: manages plans (returns paths and names).
 *
 * Client perspective:
 * - **GET** METHOD: returns plan's data
 * 
 * The script calls get_plans() function to get the plans
 * that are inside the plans folder
 *
 * Error codes:
 * - **400** - main: Method not supported by the target resource
 * - **401** - main: No data but also No errors
 * - **410** - get_plans(): scandir() error: incorrect/non existing folder on path
 * - **411** - get_plans(): Unknown error during plans extraction
 * - **412** - get_plans(): No maps where found in $path
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
// VARIABLES

/// Prepare default response array with no errors
$response = array('errors' => array());
/// Plan's path (file system)
$mapspath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "static" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "maps";

// =======================================================================================
// FUNCTIONS


/**
 * Throws an error when an E_WARNING or E_NOTICE is raised.
*/
set_error_handler(function ($severity, $message, $file, $line) {   
    throw new \ErrorException($message, $severity, $severity, $file, $line);
});


/**
 * Returns an array with plans data (plans are stored in "/static/img/maps").
 *
 * The function finds the path of each file inside the maps folder 
 * and checks the mime type:
 *
 * if a files is an SVG file it gets stored inside the maps array as an
 * associative array with these properties:
 * - "name": file name
 * - "path": path relative to the webserver's root ("/static/img/maps/<file.svg>")
 * 
 * Example:
 * GET /api/plans/plans.php
 * @code
 * {
 *   "errors": [],
 *   "data": [
 *       {
 *           "name": "colorful",
 *           "path": "/static/img/maps/colorful.svg"
 *       },
 *       {
 *           "name": "realistic",
 *           "path": "/static/img/maps/realistic.svg"
 *       }
 *   ]
 *  }
 * @endcode
 * 
 * Error codes:
 * - **410**: scandir() error: incorrect/non existing folder on path
 * - **411**: Unknown error during plans extraction
 * - **412**: No maps where found in $path
 * 
 * @since 01_01
 * @param string $path file system path with plans
 * @return array $action_res array with errors or plans data 
*/
function get_plans($path){
    // array that will be returned by this function
    $action_res = array('errors' => array());

    try{
        // Convert the file system path relative to webserver's path ("/")
        // replaces OSes separator with "/" and removes the part before the webserver's root 
        $rootrelpath = str_replace($_SERVER['DOCUMENT_ROOT'], "", str_replace(DIRECTORY_SEPARATOR, "/", $path));
            
        // Get files from the maps folder using the file system path and remove "." and ".." from the array
        $files = array_diff(scandir($path), array('.', '..'));
        
        $maps = array();
        foreach ($files as $file){
            // for each file: make sure that the mime type of file is SVG
            if (mime_content_type($path . DIRECTORY_SEPARATOR . $file) === "image/svg+xml"){
                    
                // concatenate the root relative path to files path
                $filerelpath = $rootrelpath . "/" . $file;
                    
                // get the file name of file
                $mapname = pathinfo($path . DIRECTORY_SEPARATOR . $file)['filename'];
                    
                // add an array with the name and the path of the map in maps array
                $maps[] = array("name" => $mapname, "path" => $filerelpath);
            }
        }

        // set an error if no maps where found
        if (count($maps) == 0){
            array_push($action_res['errors'], array("id" => 412,
                                                    "htmlcode" => 500,
                                                    "message" => "No maps where found in '$rootrelpath'"));
        }

        $action_res["data"] = $maps; 
    }
    catch(ErrorException $e){
        // an error happened, get the function that threw the error
        $error_func = $e->getTrace()[1]["function"];
        
        if ($error_func == "scandir"){
            // scandir() threw an error: is something to do with the path
            array_push($action_res['errors'], array("id" => 410,
                                                    "htmlcode" => 500,
                                                    "message" => "scandir() error: incorrect/non existing folder on path '$path'"));
        }
        else{
            // unknown error
            array_push($action_res['errors'], array("id" => 411,
                                                    "htmlcode" => 500,
                                                    "message" => "Unknown error during plans extraction"));
        }
    }
    
    return $action_res;
}


// =======================================================================================
// MAIN
    
if (!count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))) {
            
    // check the request method
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        // If the request method is GET
        $request_res = get_plans($mapspath);
    }    
    else {
        // Unexpected request method
        // 405: The method received in the request-line is known by the origin server but not supported by the target resource.
        $request_res = array('errors' => array());
        array_push($request_res['errors'], array("id" => 400,
                                               "htmlcode" => 405,
                                               "message" => "Method not supported by the target resource"));
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
        array_push($response['errors'], array("id" => 401,
                                              "htmlcode" => 500,
                                              "message" => "No data but also No errors"));
        http_response_code(500);
    }
    
    // echo the result
    echo json_encode($response);    
}

?>