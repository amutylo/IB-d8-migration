<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d6\Node;

/**
 * Drupal 6 node source from database.
 *
 * @MigrateSource(
 *   id = "analystreport_node"
 * )
 */
class AnalystReportNode extends Node {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    //get only english nodes;
    $query->condition('n.language', 'en');
    $query->condition('n.status', 1);
    
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = parent::fields();
    //add custom fields;
    $fields['field_article_datef'] = $this->t('Datef');
    $fields['field_pdf'] = $this->t('Pdf file');
    $fields['field_salesforce_campaign_id'] = $this->t('Salesforce campaign id');
    $fields['field_resource_type'] = $this->t('Resource type');
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (parent::prepareRow($row) === FALSE) {
      return FALSE;
    }

    $metatags = [
      'title' => $row->getSourceProperty('HEAD_TITLE') . ' | [site:name]',
      'description' => $row->getSourceProperty('DESCRIPTION'),
      'keywords' => $row->getSourceProperty('KEYWORDS'),
    ];
    $row->setSourceProperty('meta_tags', serialize($metatags));

    $pdf = $row->getSourceProperty('field_pdf');
    if (isset($pdf[0]['fid'])) {
      $row->setSourceProperty('field_pdf', $pdf[0]['fid']);
    }

    if ($an_rep = $row->getSourceProperty('field_analyst_report_description')) {
      $an_rep[0]['format'] = 'full_html';
      $row->setSourceProperty('field_analyst_report_description', $an_rep);
    }
    if ($wp_url = $row->getSourceProperty('field_whitepaper_url')) {
      $row->setSourceProperty('field_whitepaper_url', $wp_url[0]['value']);
    }
    
    return TRUE;
  }
}
