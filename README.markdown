# Murphy - simple automated testing for RocketSled

by: iain@workingsoftware.com.au

Murphy is an automated testing framework that focuses on simplicity and accessibility.

The idea is not that it helps you produce bug free code, but that it helps you only fix each bug once.

The philosophy is simple: when you write a piece of code, write another piece of code that runs it.

## Goals:

* Provide a drastically simpler and more accessible method of automated testing than NUnit style frameworks (eg. PHPUnit)

* Create tests that are just like any other part of your codebase: as simple, complex, sloppy or elegant as you care to make them

* Avoid having to come up with stupid names for each of your test functions like testIfWeAreAbleToLoginAsUser

* Provide an intuitive means of creating and using database fixtures (ie. sample data sets) without lots of boilerplate queries

## Usage

Murphy works with https://github.com/iaindooley/RocketSled. See the RocketSled page for more details on installing and using packages.

For the same of this example, let's assume you've created a package called killerapp and you create a class called MakeThings:

```
rs_install_dir/
`-- packages
    `-- killerapp
        `-- make_things.class.php
```

Now you've made your class, and you want to start implementing some code. As you go along, you want to run that code in order to see if it works. So you add the following code to MakeThings:

```php
<?php
    class MakeThings
    {
        private $value;

        public function __construct($value)
        {
            $this->value = $value;
        }
        
        public makeValueGoPop()
        {
            return FALSE;
        }
    }
```

In order to easily test that ```makeValueGoPop()``` method, simply create a directory called ```make_things.class.php.murphy``` and put a file called ```default.run.php``` in it:

```
rs_install_dir/
`-- packages
    `-- killerapp
        |-- make_things.class.php
        `-- make_things.class.php.murphy
            `-- default.run.php
```

Now in ```default.run.php``` you can add a test to the Murphy test harness:

```php
<?php
    murphy\Test::add(function($runner)
    {
        $things = new MakeThings('ohai');
        
        if($this->makeValueGoPop())
            $runner->pass();
        else
            $runner->fail('Did not make it go pop');
    });
```

You can now run this from your RocketSled install root with:

```
php index.php Murphy
```

You will see your test fail. Now update your ```makeThingsGoPop()``` method to return TRUE instead:

```php
public makeValueGoPop()
{
    return TRUE;
}
```

If you re-run Murphy now, you should see a "." indicating that the test passed. Using the ```fail()``` method of the test runner ensures that a failure in one test will not result in a halting of the entire suite, and also that you will see the correct line number so you can easily locate the failure.

You can add as many ".run.php" files as you like, and call them anything you like. You can namespace them, you can put common functionality into library files, basically anything that you would normally do when writing code, you can do to your tests.

You can include more than one test in a single file, or split them up into individual files. You can then run all or part of your test suite using the "include" and "exclude" parameters:

```
//murphy will look for "*.run.php" inside a .murphy directory
//whose path starts with packages/killerapp/make
php index.php Murphy include="packages/killerapp/make"

//murphy will look for "*.run.php" inside a .murphy directory
//except whose path starts with packages/killerapp/make
php index.php Murphy exclude="packages/killerapp/make"
```

## Database fixtures


