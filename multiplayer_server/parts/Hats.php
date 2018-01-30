<?php

class Hats {

	const NONE = 1;
	const EXP = 2;
	const KONG = 3;
	const PROPELLER = 4;
	const COWBOY = 5;
	const CROWN = 6;
	const SANTA = 7;
	const PARTY = 8;
	const TOP_HAT = 9;
	const JUMP_START = 10;
	const MOON = 11;
	const THIEF = 12;
	const JIGG = 13;
	const ARTIFACT = 14;
	
	
	public static function id_to_str( $id ) {
		$str = 'Unknown';
		
		if( $id == Hats::NONE ) {
			$str = 'None';
		}
		else if( $id == Hats::EXP ) {
			$str = 'EXP';
		}
		else if( $id == Hats::KONG ) {
			$str = 'Kong';
		}
		else if( $id == Hats::PROPELLER ) {
			$str = 'Propeller';
		}
		else if( $id == Hats::COWBOY ) {
			$str = 'Cowboy'; 
		}
		else if( $id == Hats::CROWN ) {
			$str = 'Crown';
		}
		else if( $id == Hats::SANTA ) {
			$str = 'Santa';
		}
		else if( $id == Hats::PARTY ) {
			$str = 'Party';
		}
		else if( $id == Hats::TOP_HAT ) {
			$str = 'Top Hat';
		}
		else if( $id == Hats::JUMP_START ) {
			$str = 'Jump Start';
		}
		else if( $id == Hats::MOON ) {
			$str = 'Moon';
		}
		else if( $id == Hats::THIEF ) {
			$str = 'Thief';
		}
		else if( $id == Hats::JIGG ) {
			$str = 'Jigg';
		}
		else if( $id == Hats::ARTIFACT ) {
			$str = 'Artifact';
		}
		
		return( $str );
	}
	
	
	public static function str_to_id( $str ) {
		$str = strtolower($str);
		$id = 1;
		
		if( $str == 'none' || $str == 'n' || $str == '' || $str == Hats::NONE ) {
			$id = Hats::NONE;
		}
		else if( $str == 'exp' || $str == 'experience' || $str == 'e' || $str == Hats::EXP ) {
			$id = Hats::EXP;
		}
		else if ($str == 'kong' || $str == 'kongregate' || $str == 'k' || $str == Hats::KONG ) {
			$id = Hats::KONG;
		}
		else if( $str == 'propeller' || $str == 'prop' || $str == 'pr' || $str == Hats::PROPELLER ) {
			$id = Hats::PROPELLER;
		}
		else if( $str == 'cowboy' || $str == 'gallon' || $str == 'co' || $str == Hats::COWBOY ) {
			$id = Hats::COWBOY;
		}
		else if( $str == 'crown' || $str == 'cr' || $str == Hats::CROWN ) {
			$id = Hats::CROWN;
		}
		else if( $str == 'santa' || $str == 's' || $str == Hats::SANTA ) {
			$id = Hats::SANTA;
		}
		else if( $str == 'party' || $str == 'p' || $str == Hats::PARTY ) {
			$id = Hats::PARTY;
		}
		else if( $str == 'top' || $str == 'top_hat' || $str == 'tophat' || $str == Hats::TOP_HAT ) {
			$id = Hats::TOP_HAT;
		}
		else if( $str == 'start' || $str == 'jump' || $str == 'jumpstart' || $str == 'jump_start' || $str == Hats::JUMP_START ) {
			$id = Hats::JUMP_START;
		}
		else if( $str == 'moon' || $str == 'm' || $str == 'luna' || $str == Hats::MOON ) {
			$id = Hats::MOON;
		}
		else if( $str == 'thief' || $str == 't' || $str == Hats::THIEF ) {
			$id = Hats::THIEF;
		}
		else if( $str == 'jigg' || $str == 'j' || $str == 'jiggmin' || $str == Hats::JIGG ) {
			$id = Hats::JIGG;
		}
		else if( $str == 'artifact' || $str == 'a' || $str == Hats::ARTIFACT ) {
			$id = Hats::ARTIFACT;
		}
		
		return( $id );
	}
	
}

?>
