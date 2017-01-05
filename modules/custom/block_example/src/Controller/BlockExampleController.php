<?php
/**
 * Created by PhpStorm.
 * User: wfer
 * Date: 05/01/17
 * Time: 10:38
 */

namespace Drupal\block_example\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;

class BlockExampleController extends ControllerBase {


  /**
   *
   * Controller method responsible to render the information about this example
   * and a link to the block administration page.
   *
   * @return array
   *  Returns a Drupal' render array.
   */
  public function description() {
    $block_admin_link = Link::createFromRoute($this->t('The block admin page'), 'block.admin_display')->toString();

    $message = 'The Block Example provides three sample blocks which demonstrate ';
    $message .= 'the various block APIs. To experiment with the blocks, enable and configure ';
    $message .= 'them on @block_admin_link.';

    $build = array(
      '#markup' => $this->t($message, array(
        '@block_admin_link' => $block_admin_link
      )),
    );

    return $build;
  }

}