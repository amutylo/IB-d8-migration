<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Taxonomy term source from database.
 *
 *
 * @MigrateSource(
 *   id = "taxonomy_term_products",
 *   source_provider = "taxonomy"
 * )
 */
class TermProducts extends DrupalSqlBase {

  protected $vocabularyName = 'Products';

  protected $vocVid;

  protected $termsToMigrate = [
    'DB2 Web Query',
    'FOCUS',
    'IBMi',
    'InfoAssist',
    'IPF',
    'iWay Tools',
    'Omni-Gen',
    'PMF',
    'Rstat',
    'WebFOCUS',
    'WebFOCUS Hyperstage',
    'WebFOCUS Mobile'
    ];
  
  /**
   * {@inheritdoc}
   */
  public function query() {
    if (empty($this->topicsVid)) {
      $vid = $this->select('vocabulary', 'v')
        ->fields('v', ['vid'])
        ->condition('name', $this->vocabularyName)
        ->distinct()
        ->execute()
        ->fetchCol();
      $this->vocVid = $vid;
    }
    $query = $this->select('term_data', 'td')
      ->fields('td')
      ->distinct()
      ->orderBy('td.tid');

    if (isset($this->vocVid)) {
      $query->condition('td.vid', $this->vocVid);
    }
    $query->condition('name', $this->termsToMigrate, 'IN');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'tid' => $this->t('The term ID.'),
      'vid' => $this->t('Existing term VID'),
      'name' => $this->t('The name of the term.'),
      'description' => $this->t('The term description.'),
      'weight' => $this->t('Weight'),
    ];
    
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['tid']['type'] = 'integer';
    return $ids;
  }

}
