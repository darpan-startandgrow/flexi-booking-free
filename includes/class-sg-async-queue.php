<?php
/**
 * Async Queue — Background job processor for FlexiBooking.
 *
 * Offloads heavy tasks (email sending, PDF generation, analytics,
 * webhook delivery) to background processing via WordPress Cron.
 *
 * Jobs are stored in a WordPress option as a serialised array and
 * processed in batches by a recurring WP-Cron event. This avoids
 * blocking the HTTP request for time-consuming operations.
 *
 * For high-traffic sites (100K+ users/day), this can be replaced
 * with a dedicated queue service (Redis Queue, RabbitMQ) by
 * filtering `sg_booking_queue_processor`.
 *
 * Usage:
 *   $queue = SG_Async_Queue::get_instance();
 *   $queue->push( 'email.send', [ 'to' => 'user@example.com', ... ] );
 *
 * @since      1.2.0
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SG_Async_Queue {

	/**
	 * Option key for the job queue.
	 *
	 * @var string
	 */
	const QUEUE_OPTION = 'sg_booking_async_queue';

	/**
	 * WP-Cron hook name for queue processing.
	 *
	 * @var string
	 */
	const CRON_HOOK = 'sg_booking_process_queue';

	/**
	 * Maximum jobs to process per batch.
	 *
	 * @var int
	 */
	const BATCH_SIZE = 20;

	/**
	 * Maximum execution time per batch (seconds).
	 *
	 * @var int
	 */
	const MAX_EXECUTION_TIME = 25;

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — sets up cron hooks.
	 */
	private function __construct() {
		add_action( self::CRON_HOOK, array( $this, 'process_batch' ) );
		add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );
	}

	/**
	 * Add a custom cron interval for queue processing (every 1 minute).
	 *
	 * @param array $schedules Existing cron schedules.
	 * @return array Modified schedules.
	 */
	public function add_cron_interval( $schedules ) {
		if ( ! isset( $schedules['sg_every_minute'] ) ) {
			$schedules['sg_every_minute'] = array(
				'interval' => 60,
				'display'  => __( 'Every Minute (SG Queue)', 'service-booking' ),
			);
		}
		return $schedules;
	}

	/**
	 * Schedule the queue processor if not already scheduled.
	 */
	public function ensure_scheduled() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'sg_every_minute', self::CRON_HOOK );
		}
	}

	/**
	 * Push a job onto the queue.
	 *
	 * @param string $event   Event/job name (e.g. 'email.send', 'pdf.generate').
	 * @param array  $payload Job data.
	 * @return bool True on success.
	 */
	public function push( $event, array $payload = array() ) {
		/**
		 * Filter whether a job should be queued or processed immediately.
		 *
		 * Return false to skip queueing and process synchronously.
		 *
		 * @since 1.2.0
		 *
		 * @param bool   $should_queue Default true.
		 * @param string $event        Job event name.
		 * @param array  $payload      Job data.
		 */
		$should_queue = apply_filters( 'sg_booking_should_queue_job', true, $event, $payload );

		if ( ! $should_queue ) {
			// Process immediately.
			$this->execute_job( $event, $payload );
			return true;
		}

		$job = array(
			'id'         => wp_generate_uuid4(),
			'event'      => $event,
			'payload'    => $payload,
			'created_at' => current_time( 'mysql' ),
			'attempts'   => 0,
		);

		$queue   = $this->get_queue();
		$queue[] = $job;

		update_option( self::QUEUE_OPTION, $queue, false );

		// Ensure the cron event is scheduled.
		$this->ensure_scheduled();

		/**
		 * Fires when a job is added to the queue.
		 *
		 * @since 1.2.0
		 *
		 * @param array  $job   The job data (id, event, payload, created_at).
		 * @param string $event The event name.
		 */
		do_action( 'sg_booking_job_queued', $job, $event );

		return true;
	}

	/**
	 * Process a batch of queued jobs.
	 *
	 * Called by WP-Cron. Processes up to BATCH_SIZE jobs within
	 * MAX_EXECUTION_TIME seconds.
	 */
	public function process_batch() {
		$queue = $this->get_queue();

		if ( empty( $queue ) ) {
			return;
		}

		$start_time = time();
		$processed  = 0;
		$remaining  = array();
		$failed     = array();

		foreach ( $queue as $job ) {
			// Respect time and batch limits.
			if ( $processed >= self::BATCH_SIZE || ( time() - $start_time ) >= self::MAX_EXECUTION_TIME ) {
				$remaining[] = $job;
				continue;
			}

			$job['attempts'] = ( $job['attempts'] ?? 0 ) + 1;

			try {
				$this->execute_job( $job['event'], $job['payload'] );
				$processed++;

				/**
				 * Fires after a queued job is processed successfully.
				 *
				 * @since 1.2.0
				 *
				 * @param array $job The processed job data.
				 */
				do_action( 'sg_booking_job_processed', $job );
			} catch ( \Exception $e ) {
				// Retry up to 3 times.
				if ( $job['attempts'] < 3 ) {
					$remaining[] = $job;
				} else {
					$failed[] = $job;

					/**
					 * Fires when a queued job fails after all retries.
					 *
					 * @since 1.2.0
					 *
					 * @param array      $job       The failed job data.
					 * @param \Exception $exception The exception that caused the failure.
					 */
					do_action( 'sg_booking_job_failed', $job, $e );
				}
			}
		}

		update_option( self::QUEUE_OPTION, $remaining, false );

		/**
		 * Fires after a queue batch is processed.
		 *
		 * @since 1.2.0
		 *
		 * @param int   $processed Number of successfully processed jobs.
		 * @param array $remaining Jobs remaining in the queue.
		 * @param array $failed    Jobs that failed after all retries.
		 */
		do_action( 'sg_booking_queue_batch_complete', $processed, $remaining, $failed );
	}

	/**
	 * Execute a single job.
	 *
	 * Routes the job to the Event Dispatcher if available, otherwise
	 * fires a WordPress action.
	 *
	 * @param string $event   Job event name.
	 * @param array  $payload Job data.
	 */
	private function execute_job( $event, array $payload ) {
		/**
		 * Filter the job processor for custom queue backends.
		 *
		 * Return a callable to override the default processing logic.
		 *
		 * @since 1.2.0
		 *
		 * @param callable|null $processor Custom job processor.
		 * @param string        $event     Job event name.
		 * @param array         $payload   Job data.
		 */
		$custom_processor = apply_filters( 'sg_booking_queue_processor', null, $event, $payload );

		if ( is_callable( $custom_processor ) ) {
			call_user_func( $custom_processor, $event, $payload );
			return;
		}

		// Route to the Event Dispatcher for synchronous firing.
		if ( class_exists( 'SG_Event_Dispatcher' ) ) {
			SG_Event_Dispatcher::process_queued( $event, $payload );
		} else {
			// Fallback: fire a WordPress action.
			$hook_name = 'sg_booking_queue_' . str_replace( '.', '_', $event );
			do_action( $hook_name, $payload );
		}
	}

	/**
	 * Get the current queue.
	 *
	 * @return array
	 */
	private function get_queue() {
		$queue = get_option( self::QUEUE_OPTION, array() );
		return is_array( $queue ) ? $queue : array();
	}

	/**
	 * Get the number of pending jobs.
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->get_queue() );
	}

	/**
	 * Clear all pending jobs.
	 *
	 * @return void
	 */
	public function clear() {
		update_option( self::QUEUE_OPTION, array(), false );
	}

	/**
	 * Unschedule the cron event.
	 *
	 * Called during plugin deactivation.
	 */
	public static function unschedule() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}
}
