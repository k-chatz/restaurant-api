<?php

class DbHandler
{
    private $conn;

    function __construct()
    {
        require_once 'dbConnect.php';
        // opening db connection
        $db = new dbConnect();
        $this->conn = $db->connect();
    }

    /**Fetching records with prepared statement and params*/
    function mysqli_prepared_query($sql,$typeDef = FALSE,$params = FALSE){
        if($stmt = mysqli_prepare($this->conn,$sql)){
            if(count($params) == count($params,1)){
                $params = array($params);
                $multiQuery = FALSE;
            } else {
                $multiQuery = TRUE;
            }

            if($typeDef){
                $bindParams = array();
                $bindParamsReferences = array();
                $bindParams = array_pad($bindParams,(count($params,1)-count($params))/count($params),"");
                foreach($bindParams as $key => $value){
                    $bindParamsReferences[$key] = &$bindParams[$key];
                }
                array_unshift($bindParamsReferences,$typeDef);
                $bindParamsMethod = new ReflectionMethod('mysqli_stmt', 'bind_param');
                $bindParamsMethod->invokeArgs($stmt,$bindParamsReferences);
            }

            $result = array();
            foreach($params as $queryKey => $query){
                foreach($bindParams as $paramKey => $value){
                    $bindParams[$paramKey] = $query[$paramKey];
                }
                $queryResult = array();
                if(mysqli_stmt_execute($stmt)){
                    $resultMetaData = mysqli_stmt_result_metadata($stmt);
                    if($resultMetaData){
                        $stmtRow = array();
                        $rowReferences = array();
                        while ($field = mysqli_fetch_field($resultMetaData)) {
                            $rowReferences[] = &$stmtRow[$field->name];
                        }
                        mysqli_free_result($resultMetaData);
                        $bindResultMethod = new ReflectionMethod('mysqli_stmt', 'bind_result');
                        $bindResultMethod->invokeArgs($stmt, $rowReferences);
                        while(mysqli_stmt_fetch($stmt)){
                            $row = array();
                            foreach($stmtRow as $key => $value){
                                $row[$key] = $value;
                            }
                            $queryResult[] = $row;
                        }
                        mysqli_stmt_free_result($stmt);
                    } else {
                        $queryResult[] = mysqli_stmt_affected_rows($stmt);
                    }
                } else {
                    $queryResult[] = FALSE;
                }
                $result[$queryKey] = $queryResult;
            }

            mysqli_stmt_close($stmt);
        } else {
            $result = FALSE;
        }

        if($multiQuery){
            return $result;
        } else {
            return $result[0];
        }
    }


    /**Fetching single record*/
    public function selectOne($query)
    {
        $result = $this->conn->query($query) or die($this->conn->error . __LINE__);
        return $result->fetch_array(MYSQLI_ASSOC);
    }


    /**Fetching single record*/
    public function select($query)
    {
        $result = $this->conn->query($query) or die($this->conn->error . __LINE__);
        $records = array();
        while($record = $result->fetch_array(MYSQLI_ASSOC)) {
            $records[] = $record;
        }
        return $records;
    }

    /**Creating new record*/
    public function insertIntoTable($obj, $column_names, $table_name)
    {

        $c = (array)$obj;
        $keys = array_keys($c);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) { // Check the obj received. If blank insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $c[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "'" . $$desired_key . "',";
        }
        $query = "INSERT INTO " . $table_name . "(" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")";
        $r = $this->conn->query($query) or die($this->conn->error . __LINE__);

        if ($r) {
            $new_row_id = $this->conn->insert_id;
            return $new_row_id;
        } else {
            return NULL;
        }
    }


    public function getSession()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $sess = array();
        if (isset($_SESSION['uid'])) {
            $sess["uid"] = $_SESSION['uid'];
            $sess["name"] = $_SESSION['name'];
            $sess["email"] = $_SESSION['email'];
        } else {
            $sess["uid"] = '';
            $sess["name"] = 'Guest';
            $sess["email"] = '';
        }
        return $sess;
    }


    public function destroySession()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (isSet($_SESSION['uid'])) {
            unset($_SESSION['uid']);
            unset($_SESSION['name']);
            unset($_SESSION['email']);
            $info = 'info';
            if (isSet($_COOKIE[$info])) {
                setcookie($info, '', time() - $cookie_time);
            }
            $msg = "Logged Out Successfully...";
        } else {
            $msg = "Not logged in...";
        }
        return $msg;
    }

}

?>

