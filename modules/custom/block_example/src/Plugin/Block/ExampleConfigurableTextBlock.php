<?php

namespace Drupal\block_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'ExampleConfigurableTextBlock' block.
 *
 * @Block(
 *  id = "example_configurable_text_block",
 *  admin_label = @Translation("Example configurable text block"),
 * )
 */
class ExampleConfigurableTextBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['block_example_string_text'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Block contents'),
      '#description' => $this->t('This text will appear in the example block.'),
      '#default_value' => $this->configuration['block_example_string'],
    );
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['block_example_string']
      = $form_state->getValue('block_example_string_text');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'block_example_string' => $this->t('A default value. This block was created at %time', array('%time' => date('c')))
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['example_configurable_text_block']['#markup'] = 'Implement ExampleConfigurableTextBlock.';
    return $build;
  }

}
