<?php

namespace Drupal\block_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ExampleUppercaseBlock' block.
 *
 * @Block(
 *  id = "example_uppercase_block",
 *  admin_label = @Translation("Example uppercase block"),
 * )
 */
class ExampleUppercaseBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $message  = "This block's title will be changed to uppercase. Any other ";
    $message .= "block with 'uppercase' in the subject or title will also be ";
    $message .= "altered. If you change this block's title through the UI to ";
    $message .= "omit the word 'uppercase', it will still be altered to ";
    $message .= "uppercase as the subject key has not been changed.";

    return array(
      '#type' => 'markup',
      '#markup' => $this->t($message),
    );
  }

}
