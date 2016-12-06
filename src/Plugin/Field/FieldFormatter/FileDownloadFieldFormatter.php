<?php

namespace Drupal\file_download\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;


/**
 *
 * @FieldFormatter(
 *   id = "file_download_formatter",
 *   label = @Translation("File Download"),
 *   field_types = {
 *     "file",
 *     "image"
 *   }
 * )
 */
class FileDownloadFieldFormatter extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
//  public static function defaultSettings() {
//    $options = parent::defaultSettings();
//
//    $options['render_view'] = TRUE;
//    return $options;
//  }

  /**
   * {@inheritdoc}
   */
//  public function settingsForm(array $form, FormStateInterface $form_state) {
//    $form = parent::settingsForm($form, $form_state);
//    // We may decide on alternatives to rendering the view so get settings established
//    $form['render_view'] = [
//      '#type' => 'checkbox',
//      '#title' => $this->t('Render View'),
//      '#default_value' => $this->getSetting('render_view'),
//    ];
//
//    return $form;
//  }

  /**
   * {@inheritdoc}
   */
//  public function settingsSummary() {
//    $summary = array();
//    $settings = $this->getSettings();
//
//    $summary[] = t('Render View: @view', array('@view' => $settings['render_view'] ? 'TRUE' : 'FALSE'));
//    return $summary;
//  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $item = $file->_referringItem;

      $elements[$delta] = array(
        '#theme' => 'download_file_link',
        '#file' => $file,
        '#description' => $item->description,
        '#cache' => array(
          'tags' => $file->getCacheTags(),
        ),
      );

      // Pass field item attributes to the theme function.
      if (isset($item->_attributes)) {
        $elements[$delta] += array('#attributes' => array());
        $elements[$delta]['#attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
//  public static function isApplicable(FieldDefinitionInterface $field_definition) {
//    return $field_definition->getTargetEntityTypeId() === 'user' && $field_definition->getName() === 'name';
//  }

}
