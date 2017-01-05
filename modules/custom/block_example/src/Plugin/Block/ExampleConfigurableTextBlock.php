<?php

namespace Drupal\block_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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
  public function build() {
    $build = [];
    $build['example_configurable_text_block']['#markup'] = 'Implement ExampleConfigurableTextBlock.';

    return $build;
  }

}
