<?php
/**
 * Created by PhpStorm.
 * User: wfer
 * Date: 05/01/17
 * Time: 08:47
 */

namespace Drupal\pager_example\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for pager_example.page route.
 *
 * This is an example describing how a moudule can implment a pager in order to
 * reduce the number of output rows to the screen and allow a user to scroll
 * through multiple screens output.
 *
 * @package Drupal\pager_example\Controller
 */
class PagerExamplePage extends ControllerBase {


  /**
   * Entity storage for node entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * PagerExamplePage constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $nodeStorage
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   */
  public function __construct(\Drupal\Core\Entity\EntityStorageInterface $nodeStorage, \Drupal\Core\Session\AccountInterface $currentUser) {
    $this->nodeStorage = $nodeStorage;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    $controller = new static(
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('current_user')
    );

    $controller->setStringTranslation($container->get('string_translation'));

    return $controller;

  }


  /**
   *
   * Content callback for the pager_example.page route.
   *
   * @return array
   *  Returns a Drupal's render array
   */
  public function getContent() {

    $build = [
      'description' => [
        '#theme' => 'pager_example_description',
        '#description' => 'foo',
        '#attributes' => [],
      ]
    ];


    $query = $this->nodeStorage->getQuery()
      ->addTag('node_access')
      ->count();


    if ($this->currentUser->hasPermission('bypass node access')) {
      $query->condition('status', 1);
    }

    $count_nodes = $query->execute();

    if ($count_nodes == 0) {

      if ($this->currentUser->hasPermission('create page content')) {

        $text = 'There are no nodes to display.';
        $text .= ' Please <a href=":url">create a node</a>';

        $build['no-nodes'] = array(
          '#markup' => $this->t($text,
            array(
              ':url' => Url::fromRoute('node.add', array('node_type' => 'page'))->toString(),
            )
          ),
        );
      } else {
        $build['no-nodes'] = array(
          '#markup' => $this->t('There are no nodes to display'),
        );
      }

      /**
       * Ensures tthat drupal clears the cache when nodes has published, added,
       * deleted or unpublished; and when a user permission changes.
       */
      $build['#cache']['tags'][] = 'node_list';
      $build['#cache']['contexts'][] = 'user.permissions';

      return $build;

    }

    $query = $this->nodeStorage->getQuery()
      ->sort('nid', 'DESC')
      ->addTag('node_access')
      ->pager(2);

    if ($this->currentUser->hasPermission('bypass node access')) {
      $query->condition('status', 1);
    }

    $entity_ids = $query->execute();

    $nodes = $this->nodeStorage->loadMultiple($entity_ids);

//    kint($nodes);

    $rows = [];

    foreach ($nodes as  $node) {
      $rows[] = array(
        'nid' => $node->access('view') ? $node->id() : t('XXXXXX'),
        'title' => $node->access('view') ? $node->getTitle() : t('Redacted'),
      );
    }

//    kint($rows);

    $build['pager_example'] = array(
      '#rows' => $rows,
      '#header' => array(t('NID'), t('Title')),
      '#type' => 'table',
      '#empty' => t('No content available'),
    );

    $build['pager'] = array(
      '#type' => 'pager',
      '#quantity' => 5,
      '#weight' => 10,
    );

    $build['#cache']['tags'][] = 'node_list';
    $build['#cache']['contexts'][] = 'user.permissions';

    return $build;

  }


}