<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      commands
 *
 *      This contains all commands.
 *      command type : php complex {command} [options]
 *      command : makemigration, migrate, mysqldump, mysqlrestore, cron, email, etc
 *      options : -u, -f, etc
 *
**********************************************************************************************/


use \Config;
use Library\Log as Log;
use Library\Request as Request;
use Library\Database as Database;


/*
 *      Command Class
 */
class Command {

    private $request,                       // Request instance
            $argv,                          // argv array
            $cmd,                           // command
            $options,                       // options
            $status,                        // status of result
            $message;                       // message of result


    public function __construct()
    {
        $this->request = Request::getInstance();
        $this->argv = $this->request->argv;
        $this->status = false;
        $this->options = array(
            '-' => "",                      // main option
        );
        $this->message = "";
    }

    /**
     *      check command request is validated
     *      @return bool
     */
    private function isValidate()
    {
        return ($this->request->isCLI() 
                && count($this->argv) > 1
                && $this->argv[0] == "complex"
                && method_exists($this, $this->argv[1])
                && !in_array($this->argv[1], ["__construct", "isValidate", "run", "_showMessage", "_getOption"]));
    }

    /**
     *      show message in prompt
     *      @return void
     */
    private function _showMessage()
    {
        Log::addLog("staus is " . (is_null($this->status) ? 'NULL' : ($this->status ? 'TRUE' : 'FALSE')));
        echo (is_null($this->status) ? '' : ("\033[46m[" . date("Y-m-d H:i:s") . "]\033[0m " . 
            ($this->status ? "\033[32mSuccess!\033[0m " : "\033[31mFailed!\033[0m ") 
            . $this->cmd . PHP_EOL)) . $this->message . PHP_EOL;
    }

    /**
     *      get option
     *      @param  string  $key
     *      @param  bool    $isMain
     *      @return string
     */
    private function _getOption($key, $isMain=false)
    {
        if($isMain && $this->_getOption($key) == "") return $this->_getOption('-');
        return isset($this->options[$key]) ? $this->options[$key] : "";
    }

    /**
     *      run
	 * 		@return bool
     */
    public function run()
    {
        Log::addLog("command is " . implode(" ", $this->argv));

        // check validate
        if (!$this->isValidate()) {
            Log::addLog("command is invalid.");
            return $this->status;
        }

        // get cmd and options
        $this->cmd = $this->argv[1];
        for($i = 2; $i < count($this->argv); ) {
            // option key - value
            if (strlen($this->argv[$i]) == 2 && preg_match("/\-[a-zA-Z]/i", $this->argv[$i])) {
                $key = substr($this->argv[$i], 1, 1);
                $this->options[$key] = ($i < (count($this->argv) - 1)) ? $this->argv[$i+1] : "";
                $i += 2;
                continue;
            }
            // main option
            if ($this->options['-'] == "") {
                $this->options['-'] = $this->argv[$i];
            }
            $i ++;
        }

        // call cmd method
        $this->{$this->cmd}();
        $this->_showMessage();
        
        return $this->status;
    }

    /**
     *      [command] show version
     *      @return void
     */
    private function version()
    {
        // show version of config
        $this->message = "\033[34mComplex\033[0m v" . VERSION;
        $this->status = null;
    }

    /**
     *      [command] show help
     *      @return void
     */
    private function help()
    {
        // show all commands to help
        $this->message = "\033[34mList of Complex Commands\033[0m" . PHP_EOL . PHP_EOL;
        $this->message .= "\t\033[33mcron\033[0m" . PHP_EOL . "\t\tcron job" . PHP_EOL;
        $this->message .= "\t\033[33mmakecontroller\033[0m" . PHP_EOL . "\t\tmake new controller" . PHP_EOL 
            . "\t\t\033[36mmakecontroller [-c] <controller_name> -p <parent_controller>\033[0m" . PHP_EOL;
        $this->message .= "\t\033[33mmakemiddleware\033[0m" . PHP_EOL . "\t\tmake new middleware" . PHP_EOL 
            . "\t\t\033[36mmakemiddleware [-m] <middleware_name>\033[0m" . PHP_EOL;
        $this->message .= "\t\033[33mmakemigration\033[0m" . PHP_EOL . "\t\tmake new migration" . PHP_EOL 
            . "\t\t\033[36mmakemigration [-m] <migration_name> -t <table_name> -c <command_type> -d <data>\033[0m" . PHP_EOL;
        $this->message .= "\t\033[33mmakemodel\033[0m" . PHP_EOL . "\t\tmake new model" . PHP_EOL 
            . "\t\t\033[36mmakemodel [-m] <model_name> -t <table_name> -f <fields>\033[0m" . PHP_EOL;
        $this->message .= "\t\033[33mmakeservice\033[0m" . PHP_EOL . "\t\tmake new service" . PHP_EOL 
            . "\t\t\033[36mmakeservice [-s] <service_name>\033[0m" . PHP_EOL;
        $this->message .= "\t\033[33mmigrate\033[0m" . PHP_EOL . "\t\tmigrate database" . PHP_EOL 
            . "\t\t\033[36mmigrate [-m] <migration_name> -d <up_down>\033[0m" . PHP_EOL;
        $this->message .= "\t\033[33mmysqldump\033[0m" . PHP_EOL . "\t\tmysql dump" . PHP_EOL;
        $this->message .= "\t\033[33mmysqlrestore\033[0m" . PHP_EOL . "\t\tmysql restore" . PHP_EOL;
        $this->message .= "\t\033[33memail\033[0m" . PHP_EOL . "\t\tsend email" . PHP_EOL 
            . "\t\t\033[36memail [-t] <to_email_address>\033[0m" . PHP_EOL;
        $this->message .= "\t\033[33mhelp\033[0m" . PHP_EOL . "\t\tshow help" . PHP_EOL;
        $this->message .= "\t\033[33mversion\033[0m" . PHP_EOL . "\t\tshow version" . PHP_EOL;
        $this->status = null;
    }

}