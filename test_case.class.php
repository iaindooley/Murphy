<?php
    namespace murphy;
    use Plusql;

    class TestCase extends Test
    {
        public function runTests()
        {
            Fixture::load('murphy/sample.fixture.php')->execute(function($creds)
            {
                $rev_aliases = array('test_fixture1' => 'live',
                                     'test_fixture2' => 'dev');
                
                foreach($creds as $src => $alias)
                    Plusql::credentials($rev_aliases[$src],$alias);
            });
            
            if(Plusql::begin('live')->query('SELECT * FROM user')->user->username == 'iain@workingsoftware.com.au')
                $this->pass();
            if(Plusql::begin('live')->query('SELECT * FROM user')->user->username == 'iain@workingsoftware.com.au')
                $this->fail('Could not load correct username');
        }
    }
