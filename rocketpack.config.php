<?php
    RocketPack\Install::package('https://github.com/iaindooley/Murphy',array(0,1,0));

    RocketPack\Dependencies::register(function()
    {
        RocketPack\Dependency::forPackage('https://github.com/iaindooley/Murphy')
        ->add('https://github.com/iaindooley/Args',array(0,2,0))
        ->verify();
    });
