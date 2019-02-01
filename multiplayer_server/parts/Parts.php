<?php
// phpcs:ignoreFile

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

        return $str;
    }


    public static function strToId($str)
    {
        $str = strtolower($str);
        $id = 1;
        $jump_start = ['start', 'jump', 'jumpstart', 'jump_start', 'js', 10];

        if ($str === 'none' || $str === 'n' || $str === '' || $str === 1) {
            $id = Hats::NONE;
        } elseif ($str === 'exp' || $str === 'experience' || $str === 'e' || $str == 2) {
            $id = Hats::EXP;
        } elseif ($str === 'kong' || $str === 'kongregate' || $str === 'k' || $str == 3) {
            $id = Hats::KONG;
        } elseif ($str === 'propeller' || $str === 'prop' || $str === 'pr' || $str == 4) {
            $id = Hats::PROPELLER;
        } elseif ($str === 'cowboy' || $str === 'gallon' || $str === 'co' || $str == 5) {
            $id = Hats::COWBOY;
        } elseif ($str === 'crown' || $str === 'cr' || $str == 6) {
            $id = Hats::CROWN;
        } elseif ($str === 'santa' || $str === 's' || $str == 7) {
            $id = Hats::SANTA;
        } elseif ($str === 'party' || $str === 'p' || $str == 8) {
            $id = Hats::PARTY;
        } elseif ($str === 'top' || $str === 'top_hat' || $str === 'tophat' || $str == 9) {
            $id = Hats::TOP_HAT;
        } elseif (in_array($str, $jump_start)) {
            $id = Hats::JUMP_START;
        } elseif ($str === 'moon' || $str === 'm' || $str === 'luna' || $str == 11) {
            $id = Hats::MOON;
        } elseif ($str === 'thief' || $str === 't' || $str == 12) {
            $id = Hats::THIEF;
        } elseif ($str === 'jigg' || $str === 'j' || $str === 'jiggmin' || $str == 13) {
            $id = Hats::JIGG;
        } elseif ($str === 'artifact' || $str === 'a' || $str == 14) {
            $id = Hats::ARTIFACT;
        }

        return $id;
    }
}


class Heads
{
    const CLASSIC = 1;
    const TIRED = 2;
    const SMILER = 3;
    const FLOWER = 4;
    const CLASSIC_GIRL = 5;
    const GOOF = 6;
    const DOWNER = 7;
    const BALLOON = 8;
    const WORM = 9;
    const UNICORN = 10;
    const BIRD = 11;
    const SUN = 12;
    const CANDY = 13;
    const INVISIBLE = 14;
    const FOOTBALL_HELMET = 15;
    const BASKETBALL = 16;
    const STICK = 17;
    const CAT = 18;
    const ELEPHANT = 19;
    const ANT = 20;
    const ASTRONAUT = 21;
    const ALIEN = 22;
    const DINO = 23;
    const ARMOR = 24;
    const FAIRY = 25;
    const GINGERBREAD = 26;
    const BUBBLE = 27;
    const KING = 28;
    const QUEEN = 29;
    const SIR = 30;
    const VERY_INVISIBLE = 31;
    const TACO = 32;
    const SLENDER = 33;
    const SANTA = 34;
    const FROST_DJINN = 35;
    const REINDEER = 36;
    const CROCODILE = 37;
    const VALENTINE = 38;
    const BUNNY = 39;
    const GECKO = 40;
    const BAT = 41;
}


class Bodies
{
    const CLASSIC = 1;
    const STRAP = 2;
    const DRESS = 3;
    const PEC = 4;
    const GUT = 5;
    const COLLAR = 6;
    const MISS_PR2 = 7;
    const BELT = 8;
    const SNAKE = 9;
    const BIRD = 10;
    const INVISIBLE = 11;
    const BEE = 12;
    const STICK = 13;
    const CAT = 14;
    const CAR = 15;
    const BEAN = 16;
    const ANT = 17;
    const ASTRONAUT = 18;
    const ALIEN = 19;
    const GALAXY = 20;
    const BUBBLE = 21;
    const DINO = 22;
    const ARMOR = 23;
    const FAIRY = 24;
    const GINGERBREAD = 25;
    const KING = 26;
    const QUEEN = 27;
    const SIR = 28;
    const FRED = 29;
    const VERY_INVISIBLE = 30;
    const TACO = 31;
    const SLENDER = 32;
    const SANTA = 34;
    const FROST_DJINN = 35;
    const REINDEER = 36;
    const CROCODILE = 37;
    const VALENTINE = 38;
    const BUNNY = 39;
    const GECKO = 40;
    const BAT = 41;
}


class Feet
{
    const CLASSIC = 1;
    const HEEL = 2;
    const LOAFER = 3;
    const SOCCER = 4;
    const MAGNET = 5;
    const TINY = 6;
    const SANDAL = 7;
    const BARE = 8;
    const NICE = 9;
    const BIRD = 10;
    const INVISIBLE = 11;
    const STICK = 12;
    const CAT = 13;
    const TIRE = 14;
    const ELEPHANT = 15;
    const ANT = 16;
    const ASTRONAUT = 17;
    const ALIEN = 18;
    const GALAXY = 19;
    const DINO = 20;
    const ARMOR = 21;
    const FAIRY = 22;
    const GINGERBREAD = 23;
    const KING = 24;
    const QUEEN = 25;
    const SIR = 26;
    const VERY_INVISIBLE = 27;
    const BUBBLE = 28;
    const TACO = 29;
    const SLENDER = 30;
    const SANTA = 34;
    const FROST_DJINN = 35;
    const REINDEER = 36;
    const CROCODILE = 37;
    const VALENTINE = 38;
    const BUNNY = 39;
    const GECKO = 40;
    const BAT = 41;
}
