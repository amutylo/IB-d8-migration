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

    if ($pdf = $row->getSourceProperty('field_customer_story_pdf')) {
      $row->setSourceProperty('field_customer_story_pdf_fid', $pdf[0]['fid']);
    }

    if ($hb = $row->getSourceProperty('field_homepage_blurb')) {
      $hb[0]['format'] = 'full_html';
      $row->setSourceProperty('field_homepage_blurb', $hb);
    }


    //- Get taxonomy terms from node being imported
    $termIds = $this->select('term_node', 'n')
                    ->fields('n', array('tid'))
                    ->condition('n.nid', $row->getSourceProperty('nid'))
                    ->distinct()
                    ->execute()
                    ->fetchCol();

    if (!empty($termIds)) {
      $termData = $this->select('term_data', 't')
                       ->fields('t', array('vid', 'tid', 'name'))
                       ->condition('t.tid', $termIds, 'IN')
                       ->distinct()
                       ->execute()
                       ->fetchAll();

      //- Map industry taxonomy
      $industryTerms = $this->getTerms('industries', $termData);
      if (!empty($industryTerms)) {
        $row->setSourceProperty('field_industry', $industryTerms);
      }

      //- Map topic taxonomy
      $topicTerms = $this->getTerms('topics', $termData);
      if (!empty($topicTerms)) {
        $row->setSourceProperty('field_topic', $topicTerms);
      }

      //- Map product taxonomy
      $productTerms = $this->getTerms('products', $termData);
      if (!empty($productTerms)) {
        $row->setSourceProperty('field_tax_product', $productTerms);
      }

      //- Map function taxonomy
      $functionTerms = $this->getTerms('functions', $termData);
      if ( ! empty( $functionTerms )) {
        $row->setSourceProperty('field_tax_function', $functionTerms);
      }
    }

    //- Map customer taxonomy
    $customerNode = $row->getSourceProperty('field_customer_node');
    if (!empty($customerNode)) {

      $nid = $customerNode[0]['nid'];
      /**
       *  Get new node id from migrate_map_xxxx table as nid at destination is different than nid at source.
       */
      $d8nid = \Drupal::database()->select('migrate_map_infob_customer', 'mmic')
                      ->fields('mmic', array('destid1'))
                      ->condition('mmic.sourceid1', $nid)
                      ->distinct()
                      ->execute()
                      ->fetchCol();

      $d8node = \Drupal\node\Entity\Node::load($d8nid[0]);
      if ($d8node) {
        $customerTerms = $d8node->get('field_customer')->referencedEntities();
        $custTerms = [];
        foreach ($customerTerms as $term) {
          $custTerms[] = $term->tid->value;
        }
        $row->setSourceProperty('field_customer_node', $custTerms);
      }
    }

    return TRUE;
  }

  public function getTerms($vocabulary, $termData) {
    $terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadTree($vocabulary, $parent = 0, $max_depth = NULL, $load_entities = FALSE);
    $termIds = array();
    foreach ($terms as $k => $term) {
      foreach($termData as $availableTerm) {
        if ($term->name == $availableTerm['name']) {
          $termIds[] = $term->tid;
        }
      }
    }
    return $termIds;
  }
}