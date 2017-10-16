<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d6\Node;

/**
 * Drupal 6 node source from database.
 *
 * @MigrateSource(
 *   id = "case_studies_node"
 * )
 */
class CaseStudiesNode extends Node {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    // get only english node
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
    $fields['field_customer_story_pdf'] = $this->t('Pdf file');
    $fields['field_homepage_blurb'] = $this->t('Home page blurb');
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


    if ($pdf = $row->getSourceProperty('field_customer_story_pdf')) {
      $row->setSourceProperty('field_customer_story_pdf_fid', $pdf[0]['fid']);
    }
    if ($hb = $row->getSourceProperty('field_homepage_blurb')) {
      $hb[0]['format'] = 'full_html';
      $row->setSourceProperty('field_homepage_blurb', $hb);
    }

    return parent::prepareRow($row);
  }
}
