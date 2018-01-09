<?php
/**
 * Class Helper Functions
 *
 * @package Hogan
 */

/**
 * Test helper functions.
 */
class Test_Helper_Functions extends WP_UnitTestCase {

	/**
	 * Classnames
	 *
	 * Tests for helper function `hogan_classnames`.
	 */
	public function test_hogan_classnames() {
		$this->assertEquals( hogan_classnames( [
			'a' => true,
			'b' => false,
			'c' => 0,
			'd' => null,
			'e' => 1,
		] ), 'a e', 'Keeps array keys with truthy values' );

		$this->assertEquals( hogan_classnames(
			'a',
			0,
			null,
			true,
			'b'
		), 'a 1 b', 'Joins arrays of class names and ignore falsy values' );

		$this->assertEquals( hogan_classnames(
			[ 'a' => true ],
			'b',
			0
		), 'a b', 'Supports heterogenous arguments' );

		$this->assertEquals( hogan_classnames( '', 'b', ' ' ), 'b', 'Should be trimmed' );
		$this->assertEquals( hogan_classnames(), '', 'Returns an empty string for an empty configuration' );
		$this->assertEquals( hogan_classnames( [ 'a', 'b' ] ), 'a b', 'Supports an array of class names' );
		$this->assertEquals( hogan_classnames( [ 'a', 'b' ], 'c' ), 'a b c', 'Joins array arguments with string arguments' );
		$this->assertEquals( hogan_classnames( 'c', [ 'a', 'b' ] ), 'c a b', 'Joins array arguments with string arguments' );
		$this->assertEquals( hogan_classnames( [ 'a', 'b' ], [ 'c', 'd' ] ), 'a b c d', 'Handles multiple array arguments' );

		$this->assertEquals( hogan_classnames( [
			'a',
			0,
			null,
			false,
			true,
			'b',
		] ), 'a 1 b', 'Handles arrays that include falsy and true values' );

		$this->assertEquals( hogan_classnames( [ 'a', [ 'b', 'c' ] ] ), 'a b c', 'Handles arrays that include arrays' );

		$this->assertEquals( hogan_classnames( [
			'a',
			[
				'b' => true,
				'c' => false,
			],
		] ), 'a b', 'Handles arrays that include array with keys' );

		$this->assertEquals( hogan_classnames( [
			'a',
			[
				'b',
				[
					'c',
					[
						'd' => true,
					],
				],
			],
		] ), 'a b c d', 'Handles deep array recursion' );

		$this->assertEquals( hogan_classnames( [ 'a', [] ] ), 'a', 'Handles arrays that are empty' );
		$this->assertEquals( hogan_classnames( [ 'a', [ [] ] ] ), 'a', 'Handles nested arrays that have empty nested arrays' );
	}
}
