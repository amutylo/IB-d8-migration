<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Taxonomy term source from database.
 *
 * @todo Support term_relation, term_synonym table if possible.
 *
 * @MigrateSource(
 *   id = "taxonomy_term_topics",
 *   source_provider = "taxonomy"
 * )
 */
class TermTopics extends DrupalSqlBase {

  protected $vocabularyName = 'Topics';

  protected $vocVid;

  protected $termsToMigrate = [
    'Active Technologies',
    'Ad Hoc Reports',
    'Adapter',
    'BI App Development',
    'Big Data',
    'Business Analytics',
    'Business Intelligence',
    'Channels Solutions',
    'Cloud Computing',
    'Consulting',
    'Customer-facing',
    'Dashboards',
    'Data Cleansing',
    'Data Discovery',
    'Data Governance',
    'Data Integration',
    'Data Profiling',
    'Data Quality',
    'Embedded Analytics',
    'Enterprise Reporting',
    'ETL',
    'Executive Seminars',
    'Financial Reporting',
    'InfoApps',
    'Information Delivery',
    'Intelligent Search',
    'Internet of Things (IoT)',
    'Master Data Management',
    'Mobile',
    'Operational Business Intelligence',
    'Performance Management',
    'Portal',
    'Predictive Analytics',
    'Report Consolidation',
    'Security',
    'Self Service',
    'Summit',
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
