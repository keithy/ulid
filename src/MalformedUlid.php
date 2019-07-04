<?php

namespace Tuupola;

class MalformedUlid extends \Exception
{

    public function __construct($id, $code = 400, Exception $previous = null)
    {

        parent::__construct("Malformed resource id ($id)", $code, $previous);
    }

    public function reportOn($array)
    {
        $array['message'] = $this->getMessage();
        return $array;
    }

    public function traceOn($array)
    {
        $array['trace'] = $this->getTrace()[0];
        return $array;
    }
}
