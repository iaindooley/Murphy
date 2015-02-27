<?php
    namespace Murphy;
    use Args,Plusql;

    class TestFixture implements \RocketSled\Runnable
    {
        public function run()
        {
            echo 'EXPECTED OUTPUT:'.PHP_EOL;
            echo 'non db fixture
non db fixture
Non db fixture 2
Non db fixture 2
iain@workingsoftware.com.au
iaindooley@gmail.com'.PHP_EOL.PHP_EOL;

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

            $this->link = mysqli_connect($dbconfig['db_host'],
                                         $dbconfig['db_user'],
                                         $dbconfig['db_pass']) or die(mysqli_error($this->link));
            $this->link->query('DROP DATABASE IF EXISTS test_fixture1');
            $this->link->query('DROP DATABASE IF EXISTS test_fixture2');
            $this->link->query('CREATE DATABASE test_fixture1');
            $this->link->query('CREATE DATABASE test_fixture2');
            $this->link->select_db('test_fixture1');
            $this->link->query('
CREATE TABLE `user` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT \'\',
  `password` varchar(255) NOT NULL DEFAULT \'\',
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1');
            $this->link->query('
CREATE TABLE `group` (
  `group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL DEFAULT \'\',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1');
            $this->link->query('
CREATE TABLE `user_in_group` (
  `user_id` bigint(20) unsigned NOT NULL DEFAULT \'0\',
  `group_id` bigint(20) unsigned NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1');
            $this->link->select_db('test_fixture2');
            $this->link->query('
CREATE TABLE `user` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT \'\',
  `password` varchar(255) NOT NULL DEFAULT \'\',
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1');
            $this->link->query('
CREATE TABLE `group` (
  `group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL DEFAULT \'\',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1');
            $this->link->query('
CREATE TABLE `user_in_group` (
  `user_id` bigint(20) unsigned NOT NULL DEFAULT \'0\',
  `group_id` bigint(20) unsigned NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1');

            Fixture::load(dirname(__FILE__).'/sample.fixture.php')
            ->also(dirname(__FILE__).'/sample2.fixture.php')
            ->execute(function($db_aliases)
            {
                $credential_names = array('test_fixture1' => 'live',
                                          'test_fixture2' => 'dev');

                foreach($db_aliases as $src => $credentials)
                    Plusql::credentials($credential_names[$src],$credentials);
            });

            Fixture::load(dirname(__FILE__).'/sample3.fixture.php')->execute();

            foreach(Plusql::from('live')->user->select('user_id,username')->run()->user as $client)
                echo $client->username.PHP_EOL;
        }
    }
