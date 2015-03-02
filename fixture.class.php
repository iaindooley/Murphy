<?php
    namespace Murphy;
    use Exception,Closure,Args;

    class Fixture
    {
        private $callbacks;
        private $data;
        private $link;
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

        public static function add(Closure $callback)
        {
            self::instance()->callbacks[] = $callback;
        }

        public function execute(Closure $db_connect = NULL)
        {
            $databases = array();

            foreach($this->data as $key => $d)
            {
                if(isset($d['tables']))
                {
                    if(!isset($d['database']))
                        throw new InvalidFixtureFormatException('You have included a @tables directive for '.$d['tables'].' but no @database directive');

                    if(!isset($databases[$d['database']]))
                        $databases[$d['database']] = array();

                    $databases[$d['database']] = array_merge($databases[$d['database']],$d['tables']);
                }
            }

            $aliases  = array();

            if(count($databases))
            {
                if(!$dbconfig_path = Args::get('dbconfig',Args::argv))
                {
                    echo 'You need to include dbconfig in the command line arguments'.PHP_EOL;
                    exit(1);
                }

                if(!$dbconfig = include($dbconfig_path))
                {
                    echo 'You need to include dbconfig in the command line arguments'.PHP_EOL;
                    exit(1);
                }

                foreach($databases as $database => $tables)
                {
                    $this->link = mysqli_connect($dbconfig['db_host'],
                                                 $dbconfig['db_user'],
                                                 $dbconfig['db_pass']);
                    $this->link->select_db($database);
                    $tables = array_unique($tables);
                    $create_table_statements = array();

                    foreach($tables as $table)
                    {
                        if(!$query = $this->link->query('SHOW CREATE TABLE `'.$table.'`'))
                            throw new Exception(mysqli_error($this->link));

                        $row = $query->fetch_assoc();
                        $create_table_statements[] = $row['Create Table'];
                    }

                    $alias = md5($database);
                    $aliases[$database] = array($dbconfig['db_host'],
                                                $dbconfig['db_user'],
                                                $dbconfig['db_pass'],
                                                md5($database));

                    $this->link->query('DROP DATABASE IF EXISTS `'.$alias.'`') or die(mysqli_error($this->link));
                    $this->link->query('CREATE DATABASE `'.$alias.'`') or die(mysqli_error($this->link));
                    $this->link->select_db($alias);

                    foreach($create_table_statements as $stmt)
                        $this->link->query($stmt) or die(mysqli_error($this->link));
                }

                if(!$db_connect instanceof Closure)
                    throw new DbFixtureConnectionException('You have included database fixtures without a callback to pass connection details to');
            }

            foreach($this->data as $key => $d)
            {
                if(isset($aliases[$d['database']]))
                    $this->link->select_db($aliases[$d['database']][3]);

                $args = array();

                foreach($d['rows'] as $row)
                {
                    foreach($row as $index => $line)
                        $args[$d['header'][$index]] = $line;

                    self::instance()->callbacks[$key]($args);
                }
            }

            if($db_connect instanceof Closure)
                $db_connect($aliases);

            self::$instance = NULL;
        }

        public function also($database, $file)
        {
            $this->extractFixtureDataFromFile($database, $file);
            return $this;
        }

        public static function load($database, $file)
        {
            self::instance()->extractFixtureDataFromFile($database, $file);
            return self::instance();
        }

        private function extractFixtureDataFromFile($database, $path)
        {
            require($path);
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
                else if(preg_match('/Murphy\\\\Fixture::add\(/U',$cont,$matches))
                    $docblocks[] = $previous_docblock;
            }

            $offset = count($this->data);

            foreach($docblocks as $key => $block)
            {
                $cur_index = $key+$offset;
                $this->data[$key+$offset] = array('rows' => array());

                foreach($block as $b)
                {
                    if(strpos(trim($b),'/*') !== 0)
                    {
                        $b = trim(str_replace('*','',$b));

                        if(strpos($b,'@database ') === 0){
                            //$database = trim(str_replace('@database','',$b));
                        }
                        else if(strpos($b,'@tables ') === 0)
                            $this->data[$cur_index]['tables'] = explode(',',trim(str_replace('@tables','',$b)));
                        else if(!isset($this->data[$cur_index]['header']))
                            $this->data[$cur_index]['header'] = array_map('trim',explode('|',$b));
                        else
                            $this->data[$cur_index]['rows'][] = array_map('trim',explode('|',$b));
                    }
                }

                if(!$database)
                    throw new InvalidFixtureFormatException('You must specify the @database directive for fixture: '.$fixture_name.' in: '.$path);

                $this->data[$cur_index]['database'] = $database;
            }
        }
    }

    class DuplicateFixtureException extends Exception{}
    class InvalidFixtureFormatException extends Exception{}
    class DbFixtureConnectionException extends Exception{}
