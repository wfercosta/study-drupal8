<?php
/**
 * Created by PhpStorm.
 * User: wfer
 * Date: 09/01/17
 * Time: 13:09
 */

namespace Drupal\cron_example\Plugin\QueueWorker;

/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "cron_example_queue_1",
 *   title = @Translation("First worker in cron_example"),
 *   cron = {"time" = 1}
 * )
 *
 * QueueWorkers are new in Drupal 8. They define a queue, which in this case
 * is identified as cron_example_queue_1 and contain a process that operates on
 * all the data given to the queue.
 *
 * @see queue_example.module
 */
class ReportWorkerOne extends ReportWorkerBase {

    /**
     * {@inheritdoc}
     */
    public function processItem($data) {
        $this->reportWork(1, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        // TODO: Implement getConfiguration() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $configuration)
    {
        // TODO: Implement setConfiguration() method.
    }

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration()
    {
        // TODO: Implement defaultConfiguration() method.
    }

    /**
     * {@inheritdoc}
     */
    public function calculateDependencies()
    {
        // TODO: Implement calculateDependencies() method.
    }


}