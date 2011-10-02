<?php
    namespace murphy;

    abstract class Test implements \rocketsled\Runnable
    {
        public function run()
        {
            global $argv;
            
            if(!isset($argv))
                exit(1);

            $this->runTests();
            exit(0);
        }
        
        abstract public function runTests();

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
            $class = $tr[1]['class'];
            echo PHP_EOL.'FAIL: error in: '.$file.': '.$class.' on line: '.$line.': '.$msg.PHP_EOL;
        }
    }
