<?php
    class TestConfig implements rocketsled\Runnable
    {
        public function run()
        {
            echo 'TEST_USER: '.murphy\TEST_USER.PHP_EOL;
            echo 'TEST_PASS: '.murphy\TEST_PASS.PHP_EOL;
        }
    }
