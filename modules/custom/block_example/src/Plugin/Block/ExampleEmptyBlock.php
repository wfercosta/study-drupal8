<?php

namespace Drupal\block_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ExampleEmptyBlock' block.
 *
 * @Block(
 *  id = "example_empty_block",
 *  admin_label = @Translation("Example empty block"),
 * )
 */
class ExampleEmptyBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return array();
  }

}
