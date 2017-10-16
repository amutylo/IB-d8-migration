<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d6\Node;

/**
 * Drupal 6 node source from database.
 *
 * @MigrateSource(
 *   id = "press_release_node"
 * )
 */
class PressReleaseNode extends Node {


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
    $fields['field_sub_heading'] = $this->t('Subheading');
    $fields['field_city'] = $this->t('City');
    $fields['field_article_datef'] = $this->t('Datef');
    $fields['field_press_contact'] = $this->t('Press contact reference');

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

    if ($date = $row->getSourceProperty('field_article_datef')) {
      $timestamp = strtotime($date[0]['value']);
      $date[0]['value'] = date('Y-m-d', $timestamp);
      $row->setSourceProperty('field_article_datef', $date);
    }

    return TRUE;
  }
}
