<?php

/**
 * @file
 * Provide views data for file_download_counter.module.
 */

/**
 * Implements hook_views_data().
 */
function file_download_counter_views_data() {
  $data['file_download_counter']['table']['group']  = t('File Downloads');

  $data['file_download_counter']['table']['join'] = array(
    'file_managed' => array(
      'left_field' => 'fid',
      'field' => 'fid',
    ),
  );

  $data['file_download_counter']['totalcount'] = array(
    'title' => t('Total downloads'),
    'help' => t('The total number of times the file has been downloaded.'),
    'field' => array(
      'id' => 'file_download_counter_numeric',
      'click sortable' => TRUE,
     ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );

  $data['file_download_counter']['daycount'] = array(
    'title' => t('Downloads today'),
    'help' => t('The total number of times the file has been downloaded today.'),
    'field' => array(
      'id' => 'file_download_counter_numeric',
      'click sortable' => TRUE,
     ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );

  $data['file_download_counter']['timestamp'] = array(
    'title' => t('Most recent download'),
    'help' => t('The most recent time the file has been downloaded.'),
    'field' => array(
      'id' => 'file_download_counter_date',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'id' => 'date',
    ),
    'argument' => array(
      'id' => 'date',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );

  return $data;
}
