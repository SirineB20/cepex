<?php

namespace Drupal\taxonomy_term_depth\QueueManager;

use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Core\Utility\Error;

/**
 * The class manager handles the management of taxonomy term depth updates.
 */
class Manager {

  const QUEUE_ID = 'taxonomy_term_depth_update_depth';

  const BATCH_COUNT = 20;

  /**
   * The vocabulary ID.
   *
   * @var mixed
   */
  protected $vid = NULL;

  /**
   * The queue interface.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $queue = NULL;

  /**
   * Sets the vocabulary ID.
   */
  public function setVid($vid = NULL) {
    $this->vid = $vid;
    return $this;
  }

  /**
   * Constructs a new Manager instance.
   */
  public function __construct() {
    $this->setVid();

    /** @var \Drupal\Core\Queue\QueueFactory $queue_factory */
    $queue_factory = \Drupal::service('queue');
    /** @var \Drupal\Core\Queue\QueueInterface $queue */
    $this->queue = $queue_factory->get(static::QUEUE_ID);
  }

  /**
   * Clears the term depth queue.
   */
  public function clear() {
    $this->queue->deleteQueue();
  }

  /**
   * Retrieves the size of the term depth queue.
   */
  public function queueSize() {
    return $this->queue->numberOfItems();
  }

  /**
   * Adds terms to the depth queue in batches.
   */
  public function queueBatch($queue_all = TRUE) {
    $query = $this->getTermsQuery();

    if (!$queue_all) {
      $query->isNull('td.depth_level');
    }
    else {
      // Delete queue if have one.
      $this->clear();
    }

    $ids = [];
    foreach ($query->execute() as $row) {
      if (count($ids) >= static::BATCH_COUNT) {
        $this->queueByIds($ids);
        $ids = [];
      }

      $ids[] = $row->tid;
    }

    // Queue remaining items.
    $this->queueByIds($ids);

    return TRUE;
  }

  /**
   * Queues batch missing terms.
   */
  public function queueBatchMissing() {
    return $this->queueBatch(FALSE);
  }

  /**
   * Queues by term IDs.
   */
  public function queueByIds($ids) {
    if (empty($ids)) {
      return FALSE;
    }

    $this->clearDepths($ids);
    foreach ($ids as $tid) {
      $this->queue->createItem([
        'tid' => $tid,
      ]);
    }

    $this->processQueue();

    return TRUE;
  }

  /**
   * Clears taxonomy term depths.
   */
  public function clearDepths($ids = NULL) {
    $query = \Drupal::database()->update('taxonomy_term_field_data');
    $query->fields([
      'depth_level' => NULL,
    ]);

    if ($this->vid !== NULL) {
      $query->condition('vid', $this->vid);
    }

    if ($ids !== NULL && is_array($ids) && !empty($ids)) {
      $query->condition('tid', $ids, 'IN');
    }

    return $query->execute();
  }

  /**
   * Gets the taxonomy terms query by id.
   */
  protected function getTermsQuery() {
    $query = \Drupal::database()->select('taxonomy_term_field_data', 'td');
    $query->fields('td', ['tid']);

    if ($this->vid !== NULL) {
      $query->condition('td.vid', $this->vid);
    }

    return $query;
  }

  /**
   * Processes items in the queue.
   */
  public function processQueue() {
    $queue_worker = \Drupal::service('plugin.manager.queue_worker')
      ->createInstance('taxonomy_term_depth_update_depth');

    while ($item = $this->queue->claimItem()) {
      try {
        $queue_worker->processItem($item->data);
        $this->queue->deleteItem($item);
      }
      catch (SuspendQueueException $e) {
        $this->queue->releaseItem($item);
        break;
      }
      catch (\Exception $e) {
        Error::logException(\Drupal::logger('npq'), $e);
      }
    }
  }

  /**
   * Processes next item in the queue.
   */
  public function processNextItem() {
    $item = $this->queue->claimItem();
    taxonomy_term_depth_get_by_tid($item['tid'], TRUE);
  }

}
