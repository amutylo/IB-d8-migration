<?php

namespace Drupal\migrate_infob\Plugin\migrate\process;

use Drupal\file\Plugin\migrate\process\d6\CckFile;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateProcessPlugin(
 *   id = "ib_cck_file"
 * )
 */
class IbCckFile extends CckFile {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    // Configure the migration process plugin to look up migrated IDs from
    // the (Ib) file migration (overriding the default d6_file).
    $migration_plugin_configuration = [
      'source' => ['fid'],
      'migration' => 'infob_file',
    ];

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('plugin.manager.migrate.process')->createInstance('migration', $migration_plugin_configuration, $migration)
    );
  }

}
