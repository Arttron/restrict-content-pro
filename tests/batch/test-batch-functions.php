<?php
namespace RCP\Utils;

class BatchFunctions extends \WP_UnitTestCase {

	protected $config;

	protected $invalidConfig;

	protected $job;

	public function setUp() {

		$this->config = array(
			'name' => 'Test Job',
			'description' => 'Test Job Description',
			'callback' => '\RCP\Utils\batch_callback_test'
		);

		$this->invalidConfig = array( 'moo' => 'cow' );

		parent::setUp();
	}

	public function tearDown() {
		$this->config = array();
		$this->invalidConfig = array();
	}

	/** @covers \RCP\Utils\add_batch_job() */
	public function test_add_batch_job_returns_true() {
		$this->assertTrue( add_batch_job( $this->config ) );
	}

	/** @covers \RCP\Utils\add_batch_job() */
	public function test_add_batch_job_invalid_config_returns_WP_Error() {
		$this->assertInstanceOf( 'WP_Error', add_batch_job( $this->invalidConfig ) );
	}

	/** @covers \RCP\Utils\delete_batch_job() */
	public function test_delete_batch_job_returns_true() {
		add_batch_job( $this->config );
		$this->assertTrue( delete_batch_job( $this->config['name'] ) );
	}

	/** @covers \RCP\Utils\delete_batch_job() */
	public function test_delete_batch_job_returns_false_with_invalid_job_name() {
		$this->assertFalse( delete_batch_job( 'RCP this job name is fake' ) );
	}

	/** @covers \RCP\Utils\Job() */
	public function test_job_throws_InvalidArgumentException_with_invalid_job_name() {

		if( PHP_VERSION_ID < 70000 ) {
			$this->setExpectedException( '\InvalidArgumentException' );
		} else {
			$this->expectException( '\InvalidArgumentException' );
		}

		new Job( false );
	}

	/** @covers \RCP\Utils\Job::name() */
	public function test_job_name() {
		add_batch_job( $this->config );

		$this->job = new Job( $this->config['name'] );

		$this->assertSame( $this->config['name'], $this->job->name() );
	}

	/** @covers \RCP\Utils\Job::description() */
	public function test_job_description() {
		add_batch_job( $this->config );

		$this->job = new Job( $this->config['name'] );

		$this->assertSame( $this->config['description'], $this->job->description() );
	}

	/** @covers \RCP\Utils\Job::callback() */
	public function test_job_callback() {
		add_batch_job( $this->config );

		$this->job = new Job( $this->config['name'] );

		$this->assertSame( $this->config['callback'], $this->job->callback() );
	}
}

function batch_callback_test() {
	return true;
}

