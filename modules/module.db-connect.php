<?php
/**
 * This class pulls the database logon information out of wp-config.
 * It then evals the settings it finds into PHP and then makes the
 * database connection.
 * 
 * Acts as a Singleton. 
 * 
 * @package wpConfigConnection
 * @author Mark Flint 
 * @link www.bluecubeinteractive.com
 * @copyright Please just leave this PHPDoc header in place.
 */
Class wpConfigConnection {  
    /**
     * @var object $_singleton This is either null in the case that this class has not been
     * called yet, or an instance of the class object if the class has been called.
     * 
     * @access public
     */
    private static $_singleton;
      
    /**
     * @var resource $_con The connection.
     * @access public
     */
    public $_con;
      
    /**
     * The wp-config.php file var
     * @var string $str The string that the file is brought into.
     * @access private
     */
    private $str;
     
    /**
     * @var $filePath Path to wp-config.php file
     * @access private
     */
    private $filePath;
     
    /**
     * @var array Array of constant names used by wp-config.php for the
     * logon details
     * @access private
     */
    private $paramA=array('DB_NAME','DB_USER','DB_PASSWORD','DB_HOST');
     
    /**
    * @var bool $database Can check this var to see if your database was connected successfully
    */
    public $_database;
     
    /**
     * Constructor. This function pulls everything together and makes it happen.
     * This could be unraveled to make the whole thing more flexible later.
     * 
     * @param string $filePath Path to wp-config.php file
     * @access private
     */
    function __construct($type=1,$filePath='./wp-config.php'){
        $this->filePath=$filePath;
        $this->getFile();
        $this->serverBasedCondition();
        /**
         * eval the WP contants into PHP
         */
        foreach ($this->paramA as $p) {
            $this->evalParam('define(\''.$p.'\'','\');');
        }
		
        $this->createConstant('$table_prefix = "','";');
		
        switch ($type) {
            default:
            case 1:
                $this->conMySQL_Connect();
                break;
            case 2:
                $this->conPDO();
                break;
            case 3:
                $this->conMySQLi();
                break;
        }
    }
     
    /**
     * Make the connection using mysql_connect
     */
    private function conMySQL_Connect(){
        try {
            if (($this->_con = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)) == false){
                throw new Exception ('Could not connect to mySQL. ' . mysql_error());   
            }
        } catch (Exception $e){
            exit ('Error on line  ' . $e->getLine(). ' in ' . $e->getFile() . ': ' . $e->getMessage());
        }
        try {
            if (($this->_database = mysql_select_db(DB_NAME, $this->_con)) == false){
                throw new Exception ('Could not select database. ' . mysql_error());    
            }
        } catch (Exception $e){
            exit ('Error on line  ' . $e->getLine(). ' in ' . $e->getFile() . ': ' . $e->getMessage());
        }
    }
     
    /**
     * Make the connection using mySQLi
     */
    private function conMySQLi(){
        $this->_con = @new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
        if (mysqli_connect_errno()){
            exit ('MySQLi connection failed: ' . mysqli_connect_error());
        }
    }
     
    /**
     * Make the connection using PDO
     */
    private function conPDO(){
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
            $this->_con = @new PDO($dsn, DB_USER, DB_PASSWORD);
        } catch (PDOException $e) {
            exit ('Error on line  ' . $e->getLine(). ' in ' . $e->getFile() . ': ' . $e->getMessage() );
        }
    }
     
    /**
     * Read the wp-config.php file into a string
     * 
     * @access private
     */
    private function getFile() {
        try {
            $this->str = @file_get_contents($this->filePath);
            if ($this->str == false){
                throw new Exception ('Failed to read file (' . $this->filePath . ') into string.');
            }
        } catch (Exception $e) {
            exit ('Error on line  ' . $e->getLine(). ' in ' . $e->getFile() . ': ' . $e->getMessage() );
        }
    }
     
    /**
     * Get the logon parameter and evaluate it into PHP.
     * Eg, eval("define('DB_NAME', 'm4j3lub3_wordpress');");
     * 
     * @param string $pre This defines what to look for at the start of a logon parameter
     * definition. Eg, if you are looking for  "define('DB_NAME', 'm4j3lub3_wordpress');"
     * then the $pre bit would be "define('DB_NAME'".
     * 
     * @param string $post Like $pre, this defines what to look for at the end of the logon
     * parameter definition. In the case of WordPress it is always going to be "');"
     * 
     * @access private
     */
    private function evalParam($pre,$post){
        $str=$this->str;
        $str1=substr($str,strpos($str,$pre));
        $str1=substr($str1,0,strpos($str1,$post) + strlen($post));
		
		//echo $str1;
		//echo '<br>';
        eval($str1);
    }
 
    private function createConstant($pre,$post){
        $str=$this->str;
        $str1=substr($str,strpos($str,$pre));
		
        $str1=substr($str1,0,strpos($str1,$post) + strlen($post));
		$table_prefix = explode('"' , $str1);
		$table_prefix = $table_prefix[1];
	
		$str = "define('TABLE_PREFIX','".$table_prefix."');";

		//echo '<br>';
        eval($str);
    }
 
    /**
     * Grab the right code block if there are more than one set of definitions
     * 
     * Sets $this->str to be the right code block
     * 
     * Used for when there are conditional settings based on local or remote configuration,
     * using the condition: if ($_SERVER['HTTP_HOST']=='localhost') { ...
     * 
     * @access private
     */
    private function serverBasedCondition(){
        if(strpos($this->str, '$_SERVER["HTTP_HOST"]') || strpos($this->str, '$_SERVER[\'HTTP_HOST\']')){
            if(strpos($this->str, '$_SERVER["HTTP_HOST"]')){
                // case of double quotes - get a substring
                $this->str = substr($this->str, strpos($this->str,'$_SERVER["HTTP_HOST"]'));
            } elseif(strpos($this->str, '$_SERVER[\'HTTP_HOST\']')){
                // case of single quotes - get a substring
                $this->str = substr($this->str, strpos($this->str,'$_SERVER[\'HTTP_HOST\']'));
            }
             
            // substring from 1st occurance of {
            $this->str = substr($this->str, strpos($this->str,'{')+1);
             
            if ($_SERVER['HTTP_HOST']=='local.dev') {
                // local - substring from start to 1st occurance of } - this is now the block
                $this->str = substr($this->str, 0, strpos($this->str, '}') - 1);
            } else {
                // remote - substring from the else condition
                $this->str = substr($this->str, strpos($this->str, '{')+1);
                $this->str = substr($this->str, 0, strpos($this->str, '}') - 1);
            }
            // replace all double quote with single to make it easier to find the param definitions
            $this->str=str_replace('"','\'',$this->str);
        } 
    }
     
    /**
     * Return an instance of the class based on type of connection passed
     * 
     * $types are:
     * 1 = Procedural connection using mysql_connect()
     * 2 = OOP connection using PHP Data Objects (PDO)
     * 3 = OOP connection using mySQLi
     * 
     * @return resource Database connection
     * @access private
     */
    private static function returnInstance($type) {
        if (is_null(self::$_singleton)){
            self::$_singleton = new wpConfigConnection($type);
        }
        return self::$_singleton;
    }
     
    /**
     * Action the return of the instance based on Procedural connection using mysql_connect()
     * 
     * @access public
     * @return resource Procedural connection using mysql_connect()
     */
    public static function getInstance(){
        return self::returnInstance(1);
    }
     
    /**
     * Action the return of the instance based on OOP connection using PDO
     * 
     * @access public
     * @return resource OOP connection using PHP Data Objects (PDO)
     */
    public static function getPDOInstance(){
        return self::returnInstance(2);
    }
     
    /**
     * Action the return of the instance based on OOP connection using mySQLi
     * 
     * @access public
     * @return resource OOP connection using mySQLi
     */
    public static function getMySQLiInstance(){
        return self::returnInstance(3);
    }
}
 
 

if ( file_exists ( './../../../../wp-config.php' ) )
{
	$mfdb = new wpConfigConnection( 1 , './../../../../wp-config.php' );
	
}
else if ( './../../../../../wp-config.php' )
{
	$mfdb = new wpConfigConnection( 1 , './../../../../../wp-config.php' );
	
}
else
{
	echo 'wp-config.php cannot be found.';
	exit;
}