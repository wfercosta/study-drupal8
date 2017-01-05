<?php
/**
 * Created by PhpStorm.
 * User: wfer
 * Date: 04/01/17
 * Time: 22:20
 */

namespace Drupal\page_example\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class PageExampleController extends ControllerBase {

  /**
   * Construc a page with descriptive content.
   *
   * @return array
   *  Returna a Drupal render array.
   */
  public function description() {
    $page_example_simple_link = Link::createFromRoute($this->t('Simple page'), 'page_example_simple')->toString();

    $arguments_url = Url::fromRoute('page_example_arguments', array('first' => '23', 'second' => '56'));
    $page_example_arguments_link = Link::fromTextAndUrl($this->t('Arguments page'), $arguments_url)->toString();

    $markupText =   '<p>The Page example module provides two pages, "simple" and "arguments".</p>'
                  . '<p>The @simple_link just returns a renderable array for display.</p>'
                  . '<p>The @arguments_link takes two arguments and displays them, as in @arguments_url</p>';

    $build = array(
      "#markup" => $this->t($markupText,
        array(
          '@simple_link' => $page_example_simple_link,
          '@arguments_link' => $page_example_arguments_link,
          '@arguments_url' => $arguments_url->toString(),
        )
      ),
    );

    return $build;
  }


  /**
   * Construct a simple page to render.
   *
   * @return array
   *  Returns a Drupal render array
   */
  public function simple() {
    return array(
      '#markup' => '<p>' . $this->t('Simple page: The quick brown fox jumps over the lazy dog.') . '</p>',
    );
  }

  public function arguments($first, $second) {
    if (!is_numeric($first) || !is_numeric($second)) {
      throw new AccessDeniedException();
    }


    $list[] = $this->t("First number was @number.", array('@number' => $first));
    $list[] = $this->t("Second number was @number.", array('@number' => $second));
    $list[] = $this->t('The total was @number.', array('@number' => $first + $second));

    $render_array['page_example_arguments'] = array(
      '#theme' => 'item_list',
      '#items' => $list,
      '#title' => $this->t('Argument Information'),
    );

    return $render_array;


  }

}