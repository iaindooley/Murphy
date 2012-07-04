<?php
    rocketpack\Install::package('https://github.com/iaindooley/Murphy',array(0,0,0));

    rocketpack\Dependencies::register(function()
    {
        rocketpack\Dependency::forPackage('https://github.com/iaindooley/Murphy')
        ->add('https://github.com/iaindooley/Args',array(0,2,0))
        ->verify();
    });
