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
    $fields['field_customer_blurb'] = $this->t('Customer quote');
    $fields['field_customer_logo'] = $this->t('Customer logo');
    $fields['field_customer_descriptor'] = $this->t('Customer descriptor');
    $fields['field_customer_location'] = $this->t('Customer location');
    $fields['field_content_box_1'] = $this->t('Challenge text');
    $fields['field_content_box_2'] = $this->t('Strategy text');
    $fields['field_content_box_3'] = $this->t('Result text');
    $fields['field_video_url'] = $this->t('Video url');
    $fields['field_masthead_image'] = $this->t('Video url');
    $fields['field_customer_location_lid'] = $this->t('Customer location');
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

    if ($customer_blurb = $row->getSourceProperty('field_customer_blurb')) {
      foreach($customer_blurb as $item) {
        if (!empty($item['value'])) {
          $value = explode("\r\n", $item['value']);
          $row->setSourceProperty('field_quote', trim(strip_tags($value[0])));
          $row->setSourceProperty('field_quote_source', trim(strip_tags($value[1])));
          $row->setSourceProperty('field_quote_source_position', trim(strip_tags($value[2])));
        }
      }
    }
    drush_log(\GuzzleHttp\json_encode($row));
    if ($customer_address_lid = $row->getSourceProperty('field_customer_location_lid')) {
      $address_data = $this->select('location', 'l')
        ->fields('l', array('name', 'street', 'additional', 'city', 'province', 'postal_code', 'country'))
        ->distinct()
        ->execute()
        ->fetchCol();
      drush_log('address-data = ', 'status');
      drush_log(\GuzzleHttp\json_encode($address_data), 'status');
      if (!empty($address_data)) {
        $row->setSourceProperty('locality', $address_data['name']);
        $row->setSourceProperty('postal_code', $address_data['postal_code']);
        $row->setSourceProperty('address_line1', $address_data['street']);
        $row->setSourceProperty('address_line2', $address_data['additional']);
        $row->setSourceProperty('country_code', $address_data['country']);
        $row->setSourceProperty('administrative_area', $address_data['province']);
      }
    }
    return TRUE;
  }

  public function object2array($object) {
    return @json_decode(@json_encode($object),1);
  }
}
