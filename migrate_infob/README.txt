Migration of Drupal 6 content from www.informationbuilders.com to a new Drupal 8 instance.
add to settings.php

$databases['infob']['default'] = array (
  'database' => 'ib_prod',
  'username' => 'username',
  'password' => 'password',
  'prefix' => 'ib_',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

Then enable modules:
Migrate
Migrate Drupal
Migrate UI
Migrate Plus
Migrate Tools

after enable
migrate_infob module where all migrations live

go to command line
navigate to docroot folder
fire command
drush ms - see all migrations
our migration group is "Group: D6 imports (infob)"

fire migration -
drush mi infob_file to run file migration
drush mi infob_publications to run publications content migration
then you can run another migrations

revert migration
drush mr infob_migration_name_to revert

you can break migration with Control + C
but you have to reset migration
drush mrs infob_migration_name_to reset to idle
then
drush mr infob_migration_name_to revert 




