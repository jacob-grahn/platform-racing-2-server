<?php

class Hat {

	public $id;
	public $num;
	public $color;
	public $color2;
	
	public function __construct( $id, $num, $color, $color2 ){	
		$this->id = $id;
		$this->num = $num;
		$this->color = $color;
		$this->color2 = $color2;
	}
}