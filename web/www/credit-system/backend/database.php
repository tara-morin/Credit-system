<?php
/**
 * Database Class
 *
 * Contains connection information to query PostgresSQL.
 */

 require_once __DIR__ . '/config.php';
class Database {
    public $db_con;
    private $pdo;
    
    public function __construct() {
        $host = Config::$db["host"];
        $user = Config::$db["user"];
        $database = Config::$db["database"];
        $password = Config::$db["pass"];
        $port = Config::$db["port"];
        $db_con= pg_connect("host=$host port=$port dbname=$database user=$user password=$password");
        if ($db_con !== false){
            $this->db_con= $db_con;
            // $this->setup_database();
        }
        else{
            // echo "connection failed";
        }
    }
    public function query($query, ...$params) {
        $res = pg_query_params($this->db_con, $query, $params);

        if ($res === false) {
            // echo pg_last_error($this->db_con);
            return false;
        }

        return pg_fetch_all($res);
    }
    public function setup_database(){
        $setup_script = __DIR__ . "/init.sql";
        if (!file_exists($setup_script)) {
            // echo "init.s ql file not found!";
        }
        else{
        $sql= file_get_contents($setup_script);
        $lines= explode(";", $sql);
        $pattern= "/^--/";
        $counter = 0;
        foreach ($lines as $x){
            $x= ltrim($x);
            $x= rtrim($x);
            if (preg_match($pattern, $x)==0 && strcmp($x, "")!=0){
                $result= pg_query($this->db_con, $x);
            }
        }
        }
    }
    public function create_database(){
        $check= "select exists( SELECT datname FROM pg_catalog.pg_database WHERE lower(datname) = lower('dbname'));";
        $result= pg_query($this->db_con, $check);
        if ($result==1){
        }
        return $result;
    }
}