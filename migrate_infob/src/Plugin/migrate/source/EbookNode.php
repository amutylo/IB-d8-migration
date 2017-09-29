<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d6\Node;

/**
 * Drupal 6 node source from database.
 *
 * @MigrateSource(
 *   id = "ebook_node"
 * )
 */
class EbookNode extends Node {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    //get only english nodes;
    $query->condition('n.language', 'en');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = parent::fields();
    //add custom fields
    $fields['field_article_datef'] = $this->t('Datef');
    $fields['field_ebook_pdf'] = $this->t('Electronic book');
    $fields['field_salesforce_campaign_id'] = $this->t('Salesforce campaign id');
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

    $pdf = $row->getSourceProperty('field_ebook_pdf');
    if (isset($pdf[0]['fid'])) {
      $row->setSourceProperty('field_ebook_pdf', $pdf[0]['fid']);
    }

    return TRUE;
  }
}
