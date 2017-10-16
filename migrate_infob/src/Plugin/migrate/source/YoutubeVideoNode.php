<?php

namespace Drupal\migrate_infob\Plugin\migrate\source;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d6\Node;

/**
 * Drupal 6 node source from database.
 *
 * @MigrateSource(
 *   id = "youtube_video_node"
 * )
 */
class YoutubeVideoNode extends Node {
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
    $fields['field_homepage_blurb'] = $this->t('Home page blurb');
    $fields['field_external_youtube'] = $this->t('External youtube url');
    $fields['field_article_datef'] = $this->t('Date');
    $fields['field_pdf'] = $this->t('Pdf file');

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
    
    if ($hb = $row->getSourceProperty('field_homepage_blurb')) {
      $hb[0]['format'] = 'full_html';
      $row->setSourceProperty('field_homepage_blurb', $hb);
    }
    elseif ($body = $row->getSourceProperty('body')) {
      $row->setSourceProperty('field_homepage_blurb', $body);
    }
    return TRUE;
  }
}
