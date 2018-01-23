<?php

class Prize {
	
	private $type;
	private $id;
	private $name;
	private $desc;
	private $universal;
	
	
	public function __construct( $type, $id, $name='', $desc='', $universal=false ) {
		$this->type = $type;
		$this->id = $id;
		$this->name = $name;
		$this->desc = $desc;
		$this->universal = $universal;
		Prizes::add( $this );
	}
	
	
	public function get_type() {
		return( $this->type );
	}
	
	
	public function get_id() {
		return( $this->id );
	}
	
	
	public function is_universal() {
		return( $this->universal );
	}
	
	
	public function to_obj() {
		$obj = new stdClass();
		$obj->type = $this->type;
		$obj->id = $this->id;
		$obj->name = $this->name;
		$obj->desc = $this->desc;
		$obj->universal = $this->universal;
		return( $obj );
	}
	
	
	public function to_str() {
		$obj = $this->to_obj();
		$str = json_encode( $obj );
		return $str;
	}
	
}
	
?>
