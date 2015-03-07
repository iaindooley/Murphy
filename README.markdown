# Murphy - simple automated testing for RocketSled

by: iain@workingsoftware.com.au

Murphy is an automated testing framework that focuses on simplicity and accessibility.

The idea is not that it helps you produce bug free code, but that it helps you only fix each bug once.

The philosophy is simple: when you write a piece of code, write another piece of code that runs it.

###NB: If you were using Murphy prior to October 30, 2013 this release will not work with older versions of RocketSled. If you are using an older version of RocketSled (tagged 1.3 or prior) then you'll need to use Murphy released tagged 0.2.3 or prior.

## Goals:

* Provide a drastically simpler and more accessible method of automated testing than NUnit style frameworks (eg. PHPUnit)

* Create tests that are just like any other part of your codebase: as simple, complex, sloppy or elegant as you care to make them

* Avoid having to come up with stupid names for each of your test functions like testIfWeAreAbleToLoginAsUser

* Provide an intuitive means of creating and using database fixtures (ie. sample data sets) without lots of boilerplate queries

## Usage

Murphy works with https://github.com/iaindooley/RocketSled. See the RocketSled page for more details on installing and using packages.

For the sake of this example, let's assume you've created a package called killerapp and you create a class called MakeThings:

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

        public function makeValueGoPop()
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
    Murphy\Test::add(function($runner)
    {
        $things = new MakeThings('ohai');

        if($things->makeValueGoPop())
            $runner->pass();
        else
            $runner->fail('Did not make it go pop');
    });
```

You can now run this from your RocketSled install root with:

```
php index.php Murphy
```

You will see your test fail with output like this:

```
====Output from packages/killerapp/make_things.class.php.murphy/default.run.php===========

FAIL: error in: /path/to/RocketSled/packages/killerapp/make_things.class.php.murphy/default.run.php on line: 9: Did not make it go pop

=====================================
```

Now update your ```makeThingsGoPop()``` method to return TRUE instead:

```php
public function makeValueGoPop()
{
    return TRUE;
}
```

If you re-run Murphy now, you should see a "." indicating that the test passed like this:

```
====Output from packages/killerapp/make_things.class.php.murphy/default.run.php===========
.

=====================================
```

Using the ```fail()``` method of the test runner ensures that a failure in one test will not result in a halting of the entire suite, and also that you will see the correct line number so you can easily locate the failure.

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

NB: Murphy database fixtures currently work only with the mysql library in PHP.

The way that Murphy handles database fixtures has been heavily inspired by http://lettuce.it/ so I'd first like to give a very brief explanation of what a database fixture is, how I've worked with them in the past and why I was so impressed by Lettuce.

A simple way to think about database fixtures and "cut through the jargon" is just to imagine you have some code and it operates on some data that is stored in a database. Imagine you're testing your code by submitting a form, and each time you submit the form, you go and look in the database to see if the result was correct. If it's not correct, you fix your code and try again. In order to speed things up, you might have a file created with ```mysqldump``` in order to re-import your "base line" data after each test.

That "base line" data is what you would call a fixture. It's something that should exist prior to your test code running.

Now in order to have that data in the database to begin with you had to get it in there somehow - either with forms or you wrote some SQL statements by hand. If you're using a framework that has scaffolding (ie. a system that automatically generates web forms based on your database structure) then it would probably be simpler to use those forms to submit your test data, then do a database dump so you had your fixture file.

When creating database fixtures with PHPUnit you create an XML document which kind of approximates a table structure, eg. one node per "row" with content being in attributes. It's quite honestly one of the worst tasks I have ever had to perform as a programmer.

The problem with all three of these methods (writing SQL by hand, using some scaffolded forms or creating blood curdling XML documents) is two fold:

1. You have to account for all fields/columns in your tables, even if the data in them is inconsequential to the test you're writing

2. You have to hold the "mapping" of objects in your head (or on a piece of paper)

To illustrate that last point, here's a simple example. Say you have two tables user and group, and they are in a many to many relationship with a joining table user_in_group your insert queries might look something like this:

```
INSERT INTO user(user_id,name) VALUES(1,'Iain');
INSERT INTO group(group_id,name) VALUES(1,'Coders');
INSERT INTO user_in_group(user_id,group_id) VALUES(1,1);
```

As you can see I've had to hold "in my head" the fact that Iain (uid 1) is a member of the group Coders (gid 1). When dealing with complex databases this quickly spirals completely out of control.

The approach that Lettuce has is quite ingenious. The "fixture" format is just a pipe delimited table-like representation of data in whatever format is most convenient to you. So the above could be written as:

```
user name | group name
Iain      | Coders
```

In Lettuce, you then define a function which recevies each of these "rows" as an array (or rather a Dictionary in Python). For each row, you can write code which will insert the rows into the database. By doing this with code, you're able to automatically populate certain fields, derive values for one field from another, and more importantly scale the production of lots of fixture data into a very complex database without completely caving in your skull.

So without further ado, here is the way to create a fixture in Murphy:

```php
    /**
    * @tables user,group,user_in_group
    * user  | group
    * Pete  | Sydney
    * Iain  | Canberra
    */
    \Murphy\Fixture::add(function($row)
    {
        $this->link->query('INSERT INTO user(name) VALUES(\''.$row['user'].'\'');
        $user_id = mysqli_insert_id($this->link);
        $this->link->query('INSERT INTO group(name) VALUES(\''.$row['group'].'\'');
        $group_id = mysqli_insert_id($this->link);
        $this->link->query('INSERT INTO user_in_group(user_id,group_id) VALUES('.(int)$user_id.','.(int)$group_id.')');
    });
