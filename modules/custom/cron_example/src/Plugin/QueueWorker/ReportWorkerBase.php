<?php
/**
 * Created by PhpStorm.
 * User: wfer
 * Date: 09/01/17
 * Time: 13:04
 */

namespace Drupal\cron_example\Plugin\QueueWorker;


use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;



/**
 * Provides basic funcionality to the workers
 * @package Drupal\cron_example\Plugin\QueueWorker
 */
abstract class ReportWorkerBase extends QueueWorkerBase implements  ConfigurablePluginInterface
{

    use StringTranslationTrait;


    /**
     * The state.
     *
     * @var \Drupal\Core\State\StateInterface
     */
    protected $state;

    /**
     * The logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * ReportWorkerBase constructor.
     *
     * @param array $configuration
     *   The configuration of the instance.
     * @param string $plugin_id
     *   The plugin id.
     * @param mixed $plugin_definition
     *   The plugin definition.
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->state = \Drupal::getContainer()->get('state');
        $this->logger = \Drupal::getContainer()->get('logger.factory');
    }

    /**
     * Simple reporter log and display information about the queue.
     *
     * @param int $worker
     *   Worker number.
     * @param object $item
     *   The $item which was stored in the cron queue.
     */
    protected function reportWork($worker, $item) {
        if ($this->state->get('cron_example_show_status_message')) {
            drupal_set_message(
                $this->t('Queue @worker worker processed item with sequence @sequence created at @time', [
                    '@worker' => $worker,
                    '@sequence' => $item->sequence,
                    '@time' => date_iso8601($item->created),
                ])
            );
        }
        $this->logger->get('cron_example')->info('Queue @worker worker processed item with sequence @sequence created at @time', [
            '@worker' => $worker,
            '@sequence' => $item->sequence,
            '@time' => date_iso8601($item->created),
        ]);
    }

}