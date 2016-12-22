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
  public static function defaultSettings() {
    $options = parent::defaultSettings();

    $options['link_title'] = 'title';
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    //
    $form['link_title'] = [
      '#type' => 'radios',
      '#options' => array('file' => 'Title of file', 'empty' => 'Nothing'),
      '#title' => $this->t('Title Display'),
      '#description' => $this->t('Control what is displayed in the title of the link'),
      '#default_value' => $this->getSetting('link_title'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $settings = $this->getSettings();

    $summary[] = t('Title Display: @view', array('@view' => $settings['link_title']));
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getSettings();

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $item = $file->_referringItem;

      switch ($settings['link_title']) {
        case 'empty':
          $title = '&nbsp;';
          break;

        default:
          // If title has no value then filename is subsituted
          // See template_preprocess_download_file_link()
          $title = NULL;
      }

      $uri = $file->getFileUri();

//      if (file_exists($uri)) {
        $elements[$delta] = array(
          '#theme' => 'download_file_link',
          '#file' => $file,
          '#description' => $title,
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
//      }
//      else {
//
//        $elements[$delta] = array(
//          '#cache' => array(
//            'tags' => $file->getCacheTags(),
//          ),
//        );
//      }

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
