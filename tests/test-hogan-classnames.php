<?php
/**
 * Class Hogan_Classnames_Tests
 *
 * @package Hogan
 */

/**
 * `hogan_classnames` test cases.
 */
class Hogan_Classnames_Tests extends WP_UnitTestCase {

	/**
	 * Keeps array keys with truthy values
	 */
	public function testKeepsArrayKeysWithTruthy() {
		$this->assertEquals( hogan_classnames( [
			'a' => true,
			'b' => false,
			'c' => 0,
			'd' => null,
			'e' => 1,
		] ), 'a e' );
	}

	/**
	 * Joins arrays of class names and ignore falsy values
	 */
	public function testJoinsArraysOfClassNamesAndIgnoreFalsyValues() {
		$this->assertEquals( hogan_classnames(
			'a',
			0,
			null,
			true,
			'b'
		), 'a 1 b' );
	}

	/**
	 * Supports heterogenous arguments
	 */
	public function testSupportsHeterogenousArguments() {
		$this->assertEquals( hogan_classnames(
			[ 'a' => true ],
			'b',
			0
		), 'a b' );
	}

	/**
	 * Should be trimmed
	 */
	public function testShouldBeTrimmed() {
		$this->assertEquals( hogan_classnames( '', 'b', ' ' ), 'b' );
	}

	/**
	 * Returns an empty string for an empty configuration
	 */
	public function testReturnsAnEmptyStringForAnEmptyConfiguration() {
		$this->assertEquals( hogan_classnames(), '' );
	}

	/**
	 * Supports an array of class names
	 */
	public function testSupportsAnArrayOfClassNames() {
		$this->assertEquals( hogan_classnames( [ 'a', 'b' ] ), 'a b' );
	}

	/**
	 * Joins array arguments with string arguments
	 */
	public function testJoinsArrayArgumentsWithStringArguments() {
		$this->assertEquals( hogan_classnames( [ 'a', 'b' ], 'c' ), 'a b c' );
		$this->assertEquals( hogan_classnames( 'c', [ 'a', 'b' ] ), 'c a b' );
	}

	/**
	 * Handles multiple array arguments
	 */
	public function testHandlesMultipleArrayArguments() {
		$this->assertEquals( hogan_classnames( [ 'a', 'b' ], [ 'c', 'd' ] ), 'a b c d' );
	}

	/**
	 * Handles arrays that include falsy and true values
	 */
	public function testHandlesArraysThatIncludeFalsyAndTrueValues() {
		$this->assertEquals( hogan_classnames( [
			'a',
			0,
			null,
			false,
			true,
			'b',
		] ), 'a 1 b' );
	}

	/**
	 * Handles arrays that include arrays
	 */
	public function testHandlesArraysThatIncludeArrays() {
		$this->assertEquals( hogan_classnames( [ 'a', [ 'b', 'c' ] ] ), 'a b c' );
	}

	/**
	 * Handles arrays that include array with keys
	 */
	public function testHandlesArraysThatIncludeArrayWithKeys() {
		$this->assertEquals( hogan_classnames( [
			'a',
			[
				'b' => true,
				'c' => false,
			],
		] ), 'a b' );
	}

	/**
	 * Handles deep array recursion
	 */
	public function testHandlesDeepArrayRecursion() {
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
		] ), 'a b c d' );
	}

	/**
	 * Handles arrays that are empty
	 */
	public function testHandlesArraysThatAreEmpty() {
		$this->assertEquals( hogan_classnames( [ 'a', [] ] ), 'a' );
	}

	/**
	 * Handles nested arrays that have empty nested arrays
	 */
	public function testHandlesNestedArraysThatHaveEmptyNestedArrays() {
		$this->assertEquals( hogan_classnames( [ 'a', [ [] ] ] ), 'a' );
	}
}
