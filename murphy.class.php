<?php
    class Murphy implements RocketSled\Runnable
    {
        public function run()
        {
            if(!$include = Args::get('include',Args::argv))
                $include = array();
            else
                $include = explode(',',$include);

            if(!$exclude = Args::get('exclude',Args::argv))
                $exclude = array();
            else
                $exclude = explode(',',$exclude);

            if(!($dbconfig_path = Args::get('dbconfig',Args::argv)) || !($dbconfig = include($dbconfig_path)))
            {
                echo "You need to include 'dbconfig' in the command line arguments.".PHP_EOL;
                echo "The file should have the following format: ".PHP_EOL;
                echo "<?php".PHP_EOL;
                echo "return array('db_host' => 'localhost',".PHP_EOL;
                echo "             'db_user' => 'root',".PHP_EOL;
                echo "             'db_pass' => 'root',".PHP_EOL;
                echo "             'db_name' => 'killerapp',".PHP_EOL;
                echo "             'db_port' => 3309".PHP_EOL;
                echo "?>".PHP_EOL;
                echo PHP_EOL;
                echo "Usage: php index.php Murphy dbconfig=/path/to/dbconfig.php".PHP_EOL;
                exit(1);
            }

            $tests = RocketSled::filteredPackages(function($arg)
            {
                $ret = FALSE;

                if(RocketSled::endsWith($arg,'.run.php'))
                    $ret = $arg;

                return $ret;
            });

            foreach($tests as $path)
            {
                if(count($include))
                    $use = FALSE;
                else
                    $use = TRUE;

                foreach($exclude as $exc)
                {
                    if(strpos(realpath($path),realpath($exc)) === 0)
                        $use = FALSE;
                }

                foreach($include as $inc)
                {
                    if(strpos(realpath($path),realpath($inc)) === 0)
                        $use = TRUE;
                }

                if(strpos($path,'.murphy/') === FALSE)
                    $use = FALSE;

                if($use)
                {
                    $output = '';

                    exec('php index.php "Murphy\\Test" path='.escapeshellarg($path).' dbconfig='.escapeshellarg(Args::get('dbconfig',Args::argv)),$output,$exit_code);

                    if($exit_code)
                        echo 'FATAL ERROR: '.$path.' terminated abnormally'.PHP_EOL;

                    echo PHP_EOL.'====Output from '.$path.'==========='.PHP_EOL;

                    foreach($output as $opline)
                        echo $opline.PHP_EOL;

                    echo PHP_EOL.'====================================='.PHP_EOL;
                }
            }
        }
    }

    class InvalidMurphyTestException extends Exception{}
