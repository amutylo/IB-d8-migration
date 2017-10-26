<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d6\Node;

/**
 * Drupal 6 node source from database.
 *
 * @MigrateSource(
 *   id = "think_tank_node"
 * )
 */
class ThinkTankNode extends Node {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
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
    $fields['field_article_link'] = $this->t('Article link');
    $fields['field_article_author'] = $this->t('Article author');
    $fields['field_article_author_title'] = $this->t('Article author title');
    $fields['field_article_pub'] = $this->t('Article publication reference');
    $fields['field_news_type'] = $this->t('News type');
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
      'title' => '[node:title] | [site:name]',
      'description' => $row->getSourceProperty('DESCRIPTION'),
      'keywords' => $row->getSourceProperty('KEYWORDS'),
    ];
    $row->setSourceProperty('meta_tags', serialize($metatags));

    if ($body = $row->getSourceProperty('body')) {
      $value[0]['value'] = $body;
      $value[0]['format'] = 'full_html';
      $row->setSourceProperty('body', $value);
    }


    $link = $row->getSourceProperty('field_article_link');
    if (isset($link[0]['value'])) {
      $row->setSourceProperty('field_article_link', $link[0]['value']);
    }

    if ($date = $row->getSourceProperty('field_article_datef')) {
      $timestamp = strtotime($date[0]['value']);
      $date[0]['value'] = date('Y-m-d', $timestamp);
      $row->setSourceProperty('field_article_datef', $date);
    }
    
    return TRUE;
  }
}
