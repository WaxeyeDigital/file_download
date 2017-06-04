<?php

namespace Drupal\file_download\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\EntityInterface;


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

    $options['link_title'] = 'file';
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['link_title'] = [
      '#type' => 'radios',
      '#options' => $this->getDisplayOptions(),
      '#title' => $this->t('Title Display'),
      '#description' => $this->t('Control what is displayed in the title of the link'),
      '#default_value' => $this->getSetting('link_title'),
    ];

    $fieldName = $this->fieldDefinition->getName();

    return $form;
  }

  /**
   * @return array
   */
  private function getDisplayOptions() {
    return [
      'file' => $this->t('Title of file'),
      'entity_title' => 'Title of parent entity',
      'empty' => $this->t('Nothing'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();
    $displayOptions = $this->getDisplayOptions();

    $selectedTitleDisplay = $settings['link_title'];
    $tArgs = ['@view' => $displayOptions[$selectedTitleDisplay]];
    $summary[] = $this->t('Title Display: @view', $tArgs);

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

        // This is useful for instance if you are using an icon
        case 'empty':
          $title = '';
          break;

        case 'entity_title':
          $entity = $items->getEntity();
          if ($entity->get('title')->getValue() != NULL) {
            $title = $entity->get('title')->getValue()[0]['value'];
          }
          break;

        // This equates to choosing filename
        default:
          // If title has no value then filename is substituted
          // See template_preprocess_download_file_link()
          $title = NULL;
      }

        $elements[$delta] = array(
          '#theme' => 'download_file_link',
          '#file' => $file,
          '#title' => $title,
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

}
