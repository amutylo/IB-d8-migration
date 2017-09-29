<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d6\Node;

/**
 * Drupal 6 node source from database.
 *
 * @MigrateSource(
 *   id = "customer_node"
 * )
 */
class CustomerNode extends Node {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
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
    $fields['field_customer_blurb'] = $this->t('Customer quote');
    $fields['field_customer_logo'] = $this->t('Customer logo');
    $fields['field_customer_descriptor'] = $this->t('Customer descriptor');
    $fields['field_customer_location'] = $this->t('Customer location');
    $fields['field_content_box_1'] = $this->t('Challenge text');
    $fields['field_content_box_2'] = $this->t('Strategy text');
    $fields['field_content_box_3'] = $this->t('Result text');
    $fields['field_video_url'] = $this->t('Video url');
    $fields['field_masthead_image'] = $this->t('Video url');
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

    $clogo = $row->getSourceProperty('field_customer_logo');
    if (isset($clogo[0]['fid'])) {
      $row->setSourceProperty('field_customer_logo', $clogo[0]['fid']);
    }

    $img = $row->getSourceProperty('field_masthead_image');
    if (isset($img[0]['fid'])) {
      $row->setSourceProperty('field_masthead_image', $img[0]['fid']);
    }

    return TRUE;
  }
  
}
