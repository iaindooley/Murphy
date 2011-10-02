<?php
    namespace murphy;
    use Args,Plusql;

    class TestFixture implements \rocketsled\Runnable
    {
        public function run()
        {
            if(!$mysql_root = Args::get('mysql_root',Args::argv))
                die('You need to pass in mysql_root');

            mysql_connect('localhost','root',$mysql_root) or die(mysql_error());
            mysql_query('DROP DATABASE IF EXISTS test_fixture1');
            mysql_query('DROP DATABASE IF EXISTS test_fixture2');
            mysql_query('CREATE DATABASE test_fixture1');
            mysql_query('CREATE DATABASE test_fixture2');
            mysql_select_db('test_fixture1');
            mysql_query('
CREATE TABLE `user` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT \'\',
  `password` varchar(255) NOT NULL DEFAULT \'\',
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1');
            mysql_query('
CREATE TABLE `group` (
  `group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL DEFAULT \'\',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1');
            mysql_query('
CREATE TABLE `user_in_group` (
  `user_id` bigint(20) unsigned NOT NULL DEFAULT \'0\',
  `group_id` bigint(20) unsigned NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1');
            mysql_select_db('test_fixture2');
            mysql_query('
CREATE TABLE `user` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT \'\',
  `password` varchar(255) NOT NULL DEFAULT \'\',
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1');
            mysql_query('
CREATE TABLE `group` (
  `group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL DEFAULT \'\',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1');
            mysql_query('
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
            
            foreach(Plusql::begin('live')->query('SELECT user_id,username FROM `user`')->user as $client)
                echo $client->username.PHP_EOL;
        }
    }
