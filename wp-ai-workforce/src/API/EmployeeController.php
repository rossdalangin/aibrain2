<?php
declare(strict_types=1);

namespace NexusAI\Workforce\API;

use WP_REST_Request;
use WP_REST_Response;
use NexusAI\Workforce\Repositories\EmployeeRepository;
use NexusAI\Workforce\Utils\AuditLogger;

/**
 * Controller for AI Employee REST endpoints.
 */
class EmployeeController {

	/**
	 * @var EmployeeRepository
	 */
	private $repository;

	public function __construct() {
		$this->repository = new EmployeeRepository();
	}

	public function get_items( WP_REST_Request $request ): WP_REST_Response {
		$items = $this->repository->get_all();
		return new WP_REST_Response( $items, 200 );
	}

	public function get_item( WP_REST_Request $request ): WP_REST_Response {
		$id = (int) $request['id'];
		$item = $this->repository->get_by_id( $id );

		if ( ! $item ) {
			return new WP_REST_Response( [ 'message' => 'Employee not found' ], 404 );
		}

		return new WP_REST_Response( $item, 200 );
	}

	public function create_item( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_params();

		// Sanitize and prepare data
		$data = [
			'name'             => sanitize_text_field( $params['name'] ?? '' ),
			'position'         => sanitize_text_field( $params['position'] ?? '' ),
			'department_id'    => absint( $params['department_id'] ?? 0 ),
			'role_description' => wp_kses_post( $params['role_description'] ?? '' ),
			'skills'           => wp_kses_post( $params['skills'] ?? '' ),
			'kpis'             => wp_kses_post( $params['kpis'] ?? '' ),
			'prompt_template'  => wp_kses_post( $params['prompt_template'] ?? '' ),
			'thinking_process' => wp_kses_post( $params['thinking_process'] ?? '' ),
			'output_format'    => wp_kses_post( $params['output_format'] ?? '' ),
			'negative_prompts' => wp_kses_post( $params['negative_prompts'] ?? '' ),
			'examples'         => wp_kses_post( $params['examples'] ?? '' ),
			'model_settings'   => wp_json_encode( $params['model_settings'] ?? [] ),
		];

		$id = $this->repository->create( $data );

		( new AuditLogger() )->log( 'employee_hired', "Deployed new AI agent: {$data['name']} as {$data['position']}", $id );

		return new WP_REST_Response( [ 'id' => $id ], 201 );
	}

	public function update_item( WP_REST_Request $request ): WP_REST_Response {
		$id = (int) $request['id'];
		$params = $request->get_params();

		$data = [];
		if ( isset( $params['name'] ) ) $data['name'] = sanitize_text_field( $params['name'] );
		if ( isset( $params['position'] ) ) $data['position'] = sanitize_text_field( $params['position'] );

		$success = $this->repository->update( $id, $data );
		return new WP_REST_Response( [ 'success' => $success ], 200 );
	}

	public function delete_item( WP_REST_Request $request ): WP_REST_Response {
		$id = (int) $request['id'];
		$success = $this->repository->delete( $id );
		return new WP_REST_Response( [ 'success' => $success ], 200 );
	}
}
