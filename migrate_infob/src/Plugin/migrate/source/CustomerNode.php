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
    $query->condition('n.status', 1);
    
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = parent::fields();
    //add custom fields
    $fields['field_customer_blurb'] = $this->t('Customer quote');
    $fields['field_customer_logo'] = $this->t('Customer logo');
    $fields['field_customer_descriptor'] = $this->t('Customer description');
    $fields['field_customer_location'] = $this->t('Address');
    $fields['field_content_box_1'] = $this->t('Challenge text');
    $fields['field_content_box_2'] = $this->t('Strategy text');
    $fields['field_content_box_3'] = $this->t('Result text');
    $fields['field_video_url'] = $this->t('Video url');
    $fields['field_masthead_image'] = $this->t('Image');
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

    $clogo = $row->getSourceProperty('field_customer_logo');
    if (!empty($clogo) && isset($clogo[0]['fid'])) {
      $row->setSourceProperty('field_customer_logo', $clogo[0]['fid']);
    } else {
      $row->setSourceProperty('field_customer_logo', null);
    }

    $img = $row->getSourceProperty('field_masthead_image');
    if (!empty($img) && isset($img[0]['fid'])) {
      $row->setSourceProperty('field_masthead_image', $img[0]['fid']);
    } else {
      $row->setSourceProperty('field_customer_logo', null);
    }

    $description = $row->getSourceProperty('field_customer_descriptor');
    if (isset($description[0]['value'])) {
      $description[0]['format'] = 'full_html';
      $row->setSourceProperty('field_customer_descriptor', $description);
    }

    if ($customer_blurb = $row->getSourceProperty('field_customer_blurb')) {
      foreach($customer_blurb as $item) {
        if (!empty($item['value'])) {
          $value = explode("\r\n", $item['value']);
          $value[0] = iconv('UTF-8', 'ASCII//TRANSLIT', $value[0]);
          $value[0] = str_replace('"', '', $value[0]);
          $row->setSourceProperty('field_quote', array('value' => trim(strip_tags($value[0])), 'format' => 'full_html'));
          $row->setSourceProperty('field_quote_source', trim(strip_tags($value[1])));
          $row->setSourceProperty('field_quote_source_position', trim(strip_tags($value[2])));
        }
      }
    }

    if ($cbox1 = $row->getSourceProperty('field_content_box_1')){
      $cbox1[0]['format'] = 'full_html';
      $row->setSourceProperty('field_content_box_1', $cbox1);
    }
    if ($cbox2 = $row->getSourceProperty('field_content_box_2')){
      $cbox2[0]['format'] = 'full_html';
      $row->setSourceProperty('field_content_box_2', $cbox2);
    }

    if ($cbox3 = $row->getSourceProperty('field_content_box_3')){
      $cbox3[0]['format'] = 'full_html';
      $row->setSourceProperty('field_content_box_3', $cbox3);
    }

    //migrate address data;
    if ($location = $row->getSourceProperty('field_customer_location')) {
      $lid = $location[0]['lid'];
      $address_data = $this->select('location', 'l')
        ->fields('l', array('lid', 'street', 'additional', 'city', 'province', 'postal_code', 'country'))
        ->condition('l.lid', $lid)
        ->distinct()
        ->execute()
        ->fetchAllAssoc('lid');
      if (!empty($address_data)) {
        $address = array();
        foreach ($address_data[$lid] as $label => $data) {
          switch ($label){
            case 'street':
              $address[0]['address_line1'] = $data;
            break;
            case 'additional':
              $address[0]['address_line2'] = $data;
              break;
            case 'city':
              $address[0]['locality'] = $data;
              break;
            case 'province':
              $address[0]['administrative_area'] = $data;
              break;
            case 'postal_code':
              $address[0]['postal_code'] = $data;
              break;
            case 'country':
              $address[0]['country_code'] = strtoupper($data);
              break;
          }
        }
        $row->setSourceProperty('address', $address);
      }
    }

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
    }

    //- Map customer taxonomy
    $customerTerms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadTree('customers', $parent = 0, $max_depth = NULL, $load_entities = FALSE);

    $termMatch = null;
    foreach ($customerTerms as $term) {
      similar_text($row->getSourceProperty('title') , $term->name, $percent);
      if ($percent > $termMatch) {
        $termMatch = $percent;
        $customerTermId = $term->tid;
      }
    }

    if (isset($customerTermId)) {
      $row->setSourceProperty('field_customer', $customerTermId);
    }

    return TRUE;
  }

  public function object2array($object) {
    return @json_decode(@json_encode($object),1);
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
