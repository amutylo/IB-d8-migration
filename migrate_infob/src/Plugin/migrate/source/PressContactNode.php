<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d6\Node;

/**
 * Drupal 6 node source from database.
 *
 * @MigrateSource(
 *   id = "press_contact_node"
 * )
 */
class PressContactNode extends Node {


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
    $fields['field_author_company'] = $this->t('Company');
    $fields['field_tip_author_name'] = $this->t('Author name');
    $fields['field_email'] = $this->t('Email');
    $fields['field_phone'] = $this->t('Phone');
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

    return TRUE;
  }
}
