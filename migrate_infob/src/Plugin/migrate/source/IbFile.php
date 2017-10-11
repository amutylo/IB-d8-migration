<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;

use Drupal\file\Plugin\migrate\source\d6\File;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Row;

/**
 * Filtered list of files to migrate.
 *
 * @MigrateSource(
 *   id = "infob_file"
 * )
 */
class IbFile extends File {

  /**
   * Tables and columns in the D6 database containing the fids we want to
   * migrate.
   *
   * @var array
   *   For each element, the table name and column name for the file field we
   *   want to migrate.
   */
  protected $fileFields = [
    ['table' => 'content_field_masthead_image', 'column' => 'field_masthead_image_fid'],
    ['table' => 'content_field_customer_logo', 'column' => 'field_customer_logo_fid'],
    ['table' => 'content_field_pb_thumbnail', 'column' => 'field_pb_thumbnail_fid'],
    ['table' => 'content_field_pdf', 'column' => 'field_pdf_fid'],
  ];

  /**
   * A cache of the file IDs whitelisted for migration.
   *
   * @var array
   *   List of file IDs.
   */
  protected $fidsToMigrate = [];

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Ignore any fids not referenced by our automatically-migrated nodes.
    if (empty($this->fidsToMigrate)) {
      foreach ($this->fileFields as $field_info) {
        $fids = $this->select($field_info['table'], 't')
          ->fields('t', [$field_info['column']])
          ->isNotNull($field_info['column'])
          ->distinct()
          ->execute()
          ->fetchCol();
        $this->fidsToMigrate = array_merge($this->fidsToMigrate, $fids);
      }
      $pdf_ids = $this->select('files', 'f')
        ->fields('f', array('fid'))
        ->condition('filemime', 'application/pdf')
        ->distinct()
        ->execute()
        ->fetchCol();
      $this->fidsToMigrate = array_merge($this->fidsToMigrate, $pdf_ids);
    }


    $file_path = 'http://www.informationbuilders.com/' . $row->getSourceProperty('filepath');
    $file_exist = $this->fileExists($file_path);

    if ($file_exist && in_array($row->getSourceProperty('fid'), $this->fidsToMigrate)) {
      return parent::prepareRow($row);
    }
    else {

      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
      return FALSE;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function fileExists($path){
    return (@fopen($path,"r")==true);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    
    return $query;
  }
}
