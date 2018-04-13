<?php

namespace pr2\multi;

class Hats
{

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


    public static function idToStr($id)
    {
        $str = 'Unknown';

        if ($id == Hats::NONE) {
            $str = 'None';
        } elseif ($id == Hats::EXP) {
            $str = 'EXP';
        } elseif ($id == Hats::KONG) {
            $str = 'Kong';
        } elseif ($id == Hats::PROPELLER) {
            $str = 'Propeller';
        } elseif ($id == Hats::COWBOY) {
            $str = 'Cowboy';
        } elseif ($id == Hats::CROWN) {
            $str = 'Crown';
        } elseif ($id == Hats::SANTA) {
            $str = 'Santa';
        } elseif ($id == Hats::PARTY) {
            $str = 'Party';
        } elseif ($id == Hats::TOP_HAT) {
            $str = 'Top Hat';
        } elseif ($id == Hats::JUMP_START) {
            $str = 'Jump Start';
        } elseif ($id == Hats::MOON) {
            $str = 'Moon';
        } elseif ($id == Hats::THIEF) {
            $str = 'Thief';
        } elseif ($id == Hats::JIGG) {
            $str = 'Jigg';
        } elseif ($id == Hats::ARTIFACT) {
            $str = 'Artifact';
        }

        return( $str );
    }


    public static function strToId($str)
    {
        $str = strtolower($str);
        $id = 1;
        $jump_start = ['start', 'jump', 'jumpstart', 'jump_start', Hats::JUMP_START];

        if ($str == 'none' || $str == 'n' || $str == '' || $str == Hats::NONE) {
            $id = Hats::NONE;
        } elseif ($str == 'exp' || $str == 'experience' || $str == 'e' || $str == Hats::EXP) {
            $id = Hats::EXP;
        } elseif ($str == 'kong' || $str == 'kongregate' || $str == 'k' || $str == Hats::KONG) {
            $id = Hats::KONG;
        } elseif ($str == 'propeller' || $str == 'prop' || $str == 'pr' || $str == Hats::PROPELLER) {
            $id = Hats::PROPELLER;
        } elseif ($str == 'cowboy' || $str == 'gallon' || $str == 'co' || $str == Hats::COWBOY) {
            $id = Hats::COWBOY;
        } elseif ($str == 'crown' || $str == 'cr' || $str == Hats::CROWN) {
            $id = Hats::CROWN;
        } elseif ($str == 'santa' || $str == 's' || $str == Hats::SANTA) {
            $id = Hats::SANTA;
        } elseif ($str == 'party' || $str == 'p' || $str == Hats::PARTY) {
            $id = Hats::PARTY;
        } elseif ($str == 'top' || $str == 'top_hat' || $str == 'tophat' || $str == Hats::TOP_HAT) {
            $id = Hats::TOP_HAT;
        } elseif (array_search($str, $jump_start) !== false) {
            $id = Hats::JUMP_START;
        } elseif ($str == 'moon' || $str == 'm' || $str == 'luna' || $str == Hats::MOON) {
            $id = Hats::MOON;
        } elseif ($str == 'thief' || $str == 't' || $str == Hats::THIEF) {
            $id = Hats::THIEF;
        } elseif ($str == 'jigg' || $str == 'j' || $str == 'jiggmin' || $str == Hats::JIGG) {
            $id = Hats::JIGG;
        } elseif ($str == 'artifact' || $str == 'a' || $str == Hats::ARTIFACT) {
            $id = Hats::ARTIFACT;
        }

        return( $id );
    }
}
