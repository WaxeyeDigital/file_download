<?php

namespace Drupal\file_download\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Access\AccessResult;


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
    $options['custom_title_text'] = '';
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

    $form['custom_title_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom text'),
      '#default_value' => $this->getSetting('custom_title_text'),
      '#placeholder' => $this->t('e.g. "Download"'),
      '#description' => $this->t('Provide a custom text to display for all download links.'),
      '#states' => [
        'visible' => [
          ":input[name=\"fields[{$fieldName}][settings_edit_form][settings][link_title]\"]" => ['value' => 'custom'],
        ],
      ],
    ];

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
      'custom' => $this->t('Custom text')
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

    if ($selectedTitleDisplay === 'custom') {
      $tArgs = ['@text' => $settings['custom_title_text']];
      $summary[] = $this->t('Custom text: @text', $tArgs);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getSettings();
    $current_user = \Drupal::currentUser();

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $item = $file->_referringItem;

      switch ($settings['link_title']) {
        case 'empty':
          $title = '';
          break;

        case 'entity_title':
          $entity = $items->getEntity();
          if ($entity->get('title')->getValue() != NULL) {
            $title = $entity->get('title')->getValue()[0]['value'];
          }

        case 'custom':
          $title = $settings['custom_title_text'];
          break;

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
