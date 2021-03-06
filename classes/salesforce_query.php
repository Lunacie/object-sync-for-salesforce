<?php
/**
 * Class file for the Object_Sync_Sf_Select_Query class.
 *
 * @file
 */

if ( ! class_exists( 'Object_Sync_Salesforce' ) ) {
	die();
}

/**
 * Class representing a Salesforce SELECT SOQL query.
 */
class Object_Sync_Sf_Salesforce_Select_Query {

	public $fields = array();
	public $order = array();
	public $object_type;
	public $limit;
	public $offset;
	public $conditions = array();

	/**
	* Constructor which sets the query object type.
	*
	* @param string $object_type
	*   Salesforce object type to query.
	*/
	public function __construct( $object_type = '' ) {
		$this->object_type = $object_type;
	}

	/**
	* Add a condition to the query.
	*
	* @param string $field
	*   Field name.
	* @param mixed $value
	*   Condition value. If an array, it will be split into quote enclosed
	*   strings separated by commas inside of parenthesis. Note that the caller
	*   must enclose the value in quotes as needed by the SF API.
	* @param string $operator
	*   Conditional operator. One of '=', '!=', '<', '>', 'LIKE, 'IN', 'NOT IN'.
	*/
	public function add_condition( $field, $value, $operator = '=' ) {
		if ( is_array( $value ) ) {
			$value = "('" . implode( "','", $value ) . "')";

			// Set operator to IN if wasn't already changed from the default.
			if ( '=' === $operator ) {
				$operator = 'IN';
			}
		}
		$this->conditions[] = array(
			'field' => $field,
			'operator' => $operator,
			'value' => $value,
		);
	}

	/**
	* Implements PHP's magic __toString().
	*
	* Function to convert the query to a string to pass to the SF API.
	*
	* @return string
	*   SOQL query ready to be executed the SF API.
	*/
	public function __toString() {

		$query = 'SELECT ';
		$query .= implode( ', ', $this->fields );
		$query .= ' FROM ' . $this->object_type;

		if ( count( $this->conditions ) > 0 ) {
			$where = array();
			foreach ( $this->conditions as $condition ) {
				$where[] = implode( ' ', $condition );
			}
			$query .= ' WHERE ' . implode( ' AND ', $where );
		}

		if ( $this->order ) {
			$query .= ' ORDER BY ';
			$fields = array();
			foreach ( $this->order as $field => $direction ) {
				$fields[] = $field . ' ' . $direction;
			}
			$query .= implode( ', ', $fields );
		}

		if ( $this->limit ) {
			$query .= ' LIMIT ' . (int) $this->limit;
		}

		if ( $this->offset ) {
			$query .= ' OFFSET ' . (int) $this->offset;
		}

		return $query;
	}

}
