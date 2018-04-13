<?php

namespace pr2\multi;

class Prize
{

    private $type;
    private $id;
    private $name;
    private $desc;
    private $universal;


    public function __construct($type, $id, $name = '', $desc = '', $universal = false)
    {
        $this->type = $type;
        $this->id = $id;
        $this->name = $name;
        $this->desc = $desc;
        $this->universal = $universal;
        Prizes::add($this);
    }


    public function getType()
    {
        return( $this->type );
    }


    public function getId()
    {
        return( $this->id );
    }


    public function isUniversal()
    {
        return( $this->universal );
    }


    public function toObj()
    {
        $obj = new stdClass();
        $obj->type = $this->type;
        $obj->id = $this->id;
        $obj->name = $this->name;
        $obj->desc = $this->desc;
        $obj->universal = $this->universal;
        return( $obj );
    }


    public function toStr()
    {
        $obj = $this->toObj();
        $str = json_encode($obj);
        return $str;
    }
}
