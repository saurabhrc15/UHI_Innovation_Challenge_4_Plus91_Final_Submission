<?php
global $aConnStack;
$aConnStack = [];

//! Do you want pooling to be enabled
define("POOLING_ENABLED", TRUE);

//! What should be the maximum size of the pool? above specified size, connections will be closed immendiately for better memory management
define("POOL_SIZE", 4);

class DBConnManager {
    protected $sDBHost;
    protected $sDBPort;
    protected $sDBUser;
    protected $sDBPass;
    protected $sDBName;
    protected $rDBConns;
    protected $iDBConnCount;

    /*constructor to establish the connection. If a database name is passed,
     * it will connect to that database, otherwise it connects to default database
    */
    function __construct($sDBName = NULL, $iApplyReportingDBConfig = null)
    {
        
        
        if($sDBName === NULL){
            $sDBName = DEFAULT_DATABASE;
        }
        $this->sDBHost = DATABASE_HOST;
        $this->sDBUser = DATABASE_USER;
        $this->sDBPass = DATABASE_PASS;

        if(defined("DATABASE_PORT")) {
            $this->sDBPort = DATABASE_PORT;
        }
        else {
            //! Revert default to php ini mysqli default port
            $this->sDBPort = ini_get("mysqli.default_port");
        }

        $this->sDBName = $sDBName;
        $this->rDBConns = array();
        $this->iDBConnCount = 0;
    }

    // It will return the connection instance
    /*
    * 
    */
    function getConnInstance()
    {
        global $aConnStack;

        if(POOLING_ENABLED && (!isset($aConnStack[$this->sDBName]) || $aConnStack[$this->sDBName]===NULL)) {
            $aConnStack[$this->sDBName] = array();
        }

        //! If we don't have a connection available in connection stack, create a new one and give it
        if(!POOLING_ENABLED || count($aConnStack[$this->sDBName])==0){
             $this->rDBConns[$this->iDBConnCount] = new mysqli($this->sDBHost, $this->sDBUser, $this->sDBPass, $this->sDBName, $this->sDBPort);
            if($this->rDBConns[$this->iDBConnCount]->connect_error){
                die($this->rDBConns[$this->iDBConnCount]->connect_error);
            }
            $this->iDBConnCount++;           

            return $this->rDBConns[$this->iDBConnCount-1];    
            
        }
        else {

            //! Reuse them
            $existingConn = array_pop($aConnStack[$this->sDBName]);
            $this->rDBConns[$this->iDBConnCount] = $existingConn;
            $this->iDBConnCount++;
            return $existingConn;
        }       
    }

    //! close all the connection when class is destructed
    function  __destruct()
    {
        
        global $aConnStack;
        $iPooledConn = count($aConnStack[$this->sDBName]);
        for($ii = 0; $ii< $this->iDBConnCount; $ii++){
            if(is_object($this->rDBConns[$ii]) && $this->rDBConns[$ii]->ping()){
                if(POOLING_ENABLED && $iPooledConn<POOL_SIZE) {
                    array_push($aConnStack[$this->sDBName], $this->rDBConns[$ii]);
                    $iPooledConn++;
                }
                else {
                    $this->rDBConns[$ii]->close();                
                }
            }
            else {
            }
        }    
    }

    //! To close all the pooled connection at the end of the script
    static public function closePooledConnection() {
        if(POOLING_ENABLED) {
            global $aConnStack;
            foreach ($aConnStack as $sDBName => $aDBConn) {
                for($ii = 0; $ii< count($aDBConn); $ii++){
                    if(is_object($aDBConn[$ii]) && $aDBConn[$ii]->ping()){
                        $aDBConn[$ii]->close();
                    }
                }    
            }
        }
    }
}

//! Register shutdown function to close conenctions at the end
if(POOLING_ENABLED) {
    register_shutdown_function('DBConnManager::closePooledConnection');
}