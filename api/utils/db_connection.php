<?php
/**
 * **DB_CONNECTION**: manages database connection and queries.
 * 
 * This script defines a function to connect to the database
 * and two function that execute queries (one returns data in an array and the other as JSON)
 * 
 * @file
 * @since 01_01
 * @author Stefano Zenaro (https://github.com/mario33881)
 * @copyright MIT
*/

$db_dbname = "db100_100";


/**
 * Connects to a database and returns errors or connection object.
 * 
 * This function connects to the database by reading credentials stored inside the 'credentials.ini' file. 
 *
 * @since 01_01
 * @param string $dbname database name
 * @return array $conn_res array with errors or connection object
*/
function dbconn($dbname){
    // array with default values
    $conn_res = array('errors' => array(), 'connect_obj' => NULL);
    
    // ini file with credentials
    $ini_array = parse_ini_file(__dir__ . '/../../../credentials/credentials.ini');
    
    // db data
    $servername = 'localhost';            // name/ip of the host
    $username = $ini_array['DB_USER100']; // username
    $password = $ini_array['DB_PASS100']; // password
    
    // connect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // check if connection was successful
    if ($conn->connect_error) {
        // add the error
        array_push($conn_res['errors'], array('id' => 110,
                                              'htmlcode' => 500,
                                              'message' => "Couldn't connect to the database"));
    }
    
    $conn->set_charset('utf8mb4');
    
    $conn_res['connect_obj'] = $conn;
    
    return $conn_res;
}


/**
 * This function executes the query and returns the data.
 *
 * Executes the query, obtains data and stores it in an array,
 * clears memory and returns the array
 *
 * @since 01_01
 * @param object $t_mysqli connection object
 * @param string $t_query query to execute
 * @return array $query_res array with query data or errors
*/
function queryToArray($t_mysqli, $t_query){
    $query_res = array('errors' => array(), 'query_data' => NULL);
    
    // ESEGUI LA QUERY
    $result = $t_mysqli->query($t_query);
    
    // LOOP PER OTTENERE ARRAY DEI DATI RACCOLTI
    $data = array();
    
    foreach ($result as $row) {
        $data[] = $row;
    }
    
    if (count($data) == 0) {
        array_push($query_res['errors'], array('id' => 120,
                                               'htmlcode' => 500,
                                               'message' => "Select returned nothing"));
    }
    
    // LIBERA MEMORIA OCCUPATA DA "result"
    $result->close();
    
    $query_res['query_data'] = $data;
    
    // RESTITUISCI I DATI SOTTO FORMA DI ARRAY
    return $query_res;
}


/**
 * Returns data as JSON
 * 
 * Uses the queryToArray() to get data as an array
 * and then encodes it into JSON.
 * @since 01_01
 * @param object $t_mysqli connection object
 * @param string $t_query query to execute
 * @return array $query_res array with query data or errors
*/
function queryToJson($t_mysqli, $t_query){
    $query_res = array('errors' => array(), 'query_data' => NULL);
    
    $queryarray_res = queryToArray($t_mysqli, $t_query);
    
    $query_res['errors'] = $queryarray_res['errors'];
    $query_res['query_data'] = json_encode($queryarray_res['query_data']);
    
    return $query_res;
}
?>