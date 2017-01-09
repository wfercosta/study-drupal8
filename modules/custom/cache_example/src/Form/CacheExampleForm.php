<?php
/**
 * Created by PhpStorm.
 * User: wfer
 * Date: 06/01/17
 * Time: 08:13
 */

namespace Drupal\cache_example\Form;


use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CacheExampleForm extends FormBase {

  const CACHE_EXAMPLE_FILE_COUNT = 'cache_example_files_count';
  const CACHE_EXAMPLE_EXPIRING_ITEM = 'cache_example_expiring_item';


  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The cache.default cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * Dependecy injection through the constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   */
  public function __construct(RequestStack $request_stack
      , TranslationInterface $translation
      , AccountProxyInterface $currentUser
      , CacheBackendInterface $cacheBackend) {

    $this->setRequestStack($request_stack);
    $this->setStringTranslation($translation);
    $this->currentUser = $currentUser;
    $this->cacheBackend = $cacheBackend;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('request_stack'),
      $container->get('string_translation'),
      $container->get('current_user'),
      $container->get('cache.default')
    );

  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cron_cache';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $start_time = microtime(TRUE);

    if ($cache = $this->cacheBackend->get(CacheExampleForm::CACHE_EXAMPLE_FILE_COUNT)) {
      $files_count = $cache->data;
    } else {
      $files_count = count(file_scan_directory('core', '/.php/'));
      $this->cacheBackend->set(CacheExampleForm::CACHE_EXAMPLE_FILE_COUNT, $files_count, CacheBackendInterface::CACHE_PERMANENT);
    }

    $end_time = microtime(TRUE);
    $duration = $start_time - $end_time;

    $intro_message = '<p>' . $this->t("This example will search Drupal's core folder and display a count of the PHP files in it.") . ' ';
    $intro_message .= $this->t('This can take a while, since there are a lot of files to be searched.') . ' ';
    $intro_message .= $this->t('We will search filesystem just once and save output to the cache. We will use cached data for later requests.') . '</p>';
    $intro_message .= '<p>'
      . $this->t(
        '<a href="@url">Reload this page</a> to see cache in action.',
        array('@url' => $this->getRequest()->getRequestUri())
      )
      . ' ';

    $intro_message .= $this->t('You can use the button below to remove cached data.') . '</p>';

    $form['file_search'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('File search caching'),
    );


    $form['file_search']['introduction'] = array(
      '#markup' => $intro_message,
    );


    $color = empty($cache) ? 'red' :  'green';
    $retrieval = empty($cache) ? $this->t('calculated by transversing the file system')
        : $this->t('retrieved from cache');

    $from['file_search']['statistics'] = array(
      '#type' => 'item',
      '#markup' => $this->t('%count files exist in this Drupal installation; @retrieval in @time ms. <br/>(Source: <span style="color:@color;">@source</span>)', array(
          '%count' => $files_count,
          '@retrieval' => $retrieval,
          '@time' => number_format($duration * 1000, 2),
          '@color' => $color,
          '@source' => empty($cache) ? $this->t('actual file search') : $this->t('cached'),
        )
      ),
    );

    $form['file_search']['remove_file_count'] = array(
      '#type' => 'submit',
      '#submit' => array(array($this, 'expireFiles')),
      '#value' => $this->t('Explicitly remove cached file count'),
    );


    $form['expiration_demo'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Cache expiration settings'),
    );

    $form['expiration_demo']['explanation'] = array(
      '#markup' => $this->t('A cache item can be set as CACHE_PERMANENT, meaning that it will only be removed when explicitly cleared, or it can have an expiration time (a Unix timestamp).'),
    );

    $item = $this->cacheBackend->get(CacheExampleForm::CACHE_EXAMPLE_EXPIRING_ITEM, TRUE);

    if ($item === FALSE) {
      $item_status = $this->t('Cache item does not exists');
    }
    else {
      $item_status = $item->valid ? $this->t('Cache item exists and is set to expire at %time'
          , array('%time' => $item->data)) : $this->t('Cache_item is invalid');
    }

    $form['expiration_demo']['current_status'] = array(
      '#type' => 'item',
      '#title' => $this->t('Current status of cache item "@name"', array('@name' => CacheExampleForm::CACHE_EXAMPLE_EXPIRING_ITEM)),
      '#markup' => $item_status,
    );

    $form['expiration_demo']['expiration'] = array(
      '#type' => 'select',
      '#title' => $this->t('Time before cache expiration'),
      '#options' => array(
        'never_remove' => $this->t('CACHE_PERMANENT'),
        -10 => $this->t('Immediate expiration'),
        10 => $this->t('10 seconds from form submission'),
        60 => $this->t('1 minute from form submission'),
        300 => $this->t('5 minutes from form submission'),
      ),
      '#default_value' => -10,
      '#description' => $this->t('Any cache item can be set to only expire when explicitly cleared, or to expire at a given time.'),
    );

    $form['expiration_demo']['create_cache_item'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Create a cache item with this expiration'),
      '#submit' => array(array($this, 'createExpiringItem')),
    );

    $form['cache_clearing'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Expire and remove options'),
      '#description' => $this->t("We have APIs to expire cached items and also to just remove them. Unfortunately, they're all the same API, cache_clear_all"),
    );

    $form['cache_clearing']['cache_clear_type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Type of cache clearing to do'),
      '#options' => array(
        'expire' => $this->t('Remove items from the "cache" bin that have expired'),
        'remove_all' => $this->t('Remove all items from the "cache" bin regardless of expiration'),
        'remove_tag' => $this->t('Remove all items in the "cache" bin with the tag "cache_example" set to 1'),
      ),
      '#default_value' => 'expire',
    );

    $form['cache_clearing']['clear_expired'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Clear or expire cache'),
      '#submit' => array(array($this, 'cacheClearing')),
      '#access' => $this->currentUser->hasPermission('administer site configuration'),
    );

    return $form;

  }


  /**
   * Submit hanlder that explicity clears cache_exmaple_files_count from cache.
   *
   * @param $form
   *  The form element.
   *
   * @param $form_State
   *  The form state element.
   */
  public function expireFiles($form, &$form_State) {
    $this->cacheBackend->delete(CacheExampleForm::CACHE_EXAMPLE_FILE_COUNT);

    drupal_set_message(
      $this->t('Cache data key "@name" was cleared.'
        , array('@name' => CacheExampleForm::CACHE_EXAMPLE_FILE_COUNT))
      , 'status');
  }

    /**
     * Submit handler to create a new cache item with specified expiration.
     *
     * @param $form
     *  Drupal form array.
     *
     * @param $form_state
     *  Drupal form state array.
     */
  public function createExpiringItem($form, &$form_state) {

    $tags = array(
      'cache_example:1',
    );

    $interval = $form_state->getValue('expiration');

    if ($interval == 'never_remove') {
      $expiration = CacheBackendInterface::CACHE_PERMANENT;
      $expiration_friendly = $this->t('Never expires');

    } else {
        $expiration = time() + $interval;
        $expiration_friendly = format_date($expiration);
    }

    $this->cacheBackend->set('cache_example_expiring_item', $expiration_friendly, $expiration, $tags);

    drupal_set_message(
        $this->t('cache_example_expiring_item was set to expire at %time'
            , array('%time' => $expiration_friendly)));

  }

    /**
     * Submit handler to demonstrate the various uses of cache_clear_all().
     *
     * @param $form
     *  Drupal form array.
     *
     * @param $form_state
     *  Drupal form state array.
     */
  public function cacheClearing($form, &$form_state) {

      switch ($form_state->getValue('cache_clear_type')) {
          case 'expire':
              $this->cacheBackend->garbageCollection();
              drupal_set_message(
                  $this->t('\Drupal::cache()->garbageCollection() was called, removing any expired cache items.'));
              break;

          case 'remove_all':
              $this->cacheBackend->deleteAll();
              drupal_set_message($this->t('ALL entries in the "cache" bin were removed with \Drupal::cache()->deleteAll().'));
              break;

          case 'remove_tag':

              $tags = array(
                'cache_example:1'
              );

              Cache::invalidateTags($tags);
              drupal_set_message(
                  $this->t('Cache entries with the tag "cache_example" set to 1 in the "cache" bin were invalidated with \Drupal\Core\Cache\Cache::invalidateTags($tags).'));

              break;
      }

  }

}