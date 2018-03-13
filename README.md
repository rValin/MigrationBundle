RValinMigrationBundle
=============

RValinMigrationBundle adds support of migration in symfony (2 -> 4).  
It provide a flexible and easy to use solution to run custom sql query, command or scripts on a project.

Installation
------------

1) Use [Composer](https://getcomposer.org/) to download the library
```
composer require rvalin/migrationBundle
```


2) Then add the WhiteOctoberPagerfantaBundle to your application kernel:
```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new RValin\MigrationBundle\RValinMigrationBundle(),
        // ...
    );
}
```

3) Then update your database

```
php bin/console do:sc:up -f
```

Usage
-----

### Generate a migration class

To create a migration class use the following command :
```
php bin/console migration:script:generate --bundle=FooBundle
```
You can use the option "name" to set a custom name for the migration.   
Migration created with this command will be an instance of RValin\MigrationBundle\Tools\MigrationInterface.  

### Create your migration

By default migration extend from RValin\MigrationBundle\Tools\DefaultMigration.  
This class provide easy solution to run sql query and command.

Your code must be in the function "execute" from the Migration class.  
This function should return true of false whether the migrations worked.

#### SQL query

You can execute sql query using the function executeSql.  
```
function execute() {
    $query = 'UPDATE user SET enabled = false WHERE id = ?';
    $args = [1];
    
    return $this->executeSql($query, $args);
}
```

#### Command

You can run a command using executeCommand.  
See [Symfony](https://symfony.com/doc/current/console/command_in_controller.html) document for more details.
```
function execute() {
    $commandArgs = array(
        'command' => 'swiftmailer:spool:send',
        // (optional) define the value of command arguments
        'fooArgument' => 'barValue',
        // (optional) pass options to the command
        '--message-limit' => $messages,
    );
    
    $this->executeCommand($commandArgs);
}
```

### Run migrations

To run your migrations use :
```
php bin/console migration:script:run --execute
```

If you do not use the option "execute", command and sql query won't be executed.

Once a migration has been successfully run it won't be executed again.
Remove the entry from the table rvalin_migration to run a migration again or reverse it.

You can filter the migration you run using this options:  
**name** to run a specific migration  
**bundle** to run the migration of a specific version  
**maxVersion** Max version of the migration to run  
**maxDate** Max creation date of the migration to run

### Reverse migrations
To reverse migrations use
```
php bin/console migration:script:reverse --execute
```
If you do not use the option "execute", command and sql query won't be executed.

Only migration that has been run will be reverse.  

You can filter the migration you run using this options:  
**name** to run a specific migration  
**bundle** to run the migration of a specific version  
**minVersion** Min version of the migration to run  
**minDate** Min creation date of the migration to run