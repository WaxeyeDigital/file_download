<?php

namespace Drupal\file_download\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the "file_download_uri_formatter" formatter.
 *
 * @FieldFormatter(
 *   id = "file_download_uri_formatter",
 *   label = @Translation("File Download URI"),
 *   field_types = {
 *     "file",
 *     "image"
 *   }
 * )
 */
class FileDownloadUriFieldFormatter extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $options = parent::defaultSettings();
    $options['absolute_url'] = FALSE;
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['absolute_url'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Absolute URL'),
      '#default_value' => $this->getSetting('absolute_url'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();

    if ($settings['absolute_url']) {
      $summary[] = $this->t('Absolute URL');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getSettings();

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $uri = $file->getFileUri();
      $parts = explode('://', $uri);
      $url = Url::fromRoute('file_download.link', ['scheme' => $parts[0], 'fid' => $file->id()]);
      $url->setAbsolute($settings['absolute_url']);

      $elements[$delta] = ['#markup' => $url->toString()];
    }

    return $elements;
  }

}
