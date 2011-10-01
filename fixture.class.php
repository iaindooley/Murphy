<?php
    namespace murphy;
    use Exception,Closure;
    
    class Fixture
    {
        private $callbacks;
        private $data;
        private static $instance = NULL;
        
        private function __construct()
        {
            $this->callbacks = array();
            $this->data = array();
        }
        
        private static function instance()
        {
            if(self::$instance === NULL)
                self::$instance = new Fixture();
            
            return self::$instance;
        }
        
        public static function add($name,Closure $callback)
        {
            
            if(isset(self::instance()->callbacks[$name]))
                throw new DuplicateFixtureException('You have already added a fixture called: '.$name);
            
            self::instance()->callbacks[$name] = $callback;
        }
        
        public function execute()
        {
die(print_r($this->data));
            $tables = array();
            
            foreach($this->data as $database => $d)
                $tables = array_merge($tables,$d['tables']);
            
            $tables = array_unique($tables);
            
            foreach($this->data as $d)
            {
                $args = array();

                foreach($d['rows'] as $row)
                {
                    foreach($row as $index => $line)
                        $args[$d['header'][$index]] = $line;
                    
                    die(print_r($args));
                }
            }
        }

        public function also($file)
        {
            $this->extractFixtureDataFromFile($file);
            return $this;
        }
        
        public static function load($file)
        {
            self::instance()->extractFixtureDataFromFile($file);
            return self::instance();
        }
        
        private function extractFixtureDataFromFile($file)
        {
            $path = PACKAGES_DIR.'/'.$file;
            require_once($path);
            $contents = file($path,FILE_IGNORE_NEW_LINES);
            $docblocks = array();
            $cur_docblock = NULL;
            $previous_docblock = NULL;
        
            foreach($contents as $cont)
            {
                if(strpos($cont,'/**') !== FALSE)
                    $cur_docblock = array();
        
                if(strpos($cont,'*/') !== FALSE)
                {
                    $previous_docblock  = $cur_docblock;
                    $cur_docblock       = NULL;
                }
                
                if($cur_docblock !== NULL)
                    $cur_docblock[] = $cont;
                else if(preg_match('/murphy\\\\Fixture::add\(\'(.*)\'/U',$cont,$matches))
                    $docblocks[$matches[1]] = $previous_docblock;
            }
        
            foreach($docblocks as $fixture_name => $block)
            {
                $this->data[$fixture_name] = array('rows' => array());
                $database = 'default';

                foreach($block as $b)
                {
                    if(strpos(trim($b),'/*') !== 0)
                    {
                        $b = trim(str_replace('*','',$b));
                        
                        if(strpos($b,'@database ') === 0)
                            $database = trim(str_replace('@database','',$b));
                        else if(strpos($b,'@tables ') === 0)
                            $this->data[$fixture_name]['tables'] = explode(',',trim(str_replace('@tables','',$b)));
                        else if(!isset($this->data[$fixture_name]['header']))
                            $this->data[$fixture_name]['header'] = array_map('trim',explode('|',$b));
                        else
                            $this->data[$fixture_name]['rows'][] = array_map('trim',explode('|',$b));
                    }
                }
                
                $this->data[$fixture_name]['database'] = $database;
            }
        }
    }

    class DuplicateFixtureException extends Exception{}
