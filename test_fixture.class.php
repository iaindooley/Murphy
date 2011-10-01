<?php
    class TestFixture implements rocketsled\Runnable
    {
        public function run()
        {
            murphy\Fixture::load('murphy/sample.fixture.php')
            ->also('murphy/sample2.fixture.php')
            ->execute();
        }
    }
