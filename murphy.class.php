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

            $tests = RocketSled\filteredPackages(function($arg)
            {
                $ret = FALSE;
                
                if(RocketSled\endsWith($arg,'.run.php'))
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
                    if(strpos($path,$exc) === 0)
                        $use = FALSE;
                }

                foreach($include as $inc)
                {
                    if(strpos($path,$inc) === 0)
                        $use = TRUE;
                }
                
                if(strpos($path,'.murphy/') === FALSE)
                    $use = FALSE;
                
                if($use)
                {
                    $output = '';

                    exec('php index.php "Murphy\\Test" path='.escapeshellarg($path).' mysql_root='.escapeshellarg(Args::get('mysql_root',Args::argv)),$output,$exit_code);
                                
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
