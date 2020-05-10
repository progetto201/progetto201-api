<?php
/** 
 * 
 * **DISKINFO**: manages disk space information.
 *
 * Client perspective:
 * - **GET** METHOD: gets free, occupied and total space of the main partition.
 * 
 * The script identifies the OS to choose
 * which is the main partition,
 * uses built-in functions to get free and total space
 * and calculates the occupied space.
 * 
 * After that it uses the HumanSize() function
 * to convert free, total and occupied space in bytes
 * to a human readable unit (for example MB, GB,...).
 * 
 * Finally the data is printed to the client.
 * 
 * Error codes:
 * - **700** - main: Request Method not supported by the target resource
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
// VARIABLES

/// Prepare default response array with no errors
$response = array("errors" => array());

// =======================================================================================
// FUNCTIONS


/**
 * This function changes the measurement unit of bytes.
 *
 * A loop divides the passed value by 1024 until
 * it gets smaller than 1024.
 * During the loop an index gets incremented
 * to choose the correct measurement unit.
 * 
 * Finally the function rounds the value
 * and returns an array with the value and the measurement unit.
 * 
 * @since 01_01
 * @param int $Bytes bytes to convert in a new measurement unit
 * @return array $humanbytes array with new bytes value and human readable unit
*/
function HumanSize($Bytes){

    $Type = array("", "K", "M", "G", "T", "P", "E", "Z", "Y"); // byte units
    $Index = 0; // index (used to choose $Type)
        
    while($Bytes >= 1024){
        // while bytes are greater or equal 1024
        // I can change the measurement unit

        $Bytes /= 1024; // divide bytes by 1024 times
        $Index++;       // increment index to change measurement unit
    }

    $Bytes = round($Bytes, 2); // round the bytes to 2 decimal numbers
    $humanbytes = array("value" => $Bytes, "meas_unit" => $Type[$Index]."B"); 
        
    return($humanbytes);
}


// =======================================================================================
// MAIN

if (!count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))) {

    // check the request method
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $os = php_uname('s'); // get the OS name

        $diskInfos = array(); // create an array that will contain free space, occupied space and total space

        $maindisk = "/"; // Set default main partition to "/" (linux/mac machine)

        if (strpos($os, "Windows") !== false){
            // The OS is Windows, change the main partition
            $maindisk = "C:";
        }

        // Obtain free and total space, calculate occupied space
        $freespace_bytes = disk_free_space($maindisk);
        $totalspace_bytes = disk_total_space($maindisk);
        $occupiedspace_bytes = $totalspace_bytes - $freespace_bytes;

        // Change measurement unit
        $freespace = HumanSize($freespace_bytes);
        $totalspace = HumanSize($totalspace_bytes);
        $occupiedspace = HumanSize($occupiedspace_bytes);
        
        // add bytes original value (used for the graph)
        $freespace["bytes_value"] = $freespace_bytes;
        $totalspace["bytes_value"] = $totalspace_bytes;
        $occupiedspace["bytes_value"] = $occupiedspace_bytes;

        // add disk infos to one array
        $diskInfos["freespace"] = $freespace;
        $diskInfos["totalspace"] = $totalspace;
        $diskInfos["occupiedspace"] = $occupiedspace;
        
        // set response data
        $response["data"] = $diskInfos;
    }
    else {
        // Unexpected request method
        // 405: The method received in the request-line is known by the origin server but not supported by the target resource.
        $request_res = array('errors' => array());
        array_push($request_res['errors'], array("id" => 700,
                                                 "htmlcode" => 405,
                                                 "message" => "Method not supported by the target resource"));
    }

    $json_response = json_encode($response); // convert array to JSON
    print $json_response;                    // print JSON to the client
}

?>