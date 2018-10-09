<?php

class WP_Revision_List_Tests_Settings extends WP_UnitTestCase {

	public function test_settings_input() {

		$settings = new WP_Revision_List_Settings();

		$args = array(
			'name' => 'test-name',
			'key' => 'test-key',
			'maxlength' => 25,
			'size' => 20,
			'after' => 'This is the after text',
			'type' => 'text',
		);

		$option = [
			'test-name' => 'my value',
		];

		update_option( 'test-key', $option );

		ob_start();
		$settings->settings_input( $args );
		$html = ob_get_clean();

		$this->assertContains( 'id="test-name"', $html );
		$this->assertContains( 'name="test-key[test-name]"', $html );
		$this->assertContains( 'type="text"', $html );
		$this->assertContains( 'value="my value"', $html );
		$this->assertContains( 'size="20"', $html );
		$this->assertContains( 'maxlength="25"', $html );
		$this->assertContains( 'This is the after text', $html );

		$this->assertNotContains( 'type="number"', $html );
		$this->assertNotContains( 'min="10"', $html );
		$this->assertNotContains( 'max="20"', $html );
		$this->assertNotContains( 'step="2"', $html );

		$args['type'] = 'number';
		$args['min'] = 10;
		$args['max'] = 20;
		$args['step'] = 2;

		ob_start();
		$settings->settings_input( $args );
		$html = ob_get_clean();

		$this->assertContains( 'min="10"', $html );
		$this->assertContains( 'max="20"', $html );
		$this->assertContains( 'step="2"', $html );

		$this->assertNotContains( 'type="text"', $html );

		delete_option( 'test-key' );
	}
}