```

As you can see, each "row" of my fixture is passed into the anonymous function below it sequentially, and I can write some code that creates the structure I need.

But the best thing is that when I'm writing my fixture data, I can think in terms of the "outcome" for my application - ie. that the user Pete is in the group Sydney, not that Pete has user id 1 and the group Sydney has user id 1 and therefore I need to make an entry for 1,1 in the user_in_group table.

Just like the test code, you can organise your fixture code however you like. You can namespace your fixture file, and put common tasks into functions or separate files if they're used by more than one fixture etc. Your fixtures are just like any other piece of code in your application.

In order to use a fixture from your test code, you load and execute it. In your ```default.run.php``` file you would put the following code at the start of your test:

```php

<?php
\Murphy\Fixture::load('killerapp', dirname(__FILE__).'/fixture.php')->execute();
```

The only problem is now your data has been created in a database that you don't know how to access. You can pass in an anonymous function to the ```execute()``` method that will receive the details of the new test database that was created in order to allow you to establish a connection to the test database:

```php
\Murphy\Fixture::load('killerapp', dirname(__FILE__).'/fixture.php')->execute(function($aliases)
{
    //get the connection details for the killerapp database
    $aliases = $aliases['killerapp'];
    $host = $aliases[0];
    $username = $aliases[1];
    $password = $aliases[2];
    $dbname = $aliases[3];
    $this->link = mysqli_connect($host,$username,$password);
    $this->link->select_db($dbname);
});
```

The rest of your test will now have access to that test database. You can include more than one fixture in any file - they will be executed sequentially. You can also load multiple fixtures together and have them executed all at once using the ```also()``` method. This can help you to keep your fixtures modular and reusable:

```php
//load some base fixture data
Murphy\Fixture::load(dirname(__FILE__).'/../common/base.php')
//also load some extra fixture data
->also('killerapp', dirname(__FILE__).'/extra.php')
->execute(function($aliases)
{
    //get the connection details for the killerapp database
    $aliases = $aliases['killerapp'];
    $host = $aliases[0];
    $username = $aliases[1];
    $password = $aliases[2];
    $dbname = $aliases[3];
    $this->link = mysqli_connect($host,$username,$password);
    $this->link->select_db($dbname);
});
```

## Non-database fixtures

You can also use the Fixture system to create non-database fixtures. If you just omit the ```@database``` and ```@tables``` decorators in the docblock above your fixture, the system will still split the data in your fixture up and pass it in as a bunch of rows. For example you could use it to create a data file:

```php
/**
* user | group
* Iain | Sydney
* Pete | Canberra
*/
Murphy\Fixture::add(function($row)
{
    file_put_contents(dirname(__FILE__).'../../cache/data.txt','"'.implode('","',$row).'"',FILE_APPEND);
});
```

You could also use this style of fixture to _create_ your database if it didn't already exist (for example if you were create a re-usable module that didn't expect to have a database already installed or to know the name of the database that would be present).

## Running database fixtures

When you run a database fixture you need to pass in the config file of your mysql install as a ```dbconfig``` parameter to Murphy on the command line:

```
php index.php Murphy dbconfig=/path/to/dbconfig.php
```

The file dbconfig.php should have the following format:

```
<?php
return array('db_host' => 'localhost',
             'db_user' => 'root',
             'db_pass' => 'root',
             'db_name' => 'killerapp',
             'db_port' => 3309
             );
?>
```

## A complex example

One of the things that always pisses me off about reading through testing framework documentation is that they provide these really trivial examples. Well if you take a look at https://github.com/iaindooley/PluSQL you can see in the .murphy directories all the tests for how PluSQL should operate. The PluSQL README file also has info about how to download and run the Murphy tests.

## Convergence Testing

Some people may be a bit uncomfortable with this style of testing, because if you're writing code to create the fixtures that your tests run against, and then your tests are just like any other piece of code, doesn't that mean you'll just have more bugs?

The way I see it is that you're writing two pieces of code that test each other, and converging on quality. That's why I'd like to call this style of testing Convergence testing.

The chances that you will create completely complementary bugs is remote, but not impossible. It will happen. And when it does, you'll fix your tests, and that's one less bug to worry about.

The goal of convergence testing is not to produce bug free code, but to fix each bug only once.
