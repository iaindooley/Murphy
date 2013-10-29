<?php
    RocketPack\Dependency::register(function()
    {
        RocketPack\Dependency::into(dirname(__FILE__).'/../')
        ->add('https://github.com/iaindooley/Args')
        ;
    });
