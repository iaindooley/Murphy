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
            {
                try
                {
                    $test($this);
                }
                
                catch(\Exception $exc)
                {
                    echo PHP_EOL.self::getExceptionTraceAsString($exc).PHP_EOL;
                }
            }
            
            exit(0);
        }

        /**
        * copy/paste from http://stackoverflow.com/questions/1949345/how-can-i-get-the-full-string-of-php-s-gettraceasstring
        */
        public static function getExceptionTraceAsString($exception) {
            $rtn = "";
            $count = 0;
            foreach ($exception->getTrace() as $frame) {
                $args = "";
                if (isset($frame['args'])) {
                    $args = array();
                    foreach ($frame['args'] as $arg) {
                        if (is_string($arg)) {
                            $args[] = "'" . $arg . "'";
                        } elseif (is_array($arg)) {
                            $args[] = "Array";
                        } elseif (is_null($arg)) {
                            $args[] = 'NULL';
                        } elseif (is_bool($arg)) {
                            $args[] = ($arg) ? "true" : "false";
                        } elseif (is_object($arg)) {
                            $args[] = get_class($arg);
                        } elseif (is_resource($arg)) {
                            $args[] = get_resource_type($arg);
                        } else {
                            $args[] = $arg;
                        }   
                    }   
                    $args = join(", ", $args);
                }
                $rtn .= sprintf( "#%s %s(%s): %s(%s)\n",
                                         $count,
                                         $frame['file'],
                                         $frame['line'],
                                         $frame['function'],
                                         $args );
                $count++;
            }
            return $rtn;
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
