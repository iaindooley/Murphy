<?php
    namespace murphy;

    class Murphy implements \rocketsled\Runnable
    {
        public function run()
        {
            if(Application::param('include'))
                $include = explode(',',Application::param('include'));
            else
                $include = array();

            if(Application::param('exclude'))
                $exclude = explode(',',Application::param('exclude'));
            else
                $exclude = array();


            $files = shell_exec('find . -name "test_*.class.php"');
            
            foreach(explode(PHP_EOL,$files) as $path)
            {
                $output = '';
                $class = rsFileToClass(basename($path));
                
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
                
                if($use)
                {
                    try
                    {
                        $refl = new ReflectionClass($class);
                        
                        if($refl->isSubClassOf('TestHandler'))
                        {
                            exec('php index.php h='.$class.' mysql_root='.escapeshellarg(Application::param('mysql_root')),$output,$exit_code);
                            
                            if($exit_code)
                                echo 'FATAL ERROR: '.$class.' terminated abnormally'.PHP_EOL;
    
                            echo PHP_EOL.'====Output from '.$class.'==========='.PHP_EOL;
                            
                            foreach($output as $opline)
                                echo $opline.PHP_EOL;
    
                            echo PHP_EOL.'====================================='.PHP_EOL;
                        }
                    }
                    
                    catch(ReflectionException $exc)
                    {
                        //echo PHP_EOL.'The class: '.$class.' looks like it should exist, but it doesn\'t'.PHP_EOL;
                    }
                }
            }
        }
    }
