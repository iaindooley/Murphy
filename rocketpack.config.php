<?php
    rocketpack\Install::package('Murphy',array(0,1,0));

    rocketpack\Dependencies::register(function()
    {
        rocketpack\Dependency::forPackage('Murphy')
        ->add('Args',array(0,1,0))
        ->verify();
    });
