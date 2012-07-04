<?php
    namespace Murphy;
    use Args,Closure;

    class Test implements \RocketSled\Runnable
    {
        private static $instance = NULL;
        private $tests;
        
        public function __construct()
        {
            $this->tests = array();
        }
        
        public static function add(Closure $function)
        {
            self::instance()->tests[] = $function;
        }
        
        public static function instance()
        {
            if(self::$instance === NULL)
                self::$instance = new Test();
            
            return self::$instance;
        }

        public function run()
        {
            global $argv;
            
            if(!isset($argv))
                exit(1);
            if(!$path = Args::get('path',Args::argv))
                die('You need to include the path argument pointing to the class file to be tested');

            require($path);
            
            foreach(self::instance()->tests as $test)
                $test($this);
            
            exit(0);
        }
        
        public function pass()
        {
            //later we might store some stats here - ie. class names
            //and files, to do some crude code coverage
            echo '.';
        }

        public function fail($msg)
        {
            $tr = debug_backtrace();
            $file = $tr[0]['file'];
            $line = $tr[0]['line'];
            //$class = $tr[1]['class'];
            echo PHP_EOL.'FAIL: error in: '.$file.' on line: '.$line.': '.$msg.PHP_EOL;
        }
    }
