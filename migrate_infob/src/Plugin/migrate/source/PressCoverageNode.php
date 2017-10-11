<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d6\Node;

/**
 * Drupal 6 node source from database.
 *
 * @MigrateSource(
 *   id = "press_coverage_node"
 * )
 */
class PressCoverageNode extends Node {
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
    //add custom fields
    $fields['field_article_datef'] = $this->t('Datef');
    $fields['field_pdf'] = $this->t('Electronic book');
    $fields['field_article_link'] = $this->t('Article link');
    $fields['field_homepage_blurb'] = $this->t('Home page blur');
    $fields['field_article_pub'] = $this->t('Article publication');
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


    $link = $row->getSourceProperty('field_article_link');
    if (isset($link[0]['value'])) {
      $row->setSourceProperty('field_article_link', $link[0]['value']);
    }

    return TRUE;
  }
}
